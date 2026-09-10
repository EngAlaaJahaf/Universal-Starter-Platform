<?php

/**
 * Admin: «مرشد عصب التقنية» conversation log + per-member quota management.
 * - index():       per-user summary (messages, errors, quota) + global stats.
 * - conversation(): full chat transcript for one member.
 * - reset():       zero a member's used-counter (today).
 * - boost():       grant one-time extra messages, OR set a permanent daily override.
 */
class AiLogsController extends AdminController
{
    public function index()
    {
        $db = Database::getInstance();
        $userId = (int) ($_GET['user_id'] ?? 0);
        $search = trim((string) ($_GET['q'] ?? ''));

        $quotaReady = AiChatAssistant::quotaColumnsReady($db);
        $tableReady = AiChatAssistant::conversationTableReady($db);

        // Global stats (fail gracefully when the table is still missing).
        $stats = ['total' => 0, 'ok' => 0, 'error' => 0, 'users' => 0];
        $missingTable = !$tableReady;
        if ($tableReady) {
            try {
                $r = $db->fetch("SELECT COUNT(*) AS total,
                                    COALESCE(SUM(status = 'ok'), 0) AS ok,
                                    COALESCE(SUM(status = 'error'), 0) AS err,
                                    COUNT(DISTINCT user_id) AS users
                                 FROM ai_conversations");
                $stats = [
                    'total' => (int) ($r['total'] ?? 0),
                    'ok'    => (int) ($r['ok'] ?? 0),
                    'error' => (int) ($r['err'] ?? 0),
                    'users' => (int) ($r['users'] ?? 0),
                ];
            } catch (Throwable $e) {
                $missingTable = true;
            }
        }

        // Per-user rows (even users with zero conversations, for quota mgmt).
        $users = [];
        if ($quotaReady) {
            $where = '1=1';
            $params = [];
            if ($userId > 0) {
                $where .= ' AND u.id = ?';
                $params[] = $userId;
            } elseif ($search !== '') {
                $where .= ' AND (u.username LIKE ? OR u.email LIKE ?)';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            $limit = $userId > 0 ? 1 : 100;

            try {
                if ($tableReady) {
                    $users = $db->fetchAll(
                        "SELECT u.id, u.username, u.email, u.status,
                                u.ai_quota_date, u.ai_quota_used, u.ai_quota_boost, u.ai_quota_daily,
                                COUNT(c.id) AS total_msgs,
                                COALESCE(SUM(c.status = 'error'), 0) AS err_msgs,
                                MAX(c.created_at) AS last_at
                         FROM users u
                         LEFT JOIN ai_conversations c ON c.user_id = u.id
                         WHERE {$where}
                         GROUP BY u.id
                         ORDER BY (last_at IS NULL), last_at DESC, u.id DESC
                         LIMIT {$limit}",
                        $params
                    );
                } else {
                    $users = $db->fetchAll(
                        "SELECT u.id, u.username, u.email, u.status,
                                u.ai_quota_date, u.ai_quota_used, u.ai_quota_boost, u.ai_quota_daily,
                                0 AS total_msgs, 0 AS err_msgs, NULL AS last_at
                         FROM users u
                         WHERE {$where}
                         ORDER BY u.id DESC
                         LIMIT {$limit}",
                        $params
                    );
                }
            } catch (Throwable $e) {
                $users = [];
            }
        }

        if (!is_array($users)) {
            $users = [];
        }

        $this->renderAdmin('admin/ai_logs/index', [
            'stats'       => $stats,
            'users'       => $users,
            'quotaReady'  => $quotaReady,
            'tableReady'  => $tableReady,
            'missingTable'=> $missingTable,
            'userId'      => $userId,
            'search'      => $search,
        ]);
    }

    public function conversation()
    {
        $db = Database::getInstance();
        $userId = (int) ($_GET['user_id'] ?? 0);
        $anchor = (int) ($_GET['anchor'] ?? 0);

        $user = $db->fetch('SELECT id, username, email FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            header('Location: ' . app_url('admin/ai-logs'));
            exit;
        }

        $rows = [];
        if (AiChatAssistant::conversationTableReady($db)) {
            $rows = $db->fetchAll(
                'SELECT * FROM ai_conversations WHERE user_id = ? ORDER BY id ASC',
                [$userId]
            ) ?: [];
        }

        // Decorate each exchange.
        foreach ($rows as &$row) {
            $row['sources_list'] = [];
            $raw = (string) ($row['sources'] ?? '');
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $row['sources_list'] = $decoded;
                }
            }
        }
        unset($row);

        $this->renderAdmin('admin/ai_logs/conversation', [
            'user'  => $user,
            'rows'  => $rows,
            'anchor'=> $anchor,
        ]);
    }

    public function reset()
    {
        $this->guardAdmin();
        CSRF::validate();

        $db = Database::getInstance();
        $userId = (int) ($_POST['user_id'] ?? 0);

        $user = $db->fetch('SELECT id, username FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            header('Location: ' . app_url('admin/ai-logs'));
            exit;
        }

        $today = AiChatAssistant::todaySiteDate();
        AiChatAssistant::adminResetQuota($db, $userId);
        if (class_exists('ActivityLogger')) {
            ActivityLogger::log('update', 'ai_quota', $userId, "تصفير عداد أسئلة المرشد للمستخدم: {$user['username']} ({$today})");
        }
        Session::flash('success', 'تم تصفير عداد أسئلة المرشد لهذا المستخدم.');
        header('Location: ' . app_url('admin/ai-logs?user_id=' . $userId));
        exit;
    }

    public function boost()
    {
        $this->guardAdmin();
        CSRF::validate();

        $db = Database::getInstance();
        $userId = (int) ($_POST['user_id'] ?? 0);
        $amount = (int) ($_POST['amount'] ?? 0);
        $kind = ($_POST['kind'] ?? 'extra') === 'daily' ? 'daily' : 'extra';

        $user = $db->fetch('SELECT id, username FROM users WHERE id = ?', [$userId]);
        if (!$user) {
            Session::flash('error', 'المستخدم غير موجود.');
            header('Location: ' . app_url('admin/ai-logs'));
            exit;
        }

        if ($amount < 1 || $amount > 1000) {
            Session::flash('error', 'أدخل عدداً صحيحاً بين 1 و1000.');
            header('Location: ' . app_url('admin/ai-logs?user_id=' . $userId));
            exit;
        }

        if ($kind === 'extra') {
            AiChatAssistant::adminBoost($db, $userId, $amount);
            ActivityLogger::log('update', 'ai_quota', $userId, "منح {$amount} رسالة إضافية مؤقتة للمستخدم: {$user['username']}");
            Session::flash('success', "تمت إضافة {$amount} رسالة إضافية مؤقتة (تُستهلك مرة واحدة).");
        } else {
            AiChatAssistant::adminSetDaily($db, $userId, $amount);
            ActivityLogger::log('update', 'ai_quota', $userId, "تحديد الحصة اليومية الدائمة لـ {$user['username']} إلى {$amount}");
            Session::flash('success', "تم تحديد الحصة اليومية الدائمة للمستخدم إلى {$amount} سؤالاً يومياً.");
        }

        header('Location: ' . app_url('admin/ai-logs?user_id=' . $userId));
        exit;
    }
}