<?php

class BackupController extends AdminController
{
 public function index()
 {
 $db = Database::getInstance();

 // Gather system stats
 $articlesCount = (int) ($db->query("SELECT COUNT(*) FROM articles")->fetchColumn() ?? 0);
 $usersCount = (int) ($db->query("SELECT COUNT(*) FROM users")->fetchColumn() ?? 0);
 $settingsCount = (int) ($db->query("SELECT COUNT(*) FROM settings")->fetchColumn() ?? 0);
 $subscribersCount = (int) ($db->query("SELECT COUNT(*) FROM newsletters")->fetchColumn() ?? 0);
 $categoriesCount = (int) ($db->query("SELECT COUNT(*) FROM categories")->fetchColumn() ?? 0);

 // Approximate DB size in MB
 $dbSizeStmt = $db->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'");
 $dbSizeMb = round((float) ($dbSizeStmt->fetchColumn() ?? 0), 2);

 $this->renderAdmin('admin/backup/index', [
 'title' => 'مركز النسخ الاحتياطي والاستيراد والتصدير',
 'articlesCount' => $articlesCount,
 'usersCount' => $usersCount,
 'settingsCount' => $settingsCount,
 'subscribersCount' => $subscribersCount,
 'categoriesCount' => $categoriesCount,
 'dbSizeMb' => $dbSizeMb
 ]);
 }

 /**
 * 1. Export Full SQL Database Dump
 */
 public function exportDatabase()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

 $filename = 'tech_news_db_backup_' . date('Y-m-d_H-i-s') . '.sql';

 header('Content-Type: application/sql; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');
 header('Pragma: no-cache');
 header('Expires: 0');

 echo "-- ============================================================================\n";
 echo "-- Full Database Backup: " . DB_NAME . "\n";
 echo "-- Generated At: " . date('Y-m-d H:i:s') . "\n";
 echo "-- Server: " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\n";
 echo "-- ============================================================================\n\n";
 echo "SET FOREIGN_KEY_CHECKS = 0;\n";
 echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
 echo "SET time_zone = \"+00:00\";\n\n";

 foreach ($tables as $table) {
 $createStmt = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
 $createSql = $createStmt['Create Table'] ?? '';

 echo "\n-- --------------------------------------------------------\n";
 echo "-- Structure & Data for table `$table`\n";
 echo "-- --------------------------------------------------------\n";
 echo "DROP TABLE IF EXISTS `$table`;\n";
 echo $createSql . ";\n\n";

 $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
 if (!empty($rows)) {
 $columns = array_keys($rows[0]);
 $colNames = implode('`, `', $columns);

 $batch = [];
 foreach ($rows as $r) {
 $vals = [];
 foreach ($r as $val) {
 if ($val === null) {
 $vals[] = 'NULL';
 } elseif (is_numeric($val) && !is_string($val)) {
 $vals[] = $val;
 } else {
 $vals[] = $db->getPdo()->quote((string) $val);
 }
 }
 $batch[] = "(" . implode(', ', $vals) . ")";
 if (count($batch) >= 100) {
 echo "INSERT INTO `$table` (`$colNames`) VALUES\n" . implode(",\n", $batch) . ";\n";
 $batch = [];
 }
 }
 if (!empty($batch)) {
 echo "INSERT INTO `$table` (`$colNames`) VALUES\n" . implode(",\n", $batch) . ";\n";
 }
 }
 }

 echo "\nSET FOREIGN_KEY_CHECKS = 1;\n";

