<?php

class ArticlesApiController
{
    public function index()
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = Database::getInstance();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $stmt = $db->prepare("
            SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image, a.views_count,
                   a.published_at, c.name as category_name, u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published'
            ORDER BY a.published_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'page'    => $page,
            'data'    => $articles
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function show($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT a.*, c.name as category_name, u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE (a.id = ? OR a.slug = ?) AND a.status = 'published'
        ");
        $stmt->execute([$id, $id]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'المقال غير موجود'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(['success' => true, 'data' => $article], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
