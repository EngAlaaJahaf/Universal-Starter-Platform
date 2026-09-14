<?php
/**
 * CORS policy tests (SH-05) — fail-secure behaviour, allowlist handling.
 * Uses the setAllowlistOverride() test seam because the CORS_ALLOWED_ORIGINS
 * constant is already bound at boot time from the process environment.
 */
TestRunner::suite('CORS');

// Pure allowlist parser.
TestRunner::is([], Cors::parseList(''), 'parseList empty string -> []');
TestRunner::is([], Cors::parseList('*'), 'parseList wildcard "*" is rejected (fail-secure)');
TestRunner::is(['https://app.example.com', 'https://tools.example.com'], Cors::parseList('https://app.example.com,https://tools.example.com,https://app.example.com'), 'parseList dedupes + normalises');
TestRunner::is(['https://a.example.com'], Cors::parseList(',https://a.example.com, '), 'parseList drops empty segments + trims');
TestRunner::is(['https://x.example.com'], Cors::parseList(' https://x.example.com/ '), 'parseList strips trailing slash');

// isAllowedOrigin checks.
Cors::setAllowlistOverride(['https://app.example.com', 'https://tools.example.com']);
TestRunner::isTrue(Cors::isAllowedOrigin('https://app.example.com'), 'exact allowed origin accepted');
TestRunner::isTrue(Cors::isAllowedOrigin('https://tools.example.com/'), 'trailing slash normalised and accepted');
TestRunner::isFalse(Cors::isAllowedOrigin('https://evil.example.com'), 'disallowed origin rejected');
TestRunner::isFalse(Cors::isAllowedOrigin('https://app.example.com.evil.com'), 'suffix-spoofed origin rejected (exact match only)');
TestRunner::isFalse(Cors::isAllowedOrigin(''), 'empty origin rejected');
TestRunner::isFalse(Cors::isAllowedOrigin('null'), 'literal "null" origin rejected');

// Request origin detection.
$_SERVER['HTTP_ORIGIN'] = 'https://app.example.com';
TestRunner::is('https://app.example.com', Cors::requestOrigin(), 'HTTP_ORIGIN read from server');
unset($_SERVER['HTTP_ORIGIN']);
TestRunner::is('', Cors::requestOrigin(), 'no origin header -> empty (same-origin request)');

echo "\n";
return TestRunner::summary();