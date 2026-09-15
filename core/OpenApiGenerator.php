<?php

/**
 * Live OpenAPI 3.0.1 generator (SH-12a): introspects Router::routes() and
 * emits a machine-readable spec covering EVERY registered /api/v1 endpoint.
 *
 * Hand-authored operation detail (parameters, requestBody schemas) for the
 * core AI-agent endpoints is preserved verbatim via richSpec(); everything
 * else gets a generated skeleton. Public endpoints carry an empty security
 * requirement; every other operation requires the BearerAuth key.
 */
class OpenApiGenerator
{
    public static function fromRoutes(array $routes, array $publicPaths, $baseUrl)
    {
        $paths = array();
        foreach ($routes as $route) {
            if (strpos($route['pattern'], '/api/v1') !== 0) {
                continue;
            }
            // OpenAPI paths are relative to the /api/v1 server URL, so the
            // /api/v1 prefix is stripped from the emitted key.
            $rel = preg_replace('#^/api/v1#', '', $route['pattern']);
            $rel = $rel === '' ? '/' : $rel;
            $method  = strtolower((string) $route['method']);
            list($controller, $action) = explode('@', $route['handler'], 2);

            $op = self::richSpec($rel, $method);
            if ($op === null) {
                $op = array(
                    'operationId' => $action,
                    'summary'     => $action . ' (' . $controller . ')',
                    'tags'        => array(self::tagFor($rel)),
                    'responses'   => array(
                        '200' => array('description' => 'Successful response'),
                        '401' => array('description' => 'Unauthorized'),
                        '403' => array('description' => 'Forbidden')
                    )
                );
                if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $rel, $m)) {
                    foreach ($m[1] as $paramName) {
                        $op['parameters'][] = array(
                            'name'     => $paramName,
                            'in'       => 'path',
                            'required' => true,
                            'schema'   => array('type' => 'string')
                        );
                    }
                }
            }

            $op['security'] = in_array($route['pattern'], $publicPaths, true)
                ? array()
                : array(array('BearerAuth' => array()));

