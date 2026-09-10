<?php

class AiAssistantController extends Controller
{
    public function ask()
    {
        header('Content-Type: application/json; charset=utf-8');

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
                'error'    => 'سجّل دخولك إلى حسابك لتتمكن من سؤال مرشد عصب التقنية.',
                'loginUrl' => app_url('login')
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        CSRF::validate();

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

        $limit = AiChatAssistant::freeLimit();
        $user = Auth::user();
        $userId = (int) ($user['id'] ?? 0);
        $isAdmin = Auth::isAdmin();

        // Per-user DAILY budget (reset at the site's midnight).
        $db = Database::getInstance();
        $quota = AiChatAssistant::quotaUsedToday($db, $userId);
        $used = $quota['used'];

        if (!$isAdmin && $limit > 0 && $used >= $limit) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => 'استنفدت أسئلتك الثلاثة لهذا اليوم. عُد غداً وقدّمت لك 3 أسئلة جديدة.',
                'limit'   => $limit,
                'used'    => $used
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $result = AiChatAssistant::ask($question, $history, ['page_slug' => $pageSlug]);

        if (!empty($result['success'])) {
            AiChatAssistant::bumpQuota($db, $userId);
            echo json_encode([
                'success'  => true,
                'answer'   => $result['answer'],
                'provider' => $result['provider'] ?? '',
                'sources'  => $result['sources'] ?? [],
                'used'     => $used + 1,
                'limit'    => $limit,
                'resetAt'  => AiChatAssistant::todaySiteDate()
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode([
            'success' => false,
            'error'   => $result['error'] ?? 'تعذر الحصول على إجابة. حاول مجدداً.',
            'attempts'=> $result['attempts'] ?? null,
            'used'    => $used,
            'limit'   => $limit
        ], JSON_UNESCAPED_UNICODE);
    }
}