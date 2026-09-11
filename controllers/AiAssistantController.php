<?php

class AiAssistantController extends Controller
{
    /** Minimum seconds between two questions from the same IP (antibot). */
    private const MIN_INTERVAL_SECONDS = 2;

    private function throttleCheck($db)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if ($ip === '') {
            return null;
        }
        $db->query(
            "CREATE TABLE IF NOT EXISTS ai_ask_throttle (
                ip      VARCHAR(45) NOT NULL,
                last_ts INT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (ip),
                KEY last_ts (last_ts)
            ) ENGINE=InnoDB"
        );
        $now = time();
        $row = $db->fetch('SELECT last_ts FROM ai_ask_throttle WHERE ip = ?', [$ip]);
        if ($row && ($now - (int) $row['last_ts']) < self::MIN_INTERVAL_SECONDS) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error'   => 'أرسلت أكثر من سؤال خلال ثوانٍ. انتظر قليلاً ثم أعد المحاولة.',
            ], JSON_UNESCAPED_UNICODE);
            return false;
        }
        $db->query(
            'INSERT INTO ai_ask_throttle (ip, last_ts) VALUES (?, ?) ON DUPLICATE KEY UPDATE last_ts = VALUES(last_ts)',
            [$ip, $now]
        );
        return true;
    }

    public function ask()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Keep generating even if the member closes/navigates away mid-reply:
        // the answer is logged to ai_conversations and picked up again by the
        // client (GET /ai-assistant/pending) on the next page load.
        ignore_user_abort(true);

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'الطريقة غير مسموحة.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!class_exists('AiChatAssistant') || !AiChatAssistant::enabled()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'المحادث الذكي معطل حالياً من إعدادات المنصة.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Login-only: only registered members may ask the assistant.
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode([
                'success'  => false,
                'auth'     => true,
                'error'    => 'هذه الميزة متاحة للأعضاء المسجلين فقط. سجّل دخولك لتتمكن من سؤال المرشد.',
                'loginUrl' => app_url('login'),
                'registerUrl' => app_url('register')
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        CSRF::validate();

        if (!$this->throttleCheck(Database::getInstance())) {
            return;
        }

        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : null;
        if (!is_array($body)) {
            $body = [];
        }

        $question = trim((string) ($body['question'] ?? ($_POST['question'] ?? '')));
        $history = is_array($body['history'] ?? null) ? $body['history'] : [];
        $pageSlug = trim((string) ($body['page'] ?? ''));
        $pageSlug = preg_replace('/[^a-zA-Z0-9\-\_]/', '', $pageSlug);

        $len = mb_strlen($question, 'UTF-8');
        if ($len < 2 || $len > 500) {
            echo json_encode(['success' => false, 'error' => 'السؤال قصير جداً أو طويل جداً (2-500 حرف).'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);
        $isAdmin = Auth::isAdmin();

        // Per-user budget: daily allowance (per-user override or global) + one-time boosts.
        $db = Database::getInstance();
        $quota = AiChatAssistant::userQuotaSummary($db, $userId);
        $daily  = $quota['daily'];
        $used   = $quota['used'];
        $boost  = $quota['boost'];

        if (!$isAdmin && $daily > 0 && $used >= $daily && $boost <= 0) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => 'استنفدت أسئلتك لهذا اليوم. عُد غداً وقدّمت لك أسئلة جديدة، أو اطلب من الإدارة زيادة حصتك.',
                'quota'   => $quota
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Session is read-only from here on: release the lock so other page
        // loads on the same session don't block while the provider answers.
        session_write_close();

        $result = AiChatAssistant::ask($question, $history, ['page_slug' => $pageSlug]);

        if (!empty($result['success'])) {
            AiChatAssistant::consumeOne($db, $userId, $quota);
            $convId = AiChatAssistant::logConversation($db, $userId, [
                'question'  => $question,
                'answer'    => $result['answer'],
                'provider'  => $result['provider'] ?? '',
                'status'    => 'ok',
                'sources'   => $result['sources'] ?? [],
                'page_slug' => $pageSlug,
            ]);
            $quota['remaining'] = AiChatAssistant::userQuotaSummary($db, $userId)['remaining'];
            echo json_encode([
                'success'  => true,
                'answer'   => $result['answer'],
                'provider' => $result['provider'] ?? '',
                'sources'  => $result['sources'] ?? [],
                'convId'   => $convId,
                'quota'    => $quota,
                'used'     => $quota['used'],
                'limit'    => $daily,
                'boost'    => $boost
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $convId = AiChatAssistant::logConversation($db, $userId, [
            'question'  => $question,
            'status'    => 'error',
            'error'     => $result['error'] ?? 'تعذر الحصول على إجابة.',
            'provider'  => $result['provider'] ?? '',
            'page_slug' => $pageSlug,
        ]);

        echo json_encode([
            'success' => false,
            'error'   => $result['error'] ?? 'تعذر الحصول على إجابة. حاول مجدداً.',
            'attempts'=> $result['attempts'] ?? null,
            'convId'  => $convId,
            'quota'   => $quota,
            'used'    => $used,
            'limit'   => $daily
        ], JSON_UNESCAPED_UNICODE);
    }

    /** Recover a reply that was generated server-side while the member left the page. */
    public function pending()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'الطريقة غير مسموحة.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!class_exists('AiChatAssistant') || !AiChatAssistant::enabled()) {
            echo json_encode(['success' => false, 'pending' => false], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'auth' => true, 'pending' => false], JSON_UNESCAPED_UNICODE);
            return;
        }

        $userId = (int) (Auth::user()['id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode(['success' => false, 'pending' => false], JSON_UNESCAPED_UNICODE);
            return;
        }

        $row = AiChatAssistant::latestConversation(Database::getInstance(), $userId, 900);
        if (!$row) {
            echo json_encode(['success' => false, 'pending' => false], JSON_UNESCAPED_UNICODE);
            return;
        }

        $sources = json_decode((string) ($row['sources'] ?? ''), true);
        if (!is_array($sources)) {
            $sources = [];
        }

        echo json_encode([
            'success'  => true,
            'pending'  => true,
            'convId'   => (int) $row['id'],
            'question' => (string) $row['question'],
            'answer'   => (string) ($row['answer'] ?? ''),
            'status'   => (string) $row['status'],
            'error'    => (string) ($row['error'] ?? ''),
            'sources'  => $sources,
        ], JSON_UNESCAPED_UNICODE);
    }

    /** Persist a member's like/dislike on a past assistant answer. */
    public function reaction()
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'الطريقة غير مسموحة.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!Auth::isLoggedIn()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'سجّل دخولك أولاً.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        CSRF::validate();

        $raw = file_get_contents('php://input');
        $body = $raw ? json_decode($raw, true) : null;
        if (!is_array($body)) {
            $body = [];
        }

        $convId = (int) ($body['conv_id'] ?? 0);
        $value = (string) ($body['value'] ?? '');
        if (!in_array($value, ['like', 'dislike', 'love', ''], true)) {
            $value = '';
        }

        $userId = (int) (Auth::user()['id'] ?? 0);
        $db = Database::getInstance();

        if (!AiChatAssistant::conversationTableReady($db)) {
            echo json_encode(['success' => false, 'error' => 'سجلات المحادثات غير مفعّلة بعد.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $owned = $db->fetch('SELECT id FROM ai_conversations WHERE id = ? AND user_id = ? LIMIT 1', [$convId, $userId]);
        if (!$owned) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'الرسالة غير موجودة.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $db->query(
            'UPDATE ai_conversations SET reaction = ?, reaction_at = NOW() WHERE id = ? AND user_id = ?',
            [$value === '' ? null : $value, $convId, $userId]
        );

        echo json_encode(['success' => true, 'reaction' => $value], JSON_UNESCAPED_UNICODE);
    }
}