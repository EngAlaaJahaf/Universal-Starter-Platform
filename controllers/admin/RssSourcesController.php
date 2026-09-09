<?php

class RssSourcesController extends AdminController
{
 public function index()
 {
 $this->guardAdmin();
 $db = new Database();

 $q = trim((string) ($_GET['q'] ?? ''));
 $catId = (int) ($_GET['category_id'] ?? 0);

 $where = [];
 $params = [];

 if ($q !== '') {
 $where[] = '(s.name LIKE :q OR s.url LIKE :q)';
 $params[':q'] = "%{$q}%";
 }
 if ($catId > 0) {
 $where[] = 's.category_id = :cat';
 $params[':cat'] = $catId;
 }

 $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
 $sql = "SELECT s.*, c.name as category_name 
 FROM rss_sources s 
 LEFT JOIN categories c ON c.id = s.category_id 
 {$whereSql} 
 ORDER BY s.id ASC";
 
 $sources = $db->fetchAll($sql, $params);
 $categories = $db->fetchAll('SELECT id, name FROM categories ORDER BY name ASC');

 $this->view('admin/rss_sources/index', [
 'sources' => $sources,
 'categories' => $categories,
 'q' => $q,
 'catId' => $catId
 ]);
 }

 public function create()
 {
 $this->guardAdmin();
 $db = new Database();
 $categories = $db->fetchAll('SELECT id, name FROM categories ORDER BY name ASC');
 $this->view('admin/rss_sources/form', [
 'categories' => $categories,
 'mode' => 'create'
 ]);
 }

 public function store()
 {
 $this->postGuard();
 $data = Sanitizer::cleanArray($_POST);
 $name = trim($data['name'] ?? '');
 $url = trim($data['url'] ?? '');
 $categoryId = (int) ($data['category_id'] ?? 0) ?: null;
 $autoFetch = empty($data['auto_fetch']) ? 0 : 1;

 if (empty($name) || empty($url)) {
 Session::flash('error', 'اسم المصدر ورابط الـ RSS مطلوبان.');
 return $this->redirect('admin/rss-sources/create');
 }

 $db = new Database();
 $db->query("INSERT INTO rss_sources (name, url, category_id, auto_fetch) VALUES (:name, :url, :category_id, :auto_fetch)", [
 ':name' => $name,
 ':url' => $url,
 ':category_id' => $categoryId,
 ':auto_fetch' => $autoFetch
 ]);

 $id = $db->lastInsertId();
 $this->audit('create', 'rss_source', $id, null, $data);
 Session::flash('success', "تمت إضافة المصدر التقني \"{$name}\" بنجاح.");
 return $this->redirect('admin/rss-sources');
 }

 public function edit($id)
 {
 $this->guardAdmin();
 $db = new Database();
 $source = $db->fetch("SELECT * FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
 if (!$source) {
 http_response_code(404);
 return $this->view('errors/404');
 }
 $categories = $db->fetchAll('SELECT id, name FROM categories ORDER BY name ASC');
 $this->view('admin/rss_sources/form', [
 'source' => $source,
 'categories' => $categories,
 'mode' => 'edit'
 ]);
 }

 public function update($id)
 {
 $this->postGuard();
 $db = new Database();
 $old = $db->fetch("SELECT * FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
 if (!$old) {
 return $this->redirect('admin/rss-sources');
 }

 $data = Sanitizer::cleanArray($_POST);
 $name = trim($data['name'] ?? '');
 $url = trim($data['url'] ?? '');
 $categoryId = (int) ($data['category_id'] ?? 0) ?: null;
 $autoFetch = empty($data['auto_fetch']) ? 0 : 1;

 $db->query("UPDATE rss_sources SET name = :name, url = :url, category_id = :category_id, auto_fetch = :auto_fetch WHERE id = :id", [
 ':name' => $name,
 ':url' => $url,
 ':category_id' => $categoryId,
 ':auto_fetch' => $autoFetch,
 ':id' => (int) $id
 ]);

 $this->audit('update', 'rss_source', $id, $old, $data);
 Session::flash('success', "تم تحديث بيانات المصدر \"{$name}\" بنجاح.");
 return $this->redirect('admin/rss-sources');
 }

 public function delete($id)
 {
 $this->postGuard();
 $db = new Database();
 $old = $db->fetch("SELECT * FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
 $db->query("DELETE FROM rss_sources WHERE id = :id", [':id' => (int) $id]);
 $this->audit('delete', 'rss_source', $id, $old, null);
 Session::flash('success', 'تم حذف المصدر من القائمة نهائياً.');
 return $this->redirect('admin/rss-sources');
 }
}
