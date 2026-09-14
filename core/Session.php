<?php

class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params(array(
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ));

        session_start();
    }

    public static function set($key, $value)
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::start();
        return (isset($_SESSION) && is_array($_SESSION) && array_key_exists($key, $_SESSION)) ? $_SESSION[$key] : $default;
    }

    public static function has($key)
    {
        self::start();
        return (isset($_SESSION) && is_array($_SESSION) && array_key_exists($key, $_SESSION));
    }

    public static function remove($key)
    {
        self::start();
        if (isset($_SESSION) && is_array($_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    public static function flash($key, $value)
    {
        self::set('_flash_' . $key, $value);
    }

    public static function getFlash($key, $default = null)
    {
        $name = '_flash_' . $key;
        $value = self::get($name, $default);
        self::remove($name);
        return $value;
    }

    public static function regenerate()
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function destroy()
    {
        self::start();
        $_SESSION = array();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
