<?php

class CronController extends AdminController
{
 public function index()
 {
 $this->guardAdmin();
 $db = new Database();

 // إحصائيات اليوم
 $todayStats = $db->fetch("
 SELECT 
 COUNT(*) as total_runs,
 SUM(articles_published) as total_published,
 SUM(articles_skipped) as total_skipped,
 SUM(articles_failed) as total_failed,
 MAX(created_at) as last_run
 FROM cron_logs
 WHERE DATE(created_at) = CURDATE()
 ");

 // آخر 20 سجل
 $logs = $db->fetchAll("
 SELECT * FROM cron_logs ORDER BY created_at DESC LIMIT 20
 ");

 // إجمالي المقالات المنشورة تلقائياً (لها source_url)
 $autoTotal = $db->fetch("SELECT COUNT(*) as cnt FROM articles WHERE source_url IS NOT NULL AND source_url != ''");

 // أحدث المقالات المنشورة تلقائياً
 $recentAutoArticles = $db->fetchAll("
 SELECT a.id, a.title, a.title_ar, a.slug, a.source_name, a.source_url, a.category_id, a.created_at,
 c.name as category_name, c.slug as category_slug
 FROM articles a 
 LEFT JOIN categories c ON c.id = a.category_id 
 WHERE a.source_url IS NOT NULL AND a.source_url != ''
 ORDER BY a.id DESC 
 LIMIT 15
 ");

 // قائمة جميع مصادر الـ RSS المتاحة مع تصنيفاتها
 $allSources = $db->fetchAll("
 SELECT s.id, s.name, s.url, s.category_id, s.last_fetched_at,
 c.name as category_name, c.slug as category_slug
 FROM rss_sources s
 LEFT JOIN categories c ON c.id = s.category_id
 ORDER BY s.id ASC
 ");

 // تشغيل يدوي لدورة جديدة (الكل أو مصادر محددة)
 $manualResult = null;
 if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run_now') {
 $cronFile = dirname(dirname(dirname(__FILE__))) . '/cron/rss_auto_publish.php';
 if (file_exists($cronFile)) {
 $statusFile = dirname(dirname(dirname(__FILE__))) . '/storage/cron_status.json';

 // إغلاق قفل الجلسة فوراً لمنع تعليق المتصفح نهائياً
 if (session_status() === PHP_SESSION_ACTIVE) {
 session_write_close();
 }

 // فحص المصادر المختارة
 $sourceIds = [];
 if (!empty($_POST['source_ids']) && is_array($_POST['source_ids'])) {
 $sourceIds = array_filter(array_map('intval', $_POST['source_ids']));
 } elseif (!empty($_POST['single_source_id'])) {
 $sourceIds = [(int)$_POST['single_source_id']];
 }

                $phpBin = 'php';
                if (defined('PHP_BINARY') && is_file(PHP_BINARY) && !preg_match('/(httpd|apache|nginx|fpm)/i', PHP_BINARY)) {
                    $phpBin = PHP_BINARY;
                } elseif (file_exists('C:\\xampp\\php\\php.exe')) {
                    $phpBin = 'C:\\xampp\\php\\php.exe';
                }

                $cmdArgs = [$phpBin, '-d', 'max_execution_time=0', $cronFile];
                if (!empty($sourceIds)) {
                    $cmdArgs[] = '--sources=' . implode(',', $sourceIds);
                }

                $nullDevice = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'NUL' : '/dev/null';
                $descriptors = [
                    0 => ['file', $nullDevice, 'r'],
                    1 => ['file', $nullDevice, 'w'],
                    2 => ['file', $nullDevice, 'w']
                ];
                $process = @proc_open($cmdArgs, $descriptors, $pipes, dirname($cronFile), null, ['bypass_shell' => true]);
 $pid = null;
 if (is_resource($process)) {
 $pStatus = proc_get_status($process);
 $pid = $pStatus['pid'] ?? null;
 }

 $totalSelected = !empty($sourceIds) ? count($sourceIds) : count($allSources);
 $statusText = !empty($sourceIds) 
 ? (count($sourceIds) === 1 ? 'جلب من مصدر محدد...' : 'جلب من ' . count($sourceIds) . ' مصادر محددة...') 
 : 'بدء دورة جديدة (كافة المصادر)...';

 @file_put_contents($statusFile, json_encode([
 'state' => 'running',
 'is_running' => true,
 'pid' => $pid,
 'current_source' => $statusText,
 'selected_source_ids' => $sourceIds,
 'progress_percent' => 2,
 'sources_processed' => 0,
 'total_sources' => $totalSelected,
 'articles_published' => 0,
 'articles_skipped' => 0,
 'articles_failed' => 0,
 'timestamp' => time()
 ], JSON_UNESCAPED_UNICODE));
 
 $manualResult = 'started';
 } else {
 $manualResult = 'not_found';
 }

 if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode([
 'ok' => ($manualResult === 'started'), 
 'result' => $manualResult, 
 'pid' => $pid ?? null,
 'source_count'=> !empty($sourceIds) ? count($sourceIds) : count($allSources)
 ]);
 exit;
 }
 }

 $this->view('admin/cron/index', [
 'todayStats' => $todayStats,
 'logs' => $logs,
 'autoTotal' => (int) ($autoTotal['cnt'] ?? 0),
 'manualResult' => $manualResult,
 'recentAutoArticles' => $recentAutoArticles,
 'allSources' => $allSources,
 ]);
 }

 public function pause()
 {
 $this->postGuardSystemAdmin();
 $root = dirname(dirname(dirname(__FILE__)));
 $statusFile = $root . '/storage/cron_status.json';

 if (file_exists($statusFile)) {
 $json = @json_decode(file_get_contents($statusFile), true);
 if (is_array($json)) {
 $json['state'] = 'paused';
 $json['is_running'] = false;
 $json['timestamp'] = time();
 @file_put_contents($statusFile, json_encode($json, JSON_UNESCAPED_UNICODE));
 }
 }

 if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => true, 'state' => 'paused']);
 exit;
 }

