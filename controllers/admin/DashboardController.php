<?php

class DashboardController extends AdminController
{
 public function index()
 {
 $this->requirePermission('dashboard.view');
 $db = Database::getInstance();

 // 1. KPI Stats
 $totalArticles = (int) $db->fetch("SELECT COUNT(*) as c FROM articles")['c'];
 $publishedArticles = (int) $db->fetch("SELECT COUNT(*) as c FROM articles WHERE status = 'published'")['c'];
 $totalViews = (int) ($db->fetch("SELECT SUM(views_count) as s FROM articles")['s'] ?? 0);
 $totalUsers = (int) $db->fetch("SELECT COUNT(*) as c FROM users")['c'];
 $pendingComments = (int) $db->fetch("SELECT COUNT(*) as c FROM comments WHERE status = 'pending'")['c'];
 $totalSubscribers = (int) $db->fetch("SELECT COUNT(*) as c FROM newsletters WHERE status = 'active'")['c'];
 $activeLiveBlogs = (int) $db->fetch("SELECT COUNT(*) as c FROM live_blogs WHERE status = 'active'")['c'];

 // 2. Recent Articles
 $recentArticles = $db->fetchAll("
 SELECT a.id, a.title, a.slug, a.status, a.views_count, a.created_at, c.name as category_name
 FROM articles a
 LEFT JOIN categories c ON a.category_id = c.id
 ORDER BY a.created_at DESC
 LIMIT 5
 ");

 // 3. Pending Comments
 $recentComments = $db->fetchAll("
 SELECT c.*, a.title as article_title, a.slug as article_slug, u.username
 FROM comments c
 JOIN articles a ON c.article_id = a.id
 LEFT JOIN users u ON c.user_id = u.id
 WHERE c.status = 'pending'
 ORDER BY c.created_at DESC
 LIMIT 5
 ");

 // 4. Recent Users
 $recentUsers = $db->fetchAll("
 SELECT u.id, u.username, u.email, u.status, u.created_at, r.name_ar as role_name
 FROM users u
 LEFT JOIN roles r ON u.role_id = r.id
 ORDER BY u.created_at DESC
 LIMIT 5
 ");

 // 5. Recent Activity Logs
 $recentLogs = $db->fetchAll("
 SELECT al.*, u.username
 FROM activity_logs al
 LEFT JOIN users u ON al.user_id = u.id
 ORDER BY al.created_at DESC
 LIMIT 6
 ");

 $this->renderAdmin('admin/dashboard', [
 'stats' => [
 'articles' => $totalArticles,
 'published' => $publishedArticles,
 'views' => $totalViews,
 'users' => $totalUsers,
 'pending_comments' => $pendingComments,
 'subscribers' => $totalSubscribers,
 'active_live_blogs' => $activeLiveBlogs
 ],
 'recentArticles' => $recentArticles,
 'recentComments' => $recentComments,
 'recentUsers' => $recentUsers,
 'recentLogs' => $recentLogs
 ]);
 }
}
