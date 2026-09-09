<?php

class Settings
{
    private static $cache = null;

    public static function load()
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = array();
        try {
            $db = new Database();
            foreach ($db->fetchAll('SELECT `group`, `key`, `value`, value_type FROM settings') as $row) {
                $value = $row['value'];
                if ($row['value_type'] === 'boolean') $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                if ($row['value_type'] === 'number') $value = is_numeric($value) ? $value + 0 : 0;
                if ($row['value_type'] === 'json') $value = json_decode($value, true);
                self::$cache[$row['key']] = $value;
            }
        } catch (Throwable $e) {
            self::$cache = array();
        }
        return self::$cache;
    }

    public static function get($key, $default = null)
    {
        $all = self::load();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function clear()
    {
        self::$cache = null;
    }
}
