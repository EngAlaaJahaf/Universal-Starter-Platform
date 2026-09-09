<?php

class AnalyticsController extends AdminController
{
 public function index()
 {
 $db = Database::getInstance();

 // High level overview stats (Real Database Counts)
 $totalArticles = (int) $db->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
 $totalViews = (int) ($db->query("SELECT SUM(views_count) FROM articles")->fetchColumn() ?: 0);
 $totalShares = (int) ($db->query("SELECT SUM(shares_count) FROM articles")->fetchColumn() ?: 0);
 $totalUsers = (int) $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
 $totalComments = (int) $db->query("SELECT COUNT(*) FROM comments WHERE status = 'approved'")->fetchColumn();
 $totalSubscribers = (int) $db->query("SELECT COUNT(*) FROM newsletters WHERE status = 'active'")->fetchColumn();
 $totalLiveUpdates = (int) $db->query("SELECT COUNT(*) FROM live_blog_entries")->fetchColumn();

 // Views per category
 $categoryViewsStmt = $db->query("
 SELECT c.name, COALESCE(SUM(a.views_count), 0) as total_views, COUNT(a.id) as article_count
 FROM categories c
 LEFT JOIN articles a ON a.category_id = c.id
 GROUP BY c.id, c.name
 ORDER BY total_views DESC
 ");
 $categoryStats = $categoryViewsStmt->fetchAll(PDO::FETCH_ASSOC);

 // Top 10 Most Viewed Articles
 $topArticlesStmt = $db->query("
 SELECT a.id, a.title, a.slug, a.views_count, a.shares_count, a.status, a.created_at,
 c.name as category_name, u.username as author_name
 FROM articles a
 LEFT JOIN categories c ON a.category_id = c.id
 LEFT JOIN users u ON a.author_id = u.id
 ORDER BY a.views_count DESC
 LIMIT 10
 ");
 $topArticles = $topArticlesStmt->fetchAll(PDO::FETCH_ASSOC);

 // Recent Audit Activity
 $recentActivity = $db->query("
 SELECT * FROM activity_logs ORDER BY id DESC LIMIT 6
 ")->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/analytics/index', [
 'totalArticles' => $totalArticles,
 'totalViews' => $totalViews,
 'totalShares' => $totalShares,
 'totalUsers' => $totalUsers,
 'totalComments' => $totalComments,
 'totalSubscribers' => $totalSubscribers,
 'totalLiveUpdates' => $totalLiveUpdates,
 'categoryStats' => $categoryStats,
 'topArticles' => $topArticles,
 'recentActivity' => $recentActivity,
 ]);
 }
}
