<?php

class SettingsController extends AdminController
{
 public function index()
 {
$db = Database::getInstance();
  $group = trim($_GET['group'] ?? 'general');

  // Self-heal: guarantee rows required by current templates exist in the DB,
  // otherwise hosts whose settings table predates the feature never show them.
  $this->ensureCoreRows($db);

  // Smart Alias Resolver for URL groups
 $aliases = [
 'ai' => 'ai_translation',
 'ai-translation' => 'ai_translation',
 'translation' => 'ai_translation',
 'translations' => 'ai_translation',
 'translator' => 'ai_translation',
 'theme' => 'appearance',
 'design' => 'appearance',
 'mail' => 'newsletter',
 'smtp' => 'newsletter',
 ];
 if (isset($aliases[$group])) {
 $group = $aliases[$group];
 }

 $stmt = $db->prepare("SELECT * FROM settings WHERE `group` = ? ORDER BY sort_order ASC, id ASC");
 $stmt->execute([$group]);
 $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

 // If requested group has 0 settings, fallback to general
 if (empty($settings) && $group !== 'general') {
 $group = 'general';
 $stmt = $db->prepare("SELECT * FROM settings WHERE `group` = ? ORDER BY sort_order ASC, id ASC");
 $stmt->execute([$group]);
 $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
 }

 // Get list of all distinct groups
 $groupsStmt = $db->query("SELECT DISTINCT `group` FROM settings ORDER BY `group` ASC");
 $allGroups = $groupsStmt->fetchAll(PDO::FETCH_COLUMN);

$this->renderAdmin('admin/settings/index', [
  'settings' => $settings,
  'currentGroup' => $group,
  'allGroups' => $allGroups
  ]);
  }

  /**
  * Idempotent self-heal migration: ensures DB rows required by the public
  * templates exist in the settings table. Settings rows ship as data, so a
  * database that predates a feature would otherwise silently miss its toggle.
  */
  private function ensureCoreRows($db)
  {
  $core = [
  'breaking_ticker_enabled' => [
  'group' => 'appearance',
  'value' => '1',
  'value_type' => 'boolean',
  'label_ar' => 'شريط المستجدات العاجلة',
  'label_en' => 'Breaking News Ticker',
  'description_ar' => 'إظهار أو إخفاء شريط أحدث المستجدات وأيقونات التواصل أعلى الموقع',
  'description_en' => 'Show or hide the breaking headlines ticker and social icons at the top of the site.',
  'sort_order' => 2,
  ],
  ];

  foreach ($core as $key => $row) {
  $check = $db->prepare("SELECT id FROM settings WHERE `group` = ? AND `key` = ? LIMIT 1");
  $check->execute([$row['group'], $key]);
  if ($check->fetch(PDO::FETCH_COLUMN)) {
  continue;
  }
  $stmt = $db->prepare(
  "INSERT INTO settings (`group`, `key`, `value`, `value_type`, `label_ar`, `label_en`, `description_ar`, `description_en`, `sort_order`)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
  );
  $stmt->execute([
  $row['group'],
  $key,
  $row['value'],
  $row['value_type'],
  $row['label_ar'],
  $row['label_en'],
  $row['description_ar'],
  $row['description_en'],
  $row['sort_order']
  ]);
  }
  }

  public function update()
  {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: ' . app_url('admin/settings'));
exit;
  }

  CSRF::validate($_POST['_csrf'] ?? '');
  $db = Database::getInstance();
  $group = $_POST['_group'] ?? 'general';

  // 1. Process regular settings input
 if (!empty($_POST['settings']) && is_array($_POST['settings'])) {
 foreach ($_POST['settings'] as $key => $value) {
 if (is_array($value)) {
 $value = json_encode($value, JSON_UNESCAPED_UNICODE);
 }
 $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `group` = ? AND `key` = ?");
 $stmt->execute([(string) $value, $group, $key]);
 }
 }

