<?php
/**
 * Security Guard unit tests — pure signature detection (no DB).
 */
TestRunner::suite('SecurityGuard');

// ── SQLi signatures ────────────────────────────────────────────────
TestRunner::isTrue(SecurityGuard::containsSqli("' OR '1'='1"), 'classic OR 1=1 injection');
TestRunner::isTrue(SecurityGuard::containsSqli("1' or '1'='1' --"), 'OR with comment terminator');
TestRunner::isTrue(SecurityGuard::containsSqli("admin' --"), 'comment injection');
TestRunner::isTrue(SecurityGuard::containsSqli("; DROP TABLE users;"), 'DROP TABLE');
TestRunner::isTrue(SecurityGuard::containsSqli("UNION SELECT username,password FROM users"), 'UNION SELECT');
TestRunner::isTrue(SecurityGuard::containsSqli("1 AND benchmark(5000000,SHA1('x'))"), 'benchmark() DoS');
TestRunner::isTrue(SecurityGuard::containsSqli("sleep(10)"), 'SLEEP() time-based');
TestRunner::isTrue(SecurityGuard::containsSqli("SELECT * FROM information_schema.tables"), 'information_schema probe');

// ── Harmless / multilingual text MUST NOT false-positive ───────────
TestRunner::isFalse(SecurityGuard::containsSqli('مرحبا بالعالم هذا نص عادي'), 'plain Arabic text');
TestRunner::isFalse(SecurityGuard::containsSqli('The quick brown fox jumps over 2026'), 'plain English text');
TestRunner::isFalse(SecurityGuard::containsSqli('الكلمة "select" داخل مقال عربي عادي'), 'Arabic sentence containing the word select');
TestRunner::isFalse(SecurityGuard::containsSqli("price=10 OR total"), 'sentence containing OR but not a SQL equal-match');

// ── XSS signatures ─────────────────────────────────────────────────
TestRunner::isTrue(SecurityGuard::containsXss('<script>alert(1)</script>'), 'script tag pair');
TestRunner::isTrue(SecurityGuard::containsXss('<img src=x onerror=alert(1)>'), 'img onerror');
TestRunner::isTrue(SecurityGuard::containsXss('<svg onload="alert(1)"></svg>'), 'svg onload');
TestRunner::isTrue(SecurityGuard::containsXss('javascript:alert(document.cookie)'), 'javascript URI');
TestRunner::isTrue(SecurityGuard::containsXss('document.cookie='), 'document.cookie exfil');
TestRunner::isTrue(SecurityGuard::containsXss('onclick="steal()"'), 'onclick handler');

// ── XSS false positives (Arabic / benign) ──────────────────────────
TestRunner::isFalse(SecurityGuard::containsXss('مقال عادي بلا أكواد'), 'plain Arabic text');
TestRunner::isFalse(SecurityGuard::containsXss('Normal HTML: <p>Hello world</p>'), 'benign HTML paragraph');
TestRunner::isFalse(SecurityGuard::containsXss('التعليق يحتوي على كلمة alert لكنها ضمن جملة عادية؟'), 'Arabic sentence mentioning alert');

// ── URL-decoded payload must still be caught ───────────────────────
TestRunner::isTrue(SecurityGuard::containsXss('%3Cscript%3Ealert(1)%3C%2Fscript%3E'), 'urlencoded script tag');

echo "\n";
return TestRunner::summary();