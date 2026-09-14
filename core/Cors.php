<?php
/**
 * Cors — fail-secure, configurable CORS policy (SH-05).
 *
 * Default (nothing configured): NO Access-Control-Allow-Origin header is
 * emitted, so browsers enforce same-origin — cross-site readers get nothing.
 *
 * To allow specific origins, configure CORS_ALLOWED_ORIGINS as a
 * comma-separated list (env var, config/hosting.php, or define()):
 *   define('CORS_ALLOWED_ORIGINS', 'https://app.example.com,https://tools.example.com');
 *
 * The wildcard '*' is explicitly rejected here on purpose.
 */

class Cors
{
    /** Allowed origins as a normalized array ('' → none allowed). */
    public static function allowedOrigins()
    {
        $raw = defined('CORS_ALLOWED_ORIGINS') ? (string) CORS_ALLOWED_ORIGINS : (string) getenv('CORS_ALLOWED_ORIGINS');
        if ($raw === '' || $raw === '*') {
            return [];
        }
        $out = [];
        foreach (explode(',', $raw) as $o) {
            $o = rtrim(trim($o), '/');
            if ($o !== '') $out[] = $o;
        }
        return array_unique($out);
    }

    public static function isAllowedOrigin($origin)
    {
        if (!is_string($origin) || $origin === '') {
            return false;
        }
        return in_array(rtrim($origin, '/'), self::allowedOrigins(), true);
    }

    /**
     * Resolve the request origin (only when a browser sent one).
     */
    public static function requestOrigin()
    {
        return isset($_SERVER['HTTP_ORIGIN']) ? (string) $_SERVER['HTTP_ORIGIN'] : '';
    }

    /**
     * Apply CORS headers for the current origin, then handle preflight.
     *
     * @param bool $handlePreflight short-circuit OPTIONS with 204 when true
     */
    public static function handle($handlePreflight = true)
    {
        $origin = self::requestOrigin();

        if ($origin !== '' && self::isAllowedOrigin($origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
            header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept, X-Requested-With');
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Max-Age: 600');
            // API auth is Bearer/key-based — no cookies, so:
            // Access-Control-Allow-Credentials intentionally NOT set.
        }

        if ($handlePreflight && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(($origin !== '' && self::isAllowedOrigin($origin)) ? 204 : 403);
            exit;
        }
    }
}