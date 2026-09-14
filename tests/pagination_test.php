<?php
/**
 * Pagination unit tests — offset/limit math and URL stability.
 */
TestRunner::suite('Pagination');

$p = new Pagination(95, 10, 3, '/articles');
TestRunner::is(20, $p->getOffset(), 'offset for page 3 = 20');
TestRunner::is(10, $p->getLimit(), 'limit = perPage');
TestRunner::isTrue($p->hasPages(), '95 items across 10/page has pages');

$p1 = new Pagination(5, 10, 1, '/x');
TestRunner::isFalse($p1->hasPages(), '5 items across 10/page has NO pages');
TestRunner::is(0, $p1->getOffset(), 'page 1 offset 0');

TestRunner::is('/articles?page=4', $p->getUrl(4), 'getUrl keeps base path');

$pOut = new Pagination(1, 10, 99, '/z'); // page beyond range
TestRunner::is(0, $pOut->getOffset(), 'out-of-range page clamps to first page');

echo "\n";
return TestRunner::summary();