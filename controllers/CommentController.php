<?php

class CommentController extends Controller
{
    private function isAjax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function store()
    {
        CSRF::verifyRequest();

        // 1. Check if comments are globally enabled
        if (Settings::get('enable_comments', '1') != '1') {
            if ($this->isAjax()) {
                return $this->json(['ok' => false, 'message' => 'التعليقات معطلة حالياً.'], 403);
            }
            Session::flash('error', 'التعليقات معطلة حالياً.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url()));
            exit;
        }

        $user = Auth::user();

        // 2. Check if guest comments are allowed
        if (!$user && Settings::get('allow_guest_comments', '1') != '1') {
            if ($this->isAjax()) {
                return $this->json(['ok' => false, 'message' => 'يرجى تسجيل الدخول أولاً لإضافة تعليق.'], 401);
            }
            Session::flash('error', 'يرجى تسجيل الدخول أولاً لإضافة تعليق.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url('login')));
            exit;
        }

        $articleId = (int) ($_POST['article_id'] ?? 0);
        $content = trim(Sanitizer::clean($_POST['content'] ?? ''));
        $guestName = trim(Sanitizer::clean($_POST['guest_name'] ?? ''));
        $guestEmail = trim(Sanitizer::clean($_POST['guest_email'] ?? ''));
        $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;

        if ($articleId < 1 || mb_strlen($content) < 2) {
            if ($this->isAjax()) {
                return $this->json(['ok' => false, 'message' => 'يرجى كتابة نص التعليق.'], 422);
            }
            Session::flash('error', 'يرجى كتابة نص التعليق بشكل واضح.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url()));
            exit;
        }

        if (!$user && empty($guestName)) {
            $guestName = 'زائر تقني';
        }

        // 3. Check if moderation is active
        $moderate = Settings::get('moderate_comments', '0') == '1';
        $initialStatus = $moderate ? 'pending' : 'approved';

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO comments (article_id, user_id, guest_name, guest_email, parent_id, content, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $articleId,
            $user ? $user['id'] : null,
            $guestName ?: null,
            $guestEmail ?: null,
            $parentId,
            $content,
            $initialStatus
        ]);

        $id = $db->lastInsertId();

        $successMsg = $moderate 
            ? 'تم استلام تعليقك بنجاح وهو بانتظار المراجعة والموافقة قبل النشر. ⏳'
            : 'تمت إضافة تعليقك ومشاركته بنجاح! 💬';

        if ($this->isAjax()) {
            return $this->json([
                'ok' => true,
                'id' => $id,
                'status' => $initialStatus,
                'message' => $successMsg,
                'comment' => [
                    'id' => $id,
                    'author' => $user ? $user['username'] : $guestName,
                    'content' => nl2br($content),
                    'created_at' => date('Y-m-d H:i')
                ]
            ]);
        }

        Session::flash('success', $successMsg);
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url()) . '#comments-section');
        exit;
    }

    public function update($id)
    {
        CSRF::verifyRequest();
        Auth::requireLogin();
        $db = Database::getInstance();
        $comment = $db->fetch('SELECT * FROM comments WHERE id = :id', [':id' => (int) $id]);
        if (!$comment || ((int) $comment['user_id'] !== (int) Auth::user()['id'] && !Auth::isAdmin())) {
            return $this->json(['ok' => false], 403);
        }
        $stmt = $db->prepare("UPDATE comments SET content = ? WHERE id = ?");
        $stmt->execute([Sanitizer::clean($_POST['content'] ?? ''), (int) $id]);
        return $this->json(['ok' => true]);
    }

    public function delete($id)
    {
        CSRF::verifyRequest();
        Auth::requireLogin();
        $db = Database::getInstance();
        $comment = $db->fetch('SELECT * FROM comments WHERE id = :id', [':id' => (int) $id]);
        if (!$comment || ((int) $comment['user_id'] !== (int) Auth::user()['id'] && !Auth::isAdmin())) {
            return $this->json(['ok' => false], 403);
        }
        $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([(int) $id]);
        return $this->json(['ok' => true]);
    }

    public function reply($parentId)
    {
        CSRF::verifyRequest();
        $db = Database::getInstance();
        $parent = $db->fetch('SELECT article_id FROM comments WHERE id = :id', [':id' => (int) $parentId]);
        if (!$parent) return $this->json(['ok' => false], 404);

        $user = Auth::user();
        $content = Sanitizer::clean($_POST['content'] ?? '');
        $stmt = $db->prepare("
            INSERT INTO comments (article_id, user_id, parent_id, content, status, created_at)
            VALUES (?, ?, ?, ?, 'approved', NOW())
        ");
        $stmt->execute([$parent['article_id'], $user ? $user['id'] : null, (int) $parentId, $content]);
        return $this->json(['ok' => true, 'id' => $db->lastInsertId()]);
    }

    public function like($id)
    {
        CSRF::verifyRequest();
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE comments SET likes_count = likes_count + 1 WHERE id = ?");
        $stmt->execute([(int) $id]);

        $row = $db->fetch('SELECT likes_count FROM comments WHERE id = :id', [':id' => (int) $id]);
        return $this->json(['ok' => true, 'likes' => (int) ($row['likes_count'] ?? 0)]);
    }

    public function report($id)
    {
        CSRF::verifyRequest();
        $db = Database::getInstance();
        $user = Auth::user();
        $reason = Sanitizer::clean($_POST['reason'] ?? 'محتوى غير لائق');
        $stmt = $db->prepare("INSERT INTO reports (user_id, reportable_type, reportable_id, reason, status) VALUES (?, 'comment', ?, ?, 'pending')");
        $stmt->execute([$user ? $user['id'] : null, (int) $id, $reason]);
        return $this->json(['ok' => true, 'message' => 'تم استلام الإبلاغ للمراجعة.']);
    }
}
