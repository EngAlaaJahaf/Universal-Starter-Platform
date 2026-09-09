<?php

require_once __DIR__ . '/ApiV1BaseController.php';

class ApiV1DocsController extends ApiV1BaseController
{
    /**
     * GET /api/v1/schema or /api/v1/openapi.json
     * Machine-readable OpenAPI 3.0 specification for AI Agents / Tool Calling / GPT Actions!
     */
    public function schema()
    {
        $baseUrl = app_url();
        $schema = [
            'openapi' => '3.0.1',
            'info'    => [
                'title'       => 'Tech News Platform AI Agent REST API',
                'description' => 'Comprehensive REST API enabling autonomous AI agents to manage articles, fetch RSS feeds, auto-translate and publish news, control categories, and monitor analytics.',
                'version'     => '1.0.0',
            ],
            'servers' => [
                ['url' => rtrim($baseUrl, '/') . '/api/v1', 'description' => 'Production API Server']
            ],
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type'   => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Custom Token',
                        'description' => 'Enter your API Key with Bearer prefix: Bearer tnp_live_...'
                    ]
                ]
            ],
            'security' => [['BearerAuth' => []]],
            'paths' => [
                '/articles' => [
                    'get' => [
                        'summary' => 'List articles with filtering and pagination',
                        'parameters' => [
                            ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                            ['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 15]],
                            ['name' => 'q', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'category_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['published', 'draft', 'archived']]],
                        ],
                        'responses' => ['200' => ['description' => 'Articles list']]
                    ],
                    'post' => [
                        'summary' => 'Create a new article',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['title'],
                                        'properties' => [
                                            'title'          => ['type' => 'string', 'description' => 'Main Arabic Title'],
                                            'content'        => ['type' => 'string', 'description' => 'Full article content HTML/Text'],
                                            'excerpt'        => ['type' => 'string', 'description' => 'Executive summary (TL;DR)'],
                                            'category_id'    => ['type' => 'integer', 'default' => 1],
                                            'status'         => ['type' => 'string', 'enum' => ['published', 'draft']],
                                            'featured_image' => ['type' => 'string', 'description' => 'Image URL'],
                                            'source_name'    => ['type' => 'string', 'description' => 'Original Source Name'],
                                            'source_url'     => ['type' => 'string', 'description' => 'Original Source URL'],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => ['201' => ['description' => 'Article created']]
                    ]
                ],
                '/articles/ai-publish' => [
                    'post' => [
                        'summary' => 'Rapid AI Ingestion: Translates English source text and publishes directly',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['title_en'],
                                        'properties' => [
                                            'title_en'       => ['type' => 'string', 'description' => 'English news headline'],
                                            'content_en'     => ['type' => 'string', 'description' => 'English article body'],
                                            'source_name'    => ['type' => 'string', 'description' => 'e.g. TechCrunch / The Verge'],
                                            'source_url'     => ['type' => 'string', 'description' => 'Direct link to source'],
                                            'featured_image' => ['type' => 'string', 'description' => 'Featured image URL'],
                                            'category_id'    => ['type' => 'integer', 'default' => 1]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => ['201' => ['description' => 'Article translated and published']]
                    ]
                ],
                '/ai/translate' => [
                    'post' => [
                        'summary' => 'Transform English tech news into high-grade journalistic Arabic',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['title'],
                                        'properties' => [
                                            'title'   => ['type' => 'string'],
                                            'content' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => ['200' => ['description' => 'Arabic translation result']]
                    ]
                ],
                '/rss-feeds/pull' => [
                    'get' => [
                        'summary' => 'Pull raw RSS items from curated tech sources for AI ingestion',
                        'parameters' => [
                            ['name' => 'source_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'url', 'in' => 'query', 'schema' => ['type' => 'string']]
                        ],
                        'responses' => ['200' => ['description' => 'Parsed live RSS items']]
                    ]
                ],
                '/tutorials' => [
                    'get' => [
                        'summary' => 'List step-by-step tutorials with step counts and difficulty',
                        'parameters' => [
                            ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                            ['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 15]],
                            ['name' => 'q', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'difficulty', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['beginner', 'intermediate', 'advanced']]],
                        ],
                        'responses' => ['200' => ['description' => 'Tutorials list']]
                    ],
                    'post' => [
                        'summary' => 'Create a step-by-step tutorial with nested steps and code snippets',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['title'],
                                        'properties' => [
                                            'title'             => ['type' => 'string'],
                                            'summary'           => ['type' => 'string'],
                                            'difficulty'        => ['type' => 'string', 'enum' => ['beginner', 'intermediate', 'advanced']],
                                            'estimated_minutes' => ['type' => 'integer'],
                                            'featured_image'    => ['type' => 'string'],
                                            'steps'             => [
                                                'type'  => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'title'         => ['type' => 'string'],
                                                        'content'       => ['type' => 'string'],
                                                        'image_url'     => ['type' => 'string'],
                                                        'image_caption' => ['type' => 'string'],
                                                        'code_snippet'  => ['type' => 'string'],
                                                        'code_language' => ['type' => 'string'],
                                                        'callout_type'  => ['type' => 'string', 'enum' => ['none', 'tip', 'warning', 'important', 'note']],
                                                        'callout_text'  => ['type' => 'string']
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'responses' => ['201' => ['description' => 'Tutorial created']]
                    ]
                ],
                '/tutorials/{id}' => [
                    'get' => [
                        'summary' => 'Retrieve a single tutorial with all structured steps, photos, codes, and callouts',
                        'responses' => ['200' => ['description' => 'Full tutorial details with steps']]
                    ]
                ],
                '/stats' => [
                    'get' => [
                        'summary' => 'Get platform analytics and counts',
                        'responses' => ['200' => ['description' => 'Platform metrics']]
                    ]
                ]
            ]
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