            $paths[$rel][$method] = $op;
        }
        ksort($paths);

        return array(
            'openapi'    => '3.0.1',
            'info'       => array(
                'title'       => 'SmartPlatform AI Agent REST API',
                'description' => 'Comprehensive REST API enabling autonomous AI agents to manage articles, fetch RSS feeds, auto-translate and publish news, control categories, and monitor analytics.',
                'version'     => '1.0.0'
            ),
            'servers'    => array(
                array('url' => rtrim((string) $baseUrl, '/') . '/api/v1', 'description' => 'Production API Server')
            ),
            'components' => array(
                'securitySchemes' => array(
                    'BearerAuth' => array(
                        'type'          => 'http',
                        'scheme'        => 'bearer',
                        'bearerFormat'  => 'Custom Token',
                        'description'   => 'Enter your API Key with Bearer prefix: Bearer tnp_live_...'
                    )
                )
            ),
            'paths'      => $paths
        );
    }

    /* ---------------------------------------------------------------- */

    private static function tagFor($relPath)
    {
        $seg = array_values(array_filter(explode('/', trim($relPath, '/'))));
        return isset($seg[0]) && $seg[0] !== '' ? $seg[0] : 'api';
    }

    /**
     * Verbatim rich operation specs (transplanted from ApiV1DocsController,
     * SH-12a). Returns null when no hand-authored detail exists for a pair.
     */
    private static function richSpec($pathKey, $method)
    {
        static $rich = null;
        if ($rich === null) {
            $rich = self::descriptions();
        }
        return $rich[$pathKey][$method] ?? null;
    }

    private static function descriptions()
    {
        $path = '/articles';
        $m = array(
            '/articles' => array(
                'get' => array(
                    'summary'    => 'List articles with filtering and pagination',
                    'parameters' => array(
                        array('name' => 'page', 'in' => 'query', 'schema' => array('type' => 'integer', 'default' => 1)),
                        array('name' => 'limit', 'in' => 'query', 'schema' => array('type' => 'integer', 'default' => 15)),
                        array('name' => 'q', 'in' => 'query', 'schema' => array('type' => 'string')),
                        array('name' => 'category_id', 'in' => 'query', 'schema' => array('type' => 'integer')),
                        array('name' => 'status', 'in' => 'query', 'schema' => array('type' => 'string', 'enum' => array('published', 'draft', 'archived'))),
                    ),
                    'responses'  => array('200' => array('description' => 'Articles list'))
                ),
                'post' => array(
                    'summary'     => 'Create a new article',
                    'requestBody' => array(
                        'required' => true,
                        'content'  => array(
                            'application/json' => array(
                                'schema' => array(
                                    'type'     => 'object',
                                    'required' => array('title'),
                                    'properties' => array(
                                        'title'          => array('type' => 'string', 'description' => 'Main Arabic Title'),
                                        'content'        => array('type' => 'string', 'description' => 'Full article content HTML/Text'),
                                        'excerpt'        => array('type' => 'string', 'description' => 'Executive summary (TL;DR)'),
                                        'category_id'    => array('type' => 'integer', 'default' => 1),
                                        'status'         => array('type' => 'string', 'enum' => array('published', 'draft')),
                                        'featured_image' => array('type' => 'string', 'description' => 'Image URL'),
                                        'source_name'    => array('type' => 'string', 'description' => 'Original Source Name'),
                                        'source_url'     => array('type' => 'string', 'description' => 'Original Source URL'),
                                    )
                                )
                            )
                        )
                    ),
                    'responses'   => array('201' => array('description' => 'Article created'))
                )
            ),
            '/articles/ai-publish' => array(
                'post' => array(
                    'summary'     => 'Rapid AI Ingestion: Translates English source text and publishes directly',
                    'requestBody' => array(
                        'required' => true,
                        'content'  => array(
                            'application/json' => array(
                                'schema' => array(
                                    'type'     => 'object',
                                    'required' => array('title_en'),
                                    'properties' => array(
                                        'title_en'       => array('type' => 'string', 'description' => 'English news headline'),
                                        'content_en'     => array('type' => 'string', 'description' => 'English article body'),
                                        'source_name'    => array('type' => 'string', 'description' => 'e.g. TechCrunch / The Verge'),
                                        'source_url'     => array('type' => 'string', 'description' => 'Direct link to source'),
                                        'featured_image' => array('type' => 'string', 'description' => 'Featured image URL'),
                                        'category_id'    => array('type' => 'integer', 'default' => 1)
                                    )
                                )
                            )
                        )
                    ),
                    'responses'   => array('201' => array('description' => 'Article translated and published'))
                )
            ),
            '/ai/translate' => array(
                'post' => array(
                    'summary'     => 'Transform English tech news into high-grade journalistic Arabic',
                    'requestBody' => array(
                        'required' => true,
                        'content'  => array(
                            'application/json' => array(
                                'schema' => array(
                                    'type'     => 'object',
                                    'required' => array('title'),
                                    'properties' => array(
                                        'title'   => array('type' => 'string'),
                                        'content' => array('type' => 'string')
                                    )
                                )
                            )
                        )
                    ),
                    'responses'   => array('200' => array('description' => 'Arabic translation result'))
                )
            ),
            '/rss-feeds/pull' => array(
                'get' => array(
                    'summary'    => 'Pull raw RSS items from curated tech sources for AI ingestion',
                    'parameters' => array(
                        array('name' => 'source_id', 'in' => 'query', 'schema' => array('type' => 'integer')),
                        array('name' => 'url', 'in' => 'query', 'schema' => array('type' => 'string'))
                    ),
                    'responses'  => array('200' => array('description' => 'Parsed live RSS items'))
                )
            ),
            '/tutorials' => array(
                'get' => array(
                    'summary'    => 'List step-by-step tutorials with step counts and difficulty',
                    'parameters' => array(
                        array('name' => 'page', 'in' => 'query', 'schema' => array('type' => 'integer', 'default' => 1)),
                        array('name' => 'limit', 'in' => 'query', 'schema' => array('type' => 'integer', 'default' => 15)),
                        array('name' => 'q', 'in' => 'query', 'schema' => array('type' => 'string')),
                        array('name' => 'difficulty', 'in' => 'query', 'schema' => array('type' => 'string', 'enum' => array('beginner', 'intermediate', 'advanced'))),
                    ),
                    'responses'  => array('200' => array('description' => 'Tutorials list'))
                ),
                'post' => array(
                    'summary'     => 'Create a step-by-step tutorial with nested steps and code snippets',
                    'requestBody' => array(
                        'required' => true,
                        'content'  => array(
                            'application/json' => array(
                                'schema' => array(
                                    'type'     => 'object',
                                    'required' => array('title'),
                                    'properties' => array(
                                        'title'             => array('type' => 'string'),
                                        'summary'           => array('type' => 'string'),
                                        'difficulty'        => array('type' => 'string', 'enum' => array('beginner', 'intermediate', 'advanced')),
                                        'estimated_minutes' => array('type' => 'integer'),
                                        'featured_image'    => array('type' => 'string'),
                                        'steps'             => array(
                                            'type'  => 'array',
                                            'items' => array(
                                                'type'     => 'object',
                                                'properties' => array(
                                                    'title'         => array('type' => 'string'),
                                                    'content'       => array('type' => 'string'),
                                                    'image_url'     => array('type' => 'string'),
                                                    'image_caption' => array('type' => 'string'),
                                                    'code_snippet'  => array('type' => 'string'),
                                                    'code_language' => array('type' => 'string'),
                                                    'callout_type'  => array('type' => 'string', 'enum' => array('none', 'tip', 'warning', 'important', 'note')),
                                                    'callout_text'  => array('type' => 'string')
                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    'responses'   => array('201' => array('description' => 'Tutorial created'))
                )
            ),
            '/tutorials/{id}' => array(
                'get' => array(
                    'summary'    => 'Retrieve a single tutorial with all structured steps, photos, codes, and callouts',
                    'responses'  => array('200' => array('description' => 'Full tutorial details with steps'))
                )
            ),
            '/stats' => array(
                'get' => array(
                    'summary'   => 'Get platform analytics and counts',
                    'responses' => array('200' => array('description' => 'Platform metrics'))
                )
            )
        );
        return $m;
    }
}