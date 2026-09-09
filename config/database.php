<?php
/**
 * Database configuration.
 * Configured for local development and shared hosting environments.
 *
 * Priority order for every value:
 *   1. config/hosting.php  (live server override — git-ignored, upload once manually)
 *   2. Real environment variables (getenv)
 *   3. Local development defaults below
 */

$__hostingOverride = __DIR__ . '/hosting.php';
if (is_file($__hostingOverride)) {
    require_once $__hostingOverride;
}
unset($__hostingOverride);

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'tech_news_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
}
if (!defined('CRON_SECRET')) {
    $cronSecretEnv = getenv('CRON_SECRET');
    define('CRON_SECRET', ($cronSecretEnv !== false && $cronSecretEnv !== '') ? $cronSecretEnv : 'cron_tnp_2026_secure_key');
}
if (!defined('BREVO_API_KEY')) {
    define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
}

// Dynamic Base Application URL Detection
if (!function_exists('detect_base_url')) {
    function detect_base_url()
    {
        if ($envUrl = getenv('BASE_URL')) {
            return rtrim($envUrl, '/') . '/';
        }
        if (!empty($_SERVER['HTTP_HOST'])) {
            $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
            $scheme = $isHttps ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];

            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $subDir = '';
            if (!empty($scriptName) && basename($scriptName) === 'index.php') {
                $dir = str_replace('\\', '/', dirname($scriptName));
                if ($dir !== '/' && $dir !== '.' && $dir !== '') {
                    $subDir = trim($dir, '/') . '/';
                }
            }

            return $scheme . $host . '/' . $subDir;
        }
        return 'http://127.0.0.1:8000/';
    }
}

if (!defined('BASE_URL')) {
    define('BASE_URL', detect_base_url());
}

function app_url($path = '')
{
    $path = trim((string) $path);
    $baseUrl = function_exists('detect_base_url') ? detect_base_url() : BASE_URL;

    if ($path === '') {
        return $baseUrl;
    }
    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
        return $path;
    }
    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

// SMTP and Web Push settings
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: '');
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');
}
if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls');
}
if (!defined('MAIL_FROM_ADDRESS')) {
    define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@technews.local');
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'منصة الأخبار التقنية');
}
if (!defined('PUSH_VAPID_PUBLIC_KEY')) {
    define('PUSH_VAPID_PUBLIC_KEY', getenv('PUSH_VAPID_PUBLIC_KEY') ?: '');
}
if (!defined('PUSH_VAPID_PRIVATE_KEY')) {
    define('PUSH_VAPID_PRIVATE_KEY', getenv('PUSH_VAPID_PRIVATE_KEY') ?: '');
}
if (!defined('PUSH_VAPID_SUBJECT')) {
    define('PUSH_VAPID_SUBJECT', getenv('PUSH_VAPID_SUBJECT') ?: 'mailto:admin@technews.local');
}
