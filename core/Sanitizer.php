<?php

class Sanitizer
{
    public static function clean($input)
    {
        if (is_array($input)) return self::cleanArray($input);
        return trim(strip_tags((string) $input));
    }

    public static function cleanArray(array $input)
    {
        $result = array();
        foreach ($input as $key => $value) $result[$key] = is_array($value) ? self::cleanArray($value) : self::clean($value);
        return $result;
    }

    public static function cleanHtml($html)
    {
        return strip_tags((string) $html, '<p><br><strong><em><ul><ol><li><a><blockquote><h2><h3><img>');
    }
}