 $this->audit('export', 'database', null, null, ['filename' => $filename]);
 exit;
 }

 /**
 * 2. Import Full SQL Database Dump
 */
 public function importDatabase()
 {
 $this->postGuard();

 if (empty($_FILES['sql_file']['tmp_name']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
 Session::flash('error', 'يرجى اختيار ملف SQL صالح للاستيراد.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $filePath = $_FILES['sql_file']['tmp_name'];
 $sqlContent = file_get_contents($filePath);

 if (empty($sqlContent)) {
 Session::flash('error', 'الملف المرفوع فارغ.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $pdo = Database::getInstance()->getPdo();
 try {
 $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
 $pdo->exec($sqlContent);
 $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

 Settings::clear();
 if (class_exists('Cache')) Cache::flush();

 $this->audit('import', 'database', null, null, ['original_name' => $_FILES['sql_file']['name']]);
 Session::flash('success', 'تم استعادة واستيراد قاعدة البيانات بنجاح تام وتحديث كافة الجداول.');
 } catch (Throwable $e) {
 Session::flash('error', 'فشل استيراد قاعدة البيانات: ' . $e->getMessage());
 }

 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 /**
 * 3. Export Articles as JSON
 */
 public function exportArticlesJson()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $articles = $db->query("
 SELECT a.*, c.name AS category_name, c.slug AS category_slug, u.username AS author_name, u.email AS author_email
 FROM articles a
 LEFT JOIN categories c ON a.category_id = c.id
 LEFT JOIN users u ON a.author_id = u.id
 ORDER BY a.id ASC
 ")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'tech_platform_articles_' . date('Y-m-d_His') . '.json';
 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');
 echo json_encode([
 'platform' => 'Technology News Platform',
 'exported_at' => date('Y-m-d H:i:s'),
 'total_items' => count($articles),
 'articles' => $articles
 ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

 $this->audit('export', 'articles_json', null, null, ['count' => count($articles)]);
 exit;
 }

 /**
 * 4. Export Articles as CSV (Excel Compatible with UTF-8 BOM)
 */
 public function exportArticlesCsv()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $articles = $db->query("
 SELECT a.id, a.title, a.slug, c.name AS category, u.username AS author, a.status, a.views_count, a.published_at, a.created_at
 FROM articles a
 LEFT JOIN categories c ON a.category_id = c.id
 LEFT JOIN users u ON a.author_id = u.id
 ORDER BY a.id DESC
 ")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'articles_export_' . date('Y-m-d') . '.csv';
 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');

 $out = fopen('php://output', 'w');
 // UTF-8 BOM for Arabic support in Excel
 fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

 fputcsv($out, ['المعرف ID', 'عنوان المقال', 'الرابط الدائم (Slug)', 'التصنيف', 'الكاتب', 'الحالة', 'المشاهدات', 'تاريخ النشر', 'تاريخ الإنشاء']);
 foreach ($articles as $row) {
 fputcsv($out, [
 $row['id'],
 $row['title'],
 $row['slug'],
 $row['category'] ?? 'عام',
 $row['author'] ?? 'محرر',
 $row['status'],
 $row['views_count'],
 $row['published_at'],
 $row['created_at']
 ]);
 }
 fclose($out);

 $this->audit('export', 'articles_csv', null, null, ['count' => count($articles)]);
 exit;
 }

 /**
 * 5. Import Articles from JSON
 */
 public function importArticlesJson()
 {
 $this->postGuard();

 if (empty($_FILES['articles_json_file']['tmp_name']) || $_FILES['articles_json_file']['error'] !== UPLOAD_ERR_OK) {
 Session::flash('error', 'يرجى اختيار ملف JSON صالح.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $jsonStr = file_get_contents($_FILES['articles_json_file']['tmp_name']);
 $data = json_decode($jsonStr, true);

 if (!$data || (!isset($data['articles']) && !is_array($data))) {
 Session::flash('error', 'صيغة ملف JSON غير صالحة ولا تحتوي على مصفوفة مقالات.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $articlesList = isset($data['articles']) ? $data['articles'] : $data;
 $db = Database::getInstance();
 $defaultCatId = get_default_category_id($db);
 $importedCount = 0;
 $skippedCount = 0;

 foreach ($articlesList as $item) {
 $title = trim($item['title'] ?? '');
 $content = trim($item['content'] ?? ($item['content_ar'] ?? ''));
 if (empty($title)) {
 $skippedCount++;
 continue;
 }

 $slug = !empty($item['slug']) ? trim($item['slug']) : Article::generateSlug($title);

 // Ensure unique slug
 $existing = $db->fetch("SELECT id FROM articles WHERE slug = ?", [$slug]);
 if ($existing) {
 $slug .= '-' . substr(md5(uniqid()), 0, 5);
 }

 $catId = $defaultCatId;
 if (!empty($item['category_name'])) {
 $cat = $db->fetch("SELECT id FROM categories WHERE name = ?", [$item['category_name']]);
 if ($cat) {
 $catId = (int) $cat['id'];
 }
 }

 $stmt = $db->prepare("
 INSERT INTO articles (category_id, author_id, title, slug, excerpt, content, featured_image, status, reading_time_minutes, is_featured, published_at, created_at)
 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
 ");

 $authorId = Auth::id() ?: 1;
 $stmt->execute([
 $catId,
 $authorId,
 $title,
 $slug,
 $item['excerpt'] ?? mb_substr(strip_tags($content), 0, 200),
 $content,
 $item['featured_image'] ?? null,
 $item['status'] ?? 'published',
 (int) ($item['reading_time_minutes'] ?? 3),
 !empty($item['is_featured']) ? 1 : 0,
 $item['published_at'] ?? date('Y-m-d H:i:s'),
 date('Y-m-d H:i:s')
 ]);
 $importedCount++;
 }

 $this->audit('import', 'articles_json', null, null, ['imported' => $importedCount, 'skipped' => $skippedCount]);
 Session::flash('success', "تم استيراد ($importedCount) مقال بنجاح وتجاوز ($skippedCount) عنصر غير صالح.");
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 /**
 * 6. Export Settings Package (JSON)
 */
 public function exportSettingsJson()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $settings = $db->query("SELECT `group`, `key`, `value`, `value_type`, `label_ar`, `description_ar` FROM settings ORDER BY `group`, sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'tech_platform_settings_package_' . date('Y-m-d') . '.json';
 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');
 echo json_encode([
 'platform' => 'Technology News Platform Settings Package',
 'version' => '2.5',
 'exported_at' => date('Y-m-d H:i:s'),
 'settings' => $settings
 ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

 $this->audit('export', 'settings_json', null, null, ['count' => count($settings)]);
 exit;
 }

 /**
 * 7. Import Settings Package (JSON)
 */
 public function importSettingsJson()
 {
 $this->postGuard();

 if (empty($_FILES['settings_json_file']['tmp_name']) || $_FILES['settings_json_file']['error'] !== UPLOAD_ERR_OK) {
 Session::flash('error', 'يرجى اختيار ملف حزمة الإعدادات JSON.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $jsonStr = file_get_contents($_FILES['settings_json_file']['tmp_name']);
 $data = json_decode($jsonStr, true);

 if (!$data || !isset($data['settings']) || !is_array($data['settings'])) {
 Session::flash('error', 'صيغة ملف الإعدادات غير صالحة.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $db = Database::getInstance();
 $updatedCount = 0;

 foreach ($data['settings'] as $item) {
 if (empty($item['key'])) continue;

 $stmt = $db->prepare("
 UPDATE settings SET `value` = ? WHERE `key` = ?
 ");
 $stmt->execute([$item['value'] ?? '', $item['key']]);
 $updatedCount++;
 }

 Settings::clear();
 if (class_exists('Cache')) Cache::flush();

 $this->audit('import', 'settings_json', null, null, ['updated' => $updatedCount]);
 Session::flash('success', "تم استيراد وتطبيق حزمة الإعدادات بنجاح ($updatedCount إعداد تم تحديثه).");
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 /**
 * 8. Export Newsletter Subscribers as CSV
 */
 public function exportSubscribersCsv()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $subscribers = $db->query("SELECT id, email, status, subscribed_at FROM newsletters ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'newsletter_subscribers_' . date('Y-m-d') . '.csv';
 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');

 $out = fopen('php://output', 'w');
 fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
 fputcsv($out, ['ID', 'البريد الإلكتروني', 'الحالة', 'تاريخ الاشتراك']);
 foreach ($subscribers as $s) {
 fputcsv($out, [$s['id'], $s['email'], $s['status'] ?? 'active', $s['subscribed_at'] ?? '']);
 }
 fclose($out);

 $this->audit('export', 'subscribers_csv', null, null, ['count' => count($subscribers)]);
 exit;
 }

 /**
 * 9. Import Newsletter Subscribers from CSV
 */
 public function importSubscribersCsv()
 {
 $this->postGuard();

 if (empty($_FILES['subscribers_csv_file']['tmp_name']) || $_FILES['subscribers_csv_file']['error'] !== UPLOAD_ERR_OK) {
 Session::flash('error', 'يرجى اختيار ملف CSV صالح.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $file = fopen($_FILES['subscribers_csv_file']['tmp_name'], 'r');
 if (!$file) {
 Session::flash('error', 'تعذر قراءة ملف CSV.');
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 $db = Database::getInstance();
 $stmt = $db->prepare("INSERT IGNORE INTO newsletters (email, status, subscribed_at) VALUES (?, 'active', NOW())");

 $added = 0;
 while (($row = fgetcsv($file, 1000, ",")) !== false) {
 foreach ($row as $cell) {
 $email = filter_var(trim($cell), FILTER_VALIDATE_EMAIL);
 if ($email) {
 $stmt->execute([$email]);
 if ($stmt->rowCount() > 0) {
 $added++;
 }
 }
 }
 }
 fclose($file);

 $this->audit('import', 'subscribers_csv', null, null, ['added' => $added]);
 Session::flash('success', "تم استيراد ($added) مشترك جديد في النشرة البريدية بنجاح.");
 header('Location: ' . app_url('admin/backup'));
 exit;
 }

 /**
 * 10. Export Polls Data as JSON
 */
 public function exportPollsJson()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $polls = $db->query("SELECT * FROM polls ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

 foreach ($polls as &$p) {
 $p['options'] = $db->query("SELECT * FROM poll_options WHERE poll_id = " . (int)$p['id'] . " ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
 $p['total_votes'] = array_sum(array_column($p['options'], 'votes_count'));
 }

 $filename = 'polls_analytics_export_' . date('Y-m-d') . '.json';
 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');
 echo json_encode($polls, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

 $this->audit('export', 'polls_json', null, null, ['count' => count($polls)]);
 exit;
 }

 /**
 * 11. Export Polls Data as CSV (Excel)
 */
 public function exportPollsCsv()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $polls = $db->query("SELECT * FROM polls ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'polls_analytics_export_' . date('Y-m-d') . '.csv';
 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');

 $out = fopen('php://output', 'w');
 fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
 fputcsv($out, ['ID', 'السؤال', 'الحالة', 'مميز بالرئيسية', 'إجمالي الأصوات', 'تفاصيل الخيارات والنسب', 'تاريخ الإنشاء']);

 foreach ($polls as $p) {
 $options = $db->query("SELECT * FROM poll_options WHERE poll_id = " . (int)$p['id'] . " ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
 $totalVotes = array_sum(array_column($options, 'votes_count'));
 
 $optDetails = [];
 foreach ($options as $o) {
 $pct = $totalVotes > 0 ? round(($o['votes_count'] / $totalVotes) * 100) : 0;
 $optDetails[] = "{$o['title']} ({$o['votes_count']} صوت - {$pct}%)";
 }

 fputcsv($out, [
 $p['id'],
 $p['question'],
 $p['status'],
 !empty($p['is_featured']) ? 'نعم' : 'لا',
 $totalVotes,
 implode(' | ', $optDetails),
 $p['created_at']
 ]);
 }
 fclose($out);

 $this->audit('export', 'polls_csv', null, null, ['count' => count($polls)]);
 exit;
 }

 /**
 * 12. Export Tutorials Studio Archive as JSON
 */
 public function exportTutorialsJson()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $tutorials = $db->query("
 SELECT t.*, c.name as category_name 
 FROM tutorials t 
 LEFT JOIN categories c ON c.id = t.category_id 
 ORDER BY t.id DESC
 ")->fetchAll(PDO::FETCH_ASSOC);

 foreach ($tutorials as &$tut) {
 $tut['steps'] = $db->query("SELECT * FROM tutorial_steps WHERE tutorial_id = " . (int)$tut['id'] . " ORDER BY step_number ASC")->fetchAll(PDO::FETCH_ASSOC);
 }

 $filename = 'tutorials_studio_archive_' . date('Y-m-d') . '.json';
 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');
 echo json_encode($tutorials, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

 $this->audit('export', 'tutorials_json', null, null, ['count' => count($tutorials)]);
 exit;
 }

 /**
 * 13. Export RSS Feeds as standard OPML (XML)
 */
 public function exportRssOpml()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $sources = $db->query("
 SELECT s.*, c.name as category_name 
 FROM rss_sources s 
 LEFT JOIN categories c ON c.id = s.category_id 
 ORDER BY s.id ASC
 ")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'tech_news_feeds_' . date('Y-m-d') . '.opml';
 header('Content-Type: text/xml; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');

 echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
 echo '<opml version="2.0">' . "\n";
 echo " <head>\n";
 echo " <title>مصادر الأخبار التقنية - عصب التقنية</title>\n";
 echo " <dateCreated>" . date('r') . "</dateCreated>\n";
 echo " </head>\n";
 echo " <body>\n";

 $byCategory = [];
 foreach ($sources as $s) {
 $cat = $s['category_name'] ?: 'عام';
 $byCategory[$cat][] = $s;
 }

 foreach ($byCategory as $catName => $items) {
 echo ' <outline text="' . htmlspecialchars($catName, ENT_XML1, 'UTF-8') . '" title="' . htmlspecialchars($catName, ENT_XML1, 'UTF-8') . '">' . "\n";
 foreach ($items as $item) {
 echo ' <outline type="rss" text="' . htmlspecialchars($item['name'], ENT_XML1, 'UTF-8') . '" title="' . htmlspecialchars($item['name'], ENT_XML1, 'UTF-8') . '" xmlUrl="' . htmlspecialchars($item['url'], ENT_XML1, 'UTF-8') . '" />' . "\n";
 }
 echo " </outline>\n";
 }

 echo " </body>\n";
 echo "</opml>";

 $this->audit('export', 'rss_opml', null, null, ['count' => count($sources)]);
 exit;
 }

 /**
 * 14. Export AI Classifier Rules as JSON
 */
 public function exportClassifierRulesJson()
 {
 $this->guardAdmin();
 $rules = [];
 if (class_exists('CategoryClassifier')) {
 $rules = [
 'rules' => CategoryClassifier::getRules(),
 'source_rules' => CategoryClassifier::getSourceRules(),
 'exported_at' => date('Y-m-d H:i:s')
 ];
 }

 $filename = 'ai_classifier_rules_package_' . date('Y-m-d') . '.json';
 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');
 echo json_encode($rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

 $this->audit('export', 'classifier_rules_json', null, null, []);
 exit;
 }

 /**
 * 15. Export Contact Messages as CSV (Excel)
 */
 public function exportContactMessagesCsv()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'contact_messages_archive_' . date('Y-m-d') . '.csv';
 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');

 $out = fopen('php://output', 'w');
 fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
 fputcsv($out, ['ID', 'الاسم', 'البريد الإلكتروني', 'الموضوع', 'نص الرسالة', 'الحالة', 'تاريخ الإرسال']);

 foreach ($messages as $m) {
 fputcsv($out, [
 $m['id'],
 $m['name'] ?? '',
 $m['email'] ?? '',
 $m['subject'] ?? '',
 $m['message'] ?? '',
 $m['status'] ?? 'unread',
 $m['created_at'] ?? ''
 ]);
 }
 fclose($out);

 $this->audit('export', 'contact_messages_csv', null, null, ['count' => count($messages)]);
 exit;
 }

 /**
 * 16. Export Live Blog Timeline Archive as JSON
 */
 public function exportLiveBlogJson()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $blogs = $db->query("SELECT * FROM live_blogs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

 foreach ($blogs as &$b) {
 $b['entries'] = $db->query("SELECT * FROM live_blog_entries WHERE live_blog_id = " . (int)$b['id'] . " ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
 }

 $filename = 'live_blogs_archive_' . date('Y-m-d') . '.json';
 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');
 echo json_encode($blogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

 $this->audit('export', 'live_blogs_json', null, null, ['count' => count($blogs)]);
 exit;
 }

 /**
 * 17. Export System Audit Activity Logs as CSV
 */
 public function exportActivityLogsCsv()
 {
 $this->guardAdmin();
 $db = Database::getInstance();
 $logs = $db->query("
 SELECT a.*, u.username 
 FROM activity_logs a 
 LEFT JOIN users u ON u.id = a.user_id 
 ORDER BY a.id DESC LIMIT 5000
 ")->fetchAll(PDO::FETCH_ASSOC);

 $filename = 'system_activity_logs_' . date('Y-m-d') . '.csv';
 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="' . $filename . '"');

 $out = fopen('php://output', 'w');
 fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
 fputcsv($out, ['ID', 'المستخدم', 'الحدث (Action)', 'القسم (Entity)', 'الوصف', 'الآي بي (IP)', 'التاريخ']);

 foreach ($logs as $l) {
 fputcsv($out, [
 $l['id'],
 $l['username'] ?? 'النظام',
 $l['action'] ?? '',
 $l['entity_type'] ?? '',
 $l['description'] ?? '',
 $l['ip_address'] ?? '',
 $l['created_at'] ?? ''
 ]);
 }
 fclose($out);

 $this->audit('export', 'activity_logs_csv', null, null, ['count' => count($logs)]);
 exit;
 }
}
