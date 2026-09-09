<?php

class ArticlesController extends AdminController
{
 public function index()
 {
 $this->guardAdmin();
 $db = new Database();
 $q = trim((string) ($_GET['q'] ?? ''));
 $status = trim((string) ($_GET['status'] ?? ''));
 $where = array();
 $params = array();

 if ($q !== '') {
 $where[] = '(a.title LIKE :q OR a.slug LIKE :q)';
 $params[':q'] = "%{$q}%";
 }
 if ($status !== '') {
 $where[] = 'a.status = :status';
 $params[':status'] = $status;
 }

 $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
 $sql = "SELECT a.*, c.name as category_name, u.username as author_name 
 FROM articles a 
 LEFT JOIN categories c ON c.id = a.category_id 
 LEFT JOIN users u ON u.id = a.author_id 
 {$whereSql} 
 ORDER BY a.id DESC LIMIT 100";
 $articles = $db->fetchAll($sql, $params);

 $this->view('admin/articles/index', array(
 'articles' => $articles,
 'q' => $q,
 'status' => $status,
 ));
 }

 public function create()
 {
 $this->guardAdmin();
 $db = new Database();
 $categories = $db->fetchAll('SELECT id, name FROM categories ORDER BY name');
 $this->view('admin/articles/form', array('categories' => $categories, 'mode' => 'create'));
 }

 public function store()
 {
 $this->postGuard();
 $data = Sanitizer::cleanArray($_POST);
 $slug = $this->slug($data['slug'] ?: $data['title']);
 $db = new Database();
 
 $categoryId = (int) ($data['category_id'] ?? 0);
 if ($categoryId <= 0) {
 $categoryId = get_default_category_id($db);
 }

 $db->query('INSERT INTO articles (title,title_ar,title_en,slug,content,content_ar,content_en,excerpt,featured_image,category_id,author_id,status,article_type,allow_comments,is_featured,is_premium,source_name,source_url,published_at,reading_time_minutes,views_count,shares_count) VALUES (:title,:title_ar,:title_en,:slug,:content,:content_ar,:content_en,:excerpt,:featured_image,:category_id,:author_id,:status,:article_type,:allow_comments,:is_featured,:is_premium,:source_name,:source_url,:published_at,:reading_time_minutes,:views_count,:shares_count)', array(
 ':title' => $data['title'] ?? '',
 ':title_ar' => $data['title_ar'] ?? ($data['title'] ?? ''),
 ':title_en' => $data['title_en'] ?? null,
 ':slug' => $slug,
 ':content' => $data['content'] ?? '',
 ':content_ar' => $data['content_ar'] ?? ($data['content'] ?? ''),
 ':content_en' => $data['content_en'] ?? null,
 ':excerpt' => $data['excerpt'] ?? null,
 ':featured_image' => $data['featured_image'] ?? null,
 ':category_id' => $categoryId,
 ':author_id' => Auth::user()['id'],
 ':status' => $data['status'] ?? 'draft',
 ':article_type' => $data['article_type'] ?? 'standard',
 ':allow_comments' => empty($data['allow_comments']) ? 0 : 1,
 ':is_featured' => empty($data['is_featured']) ? 0 : 1,
 ':is_premium' => empty($data['is_premium']) ? 0 : 1,
 ':source_name' => $data['source_name'] ?? null,
 ':source_url' => $data['source_url'] ?? null,
 ':published_at' => storage_datetime($data['published_at'] ?? ''),
 ':reading_time_minutes' => isset($data['reading_time_minutes']) && (int) $data['reading_time_minutes'] > 0 ? (int) $data['reading_time_minutes'] : $this->readingTime($data['content'] ?? ''),
 ':views_count' => isset($data['views_count']) ? max(0, (int) $data['views_count']) : 0,
 ':shares_count' => isset($data['shares_count']) ? max(0, (int) $data['shares_count']) : 0,
 ));
 $id = $db->lastInsertId();
 $this->audit('create', 'article', $id, null, $data);
 return $this->redirect('admin/articles');
 }

