<?php

require_once __DIR__ . '/ApiV1BaseController.php';

class ApiV1StatsController extends ApiV1BaseController
{
    /**
     * GET /api/v1/stats
     */
    public function index()
    {
        $this->requireScope('stats:read');
        $db = new Database();

        $articlesCount = $db->fetch("SELECT COUNT(*) as c FROM articles")['c'] ?? 0;
        $publishedCount = $db->fetch("SELECT COUNT(*) as c FROM articles WHERE status = 'published'")['c'] ?? 0;
        $totalViews = $db->fetch("SELECT SUM(views_count) as s FROM articles")['s'] ?? 0;
        $totalShares = $db->fetch("SELECT SUM(shares_count) as s FROM articles")['s'] ?? 0;
        $commentsCount = $db->fetch("SELECT COUNT(*) as c FROM comments")['c'] ?? 0;
        $usersCount = $db->fetch("SELECT COUNT(*) as c FROM users")['c'] ?? 0;
        $sourcesCount = $db->fetch("SELECT COUNT(*) as c FROM rss_sources")['c'] ?? 0;

        $topArticles = $db->fetchAll("SELECT id, title, views_count, shares_count FROM articles ORDER BY views_count DESC LIMIT 5");

        $this->jsonSuccess([
            'metrics' => [
                'total_articles'     => (int) $articlesCount,
                'published_articles' => (int) $publishedCount,
                'total_views'        => (int) $totalViews,
                'total_shares'       => (int) $totalShares,
                'total_comments'     => (int) $commentsCount,
                'total_users'        => (int) $usersCount,
                'total_rss_sources'  => (int) $sourcesCount,
            ],
            'top_trending_articles' => $topArticles
        ]);
    }
}
