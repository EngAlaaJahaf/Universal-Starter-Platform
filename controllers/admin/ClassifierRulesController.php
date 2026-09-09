<?php

class ClassifierRulesController extends AdminController
{
 private function isAjaxRequest(): bool
 {
 return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
 || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
 }

 public function index()
 {
 $this->guardAdmin();
 $db = new Database();

 $categories = $db->fetchAll("SELECT id, name, slug FROM categories ORDER BY id ASC");
 $rules = CategoryClassifier::getRules();
 $allConflicts = CategoryClassifier::getAllConflicts();
 $sourceRules = CategoryClassifier::getSourceRules();
 $rssSources = $db->fetchAll("
 SELECT s.id, s.name, s.url, s.category_id,
 c.name as category_name, c.slug as category_slug
 FROM rss_sources s
 LEFT JOIN categories c ON c.id = s.category_id
 ORDER BY s.id ASC
 ");

 // Calculate stats
 $totalKeywords = 0;
 foreach ($rules as $r) {
 $totalKeywords += count($r['high_priority'] ?? []) + count($r['medium_priority'] ?? []);
 }

 $this->view('admin/classifier_rules/index', [
 'categories' => $categories,
 'rules' => $rules,
 'allConflicts' => $allConflicts,
 'totalKeywords' => $totalKeywords,
 'sourceRules' => $sourceRules,
 'rssSources' => $rssSources,
 'success' => Session::getFlash('success'),
 'error' => Session::getFlash('error'),
 'warning' => Session::getFlash('warning'),
 ]);
 }

 public function updateSourceRule()
 {
 $this->guardAdmin();
 $this->postGuard();

 $sourceName = trim($_POST['source_name'] ?? '');
 $mode = trim($_POST['mode'] ?? 'smart');
 $targetSlug = trim($_POST['target_slug'] ?? 'general-tech');
 $weight = (float) ($_POST['weight'] ?? 5.0);

 if (empty($sourceName)) {
 if ($this->isAjaxRequest()) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => false, 'error' => 'اسم المصدر مطلوب.']);
 exit;
 }
 Session::flash('error', 'اسم المصدر مطلوب.');
 $this->redirect('admin/classifier-rules');
 return;
 }

 $saved = CategoryClassifier::setSourceRule($sourceName, $mode, $targetSlug, $weight);

 if ($this->isAjaxRequest()) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => $saved, 'source_name' => $sourceName, 'mode' => $mode, 'target_slug' => $targetSlug]);
 exit;
 }

 Session::flash('success', "تم تحديث إعدادات تصنيف المصدر '{$sourceName}' بنجاح.");
 $this->redirect('admin/classifier-rules');
 }

 public function add()
 {
 $this->guardAdmin();
 $this->postGuard();

 $categorySlug = trim($_POST['category_slug'] ?? '');
 $keyword = trim($_POST['keyword'] ?? '');
 $priority = trim($_POST['priority'] ?? 'high');
 $force = !empty($_POST['force']);

 if (empty($categorySlug) || empty($keyword)) {
 if ($this->isAjaxRequest()) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => false, 'error' => 'يرجى إدخال المصطلح واختيار التصنيف.']);
 exit;
 }
 Session::flash('error', 'يرجى إدخال المصطلح واختيار التصنيف.');
 $this->redirect('admin/classifier-rules');
 return;
 }

 // Check for conflicts
 $conflicts = CategoryClassifier::checkKeywordConflicts($keyword, $categorySlug);

 if (!empty($conflicts) && !$force) {
 $conflictNames = implode('، ', array_column($conflicts, 'category_name'));
 $msg = "تعارض: المصطلح '{$keyword}' مضاف بالفعل في ({$conflictNames})!";
 if ($this->isAjaxRequest()) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => false, 'conflict' => true, 'conflicts' => $conflicts, 'error' => $msg]);
 exit;
 }
 Session::flash('warning', $msg);
 $this->redirect('admin/classifier-rules');
 return;
 }

 $result = CategoryClassifier::addKeyword($categorySlug, $keyword, $priority);

 if ($this->isAjaxRequest()) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode($result);
 exit;
 }

 if ($result['ok']) {
 Session::flash('success', "تم إضافة المصطلح '{$keyword}' بنجاح إلى قاعدة التصنيف الذكي.");
 } else {
 Session::flash('error', $result['error'] ?? 'حدث خطأ أثناء إضافة المصطلح.');
 }

 $this->redirect('admin/classifier-rules');
 }

 public function checkConflict()
 {
 $this->guardAdmin();
 $keyword = trim($_GET['keyword'] ?? $_POST['keyword'] ?? '');
 $categorySlug = trim($_GET['category_slug'] ?? $_POST['category_slug'] ?? '');

 $conflicts = CategoryClassifier::checkKeywordConflicts($keyword, $categorySlug);

 header('Content-Type: application/json; charset=utf-8');
 echo json_encode([
 'has_conflict' => !empty($conflicts),
 'conflicts' => $conflicts
 ], JSON_UNESCAPED_UNICODE);
 exit;
 }

 public function delete()
 {
 $this->guardAdmin();
 $this->postGuard();

 $categorySlug = trim($_POST['category_slug'] ?? '');
 $keyword = trim($_POST['keyword'] ?? '');

 $deleted = CategoryClassifier::removeKeyword($categorySlug, $keyword);

 if ($this->isAjaxRequest()) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => $deleted]);
 exit;
 }

 if ($deleted) {
 Session::flash('success', "تم حذف المصطلح '{$keyword}' بنجاح.");
 } else {
 Session::flash('error', "تعذر حذف المصطلح.");
 }

 $this->redirect('admin/classifier-rules');
 }

 public function reclassifyAll()
 {
 $this->guardAdmin();
 $this->postGuard();

 $db = new Database();
 $catRows = $db->fetchAll("SELECT id, slug FROM categories");
 $catSlugMap = [];
 foreach ($catRows as $c) {
 $catSlugMap[$c['slug']] = (int) $c['id'];
 }

 $articles = $db->fetchAll("SELECT id, title, title_en, title_ar, content, content_en, category_id, source_name, source_url FROM articles");
 $updated = 0;

 foreach ($articles as $art) {
 $titleEn = (string) ($art['title_en'] ?? $art['title'] ?? '');
 $titleAr = (string) ($art['title_ar'] ?? $art['title'] ?? '');
 $contentEn = (string) ($art['content_en'] ?? $art['content'] ?? '');
 $sourceName = (string) ($art['source_name'] ?? '');
 $sourceUrl = (string) ($art['source_url'] ?? '');
 $currentCatId = (int) ($art['category_id'] ?? 0);

 $newCatId = CategoryClassifier::classify($titleEn, $contentEn, $titleAr, $currentCatId, $catSlugMap, $sourceName, $sourceUrl);

 if ($newCatId > 0 && $newCatId !== $currentCatId) {
 $db->query("UPDATE articles SET category_id = :cid WHERE id = :id", [
 ':cid' => $newCatId,
 ':id' => (int) $art['id']
 ]);
 $updated++;
 }
 }

 if ($this->isAjaxRequest()) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => true, 'updated_count' => $updated]);
 exit;
 }

 Session::flash('success', "تمت إعادة فحص وتصنيف {$updated} مقالاً بدقة فائقة بمراعاة المصادر والمصطلحات.");
 $this->redirect('admin/classifier-rules');
 }
}
