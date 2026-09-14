<?php
/**
 * REST API v1 routes — extracted from index.php (SH-08).
 * Executed in index.php scope where $router is defined.
 */

// Public endpoints (no API key required)
$router->get('/api/v1/market-pulse', 'ApiV1MarketController@pulse');
$router->get('/api/v1/schema', 'ApiV1DocsController@schema');
$router->get('/api/v1/openapi.json', 'ApiV1DocsController@schema');

// Protected endpoints (require API key + scope)
$router->get('/api/v1/stats', 'ApiV1StatsController@index');

$router->get('/api/v1/articles', 'ApiV1ArticlesController@index');
$router->post('/api/v1/articles', 'ApiV1ArticlesController@store');
$router->post('/api/v1/articles/ai-publish', 'ApiV1ArticlesController@aiPublish');
$router->get('/api/v1/articles/{id}', 'ApiV1ArticlesController@show');
$router->put('/api/v1/articles/{id}', 'ApiV1ArticlesController@update');
$router->delete('/api/v1/articles/{id}', 'ApiV1ArticlesController@delete');

$router->get('/api/v1/categories', 'ApiV1CategoriesController@index');
$router->post('/api/v1/categories', 'ApiV1CategoriesController@store');
$router->put('/api/v1/categories/{id}', 'ApiV1CategoriesController@update');
$router->delete('/api/v1/categories/{id}', 'ApiV1CategoriesController@delete');

$router->get('/api/v1/tutorials', 'ApiV1TutorialsController@index');
$router->post('/api/v1/tutorials', 'ApiV1TutorialsController@store');
$router->get('/api/v1/tutorials/{id_or_slug}', 'ApiV1TutorialsController@show');
$router->delete('/api/v1/tutorials/{id}', 'ApiV1TutorialsController@delete');

$router->get('/api/v1/rss-sources', 'ApiV1RssController@index');
$router->post('/api/v1/rss-sources', 'ApiV1RssController@store');
$router->get('/api/v1/rss-feeds/pull', 'ApiV1RssController@pull');
$router->delete('/api/v1/rss-sources/{id}', 'ApiV1RssController@delete');

$router->post('/api/v1/ai/translate', 'ApiV1AiController@translate');
$router->get('/api/v1/ai/glossary', 'ApiV1AiController@glossary');
