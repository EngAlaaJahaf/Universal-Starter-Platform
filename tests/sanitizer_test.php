<?php
/**
 * Sanitizer unit tests — input cleaning basics.
 */
TestRunner::suite('Sanitizer');

TestRunner::is('hello', Sanitizer::clean('  <b>hello</b>  '), 'clean() strips tags and trims');
TestRunner::is('alert(1)', Sanitizer::clean('<script>alert(1)</script>'), 'clean() strips script tags but keeps text content');
TestRunner::is('<a href="/x">a</a>bh1 dropped', Sanitizer::cleanHtml('<a href="/x">a</a><b>b</b><h1>h1 dropped</h1>'), 'cleanHtml whitelist keeps <a>, strips <b> and <h1>');
TestRunner::is('النص', Sanitizer::clean(' النص '), 'clean() preserves Arabic text');
TestRunner::is(['k' => 'v', 'nested' => ['x' => 'y']], Sanitizer::cleanArray(['k' => 'v', 'nested' => ['x' => 'y']]), 'cleanArray recurses into nested arrays');

echo "\n";
return TestRunner::summary();