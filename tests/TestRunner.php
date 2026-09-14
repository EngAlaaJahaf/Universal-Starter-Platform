<?php
/**
 * TestRunner — zero-dependency mini test harness for the Starter Platform.
 * No PHPUnit / Composer required: `php craft test` (or `php tests/run.php`).
 */

class TestRunner
{
    public static $pass = 0;
    public static $fail = 0;
    public static $failures = [];
    public static $suite = '';

    public static function suite($name)
    {
        self::$suite = $name;
        self::$pass = 0;
        self::$fail = 0;
        self::$failures = [];
    }

    public static function ok($cond, $name, $msg = '')
    {
        if ($cond) {
            self::$pass++;
            echo "  " . self::$suite . " ✓ {$name}\n";
        } else {
            self::$fail++;
            self::$failures[] = self::$suite . ' :: ' . $name . ($msg !== '' ? ' :: ' . $msg : '');
            echo "  " . self::$suite . " ✗ {$name}" . ($msg !== '' ? " — {$msg}" : '') . "\n";
        }
    }

    public static function is($expected, $actual, $name)
    {
        $ok = $expected === $actual;
        self::ok($ok, $name, $ok ? '' : 'expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }

    public static function eq($expected, $actual, $name)
    {
        $ok = $expected == $actual;
        self::ok($ok, $name, $ok ? '' : 'expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }

    public static function isTrue($actual, $name)
    {
        $ok = $actual === true || $actual === 1 || $actual === '1';
        self::ok($ok, $name, 'expected truthy, got ' . var_export($actual, true));
    }

    public static function isFalse($actual, $name)
    {
        $ok = $actual === false || $actual === 0 || $actual === '0' || $actual === null || $actual === '';
        self::ok($ok, $name, 'expected falsy, got ' . var_export($actual, true));
    }

    public static function isNull($actual, $name)
    {
        self::ok($actual === null, $name, 'expected NULL, got ' . var_export($actual, true));
    }

    public static function isNotNull($actual, $name)
    {
        self::ok($actual !== null, $name, 'expected non-NULL');
    }

    public static function contains($needle, $haystack, $name)
    {
        self::ok(strpos((string)$haystack, (string)$needle) !== false, $name,
            'expected "' . $needle . '" inside "' . substr((string)$haystack, 0, 120) . '"');
    }

    public static function notContains($needle, $haystack, $name)
    {
        self::ok(strpos((string)$haystack, (string)$needle) === false, $name,
            'unexpected "' . $needle . '" inside "' . substr((string)$haystack, 0, 120) . '"');
    }

    public static function isCount($expected, $array, $name)
    {
        self::ok(count($array) === $expected, $name,
            'expected count ' . $expected . ', got ' . count($array));
    }

    public static function throws($callable, $name)
    {
        try {
            $callable();
            self::ok(false, $name, 'expected an exception but none was thrown');
        } catch (Throwable $e) {
            self::ok(true, $name, '');
        }
    }

    public static function summary()
    {
        echo "\n";
        if (self::$fail === 0) {
            echo "  ├─ ALL GREEN — " . self::$pass . " assertions passed.\n";
            return true;
        }
        echo "  ├─ FAILED — " . self::$fail . " failed, " . self::$pass . " passed.\n\n";
        foreach (self::$failures as $f) {
            echo "     ✗ " . $f . "\n";
        }
        return false;
    }
}