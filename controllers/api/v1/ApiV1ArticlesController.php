<?php

require_once __DIR__ . '/ApiV1BaseController.php';
require_once __DIR__ . '/../../../core/AiTranslator.php';

class ApiV1ArticlesController extends ApiV1BaseController
{
    /**
     * GET /api/v1/articles
     */
    public function index()
    {
        $this->requireScope('articles:read');
        $db = new Database();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = min(50, max(1, (int) ($_GET['limit'] ?? 15)));
        $offset = ($page - 1) * $limit;

        $q = trim((string) ($_GET['q'] ?? ''));
        $categoryId = (int) ($_GET['category_id'] ?? 0);
        $status = trim((string) ($_GET['status'] ?? ''));

        $where = [];
        $params = [];

        if ($q !== '') {
            $where[] = '(a.title LIKE :q OR a.excerpt LIKE :q OR a.content LIKE :q)';
            $params[':q'] = "%{$q}%";
        }
        if ($categoryId > 0) {
            $where[] = 'a.category_id = :cat';
            $params[':cat'] = $categoryId;
        }
        if ($status !== '') {
            $where[] = 'a.status = :status';
            $params[':status'] = $status;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = $db->fetch("SELECT COUNT(*) as count FROM articles a {$whereSql}", $params)['count'] ?? 0;
        
        $sql = "SELECT a.id, a.title, a.title_ar, a.title_en, a.slug, a.excerpt, a.featured_image, 
                       a.category_id, c.name as category_name, a.status, a.source_name, a.source_url, 
                       a.views_count, a.shares_count, a.published_at, a.created_at
                FROM articles a 
                LEFT JOIN categories c ON c.id = a.category_id 
                {$whereSql} 
                ORDER BY a.id DESC 
                LIMIT {$limit} OFFSET {$offset}";

        $articles = $db->fetchAll($sql, $params);

        $this->jsonSuccess($articles, [
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $limit,
                'total_items'  => (int) $total,
                'total_pages'  => ceil($total / $limit)
            ]
        ]);
    }

