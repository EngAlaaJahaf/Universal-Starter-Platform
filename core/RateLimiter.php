<?php

class RateLimiter
{
    private static function file($key)
    {
        $safe = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $key);
        return dirname(__DIR__) . '/storage/cache/rate_' . $safe . '.json';
    }

    public static function attempt($key, $maxAttempts, $minutes)
    {
        $file = self::file($key);
        $now = time();
        $data = is_file($file) ? json_decode(file_get_contents($file), true) : array();
        $data = is_array($data) ? $data : array();
        $data = array_values(array_filter($data, function ($timestamp) use ($now, $minutes) { return $timestamp > $now - ((int) $minutes * 60); }));
        if (count($data) >= (int) $maxAttempts) {
            file_put_contents($file, json_encode($data), LOCK_EX);
            return false;
        }
        $data[] = $now;
        file_put_contents($file, json_encode($data), LOCK_EX);
        return true;
    }

    public static function clear($key)
    {
        $file = self::file($key);
        if (is_file($file)) unlink($file);
    }

    public static function remaining($key, $maxAttempts, $minutes)
    {
        $file = self::file($key);
        if (!is_file($file)) return (int) $maxAttempts;
        $data = json_decode(file_get_contents($file), true);
        $valid = array_filter((array) $data, function ($timestamp) use ($minutes) { return $timestamp > time() - ((int) $minutes * 60); });
        return max(0, (int) $maxAttempts - count($valid));
    }

    public static function hit($key)
    {
        return self::attempt($key, PHP_INT_MAX, 60);
    }
}
