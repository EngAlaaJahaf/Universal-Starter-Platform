<?php
/**
 * cache_test — Cache driver abstraction (SH-09).
 * Verifies the public API behaves identically on the file and array drivers
 * (same semantics the previous file-only cache had): round-trip, TTL expiry,
 * delete, flush, remember(), and the legacy stored-null → default behaviour.
 */
TestRunner::suite('Cache');

$tmp = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'usp_cache_test_' . bin2hex(random_bytes(3));
Cache::setDir($tmp);

$drivers = ['array', 'file'];
foreach ($drivers as $driver) {
    Cache::setDriver($driver);
    $stamp = $driver;

    Cache::set('k:simple', 'hello', 60);
    TestRunner::isTrue(Cache::has('k:simple'), "$stamp: has() true after set");
    TestRunner::is('hello', Cache::get('k:simple'), "$stamp: get() round-trip");

    Cache::set('k:array', ['a' => 1, 'b' => [2, 3]], 60);
    $val = Cache::get('k:array');
    TestRunner::isTrue(is_array($val) && ($val['b'][1] ?? null) === 3, "$stamp: array value round-trip");

    TestRunner::is('fallback', Cache::get('k:missing', 'fallback'), "$stamp: missing key returns default");
    TestRunner::isFalse(Cache::has('k:missing'), "$stamp: has() false on missing key");

    Cache::set('k:expire', 'gone', 1);
    TestRunner::is('gone', Cache::get('k:expire'), "$stamp: fresh value readable");
    sleep(2);
    TestRunner::is(null, Cache::get('k:expire', null), "$stamp: expired value returns default");

    Cache::delete('k:simple');
    TestRunner::isFalse(Cache::has('k:simple'), "$stamp: delete() removes key");

    Cache::set('k:flushme', 'x', 60);
    Cache::flush();
    TestRunner::isFalse(Cache::has('k:flushme'), "$stamp: flush() empties store");
    TestRunner::isFalse(Cache::has('k:array'), "$stamp: flush() clears prior keys too");

    $cbCount = 0;
    TestRunner::is('memo', Cache::remember('k:remember', 60, function () use (&$cbCount) { $cbCount++; return 'memo'; }), "$stamp: remember() computes on miss");
    TestRunner::is('memo', Cache::remember('k:remember', 60, function () use (&$cbCount) { $cbCount++; return 'memo'; }), "$stamp: remember() returns cached copy");
    TestRunner::is(1, $cbCount, "$stamp: remember() callback runs exactly once");

    Cache::set('k:nullish', null, 60);
    TestRunner::is('plus-default', Cache::get('k:nullish', 'plus-default'), "$stamp: stored null still resolves to default (legacy semantics)");
}

// File driver created its storage dir lazily and leaves only VALID cache files;
// a final flush() empties the sandbox completely.
Cache::setDriver('file');
Cache::set('k:dir', 'ok', 60);
TestRunner::isTrue(is_dir($tmp), 'file driver created its storage dir');
$leftovers = glob($tmp . DIRECTORY_SEPARATOR . 'cache_*.json');
TestRunner::isCount(3, $leftovers, 'file driver persisted only the expected keys (k:remember, k:nullish, k:dir)');
Cache::flush();
$empty = glob($tmp . DIRECTORY_SEPARATOR . 'cache_*.json');
TestRunner::isCount(0, $empty, 'sandbox cache dir empty after final flush()');

echo "\n";
return TestRunner::summary();