    /**
     * GET /api/v1/articles/{id}
     */
    public function show($id)
    {
        $this->requireScope('articles:read');
        $db = new Database();

        $article = $db->fetch("
            SELECT a.*, c.name as category_name, u.username as author_name 
            FROM articles a 
            LEFT JOIN categories c ON c.id = a.category_id 
            LEFT JOIN users u ON u.id = a.author_id 
            WHERE a.id = :id OR a.slug = :slug
        ", [':id' => (int) $id, ':slug' => $id]);

        if (!$article) {
            $this->jsonError('Article not found.', 404);
        }

        $this->jsonSuccess($article);
    }

    /**
     * POST /api/v1/articles
     * Full article creation
     */
    public function store()
    {
        $this->requireScope('articles:write');
        $body = $this->getJsonBody();

        $title = trim($body['title'] ?? ($body['title_ar'] ?? ''));
        if (empty($title)) {
            $this->jsonError('Field "title" or "title_ar" is required.', 422);
        }

        $content = $body['content'] ?? ($body['content_ar'] ?? '');
        $excerpt = trim($body['excerpt'] ?? mb_strimwidth(strip_tags($content), 0, 200, '…', 'UTF-8'));
        $status = in_array($body['status'] ?? '', ['published', 'draft', 'archived']) ? $body['status'] : 'published';
        $featuredImage = trim($body['featured_image'] ?? '');
        $sourceName = trim($body['source_name'] ?? '');
        $sourceUrl = trim($body['source_url'] ?? '');

        $db = new Database();
        
        // Validate category
        $categoryId = (int) ($body['category_id'] ?? 0);
        if ($categoryId > 0) {
            $catCheck = $db->fetch("SELECT id FROM categories WHERE id = :id", [':id' => $categoryId]);
            if (!$catCheck) {
                $categoryId = get_default_category_id($db);
            }
        } else {
            $categoryId = get_default_category_id($db);
        }

        $slug = trim($body['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $title), '-'));
        }
        if (!$slug) $slug = 'news-' . time();

        $check = $db->fetch("SELECT id FROM articles WHERE slug = :slug", [':slug' => $slug]);
        if ($check) {
            $slug .= '-' . time();
        }

        $db->query("
            INSERT INTO articles 
            (title, title_ar, title_en, slug, excerpt, content, content_ar, content_en, featured_image, category_id, author_id, status, source_name, source_url, allow_comments, is_featured, published_at, reading_time_minutes, views_count, shares_count, created_at)
            VALUES 
            (:title, :title_ar, :title_en, :slug, :excerpt, :content, :content_ar, :content_en, :featured_image, :category_id, 1, :status, :source_name, :source_url, 1, :is_featured, CURRENT_TIMESTAMP, :reading_time_minutes, :views_count, :shares_count, CURRENT_TIMESTAMP)
        ", [
            ':title'                => $title,
            ':title_ar'             => $title,
            ':title_en'             => $body['title_en'] ?? null,
            ':slug'                 => $slug,
            ':excerpt'              => $excerpt,
            ':content'              => $content,
            ':content_ar'           => $content,
            ':content_en'           => $body['content_en'] ?? null,
            ':featured_image'       => $featuredImage ?: null,
            ':category_id'          => $categoryId,
            ':status'               => $status,
            ':source_name'          => $sourceName ?: null,
            ':source_url'           => $sourceUrl ?: null,
            ':is_featured'          => !empty($body['is_featured']) ? 1 : 0,
            ':reading_time_minutes' => (int) ($body['reading_time_minutes'] ?? 3) ?: 3,
            ':views_count'          => (int) ($body['views_count'] ?? 0),
            ':shares_count'         => (int) ($body['shares_count'] ?? 0),
        ]);

        $newId = $db->lastInsertId();
        $created = $db->fetch("SELECT * FROM articles WHERE id = :id", [':id' => $newId]);

        $this->jsonSuccess($created, ['message' => 'Article created successfully.'], 201);
    }

    /**
     * POST /api/v1/articles/ai-publish
     * Rapid AI Ingestion: Translates English source text and publishes directly!
     */
    public function aiPublish()
    {
        $this->requireScope('articles:write');
        $body = $this->getJsonBody();

        $englishTitle = trim($body['title_en'] ?? ($body['title'] ?? ''));
        $englishContent = trim($body['content_en'] ?? ($body['content'] ?? ($body['text'] ?? '')));
        $sourceUrl = trim($body['source_url'] ?? ($body['url'] ?? ''));
        $sourceName = trim($body['source_name'] ?? 'مصدر عالمي');
        $featuredImage = trim($body['featured_image'] ?? ($body['image'] ?? ''));

        if (empty($englishTitle)) {
            $this->jsonError('Field "title_en" or "title" is required for AI translation & publishing.', 422);
        }

        $db = new Database();

        // Validate category
        $categoryId = (int) ($body['category_id'] ?? 0);
        if ($categoryId > 0) {
            $catCheck = $db->fetch("SELECT id FROM categories WHERE id = :id", [':id' => $categoryId]);
            if (!$catCheck) {
                $firstCat = $db->fetch("SELECT id FROM categories ORDER BY id ASC LIMIT 1");
                $categoryId = $firstCat ? (int) $firstCat['id'] : null;
            }
        } else {
            $firstCat = $db->fetch("SELECT id FROM categories ORDER BY id ASC LIMIT 1");
            $categoryId = $firstCat ? (int) $firstCat['id'] : null;
        }

        // Run smart AI translation & re-authoring
        $aiResult = AiTranslator::translateArticle($englishTitle, $englishContent ?: $englishTitle);

        $arabicTitle = $aiResult['title_ar'] ?? $englishTitle;
        $arabicExcerpt = $aiResult['excerpt'] ?? '';
        $arabicContent = $aiResult['content_ar'] ?? "<p>{$englishContent}</p>";
        $readingTime = $aiResult['reading_time_minutes'] ?? 3;

        $db = new Database();
        $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $arabicTitle), '-'));
        if (!$slug) $slug = 'news-' . time();

        $check = $db->fetch("SELECT id FROM articles WHERE slug = :slug", [':slug' => $slug]);
        if ($check) {
            $slug .= '-' . time();
        }

        // Attach formal canonical source block
        if (!empty($sourceUrl)) {
            $arabicContent .= "<hr><div class='alert alert-light border my-3'><strong>📌 المصدر الأصلي والتقرير الكامل: </strong><a href='" . htmlspecialchars($sourceUrl, ENT_QUOTES, 'UTF-8') . "' target='_blank' rel='noopener noreferrer' class='fw-bold text-primary'>" . htmlspecialchars($sourceName, ENT_QUOTES, 'UTF-8') . " ↗</a></div>";
        }

        $db->query("
            INSERT INTO articles 
            (title, title_ar, title_en, slug, excerpt, content, content_ar, content_en, featured_image, category_id, author_id, status, source_name, source_url, allow_comments, published_at, reading_time_minutes, created_at)
            VALUES 
            (:title, :title_ar, :title_en, :slug, :excerpt, :content, :content_ar, :content_en, :featured_image, :category_id, 1, 'published', :source_name, :source_url, 1, CURRENT_TIMESTAMP, :reading_time_minutes, CURRENT_TIMESTAMP)
        ", [
            ':title'                => $arabicTitle,
            ':title_ar'             => $arabicTitle,
            ':title_en'             => $englishTitle,
            ':slug'                 => $slug,
            ':excerpt'              => $arabicExcerpt,
            ':content'              => $arabicContent,
            ':content_ar'           => $arabicContent,
            ':content_en'           => $englishContent,
            ':featured_image'       => $featuredImage ?: null,
            ':category_id'          => $categoryId,
            ':source_name'          => $sourceName,
            ':source_url'           => $sourceUrl ?: null,
            ':reading_time_minutes' => $readingTime
        ]);

        $newId = $db->lastInsertId();
        $created = $db->fetch("SELECT * FROM articles WHERE id = :id", [':id' => $newId]);

        $this->jsonSuccess($created, [
            'message'    => 'Article translated and published by AI successfully!',
            'ai_summary' => $arabicExcerpt,
            'public_url' => app_url('article/' . $slug)
        ], 201);
    }

    /**
     * PUT /api/v1/articles/{id}
     */
    public function update($id)
    {
        $this->requireScope('articles:write');
        $body = $this->getJsonBody();
        $db = new Database();

        $old = $db->fetch("SELECT * FROM articles WHERE id = :id", [':id' => (int) $id]);
        if (!$old) {
            $this->jsonError('Article not found.', 404);
        }

        $title = trim($body['title'] ?? ($body['title_ar'] ?? $old['title']));
        $content = $body['content'] ?? ($body['content_ar'] ?? $old['content']);
        $excerpt = trim($body['excerpt'] ?? $old['excerpt']);
        $categoryId = (int) ($body['category_id'] ?? $old['category_id']);
        $status = in_array($body['status'] ?? '', ['published', 'draft', 'archived']) ? $body['status'] : $old['status'];
        $featuredImage = $body['featured_image'] ?? $old['featured_image'];
        $sourceName = $body['source_name'] ?? $old['source_name'];
        $sourceUrl = $body['source_url'] ?? $old['source_url'];

        $db->query("
            UPDATE articles SET 
            title = :title, title_ar = :title_ar, title_en = :title_en, excerpt = :excerpt, 
            content = :content, content_ar = :content_ar, content_en = :content_en, 
            featured_image = :featured_image, category_id = :category_id, status = :status, 
            source_name = :source_name, source_url = :source_url, views_count = :views_count, 
            shares_count = :shares_count, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ", [
            ':title'          => $title,
            ':title_ar'       => $title,
            ':title_en'       => $body['title_en'] ?? $old['title_en'],
            ':excerpt'        => $excerpt,
            ':content'        => $content,
            ':content_ar'     => $content,
            ':content_en'     => $body['content_en'] ?? $old['content_en'],
            ':featured_image' => $featuredImage ?: null,
            ':category_id'    => $categoryId,
            ':status'         => $status,
            ':source_name'    => $sourceName ?: null,
            ':source_url'     => $sourceUrl ?: null,
            ':views_count'    => (int) ($body['views_count'] ?? $old['views_count']),
            ':shares_count'   => (int) ($body['shares_count'] ?? $old['shares_count']),
            ':id'             => (int) $id
        ]);

        $updated = $db->fetch("SELECT * FROM articles WHERE id = :id", [':id' => (int) $id]);
        $this->jsonSuccess($updated, ['message' => 'Article updated successfully.']);
    }

    /**
     * DELETE /api/v1/articles/{id}
     */
    public function delete($id)
    {
        $this->requireScope('articles:delete');
        $db = new Database();

        $old = $db->fetch("SELECT id, title FROM articles WHERE id = :id", [':id' => (int) $id]);
        if (!$old) {
            $this->jsonError('Article not found.', 404);
        }

        $db->query("DELETE FROM articles WHERE id = :id", [':id' => (int) $id]);
        $this->jsonSuccess(['deleted_id' => (int) $id, 'title' => $old['title']], ['message' => 'Article deleted successfully.']);
    }
}
