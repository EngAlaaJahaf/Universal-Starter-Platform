<?php

class SeriesController extends Controller
{
    public function index()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT s.*, COUNT(a.id) as articles_count
            FROM series s
            LEFT JOIN articles a ON a.series_id = s.id AND a.status = 'published'
            WHERE s.status = 'published'
            GROUP BY s.id
            ORDER BY s.created_at DESC
        ");
        $series = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('pages/series_index', [
            'seriesList' => $series
        ]);
    }

    public function show($slug)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM series WHERE slug = ? AND status = 'published'");
        $stmt->execute([$slug]);
        $series = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$series) {
            header("HTTP/1.0 404 Not Found");
            $this->render('errors/404');
            return;
        }

        $stmt = $db->prepare("
            SELECT a.*, c.name as category_name, c.slug as category_slug, u.username as author_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            LEFT JOIN users u ON a.author_id = u.id
            WHERE a.series_id = ? AND a.status = 'published'
            ORDER BY a.series_order ASC, a.created_at ASC
        ");
        $stmt->execute([$series['id']]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('pages/series_show', [
            'series'   => $series,
            'articles' => $articles
        ]);
    }
}
