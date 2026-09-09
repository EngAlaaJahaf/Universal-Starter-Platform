<?php

class AdminLiveBlogController extends AdminController
{
 public function index()
 {
 $db = Database::getInstance();
 $stmt = $db->query("
 SELECT lb.*, COUNT(lbe.id) as entries_count
 FROM live_blogs lb
 LEFT JOIN live_blog_entries lbe ON lbe.live_blog_id = lb.id
 GROUP BY lb.id
 ORDER BY lb.created_at DESC
 ");
 $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/live-blog/index', [
 'blogs' => $blogs
 ]);
 }

 public function create()
 {
 $this->renderAdmin('admin/live-blog/create');
 }

 public function store()
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $titleAr = trim($_POST['title_ar'] ?? '');
 $titleEn = trim($_POST['title_en'] ?? '');
 $status = $_POST['status'] ?? 'active';

 if (empty($titleAr)) {
 Session::flash('error', 'عنوان التغطية الحية مطلوب.');
 header('Location: ' . app_url('admin/live-blog/create'));
 exit;
 }

 $db = Database::getInstance();
 $stmt = $db->prepare("INSERT INTO live_blogs (title_ar, title_en, status) VALUES (?, ?, ?)");
 $stmt->execute([$titleAr, $titleEn, $status]);

 ActivityLogger::log('create', 'live_blog', $db->lastInsertId(), "بدء تغطية حية: {$titleAr}");
 Session::flash('success', 'تم إنشاء التغطية الحية بنجاح.');
 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }

 public function entries($id)
 {
 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT * FROM live_blogs WHERE id = ?");
 $stmt->execute([(int) $id]);
 $blog = $stmt->fetch(PDO::FETCH_ASSOC);

 if (!$blog) {
 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }

 $entriesStmt = $db->prepare("SELECT * FROM live_blog_entries WHERE live_blog_id = ? ORDER BY is_pinned DESC, created_at DESC");
 $entriesStmt->execute([(int) $id]);
 $entries = $entriesStmt->fetchAll(PDO::FETCH_ASSOC);

 $chatStmt = $db->prepare("
 SELECT c.*, SUBSTRING(lbe.content_ar, 1, 100) as reply_snippet, lbe.id as target_entry_id
 FROM live_blog_chat c
 LEFT JOIN live_blog_entries lbe ON c.entry_id = lbe.id
 WHERE c.live_blog_id = ?
 ORDER BY c.id DESC
 LIMIT 60
 ");
 $chatStmt->execute([(int) $id]);
 $chats = $chatStmt->fetchAll(PDO::FETCH_ASSOC);

 $rxStmt = $db->prepare("SELECT reaction_type, COUNT(*) as total FROM live_blog_reactions WHERE live_blog_id = ? GROUP BY reaction_type");
 $rxStmt->execute([(int) $id]);
 $reactionStats = $rxStmt->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/live-blog/entries', [
 'blog' => $blog,
 'entries' => $entries,
 'chats' => $chats,
 'reactionStats' => $reactionStats
 ]);
 }

 public function storeEntry($id)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $content = trim($_POST['content_ar'] ?? '');
 $entryType = $_POST['entry_type'] ?? 'text';
 $mediaUrl = trim($_POST['media_url'] ?? '');
 $isPinned = !empty($_POST['is_pinned']) ? 1 : 0;
 $user = Auth::user();

 if (empty($content)) {
 Session::flash('error', 'محتوى التدوينة الحية مطلوب.');
 header('Location: ' . app_url('admin/live-blog/' . (int) $id . '/entries'));
 exit;
 }

 $db = Database::getInstance();
 $stmt = $db->prepare("
 INSERT INTO live_blog_entries (live_blog_id, author_id, content_ar, entry_type, media_url, is_pinned)
 VALUES (?, ?, ?, ?, ?, ?)
 ");
 $stmt->execute([(int) $id, $user ? $user['id'] : null, $content, $entryType, $mediaUrl, $isPinned]);

 ActivityLogger::log('create', 'live_blog_entry', $db->lastInsertId(), "إضافة تدوينة للتغطية الحية رقم: {$id}");
 Session::flash('success', 'تمت إضافة التدوينة بنجاح.');
 header('Location: ' . app_url('admin/live-blog/' . (int) $id . '/entries'));
 exit;
 }

 public function updateEntry($entryId)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $content = trim($_POST['content_ar'] ?? '');
 $entryType = $_POST['entry_type'] ?? 'text';
 $mediaUrl = trim($_POST['media_url'] ?? '');
 $isPinned = !empty($_POST['is_pinned']) ? 1 : 0;

 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT live_blog_id FROM live_blog_entries WHERE id = ?");
 $stmt->execute([(int) $entryId]);
 $entry = $stmt->fetch(PDO::FETCH_ASSOC);

 if (!$entry) {
 Session::flash('error', 'التدوينة غير موجودة.');
 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }

 $liveBlogId = (int) $entry['live_blog_id'];

 if (empty($content)) {
 Session::flash('error', 'محتوى التدوينة الحية مطلوب.');
 header('Location: ' . app_url('admin/live-blog/' . $liveBlogId . '/entries'));
 exit;
 }

 $updateStmt = $db->prepare("
 UPDATE live_blog_entries 
 SET content_ar = ?, entry_type = ?, media_url = ?, is_pinned = ?
 WHERE id = ?
 ");
 $updateStmt->execute([$content, $entryType, $mediaUrl, $isPinned, (int) $entryId]);

 ActivityLogger::log('update', 'live_blog_entry', (int) $entryId, "تعديل التدوينة الحية رقم: {$entryId}");
 Session::flash('success', 'تم تعديل التدوينة الحية بنجاح.');
 header('Location: ' . app_url('admin/live-blog/' . $liveBlogId . '/entries'));
 exit;
 }

 public function deleteEntry($entryId)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT live_blog_id FROM live_blog_entries WHERE id = ?");
 $stmt->execute([(int) $entryId]);
 $entry = $stmt->fetch(PDO::FETCH_ASSOC);

 $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

 if ($entry) {
 $liveBlogId = (int) $entry['live_blog_id'];
 $delStmt = $db->prepare("DELETE FROM live_blog_entries WHERE id = ?");
 $delStmt->execute([(int) $entryId]);
 ActivityLogger::log('delete', 'live_blog_entry', (int) $entryId, "حذف التدوينة الحية رقم: {$entryId}");

 if ($isAjax) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['success' => true, 'message' => 'تم حذف التدوينة بنجاح']);
 exit;
 }

 Session::flash('success', 'تم حذف التدوينة بنجاح.');
 header('Location: ' . app_url('admin/live-blog/' . $liveBlogId . '/entries'));
 exit;
 }

 if ($isAjax) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['success' => false, 'message' => 'التدوينة غير موجودة']);
 exit;
 }

 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }

 public function togglePinEntry($entryId)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT live_blog_id, is_pinned FROM live_blog_entries WHERE id = ?");
 $stmt->execute([(int) $entryId]);
 $entry = $stmt->fetch(PDO::FETCH_ASSOC);

 $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

 if ($entry) {
 $liveBlogId = (int) $entry['live_blog_id'];
 $newPin = $entry['is_pinned'] ? 0 : 1;
 $update = $db->prepare("UPDATE live_blog_entries SET is_pinned = ? WHERE id = ?");
 $update->execute([$newPin, (int) $entryId]);
 ActivityLogger::log('update', 'live_blog_entry', (int) $entryId, "تغيير حالة تثبيت التدوينة رقم: {$entryId}");

 $msg = $newPin ? 'تم تثبيت التدوينة في أعلى البث ' : 'تم إلغاء تثبيت التدوينة';

 if ($isAjax) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode([
 'success' => true,
 'is_pinned' => $newPin,
 'message' => $msg
 ], JSON_UNESCAPED_UNICODE);
 exit;
 }

 Session::flash('success', $msg);
 header('Location: ' . app_url('admin/live-blog/' . $liveBlogId . '/entries'));
 exit;
 }

 if ($isAjax) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['success' => false, 'message' => 'التدوينة غير موجودة']);
 exit;
 }

 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }

 public function uploadMedia()
 {
 header('Content-Type: application/json; charset=utf-8');
 CSRF::validate($_POST['_csrf'] ?? '');

 // 1. Check for Base64 clipboard image paste
 $base64Data = $_POST['base64_data'] ?? '';
 if (!empty($base64Data) && preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
 $data = substr($base64Data, strpos($base64Data, ',') + 1);
 $ext = strtolower($type[1]);
 if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
 $ext = 'png';
 }
 $data = base64_decode($data);
 if ($data === false) {
 echo json_encode(['success' => false, 'message' => 'فشل فك تشفير صورة الحافظة.']);
 exit;
 }

 $uploadDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'live-blog';
 if (!is_dir($uploadDir)) {
 @mkdir($uploadDir, 0777, true);
 }

 $filename = 'live_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
 $filepath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
 file_put_contents($filepath, $data);

 $relativeUrl = 'uploads/live-blog/' . $filename;
 echo json_encode([
 'success' => true,
 'url' => $relativeUrl,
 'full_url'=> app_url($relativeUrl),
 'type' => 'image'
 ], JSON_UNESCAPED_UNICODE);
 exit;
 }

 // 2. Check for File Upload (Image or Video from device)
 if (!empty($_FILES['file']['tmp_name'])) {
 $file = $_FILES['file'];
 $uploader = new Upload('live-blog');
 $relPath = $uploader->image($file); // handles both images and videos via extended allowedMimes

 if ($relPath) {
 $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
 $mediaType = in_array($ext, ['mp4', 'webm', 'ogv', 'mov']) ? 'video' : 'image';
 echo json_encode([
 'success' => true,
 'url' => $relPath,
 'full_url'=> app_url($relPath),
 'type' => $mediaType
 ], JSON_UNESCAPED_UNICODE);
 exit;
 } else {
 echo json_encode(['success' => false, 'message' => 'فشل رفع الملف. تأكد من الصيغة والحجم (أقل من 50MB).']);
 exit;
 }
 }

 echo json_encode(['success' => false, 'message' => 'لم يتم إرسال أي ملف أو صورة.']);
 exit;
 }

 public function toggleStatus($id)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT status, title_ar FROM live_blogs WHERE id = ?");
 $stmt->execute([(int) $id]);
 $blog = $stmt->fetch(PDO::FETCH_ASSOC);

 if ($blog) {
 $newStatus = ($blog['status'] === 'active') ? 'ended' : 'active';
 $update = $db->prepare("UPDATE live_blogs SET status = ? WHERE id = ?");
 $update->execute([$newStatus, (int) $id]);
 ActivityLogger::log('update', 'live_blog', (int) $id, "تغيير حالة التغطية الحية إلى: {$newStatus}");
 Session::flash('success', 'تم تغيير حالة التغطية الحية بنجاح إلى: ' . ($newStatus === 'active' ? 'مباشر الآن' : 'منتهية'));
 }

 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }

 public function delete($id)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 
 $delEntries = $db->prepare("DELETE FROM live_blog_entries WHERE live_blog_id = ?");
 $delEntries->execute([(int) $id]);

 $delBlog = $db->prepare("DELETE FROM live_blogs WHERE id = ?");
 $delBlog->execute([(int) $id]);

 ActivityLogger::log('delete', 'live_blog', (int) $id, "حذف التغطية الحية رقم: {$id}");
 Session::flash('success', 'تم حذف التغطية الحية وجميع تدويناتها بنجاح.');
 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }

 public function deleteChatMessage($msgId)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT id, live_blog_id FROM live_blog_chat WHERE id = ?");
 $stmt->execute([(int) $msgId]);
 $msg = $stmt->fetch(PDO::FETCH_ASSOC);

 $isAjax = !empty($_POST['ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

 if ($msg) {
 $del = $db->prepare("DELETE FROM live_blog_chat WHERE id = ?");
 $del->execute([(int) $msgId]);
 ActivityLogger::log('delete', 'live_blog_chat', (int) $msgId, "حذف رسالة دردشة رقم: {$msgId}");

 if ($isAjax) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['success' => true, 'message' => 'تم حذف الرسالة']);
 exit;
 }
 Session::flash('success', 'تم حذف رسالة الدردشة بنجاح.');
 header('Location: ' . app_url('admin/live-blog/' . $msg['live_blog_id'] . '/entries'));
 exit;
 }

 if ($isAjax) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['success' => false, 'message' => 'الرسالة غير موجودة']);
 exit;
 }
 header('Location: ' . app_url('admin/live-blog'));
 exit;
 }
}
