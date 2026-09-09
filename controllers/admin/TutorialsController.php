<?php

class TutorialsController extends AdminController
{
 public function index()
 {
 $tutorialModel = new Tutorial();
 $tutorials = $tutorialModel->getAllWithStepCount();

 $this->view('admin/tutorials/index', [
 'title' => 'استوديو الشروحات والدروس المصورة',
 'tutorials' => $tutorials,
 'user' => Auth::user()
 ]);
 }

 public function create()
 {
 $db = Database::getInstance();
 $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");

 $this->view('admin/tutorials/form', [
 'title' => 'إنشاء شرح متسلسل ومصور جديد',
 'tutorial' => null,
 'steps' => [],
 'categories' => $categories,
 'user' => Auth::user()
 ]);
 }

 public function store()
 {
 $this->postGuard();
 $db = Database::getInstance();

 $title = trim($_POST['title'] ?? '');
 $summary = trim($_POST['summary'] ?? '');
 $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
 $difficulty = in_array($_POST['difficulty'] ?? '', ['beginner', 'intermediate', 'advanced']) ? $_POST['difficulty'] : 'beginner';
 $estimatedMinutes = max(1, (int) ($_POST['estimated_minutes'] ?? 10));
 $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
 $featuredImage = trim($_POST['featured_image'] ?? '');

 // Handle direct upload of featured image if provided
 if (!empty($_FILES['featured_image_file']['tmp_name'])) {
 $upload = new Upload();
 $uploadedPath = $upload->image($_FILES['featured_image_file'], 'tutorials');
 if ($uploadedPath) {
 $featuredImage = $uploadedPath;
 }
 }

 if (empty($title)) {
 Session::flash('error', 'عنوان الشرح مطلوب.');
 return $this->redirect('admin/tutorials/create');
 }

 // Generate Slug
 $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', $title), '-'));
 if (!$slug) $slug = 'tutorial-' . time();
 $check = $db->fetch("SELECT id FROM tutorials WHERE slug = :slug", [':slug' => $slug]);
 if ($check) {
 $slug .= '-' . time();
 }

 $authorId = Auth::user()['id'] ?? 1;

 $db->query("
 INSERT INTO tutorials 
 (title, slug, summary, category_id, featured_image, difficulty, estimated_minutes, author_id, status, created_at, published_at)
 VALUES 
 (:title, :slug, :summary, :category_id, :featured_image, :difficulty, :estimated_minutes, :author_id, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
 ", [
 ':title' => $title,
 ':slug' => $slug,
 ':summary' => $summary,
 ':category_id' => $categoryId,
 ':featured_image' => $featuredImage ?: null,
 ':difficulty' => $difficulty,
 ':estimated_minutes' => $estimatedMinutes,
 ':author_id' => $authorId,
 ':status' => $status,
 ]);

 $tutorialId = (int) $db->lastInsertId();

 // Process & Save Steps
 $this->saveSteps($tutorialId, $_POST['steps'] ?? []);

 $this->audit('create_tutorial', 'tutorial', $tutorialId, null, ['title' => $title]);
 Session::flash('success', "تم إنشاء ونشر الشرح المصور بنجاح بعنوان: \"{$title}\"!");
 return $this->redirect('admin/tutorials');
 }

 public function edit($id)
 {
 $tutorialModel = new Tutorial();
 $tutorial = $tutorialModel->getByIdWithSteps($id);

 if (!$tutorial) {
 Session::flash('error', 'الشرح غير موجود.');
 return $this->redirect('admin/tutorials');
 }

 $db = Database::getInstance();
 $categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");

 $this->view('admin/tutorials/form', [
 'title' => 'تعديل الشرح: ' . $tutorial['title'],
 'tutorial' => $tutorial,
 'steps' => $tutorial['steps'] ?? [],
 'categories' => $categories,
 'user' => Auth::user()
 ]);
 }

 public function update($id)
 {
 $this->postGuard();
 $db = Database::getInstance();

 $tutorial = $db->fetch("SELECT * FROM tutorials WHERE id = :id", [':id' => (int) $id]);
 if (!$tutorial) {
 Session::flash('error', 'الشرح غير موجود.');
 return $this->redirect('admin/tutorials');
 }

 $title = trim($_POST['title'] ?? '');
 $summary = trim($_POST['summary'] ?? '');
 $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
 $difficulty = in_array($_POST['difficulty'] ?? '', ['beginner', 'intermediate', 'advanced']) ? $_POST['difficulty'] : 'beginner';
 $estimatedMinutes = max(1, (int) ($_POST['estimated_minutes'] ?? 10));
 $status = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
 $featuredImage = trim($_POST['featured_image'] ?? $tutorial['featured_image']);

 // Handle direct upload of featured image if provided
 if (!empty($_FILES['featured_image_file']['tmp_name'])) {
 $upload = new Upload();
 $uploadedPath = $upload->image($_FILES['featured_image_file'], 'tutorials');
 if ($uploadedPath) {
 $featuredImage = $uploadedPath;
 }
 }

 if (empty($title)) {
 Session::flash('error', 'عنوان الشرح مطلوب.');
 return $this->redirect("admin/tutorials/{$id}/edit");
 }

 $db->query("
 UPDATE tutorials SET 
 title = :title,
 summary = :summary,
 category_id = :category_id,
 featured_image = :featured_image,
 difficulty = :difficulty,
 estimated_minutes = :estimated_minutes,
 status = :status,
 updated_at = CURRENT_TIMESTAMP
 WHERE id = :id
 ", [
 ':title' => $title,
 ':summary' => $summary,
 ':category_id' => $categoryId,
 ':featured_image' => $featuredImage ?: null,
 ':difficulty' => $difficulty,
 ':estimated_minutes' => $estimatedMinutes,
 ':status' => $status,
 ':id' => (int) $id,
 ]);

