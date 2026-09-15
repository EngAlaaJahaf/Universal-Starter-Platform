<?php

class Auth
{
    /**
     * Authenticate user by username OR email
     */
    public static function login($identity, $password)
    {
        $db = new Database();
        $identity = trim((string)$identity);
        
        $user = $db->fetch(
            'SELECT u.*, r.name AS role_name, r.permissions 
             FROM users u 
             LEFT JOIN roles r ON r.id = u.role_id 
             WHERE (LOWER(u.email) = :identity_email OR LOWER(u.username) = :identity_user) 
             LIMIT 1',
            [
                ':identity_email' => strtolower($identity),
                ':identity_user'  => strtolower($identity)
            ]
        );

        if (!$user) {
            return false;
        }

        $passHash = $user['password'] ?? ($user['password_hash'] ?? '');
        if (!password_verify($password, $passHash) || ($user['status'] ?? '') !== 'active') {
            return false;
        }

        Session::regenerate();
        Session::set('auth_user_id', (int) $user['id']);

        // Client IP: honour X-Forwarded-For / CF-Connecting-IP ONLY when the
        // app is explicitly deployed behind a trusted proxy/CDN (SH-06). On
        // raw shared hosting those headers are client-controlled and MUST NOT
        // be trusted for audit logging.
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $trustProxy = defined('TRUST_PROXY') && TRUST_PROXY === true;
        if ($trustProxy) {
            $cf        = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
            $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
            if ($cf !== '') {
                $clientIp = $cf;
            } elseif ($forwarded !== '') {
                $clientIp = trim(explode(',', $forwarded)[0]);
            }
        }

        try {
            $db->query(
                'UPDATE users SET last_login_at = NOW(), last_login_ip = :ip WHERE id = :id',
                [':ip' => $clientIp, ':id' => (int)$user['id']]
            );
        } catch (Throwable $e) {}

        return $user;
    }

    /**
     * Register a new user and automatically log them in
     */
    public static function register(array $data)
    {
        $db = new Database();
        
        $role = $db->fetch("SELECT id FROM roles WHERE name IN ('user', 'member', 'reader') ORDER BY id DESC LIMIT 1");
        $roleId = $role ? (int)$role['id'] : 3;
        $status = !empty($data['status']) ? $data['status'] : 'active';
        $hash   = password_hash($data['password'], PASSWORD_DEFAULT);

        $db->query(
            'INSERT INTO users (username, email, password, password_hash, role_id, status, full_name, created_at) 
             VALUES (:username, :email, :password, :password_hash, :role_id, :status, :full_name, NOW())',
            [
                ':username'      => trim($data['username']),
                ':email'         => strtolower(trim($data['email'])),
                ':password'      => $hash,
                ':password_hash' => $hash,
                ':role_id'       => $roleId,
                ':status'        => $status,
                ':full_name'     => trim($data['full_name'] ?? ($data['username'] ?? ''))
            ]
        );

        $userId = (int)$db->lastInsertId();

        if ($userId > 0) {
            Session::regenerate();
            Session::set('auth_user_id', $userId);

            $user = $db->fetch(
                'SELECT u.*, r.name AS role_name, r.permissions 
                 FROM users u 
                 LEFT JOIN roles r ON r.id = u.role_id 
                 WHERE u.id = :id LIMIT 1',
                [':id' => $userId]
            );
            return $user;
        }

        return false;
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
        if ($user === null || ($user['id'] ?? null) !== $id) {
            $db = new Database();
            $user = $db->fetch(
                'SELECT u.*, r.name AS role_name, r.permissions 
                 FROM users u 
                 LEFT JOIN roles r ON r.id = u.role_id 
                 WHERE u.id = :id LIMIT 1',
                [':id' => $id]
            );
        }
        return $user;
    }

    public static function isLoggedIn()
    {
        return self::user() !== null;
    }

    /**
     * Lenient gate for the CMS admin panel: grants both admins AND editors
     * (content team) access. Kept as-is so existing editor workflows keep
     * working. For system-level actions use isSuperAdmin() instead.
     */
    public static function isAdmin()
    {
        $user = self::user();
        return $user && in_array($user['role_name'], ['admin', 'editor'], true);
    }

    /**
     * Strict gate (SH-06): ONLY the full 'admin' role. Use for system-level
     * actions — cron execution, security/global settings, user management —
     * where an editor must not be allowed.
     */
    public static function isSuperAdmin()
    {
        $user = self::user();
        return $user && $user['role_name'] === 'admin';
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

    /**
     * Layered ACL check (SH-11): super-admin short-circuit, then explicit
     * per-user grants from roles.permissions JSON, then the canonical
     * Acl::allows() matrix. Used by AdminController::requirePermission().
     */
    public static function can($permission)
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        if (($user['role_name'] ?? '') === Acl::SUPER_ROLE) {
            return true;
        }
        if (self::hasPermission($permission)) {
            return true;
        }
        return Acl::allows($user['role_name'] ?? '', $permission);
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
                    'success' => false,
                    'error'   => 'انتهت جلستك، يرجى تسجيل الدخول مجدداً.',
                    'code'    => 'AUTH_REQUIRED'
                ]);
                exit;
            }

            Session::flash('error', 'يرجى تسجيل الدخول أولاً للوصول إلى هذه الصفحة.');
            header('Location: ' . app_url('login'), true, 302);
            exit;
        }
    }
}
