<?php

class CommentsController extends AdminController
{
 public function index()
 {
 $db = Database::getInstance();

 // 1. Calculate counts for all statuses
 $countsStmt = $db->query("
 SELECT status, COUNT(*) as total 
 FROM comments 
 GROUP BY status
 ");
 $rawCounts = $countsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
 $counts = [
 'all' => array_sum($rawCounts),
 'pending' => (int) ($rawCounts['pending'] ?? 0),
 'approved' => (int) ($rawCounts['approved'] ?? 0),
 'rejected' => (int) ($rawCounts['rejected'] ?? 0),
 'spam' => (int) ($rawCounts['spam'] ?? 0),
 ];

 // 2. Determine active status tab
 if (isset($_GET['status'])) {
 $status = $_GET['status'];
 } else {
 $status = ($counts['pending'] > 0) ? 'pending' : 'all';
 }

 // 3. Query comments
 if ($status === 'all') {
 $stmt = $db->prepare("
 SELECT c.*, a.title as article_title, a.slug as article_slug, u.username
 FROM comments c
 JOIN articles a ON c.article_id = a.id
 LEFT JOIN users u ON c.user_id = u.id
 ORDER BY c.created_at DESC
 ");
 $stmt->execute();
 } else {
 $stmt = $db->prepare("
 SELECT c.*, a.title as article_title, a.slug as article_slug, u.username
 FROM comments c
 JOIN articles a ON c.article_id = a.id
 LEFT JOIN users u ON c.user_id = u.id
 WHERE c.status = ?
 ORDER BY c.created_at DESC
 ");
 $stmt->execute([$status]);
 }
 $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/comments/index', [
 'comments' => $comments,
 'status' => $status,
 'counts' => $counts
 ]);
 }

 public function approve($id)
 {
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $this->postGuard();
 }
 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('approve', 'comment', $id, "اعتماد التعليق رقم: {$id}");
 Session::flash('success', 'تم اعتماد التعليق ونشره بنجاح.');
 header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url('admin/comments?status=pending')));
 exit;
 }

 public function reject($id)
 {
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $this->postGuard();
 }
 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('reject', 'comment', $id, "رفض التعليق رقم: {$id}");
 Session::flash('success', 'تم رفض التعليق.');
 header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url('admin/comments?status=pending')));
 exit;
 }

 public function spam($id)
 {
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $this->postGuard();
 }
 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE comments SET status = 'spam' WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('spam', 'comment', $id, "تعيين التعليق كـ سبام: {$id}");
 Session::flash('success', 'تم تصنيف التعليق كـ سبام.');
 header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url('admin/comments?status=pending')));
 exit;
 }

 public function delete($id)
 {
 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 $this->postGuard();
 }
 $db = Database::getInstance();
 $stmt = $db->prepare("DELETE FROM comments WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('delete', 'comment', $id, "حذف التعليق رقم: {$id}");
 Session::flash('success', 'تم حذف التعليق نهائياً.');
 header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url('admin/comments')));
 exit;
 }
}