 public function edit($id)
 {
 $this->guardAdmin();
 $db = new Database();
 $article = $db->fetch('SELECT * FROM articles WHERE id=:id', array(':id' => (int) $id));
 if (!$article) {
 http_response_code(404);
 return $this->view('errors/404');
 }
 $this->view('admin/articles/form', array(
 'article' => $article,
 'categories' => $db->fetchAll('SELECT id,name FROM categories ORDER BY name'),
 'mode' => 'edit'
 ));
 }

 public function update($id)
 {
 $this->postGuard();
 $db = new Database();
 $old = $db->fetch('SELECT * FROM articles WHERE id=:id', array(':id' => (int) $id));
 if (!$old) {
 return $this->redirect('admin/articles');
 }
 $data = Sanitizer::cleanArray($_POST);
 $slug = $this->slug($data['slug'] ?: $data['title']);

 $categoryId = (int) ($data['category_id'] ?? 0);
 if ($categoryId <= 0) {
 $categoryId = get_default_category_id($db);
 }

 $db->query('UPDATE articles SET title=:title,title_ar=:title_ar,title_en=:title_en,slug=:slug,content=:content,content_ar=:content_ar,content_en=:content_en,excerpt=:excerpt,featured_image=:featured_image,category_id=:category_id,status=:status,article_type=:article_type,allow_comments=:allow_comments,is_featured=:is_featured,is_premium=:is_premium,source_name=:source_name,source_url=:source_url,published_at=:published_at,reading_time_minutes=:reading_time_minutes,views_count=:views_count,shares_count=:shares_count,updated_at=CURRENT_TIMESTAMP WHERE id=:id', array(
 ':title' => $data['title'] ?? '',
 ':title_ar' => $data['title_ar'] ?? ($data['title'] ?? ''),
 ':title_en' => $data['title_en'] ?? null,
 ':slug' => $slug,
 ':content' => $data['content'] ?? '',
 ':content_ar' => $data['content_ar'] ?? ($data['content'] ?? ''),
 ':content_en' => $data['content_en'] ?? null,
 ':excerpt' => $data['excerpt'] ?? null,
 ':featured_image' => $data['featured_image'] ?? null,
 ':category_id' => $categoryId,
 ':status' => $data['status'] ?? 'draft',
 ':article_type' => $data['article_type'] ?? 'standard',
 ':allow_comments' => empty($data['allow_comments']) ? 0 : 1,
 ':is_featured' => empty($data['is_featured']) ? 0 : 1,
 ':is_premium' => empty($data['is_premium']) ? 0 : 1,
 ':source_name' => $data['source_name'] ?? null,
 ':source_url' => $data['source_url'] ?? null,
 ':published_at' => storage_datetime($data['published_at'] ?? ''),
 ':reading_time_minutes' => isset($data['reading_time_minutes']) && (int) $data['reading_time_minutes'] > 0 ? (int) $data['reading_time_minutes'] : $this->readingTime($data['content'] ?? ''),
 ':views_count' => isset($data['views_count']) ? max(0, (int) $data['views_count']) : (int) ($old['views_count'] ?? 0),
 ':shares_count' => isset($data['shares_count']) ? max(0, (int) $data['shares_count']) : (int) ($old['shares_count'] ?? 0),
 ':id' => (int) $id
 ));
 $this->audit('update', 'article', $id, $old, $data);
 return $this->redirect('admin/articles');
 }

 public function delete($id)
 {
 $this->postGuard();
 $db = new Database();
 $old = $db->fetch('SELECT * FROM articles WHERE id=:id', array(':id' => (int) $id));
 $db->query('UPDATE articles SET status=\'archived\' WHERE id=:id', array(':id' => (int) $id));
 $this->audit('archive', 'article', $id, $old, null);
 return $this->redirect('admin/articles');
 }

