<?php
/**
 * Upload SVG sanitizer tests (SH-06) — active XML stripped, benign kept.
 */
TestRunner::suite('Upload::sanitizeSvg');

$m = new ReflectionMethod('Upload', 'sanitizeSvg');
$m->setAccessible(true);

$good = '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><defs><linearGradient id="g"><stop offset="0" stop-color="red"/></linearGradient></defs><rect width="100" height="100" fill="url(#g)"/></svg>';
$out = $m->invoke(null, $good);
TestRunner::isTrue($out !== null, 'benign SVG with gradient is accepted');
TestRunner::notContains('<script', (string)$out, 'no script in output');
TestRunner::contains('fill="url(#g)"', (string)$out, 'gradient fragment preserved');

$evilAttrs = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><path d="M0 0" onclick="x()"/></svg>';
$out2 = $m->invoke(null, $evilAttrs);
TestRunner::isTrue($out2 !== null, 'well-formed SVG parses for cleaning');

$evil = $out2 !== null ? (string)$out2 : '';
TestRunner::notContains('onload', $evil, 'onload attribute stripped');
TestRunner::notContains('onclick', $evil, 'onclick attribute stripped');

$xxe = '<!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg"><text>&xxe;</text></svg>';
TestRunner::isNull($m->invoke(null, $xxe), 'DOCTYPE+ENTITY (XXE) SVG rejected outright');

$script = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><text>hi</text></svg>';
$outScript = $m->invoke(null, $script);
TestRunner::isNotNull($outScript, 'script-bearing SVG parses for cleaning');
TestRunner::notContains('<script', (string)$outScript, 'script node removed from output');

$href = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><use xlink:href="#icon"/><use href="https://e.example/a.svg"/></svg>';
$out3 = $m->invoke(null, $href);
TestRunner::isTrue($out3 !== null, 'use/gradient SVG accepted');
TestRunner::notContains('https://', (string)$out3, 'external href stripped');
TestRunner::contains('#icon', (string)$out3, 'same-document #fragment kept');

TestRunner::isNull($m->invoke(null, 'not xml at all'), 'non-XML content rejected');

echo "\n";
return TestRunner::summary();