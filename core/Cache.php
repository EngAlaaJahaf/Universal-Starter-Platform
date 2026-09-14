<?php

/**
 * Cache — swappable cache layer (SH-09).
 *
 * Same static API as before (get/set/has/delete/flush/remember), now backed by
 * a pluggable driver selected through CACHE_DRIVER (env):
 *   file   → JSON files under storage/cache (default, zero-extension, shared-hosting safe)
 *   apcu   → in-memory APCu store (requires the apcu extension; auto-falls-back to file)
 *   array  → per-request runtime store (useful for docs/test runs)
 *
 * Driver can also be set at runtime via Cache::setDriver() — used by tests and
 * by the admin cache_driver setting when wired.
 */

class Cache
{
    /** @var string|null forced driver name: file | apcu | array */
    protected static $driver = null;

    /** @var string|null overridable storage dir for the file driver (test seam) */
    protected static $dir = null;

    /** @var array runtime store for the "array" driver */
    protected static $arrayStore = [];

    protected static $apcuPrefix = 'usp_cache_v1_';

    public static function setDriver($name)
    {
        self::$driver = strtolower((string) $name);
    }

    public static function setDir($dir)
    {
        self::$dir = rtrim((string) $dir, '/\\');
    }

    /** Resolve the active driver name (env → forced → file default). */
    public static function driver()
    {
        if (self::$driver !== null) {
            $d = self::$driver;
        } else {
            $d = defined('CACHE_DRIVER') ? strtolower(CACHE_DRIVER) : 'file';
        }

        if ($d === 'apcu') {
            if (!function_exists('apcu_enabled') || !apcu_enabled()) {
                $d = 'file'; // graceful fallback — never crash on missing extension
            }
        } elseif (!in_array($d, ['file', 'array'], true)) {
            $d = 'file';
        }

        return $d;
    }

    // ─────────────────────────  Path / key helpers (file)  ─────────────────────────

    protected static function getDir()
    {
        if (self::$dir === null) {
            self::$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
        }
        if (!is_dir(self::$dir)) {
            @mkdir(self::$dir, 0777, true);
        }
        return self::$dir;
    }

    protected static function getPath($key)
    {
        $hash = md5((string) $key);
        return self::getDir() . DIRECTORY_SEPARATOR . 'cache_' . $hash . '.json';
    }

    protected static function apcuKey($key)
    {
        return self::$apcuPrefix . md5((string) $key);
    }

    // ─────────────────────────  Drivers  ─────────────────────────

    protected static function storeGet($driver, $key, &$found)
    {
        $found = true;

        if ($driver === 'array') {
            if (array_key_exists($key, self::$arrayStore)) {
                $entry = self::$arrayStore[$key];
                if ($entry['expires_at'] !== 0 && time() > $entry['expires_at']) {
                    unset(self::$arrayStore[$key]);
                    $found = false;
                    return null;
                }
                return $entry['value'];
            }
            $found = false;
            return null;
        }

        if ($driver === 'apcu') {
            $entry = apcu_fetch(self::apcuKey($key), $ok);
            if ($ok && is_array($entry) && isset($entry['expires_at'])) {
                if ($entry['expires_at'] !== 0 && time() > $entry['expires_at']) {
                    apcu_delete(self::apcuKey($key));
                    $found = false;
                    return null;
                }
                return $entry['value'];
            }
            $found = false;
            return null;
        }

        // file driver
        $file = self::getPath($key);
        if (!file_exists($file)) {
            $found = false;
            return null;
        }
        $raw = @file_get_contents($file);
        if (!$raw) {
            $found = false;
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['expires_at'])) {
            $found = false;
            return null;
        }
        if ($data['expires_at'] !== 0 && time() > $data['expires_at']) {
            @unlink($file);
            $found = false;
            return null;
        }
        return $data['value'];
    }

    protected static function storeSet($driver, $key, $value, $ttlSeconds)
    {
        $expiresAt = ($ttlSeconds > 0) ? (time() + (int) $ttlSeconds) : 0;

        if ($driver === 'array') {
            self::$arrayStore[$key] = ['expires_at' => $expiresAt, 'value' => $value];
            return true;
        }

        if ($driver === 'apcu') {
            return apcu_store(self::apcuKey($key), ['expires_at' => $expiresAt, 'value' => $value], $expiresAt > 0 ? $expiresAt - time() : 0);
        }

        $file = self::getPath($key);
        $payload = ['key' => $key, 'expires_at' => $expiresAt, 'value' => $value];
        return @file_put_contents($file, json_encode($payload), LOCK_EX) !== false;
    }

    protected static function storeDelete($driver, $key)
    {
        if ($driver === 'array') {
            unset(self::$arrayStore[$key]);
            return true;
        }
        if ($driver === 'apcu') {
            apcu_delete(self::apcuKey($key));
            return true;
        }
        $file = self::getPath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    protected static function storeFlush($driver)
    {
        if ($driver === 'array') {
            self::$arrayStore = [];
            return true;
        }
        if ($driver === 'apcu') {
            // Only clear OUR prefixed keys — apcu_clear_cache() is too broad for shared hosting.
            if (function_exists('apcu_cache_info')) {
                $info = @apcu_cache_info(false);
                $keys = [];
                foreach (($info['cache_list'] ?? []) as $e) {
                    if (isset($e['info']) && strpos($e['info'], self::$apcuPrefix) === 0) {
                        $keys[] = $e['info'];
                    }
                }
                foreach ($keys as $k) {
                    apcu_delete($k);
                }
            }
            return true;
        }
        $dir = self::getDir();
        $files = glob($dir . DIRECTORY_SEPARATOR . 'cache_*.json');
        if ($files) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }
        return true;
    }

    // ─────────────────────────  Public API (unchanged)  ─────────────────────────

    public static function get($key, $default = null)
    {
        $driver = self::driver();
        $value = self::storeGet($driver, (string) $key, $found);
        return $found && $value !== null ? $value : $default;
    }

    public static function set($key, $value, $ttlSeconds = 3600)
    {
        return self::storeSet(self::driver(), (string) $key, $value, $ttlSeconds);
    }

    public static function has($key)
    {
        return self::get($key) !== null;
    }

    public static function delete($key)
    {
        return self::storeDelete(self::driver(), (string) $key);
    }

    public static function flush()
    {
        foreach (['file', 'apcu', 'array'] as $driver) {
            self::storeFlush($driver);
        }
        return true;
    }

    public static function remember($key, $ttlSeconds, $callback)
    {
        $val = self::get($key);
        if ($val !== null) {
            return $val;
        }
        $val = call_user_func($callback);
        self::set($key, $val, $ttlSeconds);
        return $val;
    }
}