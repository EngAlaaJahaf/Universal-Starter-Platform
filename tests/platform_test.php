<?php
/**
 * platform_test — SH-12 platform surface: Plugin hooks, live OpenAPI
 * generator, webhook HMAC signing. Pure/static pieces only (no network,
 * no DB) so the suite stays hermetic.
 */
TestRunner::suite('Platform');

/* ---------- Plugin hooks (SH-12b) ---------- */
Plugin::reset();
TestRunner::isFalse(Plugin::hasHooks('app.view'), 'no listeners by default');
TestRunner::isTrue(Plugin::hook('app.view') === null, 'firing an unbound point is a null no-op');

$seen = array();
Plugin::register('app.view', function (array &$ctx) use (&$seen) {
    $seen[] = 'default';
    $ctx['injected_by_hook'] = true;
});
Plugin::register('app.view', function (array &$ctx) use (&$seen) {
    $seen[] = 'priority';
}, 5); // priority 5 runs BEFORE default 10
Plugin::register('app.view.cb', function (array &$ctx) use (&$seen) {
    $seen[] = 'cb-first';
    return 'short-circuit';
});
Plugin::register('app.view.cb', function () use (&$seen) {
    $seen[] = 'cb-second'; // never reached
});
Plugin::register('other.point', function () { return 'other'; });

TestRunner::isTrue(Plugin::hasHooks('app.view'), 'hasHooks true after register');
$ctx = array('title' => 't');
Plugin::hook('app.view', $ctx);
TestRunner::is(array('priority', 'default'), $seen, 'listeners run by priority order');
TestRunner::isTrue(isset($ctx['injected_by_hook']) && $ctx['title'] === 't', 'context is mutable by reference');
TestRunner::is('short-circuit', Plugin::hook('app.view.cb'), 'first non-null return short-circuits');
TestRunner::is(array('priority', 'default', 'cb-first'), $seen, 'cb-second never runs after short-circuit');
TestRunner::isCount(2, Plugin::listeners('app.view'), 'listeners() returns both bound callables');
TestRunner::is('other', Plugin::hook('other.point'), 'independent point is isolated');

/* ---------- OpenAPI generator (SH-12a) ---------- */
Plugin::reset();
$sampleRoutes = array(
    array('method' => 'GET',    'pattern' => '/api/v1/articles',        'handler' => 'ApiV1ArticlesController@index'),
    array('method' => 'POST',   'pattern' => '/api/v1/articles',        'handler' => 'ApiV1ArticlesController@store'),
    array('method' => 'POST',   'pattern' => '/api/v1/articles/ai-publish', 'handler' => 'ApiV1ArticlesController@aiPublish'),
    array('method' => 'GET',    'pattern' => '/api/v1/market-pulse',     'handler' => 'ApiV1MarketController@pulse'),
    array('method' => 'GET',    'pattern' => '/api/v1/tutorials/{id}',   'handler' => 'ApiV1TutorialsController@show'),
    array('method' => 'DELETE', 'pattern' => '/api/v1/categories/{id}', 'handler' => 'ApiV1CategoriesController@delete'),
    array('method' => 'GET',    'pattern' => '/',                        'handler' => 'HomeController@index'),
    array('method' => 'GET',    'pattern' => '/admin/foo',               'handler' => 'AdminFoo@bar'),
);
$public = array('/api/v1/market-pulse', '/api/v1/schema', '/api/v1/openapi.json');
$spec = OpenApiGenerator::fromRoutes($sampleRoutes, $public, 'https://example.com/base');

TestRunner::is('3.0.1', $spec['openapi'], 'OpenAPI version pinned');
TestRunner::is('https://example.com/base/api/v1', $spec['servers'][0]['url'], 'server URL derived from app root');
TestRunner::isTrue(isset($spec['components']['securitySchemes']['BearerAuth']), 'BearerAuth scheme present');

// Path coverage: all api routes, keys relative to /api/v1, non-api excluded.
TestRunner::isCount(5, $spec['paths'], '5 api paths emitted (GET+POST share /articles), non-api excluded');
TestRunner::isTrue(isset($spec['paths']['/articles']['get']['summary']), 'rich get preserved');
TestRunner::isTrue(isset($spec['paths']['/articles']['post']['requestBody']), 'rich post requestBody preserved');
TestRunner::isTrue(isset($spec['paths']['/articles/ai-publish']['post']['requestBody']), 'ai-publish rich body preserved');
TestRunner::isTrue(isset($spec['paths']['/categories/{id}']['delete']['parameters']), 'path-token gains a generated parameter');
TestRunner::isCount(1, $spec['paths']['/categories/{id}']['delete']['parameters'], 'exactly one path parameter generated');
TestRunner::is('delete', $spec['paths']['/categories/{id}']['delete']['operationId'], 'skeleton op operationId from action');

// Security tagging: protected vs public.
TestRunner::is(array(array('BearerAuth' => array())), $spec['paths']['/articles']['get']['security'], 'protected op requires BearerAuth');
TestRunner::is(array(), $spec['paths']['/market-pulse']['get']['security'], 'public op carries empty security');

/* ---------- Webhook signatures (SH-12c) ---------- */
$payload = '{"event":"article.published","data":{"id":42}}';
$sig = Webhook::signature($payload, 's3cret');
TestRunner::isTrue(strpos($sig, 'sha256=') === 0, 'signature is prefixed sha256=...');
TestRunner::isTrue(preg_match('/^sha256=[a-f0-9]{64}$/', $sig) === 1, 'signature is 64 hex chars');
TestRunner::isTrue(Webhook::verifySignature($payload, 's3cret', $sig), 'valid signature verifies');
TestRunner::isFalse(Webhook::verifySignature($payload, 's3cret', 'sha256=' . str_repeat('0', 64)), 'tampered signature rejects');
TestRunner::isFalse(Webhook::verifySignature($payload . 'x', 's3cret', $sig), 'tampered payload rejects');
TestRunner::isFalse(Webhook::verifySignature($payload, 'wrong-secret', $sig), 'wrong secret rejects');
TestRunner::isFalse(Webhook::verifySignature($payload, 's3cret', null), 'null header rejects');
TestRunner::isFalse(Webhook::verifySignature($payload, 's3cret', ''), 'empty header rejects');
TestRunner::is(Webhook::signature('a', 'k'), Webhook::signature('a', 'k'), 'signature deterministic');
TestRunner::isTrue(Webhook::signature('a', 'k') !== Webhook::signature('a', 'k2'), 'secret change flips signature');

// Deliver() fails soft on unreachable URL (no network dependency).
$res = Webhook::deliver('http://127.0.0.1:1/unreachable', 'test');
TestRunner::is(array('ok', 'status', 'error', 'body'), array_keys($res), 'deliver returns a fixed result shape');
TestRunner::isFalse($res['ok'], 'unreachable endpoint reports ok=false (never throws)');

// Router::current() getter snapshot (SH-12a).
$router = new Router();
$router->get('/api/v1/x', 'X@y');
TestRunner::is($router, Router::current(), 'current() points at the last built router instance');
TestRunner::isCount(1, $router->routes(), 'routes() exposes registered triples');
TestRunner::is('POST', $router->post('/api/v1/x', 'X@y')->routes()[1]['method'], 'fluent add() chains and routes() sees POST');