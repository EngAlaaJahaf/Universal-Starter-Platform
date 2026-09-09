<?php

class CSRF
{
    public static function generateToken()
    {
        Session::start();
        if (!Session::has('_csrf_token')) {
            Session::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('_csrf_token');
    }

    public static function token()
    {
        return self::generateToken();
    }

    public static function getToken()
    {
        return self::generateToken();
    }

    public static function generate()
    {
        return self::generateToken();
    }

    public static function field()
    {
        return self::getField();
    }

    public static function getField()
    {
        $token = htmlspecialchars(self::generateToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf_token" value="' . $token . '"><input type="hidden" name="_csrf" value="' . $token . '">';
    }

    public static function verify($token)
    {
        Session::start();
        $stored = Session::get('_csrf_token');
        if (!is_string($token) || !is_string($stored) || empty($token)) {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function validate($token = null)
    {
        Session::start();
        if (empty($token)) {
            $token = $_POST['_csrf_token'] ?? $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        }
        if (!self::verify($token)) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(419);
                echo json_encode([
                    'success' => false,
                    'error'   => 'انتهت صلاحية جلسة الأمان (CSRF Token). يرجى إعادة تحميل الصفحة أو حفظ المقال كمسودة.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            http_response_code(419);
            exit('Invalid CSRF token.');
        }
        return true;
    }

    public static function verifyRequest()
    {
        self::validate();
    }
}
