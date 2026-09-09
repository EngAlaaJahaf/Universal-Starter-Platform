<?php

class Cache
{
    protected static $dir = null;

    protected static function getDir()
    {
        if (self::$dir === null) {
            self::$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';
            if (!is_dir(self::$dir)) {
                @mkdir(self::$dir, 0777, true);
            }
        }
        return self::$dir;
    }

    protected static function getPath($key)
    {
        $hash = md5((string) $key);
        return self::getDir() . DIRECTORY_SEPARATOR . 'cache_' . $hash . '.json';
    }

    public static function get($key, $default = null)
    {
        $file = self::getPath($key);
        if (!file_exists($file)) {
            return $default;
        }
        $raw = @file_get_contents($file);
        if (!$raw) {
            return $default;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['expires_at'])) {
            return $default;
        }
        if ($data['expires_at'] !== 0 && time() > $data['expires_at']) {
            @unlink($file);
            return $default;
        }
        return $data['value'] ?? $default;
    }

    public static function set($key, $value, $ttlSeconds = 3600)
    {
        $file = self::getPath($key);
        $expiresAt = ($ttlSeconds > 0) ? (time() + $ttlSeconds) : 0;
        $payload = [
            'key' => $key,
            'expires_at' => $expiresAt,
            'value' => $value
        ];
        return @file_put_contents($file, json_encode($payload), LOCK_EX) !== false;
    }

    public static function has($key)
    {
        return self::get($key) !== null;
    }

    public static function delete($key)
    {
        $file = self::getPath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    public static function flush()
    {
        $dir = self::getDir();
        $files = glob($dir . DIRECTORY_SEPARATOR . 'cache_*.json');
        if ($files) {
            foreach ($files as $f) {
                @unlink($f);
            }
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
