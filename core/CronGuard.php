<?php
/**
 * CronGuard — fail-closed protection for cron entry points.
 *
 * Priority for the secret:
 *   1. config/hosting.php constant CRON_SECRET (live server override)
 *   2. Real environment variable CRON_SECRET (getenv)
 *   3. '' (NOT CONFIGURED) — there is intentionally NO default secret.
 *
 * Rules:
 * - CLI (php cron/xxx.php) is always allowed: whoever can run shell
 *   commands on the server already owns the box.
 * - HTTP (?secret=...) requires a configured, non-empty secret matched
 *   with hash_equals(). When no secret is configured, HTTP is refused
 *   with 403 (fail-closed) instead of falling back to a public default.
 * - The secret value is NEVER written to logs or echoed back.
 *
 * Generate / rotate with:  php craft cron:secret
 */

class CronGuard
{
    public static function secret()
    {
        if (defined('CRON_SECRET') && is_string(CRON_SECRET) && CRON_SECRET !== '') {
            return CRON_SECRET;
        }
        $env = getenv('CRON_SECRET');
        if (is_string($env) && $env !== '') {
            return $env;
        }
        return '';
    }

    public static function isConfigured()
    {
        return self::secret() !== '';
    }

    public static function isCli()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Enforce HTTP protection. Returns true when the request may proceed.
     * On failure it emits a generic 403 and exits (no secret disclosure).
     */
    public static function checkHttp()
    {
        if (self::isCli()) {
            return true;
        }
        $configured = self::secret();
        $provided = isset($_GET['secret']) ? (string) $_GET['secret'] : '';
        if ($configured === '' || $provided === '' || !hash_equals($configured, $provided)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Forbidden']);
            exit(1);
        }
        header('Content-Type: application/json; charset=utf-8');
        return true;
    }

    /**
     * Generate a fresh secret (64 hex chars = 256 bits of entropy).
     */
    public static function generate()
    {
        return bin2hex(random_bytes(32));
    }
}