 public function toggleFeatured($id)
 {
 $this->postGuard();
 $db = new Database();
 $db->query('UPDATE articles SET is_featured=1-is_featured WHERE id=:id', array(':id' => (int) $id));
 $this->audit('toggle_featured', 'article', $id);
 return $this->redirect('admin/articles');
 }

 public function bulkAction()
 {
 $this->postGuard();
 $ids = array_map('intval', (array) ($_POST['ids'] ?? array()));
 $action = $_POST['action'] ?? '';
 if ($ids && !in_array($action, array('publish', 'archive', 'delete'), true)) {
 $action = '';
 }
 if ($ids && $action) {
 $status = $action === 'publish' ? 'published' : 'archived';
 $marks = implode(',', array_fill(0, count($ids), '?'));
 $db = new Database();
 $db->query('UPDATE articles SET status=? WHERE id IN (' . $marks . ')', array_merge(array($status), $ids));
 $this->audit('bulk_' . $action, 'article', null, null, array('ids' => $ids));
 }
 return $this->redirect('admin/articles');
 }

 /**
 * أرشفة أو حذف الأخبار القديمة مجمّعة وفق نطاق تاريخ
 * POST: action=bulk-date-archive | bulk-date-delete، date_from و/أو date_to = YYYY-MM-DD
 */
 public function bulkExpire()
 {
 $this->postGuard();
 $action = $_POST['action'] ?? '';
 $dateFrom = trim((string) ($_POST['date_from'] ?? ''));
 $dateTo = trim((string) ($_POST['date_to'] ?? ''));
 $scope = $_POST['scope'] ?? 'all'; // all | news

 $allowedActions = ['bulk-date-archive', 'bulk-date-delete'];
 if (!in_array($action, $allowedActions, true)) {
 Session::flash('error', 'إجراء غير صالح.');
 return $this->redirect('admin/articles');
 }

 $conditions = [];
 $params = [];

 if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
 $conditions[] = 'COALESCE(a.published_at, a.created_at) <= :date_to';
 $params[':date_to'] = $dateTo . ' 23:59:59';
 }
 if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
 $conditions[] = 'COALESCE(a.published_at, a.created_at) >= :date_from';
 $params[':date_from'] = $dateFrom . ' 00:00:00';
 }

 if (empty($conditions)) {
 Session::flash('error', 'يرجى تحديد تاريخ (من) و/أو (إلى) لأرشفة/حذف الأخبار القديمة.');
 return $this->redirect('admin/articles');
 }

 if ($scope === 'news') {
 $conditions[] = "a.article_type IN ('breaking','standard','video')";
 }

 $where = implode(' AND ', $conditions);

 $statusFilter = "a.status = 'published'";
 if ($action === 'bulk-date-delete') {
 $statusFilter = '1=1';
 }

 $sql = "SELECT a.id, a.title FROM articles a WHERE {$statusFilter} AND {$where}";
 $stmt = (new Database())->prepare($sql);
 $stmt->execute($params);
 $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
 $count = count($rows);

 if ($count > 0) {
 $ids = array_column($rows, 'id');
 $marks = implode(',', array_fill(0, count($ids), '?'));
 $db = new Database();
 if ($action === 'bulk-date-archive') {
 $db->query("UPDATE articles SET status='archived', updated_at=CURRENT_TIMESTAMP WHERE id IN ($marks)", $ids);
 $this->audit('bulk_date_archive', 'article', null, null, ['count' => $count, 'to' => $dateTo, 'from' => $dateFrom]);
 Session::flash('success', "تمت أرشفة {$count} خبراً قديماً بنجاح.");
 } else {
 // حذف نهائي مع تنظيف السجلات المرتبطة
 $db->query("DELETE FROM reactions WHERE article_id IN ($marks)", $ids);
 $db->query("DELETE FROM comments WHERE article_id IN ($marks)", $ids);
 $db->query("DELETE FROM article_tags WHERE article_id IN ($marks)", $ids);
 $db->query("DELETE FROM bookmarks WHERE article_id IN ($marks)", $ids);
 $db->query("DELETE FROM articles WHERE id IN ($marks)", $ids);
 $this->audit('bulk_date_delete', 'article', null, null, ['count' => $count, 'to' => $dateTo, 'from' => $dateFrom]);
 Session::flash('success', "تم حذف {$count} خبراً قديماً نهائياً (مع تفاعلات وتعليقات ووسوم مرتبطة).");
 }
 } else {
 Session::flash('info', 'لا توجد أخبار قديمة مطابقة للنطاق المحدد.');
 }

 return $this->redirect('admin/articles');
 }

 public function translatePreview()
 {
 header('Content-Type: application/json; charset=utf-8');
 try {
 $this->postGuard();
 require_once __DIR__ . '/../../core/AiTranslator.php';

 $title = trim($_POST['title'] ?? '');
 $content = trim($_POST['content'] ?? '');

 if (empty($title)) {
 echo json_encode(['success' => false, 'error' => 'عنوان المقال مطلوب للترجمة.'], JSON_UNESCAPED_UNICODE);
 exit;
 }

 $result = AiTranslator::translateArticle($title, $content ?: $title);

 echo json_encode([
 'success' => true,
 'data' => $result
 ], JSON_UNESCAPED_UNICODE);
 exit;
 } catch (Throwable $e) {
 echo json_encode([
 'success' => false,
 'error' => 'تعذر إتمام الترجمة: ' . $e->getMessage()
 ], JSON_UNESCAPED_UNICODE);
 exit;
 }
 }

 public function quickUpdateCategory()
 {
 $this->guardAdmin();
 $this->postGuard();

 $articleId = (int) ($_POST['article_id'] ?? 0);
 $categoryId = (int) ($_POST['category_id'] ?? 0);

 if ($articleId <= 0 || $categoryId <= 0) {
 header('Content-Type: application/json; charset=utf-8');
 http_response_code(400);
 echo json_encode(['ok' => false, 'error' => 'بيانات الطلب غير صالحة']);
 exit;
 }

 $db = new Database();
 $cat = $db->fetch("SELECT id, name, slug FROM categories WHERE id = :id", [':id' => $categoryId]);
 if (!$cat) {
 header('Content-Type: application/json; charset=utf-8');
 http_response_code(404);
 echo json_encode(['ok' => false, 'error' => 'التصنيف المطلوب غير موجود']);
 exit;
 }

 $db->query("UPDATE articles SET category_id = :cid WHERE id = :aid", [
 ':cid' => $categoryId,
 ':aid' => $articleId
 ]);

 $badgeClass = 'bg-primary-subtle text-primary';
 if ($cat['slug'] === 'artificial-intelligence') $badgeClass = 'bg-purple-subtle text-purple';
 elseif ($cat['slug'] === 'cybersecurity') $badgeClass = 'bg-danger-subtle text-danger';
 elseif ($cat['slug'] === 'hardware-devices') $badgeClass = 'bg-info-subtle text-info';
 elseif ($cat['slug'] === 'software-development') $badgeClass = 'bg-success-subtle text-success';

 header('Content-Type: application/json; charset=utf-8');
 echo json_encode([
 'ok' => true,
 'article_id' => $articleId,
 'category_id' => $categoryId,
 'category_name' => $cat['name'],
 'category_slug' => $cat['slug'],
 'badge_class' => $badgeClass
 ], JSON_UNESCAPED_UNICODE);
 exit;
 }

 private function slug($value)
 {
 $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $value), '-'));
 return $slug ?: 'article-' . time();
 }

 private function readingTime($content)
 {
 $words = preg_split('/\s+/u', trim(strip_tags($content)));
 return max(1, (int) ceil(count($words) / 200));
 }
}
