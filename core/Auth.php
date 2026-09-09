<?php

class Auth
{
    public static function login($email, $password)
    {
        $db = new Database();
        $user = $db->fetch('SELECT u.*, r.name AS role_name, r.permissions FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.email = :email LIMIT 1', array(':email' => strtolower(trim($email))));

        if (!$user || !password_verify($password, $user['password_hash']) || ($user['status'] ?? '') !== 'active') {
            return false;
        }

        Session::regenerate();
        Session::set('auth_user_id', (int) $user['id']);
        $db->query('UPDATE users SET last_login_at = CURRENT_TIMESTAMP, login_count = login_count + 1 WHERE id = :id', array(':id' => $user['id']));
        return $user;
    }

    public static function register(array $data)
    {
        $db = new Database();
        $role = $db->fetch('SELECT id FROM roles WHERE is_default = 1 OR name = :name ORDER BY is_default DESC LIMIT 1', array(':name' => 'reader'));
        $roleId = $role ? $role['id'] : null;
        $status = !empty($data['status']) ? $data['status'] : 'active';

        $db->query('INSERT INTO users (username, email, password_hash, role_id, status, preferred_language, theme_preference) VALUES (:username, :email, :password_hash, :role_id, :status, :preferred_language, :theme_preference)', array(
            ':username' => trim($data['username']),
            ':email' => strtolower(trim($data['email'])),
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role_id' => $roleId,
            ':status' => $status,
            ':preferred_language' => $data['preferred_language'] ?? 'ar',
            ':theme_preference' => 'auto',
        ));

        return $db->lastInsertId();
    }

    public static function logout()
    {
        Session::destroy();
    }

    public static function user()
    {
        $id = Session::get('auth_user_id');
        if (!$id) {
            return null;
        }

        static $user;
        if ($user === null) {
            $db = new Database();
            $user = $db->fetch('SELECT u.*, r.name AS role_name, r.permissions FROM users u LEFT JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1', array(':id' => $id));
        }
        return $user;
    }

    public static function isLoggedIn()
    {
        return self::user() !== null;
    }

    public static function isAdmin()
    {
        $user = self::user();
        return $user && in_array($user['role_name'], array('admin', 'editor'), true);
    }

    public static function hasPermission($permission)
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if ($user['role_name'] === 'admin') {
            return true;
        }
        $permissions = json_decode($user['permissions'] ?? '{}', true);
        return !empty($permissions[$permission]);
    }

    public static function check()
    {
        return self::isLoggedIn();
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode([
                    'success'       => false,
                    'error'         => 'انتهت جلستك، يرجى تسجيل الدخول إلى لوحة التحكم مجدداً.',
                    'require_login' => true,
                    'login_url'     => app_url('login')
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            Session::flash('error', 'يرجى تسجيل الدخول أولاً.');
            header('Location: ' . app_url('login'));
            exit;
        }
    }
}
