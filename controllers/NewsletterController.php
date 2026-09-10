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

        $this->sendWelcomeEmail($email, $name);

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

    private function sendWelcomeEmail($email, $name = '')
    {
        try {
            $welcomeEnabled = Settings::get('newsletter_welcome_enabled', '1');
            if ($welcomeEnabled === false || $welcomeEnabled === '0' || $welcomeEnabled === 0 || $welcomeEnabled === '') {
                return;
            }
            $subject = (string) Settings::get('newsletter_welcome_subject', 'مرحباً بك في نشرة عصب التقنية 🎉');
            $rawBody = (string) Settings::get(
                'newsletter_welcome_body',
                "شكراً لاشتراكك في نشرة عصب التقنية البريدية 🌟\n\nستصلك أهم أخبار التقنية والذكاء الاصطناعي والهواتف والأمن السيبراني مباشرة إلى بريدك."
            );

            $siteName = (string) Settings::get('site_name', 'عصب التقنية');
            $loginUrl = app_url('login');
            $unsubscribeUrl = app_url('newsletter/unsubscribe/' . base64_encode($email));

            if ($name !== '') {
                $rawBody .= "\n\n" . ('أهلاً بك يا ' . $name);
            }

            $text = str_replace(["\r\n", "\r"], ["\n", "\n"], trim($rawBody));
            $plain = nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));

            $html = '<div dir="rtl" style="font-family:Tahoma,Arial,sans-serif;max-width:600px;margin:auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden">'
                . '<div style="background:linear-gradient(135deg,#101828,#1e293b);padding:24px;text-align:center">'
                . '<h1 style="color:#00f2fe;margin:0;font-size:22px">' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '</h1>'
                . '</div>'
                . '<div style="padding:28px;color:#0f172a;font-size:15px;line-height:1.8">'
                . $plain
                . '<p style="margin-top:24px;padding-top:16px;border-top:1px solid #e2e8f0;color:#64748b;font-size:13px">'
                . 'للإلغاء في أي وقت: <a href="' . htmlspecialchars($unsubscribeUrl, ENT_QUOTES, 'UTF-8') . '">إلغاء الاشتراك</a>'
                . '</p></div></div>';

            Mailer::send($email, $subject, $html, $text);
        } catch (Throwable $e) {
            error_log('Newsletter welcome email failed: ' . $e->getMessage());
        }
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
