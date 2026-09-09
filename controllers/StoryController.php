<?php

class StoryController extends Controller
{
    public function index()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT a.id, a.title, a.slug, a.excerpt, a.featured_image, a.created_at,
                   c.name as category_name, u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.status = 'published' AND a.featured_image IS NOT NULL AND a.featured_image != ''
            ORDER BY a.published_at DESC, a.created_at DESC
            LIMIT 10
        ");
        $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('stories/index', [
            'stories' => $stories
        ]);
    }
}
