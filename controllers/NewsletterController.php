<?php

class NewsletterController extends Controller
{
    public function subscribe()
    {
        CSRF::verifyRequest();
        $email = strtolower(trim($_POST['email'] ?? ''));
        $name = Sanitizer::clean($_POST['name'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($this->isAjax()) {
                http_response_code(422);
                return $this->json(['ok' => false, 'message' => 'البريد الإلكتروني غير صحيح.']);
            }
            Session::flash('error', 'البريد الإلكتروني غير صحيح.');
            $referer = $_SERVER['HTTP_REFERER'] ?? app_url();
            header('Location: ' . $referer);
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO newsletters (email, name, status) 
            VALUES (?, ?, 'active') 
            ON DUPLICATE KEY UPDATE name = VALUES(name), status = 'active', unsubscribed_at = NULL
        ");
        $stmt->execute([$email, $name]);

        if ($this->isAjax()) {
            return $this->json(['ok' => true, 'message' => 'تم الاشتراك في النشرة البريدية بنجاح.']);
        }

        Session::flash('success', 'شكراً لاشتراكك في النشرة البريدية بنجاح! 📬');
        $referer = $_SERVER['HTTP_REFERER'] ?? app_url();
        header('Location: ' . $referer);
        exit;
    }

    public function unsubscribe($token)
    {
        $email = base64_decode($token, true);
        if ($email) {
            $db = Database::getInstance();
            $stmt = $db->prepare("UPDATE newsletters SET status = 'unsubscribed', unsubscribed_at = CURRENT_TIMESTAMP WHERE email = ?");
            $stmt->execute([$email]);
        }
        Session::flash('success', 'تم إلغاء الاشتراك في النشرة البريدية.');
        header('Location: ' . app_url());
        exit;
    }

    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