 // 2. Process file uploads for brand assets (site_logo, site_favicon, og_image)
 if (!empty($_FILES)) {
 $brandDir = APP_ROOT . '/uploads/brand';
 if (!is_dir($brandDir)) {
 @mkdir($brandDir, 0777, true);
 }

 foreach ($_FILES as $inputKey => $fileData) {
 if (!empty($fileData['tmp_name']) && is_uploaded_file($fileData['tmp_name']) && $fileData['error'] === UPLOAD_ERR_OK) {
 $settingKey = str_replace('_upload_file', '', $inputKey);
 $ext = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
 $allowedExts = ['png', 'jpg', 'jpeg', 'svg', 'webp', 'ico', 'gif'];
 if (in_array($ext, $allowedExts, true)) {
 $filename = $settingKey . '_' . time() . '.' . $ext;
 $targetPath = $brandDir . '/' . $filename;
 if (move_uploaded_file($fileData['tmp_name'], $targetPath)) {
 $savedUrl = 'uploads/brand/' . $filename;
 $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
 $stmt->execute([$savedUrl, $settingKey]);
 }
 }
 }
 }
 }

 // Clear runtime settings cache
 if (class_exists('Settings')) {
 Settings::clear();
 }

 // Flush System Cache
 if (class_exists('Cache')) {
 Cache::flush();
 }

 ActivityLogger::log('update', 'settings', null, "تحديث إعدادات المجموعة: {$group}");
 Session::flash('success', 'تم حفظ وتطبيق كافة الإعدادات بنجاح.');
 header('Location: ' . app_url('admin/settings?group=' . urlencode($group)));
 exit;
 }

 /**
 * Instant AJAX upload endpoint for brand assets (Logo, Favicon, OG Image)
 */
 public function uploadAsset()
 {
 header('Content-Type: application/json; charset=utf-8');

 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 http_response_code(405);
 echo json_encode(['success' => false, 'message' => 'Method not allowed']);
 exit;
 }

 $key = $_POST['key'] ?? '';
 $allowedKeys = ['site_logo', 'site_favicon', 'default_og_image', 'site_logo_dark'];

 if (!in_array($key, $allowedKeys, true)) {
 http_response_code(400);
 echo json_encode(['success' => false, 'message' => 'Invalid asset key']);
 exit;
 }

 if (empty($_FILES['asset_file']) || $_FILES['asset_file']['error'] !== UPLOAD_ERR_OK) {
 http_response_code(400);
 echo json_encode(['success' => false, 'message' => 'لم يتم استلام أي ملف صالح']);
 exit;
 }

 $file = $_FILES['asset_file'];
 $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
 $allowedExts = ['png', 'jpg', 'jpeg', 'svg', 'webp', 'ico', 'gif'];

 if (!in_array($ext, $allowedExts, true)) {
 http_response_code(400);
 echo json_encode(['success' => false, 'message' => 'نوع الملف غير مدعوم. الصيغ المسموحة: ' . implode(', ', $allowedExts)]);
 exit;
 }

 $brandDir = APP_ROOT . '/uploads/brand';
 if (!is_dir($brandDir)) {
 @mkdir($brandDir, 0777, true);
 }

 $filename = $key . '_' . time() . '.' . $ext;
 $targetPath = $brandDir . '/' . $filename;

 if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
 http_response_code(500);
 echo json_encode(['success' => false, 'message' => 'فشل حفظ الملف على الخادم']);
 exit;
 }

 $savedUrl = 'uploads/brand/' . $filename;
 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
 $stmt->execute([$savedUrl, $key]);

 // Invalidate runtime settings and app caches
 if (class_exists('Settings')) {
 Settings::clear();
 }
 if (class_exists('Cache')) {
 Cache::flush();
 }

 ActivityLogger::log('update', 'settings', null, "رفع وتطبيق الأصل البصري فورياً: {$key}");

 echo json_encode([
 'success' => true,
 'url' => $savedUrl,
 'full_url' => app_url($savedUrl),
 'message' => 'تم رفع وتطبيق الصورة فورياً بنجاح!'
 ]);
 exit;
 }

 public function quickSwitchProvider()
 {
 $this->postGuard();
 $provider = trim($_POST['provider'] ?? '');
 $allowed = ['openai', 'gemini', 'custom_api', 'mymemory'];
 if (!in_array($provider, $allowed, true)) {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['success' => false, 'error' => 'مزود غير صالح'], JSON_UNESCAPED_UNICODE);
 exit;
 }

 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `key` = 'ai_provider'");
 $stmt->execute([$provider]);

 if (class_exists('Settings')) {
 Settings::clear();
 }
 if (class_exists('Cache')) {
 Cache::flush();
 }

 ActivityLogger::log('update', 'settings', null, "التبديل الفوري لمزود الذكاء الاصطناعي إلى: {$provider}");

 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['success' => true, 'provider' => $provider], JSON_UNESCAPED_UNICODE);
 exit;
 }
}
