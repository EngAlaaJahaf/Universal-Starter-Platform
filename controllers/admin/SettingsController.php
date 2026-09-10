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
  'ai_assistant_enabled' => [
  'group' => 'ai_assistant',
  'value' => '1',
  'value_type' => 'boolean',
  'label_ar' => 'تفعيل المحادث الذكي',
  'label_en' => 'Enable AI Assistant',
  'description_ar' => 'إظهار نافذة «مرشد عصب التقنية» العائمة التي تجيب الزوار من محتوى مقالات المنصة',
  'description_en' => 'Show the floating AsabTech AI chat assistant that answers from site articles.',
  'sort_order' => 1,
  ],
  'ai_assistant_pages' => [
  'group' => 'ai_assistant',
  'value' => 'all',
  'value_type' => 'select',
  'label_ar' => 'أماكن ظهور المحادث',
  'label_en' => 'Assistant Display Areas',
  'description_ar' => 'حدد الصفحات التي يظهر فيها زر المحادث الذكي',
  'description_en' => 'Choose which pages show the chat launcher.',
  'sort_order' => 2,
  ],
  'ai_assistant_provider' => [
  'group' => 'ai_assistant',
  'value' => 'default',
  'value_type' => 'select',
  'label_ar' => 'مزود الذكاء الاصطناعي للمحادث',
  'label_en' => 'Assistant AI Provider',
  'description_ar' => 'مزود خاص بالمحادث، أو «نفس مزود المنصة» لاستخدام ما هو مضبوط في تبويب الذكاء والترجمة',
  'description_en' => 'Dedicated provider for the chat, or follow the global AI provider.',
  'sort_order' => 3,
  ],
  'ai_assistant_model' => [
  'group' => 'ai_assistant',
  'value' => '',
  'value_type' => 'text',
  'label_ar' => 'نموذج مخصص (اختياري)',
  'label_en' => 'Custom Model (optional)',
  'description_ar' => 'اسم النموذج لمزود المحادث. اتركه فارغاً لاستخدام النموذج الافتراضي للمزود',
  'description_en' => 'Model id for the assistant provider. Empty = provider default.',
  'sort_order' => 4,
  ],
  'ai_assistant_temperature' => [
  'group' => 'ai_assistant',
  'value' => '',
  'value_type' => 'text',
  'label_ar' => 'درجة الإبداع (Temperature)',
  'label_en' => 'Creativity (Temperature)',
  'description_ar' => 'قيمة من 0 إلى 1.5. فارغ = الإعداد العام للمنصة',
  'description_en' => 'Between 0 and 1.5. Empty = global setting.',
  'sort_order' => 5,
  ],
  'ai_assistant_tone' => [
  'group' => 'ai_assistant',
  'value' => 'balanced',
  'value_type' => 'select',
  'label_ar' => 'نبرة الردود',
  'label_en' => 'Reply Tone',
  'description_ar' => 'الأسلوب العام الذي يعتمد عليه المرشد في صياغة إجاباته',
  'description_en' => 'General writing style of the answers.',
  'sort_order' => 6,
  ],
  'ai_assistant_context_articles' => [
  'group' => 'ai_assistant',
  'value' => '4',
  'value_type' => 'text',
  'label_ar' => 'عدد المقالات المسترجعة كسياق',
  'label_en' => 'Context Articles Count',
  'description_ar' => 'كم مقالاً يبحث المرشد عنه ويعتمد عليه في الإجابة (من 1 إلى 6)',
  'description_en' => 'How many articles the assistant retrieves as context (1 to 6).',
  'sort_order' => 7,
  ],
  'ai_assistant_include_page' => [
  'group' => 'ai_assistant',
  'value' => '1',
  'value_type' => 'boolean',
  'label_ar' => 'تضمين المقال المفتوح حالياً',
  'label_en' => 'Include Current Article',
  'description_ar' => 'عند فتح المحادث من صفحة مقال، يُدرج محتوى المقال ضمن السياق حتى يجيب عنه مباشرة',
  'description_en' => 'When opened on an article page, include that article in the context.',
  'sort_order' => 8,
  ],
  'ai_assistant_fallback_enabled' => [
  'group' => 'ai_assistant',
  'value' => '1',
  'value_type' => 'boolean',
  'label_ar' => 'الاحتياط التلقائي بين المزودين',
  'label_en' => 'Auto Provider Fallback',
  'description_ar' => 'إذا فشل المزود المحدد، جرّب المزودات الأخرى المتاحة (Omniroute، Gemini، OpenAI...) تلقائياً',
  'description_en' => 'Try other configured providers automatically if the selected one fails.',
  'sort_order' => 9,
  ],
  'ai_assistant_free_limit' => [
  'group' => 'ai_assistant',
  'value' => '3',
  'value_type' => 'text',
  'label_ar' => 'الرسائل المجانية لكل زائر',
  'label_en' => 'Free Messages Per Visitor',
  'description_ar' => 'عدد الرسائل المجانية لكل زائر في الجلسة (0 = بدون حد). المسؤولون معفون دائماً',
  'description_en' => 'Free messages per visitor session (0 = unlimited). Admins are always exempt.',
  'sort_order' => 10,
  ],
  'ai_assistant_welcome_message' => [
  'group' => 'ai_assistant',
  'value' => 'مرحباً 👋 أنا مرشد عصب التقنية. اسألني عن آخر أخبار التقنية والمقالات المنشورة في المنصة.',
  'value_type' => 'text',
  'label_ar' => 'رسالة الترحيب',
  'label_en' => 'Welcome Message',
  'description_ar' => 'الرسالة الترحيبية التي تظهر عند فتح نافذة المحادث',
  'description_en' => 'Welcome message shown when the chat opens.',
  'sort_order' => 11,
  ],
  'ai_assistant_placeholder' => [
  'group' => 'ai_assistant',
  'value' => 'اسأل مرشد عصب التقنية...',
  'value_type' => 'text',
  'label_ar' => 'نص حقل الإدخال',
  'label_en' => 'Input Placeholder',
  'description_ar' => 'النص الإرشادي داخل حقل كتابة السؤال',
  'description_en' => 'Placeholder text inside the question input.',
  'sort_order' => 12,
  ],
  'ai_assistant_suggestions_enabled' => [
  'group' => 'ai_assistant',
  'value' => '1',
  'value_type' => 'boolean',
  'label_ar' => 'إظهار الاقتراحات السريعة',
  'label_en' => 'Show Quick Suggestions',
  'description_ar' => 'أزرار أسئلة جاهزة يضغطها الزائر لبدء المحادثة',
  'description_en' => 'Ready-to-click question chips for visitors.',
  'sort_order' => 13,
  ],
  'ai_assistant_suggestion_1' => [
  'group' => 'ai_assistant',
  'value' => 'ما آخر أخبار الذكاء الاصطناعي؟',
  'value_type' => 'text',
  'label_ar' => 'الاقتراح 1',
  'label_en' => 'Suggestion 1',
  'description_ar' => 'نص أول اقتراح سريع',
  'description_en' => 'First quick suggestion text.',
  'sort_order' => 14,
  ],
  'ai_assistant_suggestion_2' => [
  'group' => 'ai_assistant',
  'value' => 'ما أحدث الهواتف الذكية؟',
  'value_type' => 'text',
  'label_ar' => 'الاقتراح 2',
  'label_en' => 'Suggestion 2',
  'description_ar' => 'نص ثاني اقتراح سريع',
  'description_en' => 'Second quick suggestion text.',
  'sort_order' => 15,
  ],
  'ai_assistant_suggestion_3' => [
  'group' => 'ai_assistant',
  'value' => 'ما جديد الأمن السيبراني؟',
  'value_type' => 'text',
  'label_ar' => 'الاقتراح 3',
  'label_en' => 'Suggestion 3',
  'description_ar' => 'نص ثالث اقتراح سريع',
  'description_en' => 'Third quick suggestion text.',
  'sort_order' => 16,
  ],
  'ai_assistant_sources_enabled' => [
  'group' => 'ai_assistant',
  'value' => '1',
  'value_type' => 'boolean',
  'label_ar' => 'إظهار المصادر أسفل الإجابة',
  'label_en' => 'Show Sources',
  'description_ar' => 'عرض روابط المقالات التي اعتمد عليها المرشد في إجابته',
  'description_en' => 'Show article links the answer relied on.',
  'sort_order' => 17,
  ],
  'ai_assistant_privacy_note' => [
  'group' => 'ai_assistant',
  'value' => 'يعتمد مرشد عصب التقنية على المقالات المنشورة محلياً.',
  'value_type' => 'text',
  'label_ar' => 'ملاحظة أسفل المحادث',
  'label_en' => 'Footer Note',
  'description_ar' => 'سطر صغير يظهر أسفل نافذة المحادث',
  'description_en' => 'Small line at the bottom of the chat window.',
  'sort_order' => 18,
  ],
];

  foreach ($core as $key => $row) {
  // One row per key (any group): find all matches, dedupe to a single canonical
  // row in $row['group'], and normalize metas without touching user-set values.
  $list = $db->prepare("SELECT id, `group` FROM settings WHERE `key` = ? ORDER BY id ASC");
  $list->execute([$key]);
  $found = $list->fetchAll(PDO::FETCH_ASSOC);

  if (empty($found)) {
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
  continue;
  }

  // Keep the row already in the target group (else the first one) …
  $keepId = null;
  foreach ($found as $f) {
  if ($f['group'] === $row['group']) {
  $keepId = (int) $f['id'];
  break;
  }
  }
  if ($keepId === null) {
  $keepId = (int) $found[0]['id'];
  }
  // … and remove any extra duplicates (they only corrupt the settings page).
  foreach ($found as $f) {
  if ((int) $f['id'] !== $keepId) {
  $del = $db->prepare("DELETE FROM settings WHERE id = ?");
  $del->execute([(int) $f['id']]);
  }
  }
  $upd = $db->prepare(
  "UPDATE settings SET `group` = ?, sort_order = ?, value_type = ?, label_ar = ?, label_en = ?, description_ar = ?, description_en = ? WHERE id = ?"
  );
  $upd->execute([
  $row['group'],
  $row['sort_order'],
  $row['value_type'],
  $row['label_ar'],
  $row['label_en'],
  $row['description_ar'],
  $row['description_en'],
  $keepId
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
