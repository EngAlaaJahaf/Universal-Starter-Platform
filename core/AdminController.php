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
