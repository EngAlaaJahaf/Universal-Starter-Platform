<?php

class ActivityLogController extends AdminController
{
 public static $criticalActions = ['delete', 'bulk_delete', 'ban', 'regenerate', 'truncate', 'role_change'];
 public static $warningActions = ['update', 'archive', 'reject', 'spam', 'settings_update', 'toggle', 'toggle_featured'];
 public static $successActions = ['create', 'quick_publish', 'draft_article', 'upload', 'activate', 'send'];

 public function index()
 {
 $this->guardAdmin();
 $db = new Database();

 $q = trim((string) ($_GET['q'] ?? ''));
 $action = trim((string) ($_GET['action'] ?? ''));
 $entityType = trim((string) ($_GET['entity_type'] ?? ''));
 $userId = (int) ($_GET['user_id'] ?? 0);
 $severity = trim((string) ($_GET['severity'] ?? ''));
 $dateFrom = trim((string) ($_GET['date_from'] ?? ''));
 $dateTo = trim((string) ($_GET['date_to'] ?? ''));
 $perPage = max(10, min(200, (int) ($_GET['per_page'] ?? 25)));
 $page = max(1, (int) ($_GET['page'] ?? 1));

 $where = [];
 $params = [];

 if ($q !== '') {
 $where[] = "(l.action LIKE :q OR l.entity_type LIKE :q OR l.description LIKE :q OR l.new_values LIKE :q OR l.ip_address LIKE :q OR u.username LIKE :q)";
 $params[':q'] = "%{$q}%";
 }

 if ($action !== '') {
 $where[] = "l.action = :action";
 $params[':action'] = $action;
 }

 if ($entityType !== '') {
 $where[] = "l.entity_type = :entity_type";
 $params[':entity_type'] = $entityType;
 }

 if ($userId > 0) {
 $where[] = "l.user_id = :user_id";
 $params[':user_id'] = $userId;
 }

 if ($dateFrom !== '') {
 $where[] = "l.created_at >= :date_from";
 $params[':date_from'] = $dateFrom . ' 00:00:00';
 }

 if ($dateTo !== '') {
 $where[] = "l.created_at <= :date_to";
 $params[':date_to'] = $dateTo . ' 23:59:59';
 }

 if ($severity === 'critical') {
 $inMarks = implode(',', array_fill(0, count(self::$criticalActions), '?'));
 $where[] = "l.action IN (" . $inMarks . ")";
 foreach (self::$criticalActions as $ca) {
 $params[] = $ca;
 }
 } elseif ($severity === 'warning') {
 $inMarks = implode(',', array_fill(0, count(self::$warningActions), '?'));
 $where[] = "l.action IN (" . $inMarks . ")";
 foreach (self::$warningActions as $wa) {
 $params[] = $wa;
 }
 } elseif ($severity === 'success') {
 $inMarks = implode(',', array_fill(0, count(self::$successActions), '?'));
 $where[] = "l.action IN (" . $inMarks . ")";
 foreach (self::$successActions as $sa) {
 $params[] = $sa;
 }
 }

 // Build Where SQL
 $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

 // Count Total
 $countSql = "SELECT COUNT(*) as total FROM activity_logs l LEFT JOIN users u ON u.id = l.user_id {$whereSql}";
 $totalRow = $db->fetch($countSql, $params);
 $totalLogs = (int) ($totalRow['total'] ?? 0);
 $totalPages = max(1, (int) ceil($totalLogs / $perPage));
 if ($page > $totalPages) $page = $totalPages;
 $offset = ($page - 1) * $perPage;

 // Fetch Paginated Logs
 $logsSql = "SELECT l.*, u.username, u.email 
 FROM activity_logs l 
 LEFT JOIN users u ON u.id = l.user_id 
 {$whereSql} 
 ORDER BY l.created_at DESC 
 LIMIT {$perPage} OFFSET {$offset}";
 $logs = $db->fetchAll($logsSql, $params);

 // Enrich each log with GeoIP country info (compact local DB)
 foreach ($logs as &$log) {
 $cc = GeoIp::lookup($log['ip_address'] ?? '');
 $log['country_code'] = $cc ?? '';
 $log['country_name'] = $cc ? GeoIp::countryName($cc) : '';
 $log['country_flag'] = $cc ? GeoIp::flag($cc) : '';
 }
 unset($log);

 // Security & Suspicious Activity Analytics
 $criticalCount24h = (int) ($db->fetch("
 SELECT COUNT(*) as cnt 
 FROM activity_logs 
 WHERE created_at >= NOW() - INTERVAL 24 HOUR 
 AND action IN ('delete', 'bulk_delete', 'ban', 'regenerate', 'truncate')
 ")['cnt'] ?? 0);

 $total24h = (int) ($db->fetch("
 SELECT COUNT(*) as cnt 
 FROM activity_logs 
 WHERE created_at >= NOW() - INTERVAL 24 HOUR
 ")['cnt'] ?? 0);

 $uniqueActorsCount = (int) ($db->fetch("
 SELECT COUNT(DISTINCT user_id) as cnt FROM activity_logs WHERE created_at >= NOW() - INTERVAL 7 DAY
 ")['cnt'] ?? 0);

 $mostActiveEntity = $db->fetch("
 SELECT entity_type, COUNT(*) as cnt 
 FROM activity_logs 
 WHERE created_at >= NOW() - INTERVAL 7 DAY 
 GROUP BY entity_type 
 ORDER BY cnt DESC 
 LIMIT 1
 ");

 // Distinct list for filter dropdowns
 $availableActions = $db->fetchAll("SELECT DISTINCT action FROM activity_logs WHERE action IS NOT NULL ORDER BY action ASC");
 $availableEntities = $db->fetchAll("SELECT DISTINCT entity_type FROM activity_logs WHERE entity_type IS NOT NULL ORDER BY entity_type ASC");
 $availableUsers = $db->fetchAll("SELECT id, username FROM users WHERE status = 'active' ORDER BY username ASC");

 // Annotate logs with severity
 foreach ($logs as &$log) {
 $act = strtolower($log['action']);
 if (in_array($act, self::$criticalActions, true)) {
 $log['severity'] = 'critical';
 $log['badge_class'] = 'bg-danger text-white';
 $log['icon'] = 'bi-exclamation-octagon-fill';
 } elseif (in_array($act, self::$warningActions, true)) {
 $log['severity'] = 'warning';
 $log['badge_class'] = 'bg-warning text-dark';
 $log['icon'] = 'bi-exclamation-triangle-fill';
 } elseif (in_array($act, self::$successActions, true)) {
 $log['severity'] = 'success';
 $log['badge_class'] = 'bg-success text-white';
 $log['icon'] = 'bi-check-circle-fill';
 } else {
 $log['severity'] = 'info';
 $log['badge_class'] = 'bg-info-subtle text-info border';
 $log['icon'] = 'bi-info-circle-fill';
 }

 // Clean json payload for clean readable presentation
 $log['clean_new_values'] = $this->sanitizeValuesForDisplay($log['new_values']);
 $log['clean_old_values'] = $this->sanitizeValuesForDisplay($log['old_values']);
 }
 unset($log);

 $this->view('admin/activity/index', [
 'logs' => $logs,
 'totalLogs' => $totalLogs,
 'totalPages' => $totalPages,
 'page' => $page,
 'perPage' => $perPage,
 'q' => $q,
 'action' => $action,
 'entityType' => $entityType,
 'userId' => $userId,
 'severity' => $severity,
 'dateFrom' => $dateFrom,
 'dateTo' => $dateTo,
 'criticalCount24h' => $criticalCount24h,
 'total24h' => $total24h,
 'uniqueActorsCount' => $uniqueActorsCount,
 'mostActiveEntity' => $mostActiveEntity,
 'availableActions' => array_column($availableActions, 'action'),
 'availableEntities' => array_column($availableEntities, 'entity_type'),
 'availableUsers' => $availableUsers,
 ]);
 }

 public function exportCsv()
 {
 $this->guardAdmin();
 $db = new Database();
 $logs = $db->fetchAll("
 SELECT l.id, u.username, l.action, l.entity_type, l.entity_id, l.ip_address, l.created_at, l.new_values 
 FROM activity_logs l 
 LEFT JOIN users u ON u.id = l.user_id 
 ORDER BY l.id DESC LIMIT 5000
 ");

 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d_His') . '.csv"');

 $out = fopen('php://output', 'w');
 // Add UTF-8 BOM for Excel
 fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
 fputcsv($out, ['المعرف (#ID)', 'المستخدم', 'نوع الإجراء', 'الكيان', 'معرف الكيان', 'عنوان IP', 'الدولة', 'التاريخ والوقت', 'البيانات المسجلة']);

 foreach ($logs as $row) {
 $cc = GeoIp::lookup($row['ip_address'] ?? '');
 fputcsv($out, [
 $row['id'],
 $row['username'] ?? 'نظام آلي',
 $row['action'],
 $row['entity_type'],
 $row['entity_id'],
 $row['ip_address'],
 $cc ? GeoIp::countryName($cc) . ' (' . $cc . ')' : '',
 $row['created_at'],
 $row['new_values']
 ]);
 }
 fclose($out);
 exit;
 }

 public function exportJson()
 {
 $this->guardAdmin();
 $db = new Database();
 $logs = $db->fetchAll("
 SELECT l.id, u.username, l.action, l.entity_type, l.entity_id, l.ip_address, l.created_at, l.old_values, l.new_values, l.user_agent 
 FROM activity_logs l 
 LEFT JOIN users u ON u.id = l.user_id 
 ORDER BY l.id DESC LIMIT 5000
 ");

 header('Content-Type: application/json; charset=utf-8');
 header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d_His') . '.json"');
 echo json_encode([
 'exported_at' => date('Y-m-d H:i:s'),
 'total' => count($logs),
 'logs' => $logs
 ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
 exit;
 }

 public function cleanup()
 {
 $this->postGuard();
 $db = new Database();
 
 // Only keep last 90 days of logs
 $deleted = $db->query("DELETE FROM activity_logs WHERE created_at < NOW() - INTERVAL 90 DAY");
 
 $this->audit('cleanup', 'activity_log', null, null, ['deleted_older_than' => '90_days']);
 Session::flash('success', 'تم تنظيف وأرشفة سجلات النشاط الأقدم من 90 يوماً بنجاح!');
 return $this->redirect('admin/activity-log');
 }

 private function sanitizeValuesForDisplay($raw)
 {
 if (empty($raw)) return null;
 $decoded = @json_decode($raw, true);
 if (is_array($decoded)) {
 unset($decoded['_csrf'], $decoded['_csrf_token'], $decoded['password'], $decoded['password_hash'], $decoded['password_confirmation']);
 return $decoded;
 }
 return $raw;
 }
}