 Session::flash('success', 'تم إرسال أمر الإيقاف المؤقت (Pause) بنجاح.');
 return $this->redirect('admin/cron');
 }

 public function resume()
 {
 $this->postGuardSystemAdmin();
 $root = dirname(dirname(dirname(__FILE__)));
 $cronFile = $root . '/cron/rss_auto_publish.php';
 $statusFile = $root . '/storage/cron_status.json';

 $json = [];
 if (file_exists($statusFile)) {
 $json = @json_decode(file_get_contents($statusFile), true) ?: [];
 }

 // إغلاق قفل الجلسة فوراً لمنع تعليق المتصفح
 if (session_status() === PHP_SESSION_ACTIVE) {
 session_write_close();
 }

 $pid = null;
 if (file_exists($cronFile)) {
 $descriptors = [
 0 => ['file', 'NUL', 'r'],
 1 => ['file', 'NUL', 'w'],
 2 => ['file', 'NUL', 'w']
 ];
 $process = @proc_open([PHP_BINARY, '-d', 'max_execution_time=0', $cronFile], $descriptors, $pipes, dirname($cronFile), null, ['bypass_shell' => true]);
 if (is_resource($process)) {
 $pStatus = proc_get_status($process);
 $pid = $pStatus['pid'] ?? null;
 }
 }

 $json['state'] = 'running';
 $json['is_running'] = true;
 $json['pid'] = $pid;
 $json['current_source'] = 'جاري الاستئناف...';
 $json['timestamp'] = time();
 @file_put_contents($statusFile, json_encode($json, JSON_UNESCAPED_UNICODE));

 if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => true, 'state' => 'running', 'pid' => $pid]);
 exit;
 }

 Session::flash('success', 'تم استئناف مهمة النشر التلقائي بنجاح من حيث توقفت.');
 return $this->redirect('admin/cron');
 }

 public function statusJson()
 {
 if (!Auth::check() || !Auth::isSuperAdmin()) {
 header('Content-Type: application/json; charset=utf-8');
 http_response_code(403);
 echo json_encode(['error' => 'Unauthorized']);
 exit;
 }

 // تحرير قفل الجلسة مباشرة أثناء استعلام الـ AJAX الحي
 if (session_status() === PHP_SESSION_ACTIVE) {
 session_write_close();
 }

 $root = dirname(dirname(dirname(__FILE__)));
 $statusFile = $root . '/storage/cron_status.json';
 $db = new Database();

 $statusData = [
 'is_running' => false,
 'state' => 'idle',
 'current_source' => '',
 'sources_processed' => 0,
 'total_sources' => 31,
 'progress_percent' => 0,
 'articles_published' => 0,
 'articles_skipped' => 0,
 'articles_failed' => 0,
 'pid' => null,
 'updated_at' => date('Y-m-d H:i:s')
 ];

 if (file_exists($statusFile)) {
 $json = @json_decode(file_get_contents($statusFile), true);
 if (is_array($json)) {
 $statusData = array_merge($statusData, $json);
 if (($json['state'] ?? '') === 'paused') {
 $statusData['is_running'] = false;
 $statusData['state'] = 'paused';
 } elseif (($json['state'] ?? '') === 'stopped') {
 $statusData['is_running'] = false;
 $statusData['state'] = 'stopped';
 $statusData['progress_percent'] = 0;
 } elseif (!empty($json['timestamp']) && (time() - $json['timestamp']) > 150) {
 $statusData['is_running'] = false;
 $statusData['state'] = 'idle';
 }
 }
 }

 // التحقق من قاعدة البيانات لعدد المقالات المنشورة اليوم
 $todayPublished = (int) $db->fetch("SELECT COUNT(*) as c FROM articles WHERE DATE(created_at) = CURDATE() AND source_url IS NOT NULL")['c'];

 // جلب أحدث 12 مقال منشور تلقائياً
 $recent = $db->fetchAll("
 SELECT a.id, a.title, a.title_ar, a.slug, a.source_name, a.source_url, a.category_id, a.created_at,
 c.name as category_name, c.slug as category_slug
 FROM articles a 
 LEFT JOIN categories c ON c.id = a.category_id 
 WHERE a.source_url IS NOT NULL AND a.source_url != ''
 ORDER BY a.id DESC 
 LIMIT 12
 ");

 $statusData['today_published_total'] = $todayPublished;
 $statusData['recent_articles'] = $recent;

 header('Content-Type: application/json; charset=utf-8');
 echo json_encode($statusData, JSON_UNESCAPED_UNICODE);
 exit;
 }

 public function stop()
 {
 $this->postGuardSystemAdmin();
 $root = dirname(dirname(dirname(__FILE__)));
 $statusFile = $root . '/storage/cron_status.json';

 $json = [];
 if (file_exists($statusFile)) {
 $json = @json_decode(file_get_contents($statusFile), true) ?: [];
 }

 $pid = (int) ($json['pid'] ?? 0);
 if ($pid > 0) {
 if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
 @exec("taskkill /F /PID {$pid} 2>&1");
 } else {
 @exec("kill -9 {$pid} 2>&1");
 }
 }

 // On Windows: Force kill all instances via WMIC
 if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
 @exec('wmic process where "CommandLine like \'%rss_auto_publish.php%\'" delete 2>&1');
 }

 $json['is_running'] = false;
 $json['state'] = 'stopped';
 $json['pid'] = null;
 $json['current_source'] = 'تم الإيقاف بنجاح';
 $json['progress_percent'] = 0;
 $json['timestamp'] = time();
 @file_put_contents($statusFile, json_encode($json, JSON_UNESCAPED_UNICODE));

 if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
 header('Content-Type: application/json; charset=utf-8');
 echo json_encode(['ok' => true, 'state' => 'stopped']);
 exit;
 }

 Session::flash('success', 'تم إيقاف مهمة النشر التلقائي بنجاح.');
 return $this->redirect('admin/cron');
 }
}
