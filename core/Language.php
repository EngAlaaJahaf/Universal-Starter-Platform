<?php

class Language
{
    private static $locale = 'ar';
    private static $translations = array();

    public static function load($lang)
    {
        $lang = in_array($lang, array('ar', 'en'), true) ? $lang : 'ar';
        self::$locale = $lang;
        $file = dirname(__DIR__) . '/languages/' . $lang . '.php';
        self::$translations = is_file($file) ? require $file : array();
        Session::set('locale', $lang);
        setcookie('locale', $lang, time() + 31536000, '/', '', false, true);
    }

    public static function init()
    {
        if (empty(self::$translations)) {
            self::load(Session::get('locale', $_COOKIE['locale'] ?? 'ar'));
        }
    }

    public static function get($key, array $replacements = array())
    {
        self::init();
        $value = self::$translations[$key] ?? $key;
        foreach ($replacements as $name => $replacement) {
            $value = str_replace(':' . $name, $replacement, $value);
        }
        return $value;
    }

    public static function setLocale($lang)
    {
        self::load($lang);
    }

    public static function getLocale()
    {
        self::init();
        return self::$locale;
    }

    public static function getDirection()
    {
        return self::getLocale() === 'ar' ? 'rtl' : 'ltr';
    }
}

function __($key, array $replacements = array())
{
    return Language::get($key, $replacements);
}
