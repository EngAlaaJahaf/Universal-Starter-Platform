<?php
/**
 * CronGuard tests (SH-02) — fail-closed secret handling (no DB).
 * Sub-process checks use temp files instead of `php -r` so Windows quoting
 * cannot mangle the payload.
 */
TestRunner::suite('CronGuard');

$tmpDir = sys_get_temp_dir();

// Not configured -> no default secret (fail-closed).
putenv('CRON_SECRET');
TestRunner::is('', CronGuard::secret(), 'no secret configured -> EMPTY (never a public default)');
TestRunner::isFalse(CronGuard::isConfigured(), 'isConfigured() false when blank');

// Configured via env is honoured.
$testSecret = bin2hex(random_bytes(32));
putenv('CRON_SECRET=' . $testSecret);
TestRunner::is($testSecret, CronGuard::secret(), 'env secret is honoured');
TestRunner::isTrue(CronGuard::isConfigured(), 'isConfigured() true when set');

// generate() produces 256-bit hex.
$gen = CronGuard::generate();
TestRunner::is(64, strlen($gen), 'generate() returns 64 hex chars');
TestRunner::is(1, preg_match('/^[a-f0-9]{64}$/', $gen), 'generate() output is valid hex');
TestRunner::isTrue($gen !== $testSecret, 'generate() produces a fresh random secret');

// CLI is always allowed (cron entrypoints accept CLI without a key).
TestRunner::isTrue(CronGuard::isCli(), 'CLI SAPI is allowed by design');

// ── Pure authHttp decision logic (no exit side-effects) ────────────
TestRunner::isTrue(CronGuard::authHttp($testSecret, $testSecret), 'authHttp: matching secret accepted');
TestRunner::isFalse(CronGuard::authHttp($testSecret, 'wrong-secret'), 'authHttp: wrong secret rejected');
TestRunner::isFalse(CronGuard::authHttp('', $testSecret), 'authHttp: blank configured secret rejects (fail-closed)');
TestRunner::isFalse(CronGuard::authHttp($testSecret, ''), 'authHttp: blank provided secret rejects');
TestRunner::isFalse(CronGuard::authHttp(null, $testSecret), 'authHttp: null configured rejects');
TestRunner::isFalse(CronGuard::authHttp($testSecret, null), 'authHttp: null provided rejects');

// ── End-to-end HTTP exit codes via temp-file scripts ───────────────
$wrongFile = $tmpDir . '/cg_wrong.php';
$rightFile = $tmpDir . '/cg_right.php';
$noneFile  = $tmpDir . '/cg_none.php';

file_put_contents($wrongFile, '<?php
define("CRON_SECRET", "' . $testSecret . '");
$_GET["secret"] = "wrong-secret";
require ' . var_export(__DIR__ . '/../core/CronGuard.php', true) . ';
CronGuard::$sapiOverride = false;
CronGuard::checkHttp();
');
file_put_contents($rightFile, '<?php
define("CRON_SECRET", "' . $testSecret . '");
$_GET["secret"] = "' . $testSecret . '";
require ' . var_export(__DIR__ . '/../core/CronGuard.php', true) . ';
CronGuard::$sapiOverride = false;
$ok = CronGuard::checkHttp();
exit($ok ? 0 : 77);
');
file_put_contents($noneFile, '<?php
$_GET["secret"] = "anything";
require ' . var_export(__DIR__ . '/../core/CronGuard.php', true) . ';
CronGuard::$sapiOverride = false;
CronGuard::checkHttp();
');

$code = $code2 = $code3 = null;
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($wrongFile), $o1, $code);
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($rightFile), $o2, $code2);
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($noneFile), $o3, $code3);

TestRunner::is(1, $code, 'HTTP cron with wrong secret exits(1) — fail-closed');
TestRunner::is(0, $code2, 'HTTP cron with CORRECT secret proceeds (exit 0)');
TestRunner::is(1, $code3, 'HTTP cron with NO configured secret is refused (no default fallback)');

@unlink($wrongFile);
@unlink($rightFile);
@unlink($noneFile);
putenv('CRON_SECRET');

echo "\n";
return TestRunner::summary();