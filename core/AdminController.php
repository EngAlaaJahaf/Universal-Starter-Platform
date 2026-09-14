<?php

class AdminController extends Controller
{
    protected function guardAdmin()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    /**
     * Strict gate (SH-06): system-level admin actions — user management,
     * DB backups, API-key management, cron control. Editors (content team)
     * are intentionally locked out of these.
     */
    protected function guardSuperAdmin()
    {
        Auth::requireLogin();
        if (!Auth::isSuperAdmin()) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    protected function postGuard()
    {
        $this->guardAdmin();
        CSRF::verifyRequest();
    }

    protected function postGuardSystemAdmin()
    {
        $this->guardSuperAdmin();
        CSRF::verifyRequest();
    }

    /**
     * Granular RBAC gate (SH-11): layered Auth::can() — super-admin always,
     * else roles.permissions JSON grant, else the canonical Acl::allows()
     * matrix. Denials are audited (acl_denied) before a 403.
     */
    protected function requirePermission($permission)
    {
        Auth::requireLogin();
        if (!Auth::can($permission)) {
            $this->logAclDenied($permission);
            http_response_code(403);
            exit('Forbidden');
        }
    }

    protected function requirePermissionPost($permission)
    {
        $this->requirePermission($permission);
        CSRF::verifyRequest();
    }

    /**
     * Deny-audit (SH-11 "سجل صلاحيات"): records who was denied which
     * permission. Self-contained (does not reuse ActivityLogger, which
     * currently stores a null user_id), fails silently on DB errors so a
     * denial can never break the request flow.
     */
    protected function logAclDenied($permission)
    {
        try {
            $u   = Auth::user();
            $db  = new Database();
            $db->query(
                'INSERT INTO activity_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
                 VALUES (:uid, :action, :entity, NULL, NULL, :nv, :ip, :ua)',
                array(
                    ':uid'    => isset($u['id']) ? (int)$u['id'] : null,
                    ':action' => 'acl_denied',
                    ':entity' => 'permission',
                    ':nv'     => json_encode(array('permission' => $permission, 'role' => $u['role_name'] ?? null), JSON_UNESCAPED_UNICODE),
                    ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null,
                    ':ua'     => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
                )
            );
        } catch (Throwable $e) {}
    }

    protected function renderAdmin($view, array $data = array())
    {
        $this->view($view, $data);
    }

    protected function view($view, array $data = array())
    {
        $this->guardAdmin();
        $viewFile = dirname(__DIR__) . '/views/' . ltrim($view, '/') . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException('Admin view not found: ' . $view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Only skip layout if explicitly requested via $noLayout = true
        if (!empty($noLayout)) {
            echo $content;
            return;
        }

        $layoutFile = dirname(__DIR__) . '/views/layouts/admin.php';
        require $layoutFile;
    }

    protected function audit($action, $entity, $entityId = null, $oldValues = null, $newValues = null)
    {
        if (class_exists('ActivityLogger')) {
            ActivityLogger::log($action, $entity, $entityId, $oldValues, $newValues);
        }
    }
}
