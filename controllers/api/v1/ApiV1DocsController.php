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
        $schema = OpenApiGenerator::fromRoutes(
            Router::current() ? Router::current()->routes() : array(),
            array('/api/v1/market-pulse', '/api/v1/schema', '/api/v1/openapi.json'),
            app_url()
        );
        header('Content-Type: application/json; charset=utf-8');
        Cors::handle(true);
        echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
