<?php

require_once __DIR__ . '/ApiV1BaseController.php';

class ApiV1RssController extends ApiV1BaseController
{
    /**
     * GET /api/v1/rss-sources
     */
    public function index()
    {
        $this->requireScope('rss:read');
        $db = new Database();
        $sources = $db->fetchAll("
            SELECT s.*, c.name as category_name 
            FROM rss_sources s 
            LEFT JOIN categories c ON c.id = s.category_id 
            ORDER BY s.id ASC
        ");
        $this->jsonSuccess($sources);
    }

    /**
     * POST /api/v1/rss-sources
     */
    public function store()
    {
        $this->requireScope('rss:manage');
        $body = $this->getJsonBody();
        $name = trim($body['name'] ?? '');
        $url = trim($body['url'] ?? '');
        $categoryId = (int) ($body['category_id'] ?? 0) ?: null;

        if (empty($name) || empty($url)) {
            $this->jsonError('Fields "name" and "url" are required.', 422);
        }

        $db = new Database();
        $db->query("INSERT INTO rss_sources (name, url, category_id, auto_fetch) VALUES (:name, :url, :cat, 1)", [
            ':name' => $name,
            ':url'  => $url,
            ':cat'  => $categoryId
        ]);

        $id = $db->lastInsertId();
        $source = $db->fetch("SELECT * FROM rss_sources WHERE id = :id", [':id' => $id]);
        $this->jsonSuccess($source, ['message' => 'RSS Source created successfully.'], 201);
    }

    /**
     * DELETE /api/v1/rss-sources/{id}
     */
    public function delete($id)
    {
        $this->requireScope('rss:manage');
        $db = new Database();
        $db->query("DELETE FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
        $this->jsonSuccess(['deleted_id' => (int) $id], ['message' => 'RSS Source deleted successfully.']);
    }

    /**
     * GET /api/v1/rss-feeds/pull?source_id={id}
     * Live Pull for AI Agents to digest raw news items!
     */
    public function pull()
    {
        $this->requireScope('rss:read');
        $sourceId = (int) ($_GET['source_id'] ?? 0);
        $customUrl = trim((string) ($_GET['url'] ?? ''));

        $feedUrl = '';
        $sourceName = '';

        $db = new Database();
        if ($sourceId > 0) {
            $src = $db->fetch("SELECT * FROM rss_sources WHERE id = :id", [':id' => $sourceId]);
            if ($src) {
                $feedUrl = $src['url'];
                $sourceName = $src['name'];
            }
        } elseif (!empty($customUrl)) {
            $feedUrl = $customUrl;
            $sourceName = parse_url($customUrl, PHP_URL_HOST);
        }

        if (empty($feedUrl)) {
            $this->jsonError('Valid "source_id" or "url" query parameter required.', 400);
        }

        require_once __DIR__ . '/../../admin/AggregatorController.php';
        $aggregator = new AggregatorController();
        
        $ref = new ReflectionMethod('AggregatorController', 'fetchRss');
        $ref->setAccessible(true);
        $res = $ref->invoke($aggregator, $feedUrl);

        if (!$res['success']) {
            $this->jsonError($res['error'], 502);
        }

        $this->jsonSuccess($res['items'], [
            'source_name' => $sourceName,
            'source_url'  => $feedUrl,
            'count'       => count($res['items'])
        ]);
    }
}
