<?php
/**
 * RateLimiter tests (SH-05) — file-backed sliding window, self-cleaning.
 */
TestRunner::suite('RateLimiter');

$key = 'rltest_' . bin2hex(random_bytes(4));

TestRunner::isTrue(RateLimiter::attempt($key, 3, 1), 'attempt 1 allowed');
TestRunner::isTrue(RateLimiter::attempt($key, 3, 1), 'attempt 2 allowed');
TestRunner::isTrue(RateLimiter::attempt($key, 3, 1), 'attempt 3 allowed');
TestRunner::isFalse(RateLimiter::attempt($key, 3, 1), 'attempt 4 blocked (limit reached)');

TestRunner::is(0, RateLimiter::remaining($key, 3, 1), 'remaining() reports zero when exhausted');
TestRunner::isTrue(RateLimiter::clear($key) === null, 'clear() resets the bucket (no exception)');
TestRunner::is(3, RateLimiter::remaining($key, 3, 1), 'after clear() remaining is back to max');

// Per-IP key used by the API throttle.
$ipKey = 'api_ip_' . md5('203.0.113.7');
RateLimiter::clear($ipKey);
TestRunner::isTrue(RateLimiter::attempt($ipKey, 1, 1), 'per-IP bucket accepts first hit');
TestRunner::isFalse(RateLimiter::attempt($ipKey, 1, 1), 'per-IP bucket blocks second hit');
RateLimiter::clear($ipKey);

echo "\n";
return TestRunner::summary();