 // Replace/Resave Steps
 $this->saveSteps((int) $id, $_POST['steps'] ?? []);

 $this->audit('update_tutorial', 'tutorial', (int) $id, $tutorial, ['title' => $title]);
 Session::flash('success', "تم تحديث وحفظ بيانات الشرح وخطواته بنجاح!");
 return $this->redirect('admin/tutorials');
 }

 public function delete($id)
 {
 $this->postGuard();
 $db = Database::getInstance();
 $tutorial = $db->fetch("SELECT * FROM tutorials WHERE id = :id", [':id' => (int) $id]);

 if ($tutorial) {
 $db->query("DELETE FROM tutorial_steps WHERE tutorial_id = :id", [':id' => (int) $id]);
 $db->query("DELETE FROM tutorials WHERE id = :id", [':id' => (int) $id]);
 $this->audit('delete_tutorial', 'tutorial', (int) $id, $tutorial, null);
 Session::flash('success', 'تم حذف الشرح وكافة خطواته بنجاح.');
 }

 return $this->redirect('admin/tutorials');
 }

 /**
 * AJAX Step Image Upload Handler
 */
 public function ajaxUploadStepImage()
 {
 header('Content-Type: application/json');
 Auth::check();
 if (!Auth::isAdmin()) {
 http_response_code(403);
 echo json_encode(['success' => false, 'message' => 'غير مصرح لك برفع الملفات (جلسة غير مسجلة كمدير).']);
 exit;
 }

 $file = $_FILES['image'] ?? $_FILES['file'] ?? null;
 if (!$file || empty($file['tmp_name'])) {
 echo json_encode(['success' => false, 'message' => 'لم يتم إرسال ملف صورة صالح.']);
 exit;
 }

 $upload = new Upload('tutorials/steps');
 $path = $upload->image($file);
 if ($path) {
 echo json_encode([
 'success' => true, 
 'url' => app_url($path),
 'path' => $path
 ]);
 } else {
 echo json_encode([
 'success' => false, 
 'message' => 'فشل في حفظ الصورة. يرجى التأكد من الامتدادات المدعومة (JPG, PNG, WEBP, GIF, SVG, AVIF) وحجم لا يتجاوز 15MB.'
 ]);
 }
 exit;
 }

 /**
 * Helper to process and insert step array
 */
 private function saveSteps($tutorialId, $stepsData)
 {
 $db = Database::getInstance();
 $db->query("DELETE FROM tutorial_steps WHERE tutorial_id = :tid", [':tid' => $tutorialId]);

 if (!is_array($stepsData)) {
 return;
 }

 $stepNumber = 1;
 foreach ($stepsData as $index => $step) {
 $stepTitle = trim($step['title'] ?? '');
 $stepContent = trim($step['content'] ?? '');
 $imageUrl = trim($step['image_url'] ?? '');
 $imageCaption = trim($step['image_caption'] ?? '');
 $codeSnippet = trim($step['code_snippet'] ?? '');
 $codeLanguage = trim($step['code_language'] ?? 'bash') ?: 'bash';
 $calloutType = in_array($step['callout_type'] ?? '', ['none', 'tip', 'warning', 'important', 'note']) ? $step['callout_type'] : 'none';
 $calloutText = trim($step['callout_text'] ?? '');
 $sortOrder = (int) ($step['sort_order'] ?? ($index * 10));

 // Only skip completely empty steps
 if (empty($stepTitle) && empty($stepContent) && empty($imageUrl)) {
 continue;
 }

 if (empty($stepTitle)) {
 $stepTitle = "الخطوة رقم " . $stepNumber;
 }

 $db->query("
 INSERT INTO tutorial_steps 
 (tutorial_id, step_number, title, image_url, image_caption, content, code_snippet, code_language, callout_type, callout_text, sort_order, created_at)
 VALUES 
 (:tutorial_id, :step_number, :title, :image_url, :image_caption, :content, :code_snippet, :code_language, :callout_type, :callout_text, :sort_order, CURRENT_TIMESTAMP)
 ", [
 ':tutorial_id' => $tutorialId,
 ':step_number' => $stepNumber,
 ':title' => $stepTitle,
 ':image_url' => $imageUrl ?: null,
 ':image_caption' => $imageCaption ?: null,
 ':content' => $stepContent,
 ':code_snippet' => $codeSnippet ?: null,
 ':code_language' => $codeLanguage,
 ':callout_type' => $calloutType,
 ':callout_text' => $calloutText ?: null,
 ':sort_order' => $sortOrder,
 ]);

 $stepNumber++;
 }
 }
}
