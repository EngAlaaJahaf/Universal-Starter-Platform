<?php
/**
 * tests/run.php — Test foundation entrypoint (SH-07).
 * Usage: php craft test   |   php tests/run.php
 * Requires PHP CLI only (no Composer / PHPUnit). DB-backed tests are skipped
 * gracefully when MySQL is unavailable.
 */

if (php_sapi_name() !== 'cli') {
    exit("Tests must be run from the command line.\n");
}

date_default_timezone_set('UTC');

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}
require_once APP_ROOT . '/config/database.php';

if (!function_exists('app_test_autoload')) {
    function app_test_autoload($class) {
        foreach ([
            APP_ROOT . '/core/' . $class . '.php',
            APP_ROOT . '/models/' . $class . '.php',
            APP_ROOT . '/controllers/' . $class . '.php',
            APP_ROOT . '/controllers/admin/' . $class . '.php',
            APP_ROOT . '/controllers/api/' . $class . '.php',
            APP_ROOT . '/controllers/api/v1/' . $class . '.php',
        ] as $path) {
            if (file_exists($path)) {
                require_once $path;
                return;
            }
        }
    }
    spl_autoload_register('app_test_autoload');
}

require_once APP_ROOT . '/tests/TestRunner.php';

$suiteOk = true;
$suites = glob(APP_ROOT . '/tests/*_test.php');

foreach ($suites as $suite) {
    $result = require $suite; // each test file returns bool
    $suiteOk = $suiteOk && $result;
}

echo "\n" . str_repeat('=', 46) . "\n";
echo ($suiteOk ? '  ALL TEST SUITES PASSED' : '  ONE OR MORE SUITES FAILED') . "\n";
echo str_repeat('=', 46) . "\n";

exit($suiteOk ? 0 : 1);