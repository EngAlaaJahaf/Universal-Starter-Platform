<?php

class TrafficRadarController extends AdminController
{
 public function index()
 {
 $this->guardAdmin();
 $db = new Database();

 $type = trim((string) ($_GET['type'] ?? ''));
 $q = trim((string) ($_GET['q'] ?? ''));
 $perPage = max(10, min(200, (int) ($_GET['per_page'] ?? 30)));
 $page = max(1, (int) ($_GET['page'] ?? 1));

 $where = [];
 $params = [];

 if ($type !== '') {
 $where[] = "visitor_type = :type";
 $params[':type'] = $type;
 }

 if ($q !== '') {
 $where[] = "(bot_name LIKE :q OR ip_address LIKE :q OR request_uri LIKE :q OR user_agent LIKE :q)";
 $params[':q'] = "%{$q}%";
 }

 $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

 // Total count
 $countSql = "SELECT COUNT(*) as total FROM bot_traffic_logs {$whereSql}";
 $totalLogs = (int) ($db->fetch($countSql, $params)['total'] ?? 0);
 $totalPages = max(1, (int) ceil($totalLogs / $perPage));
 if ($page > $totalPages) $page = $totalPages;
 $offset = ($page - 1) * $perPage;

 // Fetch logs
 $logs = $db->fetchAll("
 SELECT * FROM bot_traffic_logs 
 {$whereSql} 
 ORDER BY created_at DESC 
 LIMIT {$perPage} OFFSET {$offset}
 ", $params);

 // Overall Traffic KPIs (Last 24 Hours)
 $kpiStats = $db->fetchAll("
 SELECT visitor_type, COUNT(*) as cnt 
 FROM bot_traffic_logs 
 WHERE created_at >= NOW() - INTERVAL 24 HOUR 
 GROUP BY visitor_type
 ");
 $kpiMap = array_column($kpiStats, 'cnt', 'visitor_type');

 $totalHits24h = array_sum($kpiMap);
 $searchEngineHits = (int) ($kpiMap['search_engine'] ?? 0);
 $aiCrawlerHits = (int) ($kpiMap['ai_crawler'] ?? 0);
 $humanHits = (int) ($kpiMap['human_visitor'] ?? 0);
 $scannerHits = (int) ($kpiMap['security_scanner'] ?? 0) + (int) ($kpiMap['malicious_bot'] ?? 0);

 // Top Visited Pages
 $topPages = $db->fetchAll("
 SELECT request_uri, COUNT(*) as cnt, AVG(response_time_ms) as avg_speed 
 FROM bot_traffic_logs 
 WHERE created_at >= NOW() - INTERVAL 24 HOUR 
 GROUP BY request_uri 
 ORDER BY cnt DESC 
 LIMIT 5
 ");

 // Top Spiders & Crawlers
 $topBots = $db->fetchAll("
 SELECT bot_name, visitor_type, COUNT(*) as cnt 
 FROM bot_traffic_logs 
 WHERE visitor_type != 'human_visitor' AND created_at >= NOW() - INTERVAL 24 HOUR 
 GROUP BY bot_name, visitor_type 
 ORDER BY cnt DESC 
 LIMIT 6
 ");

 $this->view('admin/traffic/index', [
 'logs' => $logs,
 'totalLogs' => $totalLogs,
 'totalPages' => $totalPages,
 'page' => $page,
 'perPage' => $perPage,
 'type' => $type,
 'q' => $q,
 'totalHits24h' => $totalHits24h,
 'searchEngineHits' => $searchEngineHits,
 'aiCrawlerHits' => $aiCrawlerHits,
 'humanHits' => $humanHits,
 'scannerHits' => $scannerHits,
 'topPages' => $topPages,
 'topBots' => $topBots,
 ]);
 }

 public function exportCsv()
 {
 $this->guardAdmin();
 $db = new Database();
 $logs = $db->fetchAll("SELECT * FROM bot_traffic_logs ORDER BY id DESC LIMIT 5000");

 header('Content-Type: text/csv; charset=utf-8');
 header('Content-Disposition: attachment; filename="traffic_radar_' . date('Y-m-d_His') . '.csv"');

 $out = fopen('php://output', 'w');
 fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
 fputcsv($out, ['المعرف (#ID)', 'نوع الكائن / الزائر', 'اسم العنكبوت أو المتصفح', 'عنوان IP', 'المسار المطلوب', 'طريقة HTTP', 'كود الحالة', 'السرعة (ms)', 'التاريخ والوقت', 'User Agent']);

 foreach ($logs as $row) {
 fputcsv($out, [
 $row['id'],
 $row['visitor_type'],
 $row['bot_name'],
 $row['ip_address'],
 $row['request_uri'],
 $row['http_method'],
 $row['status_code'],
 $row['response_time_ms'],
 $row['created_at'],
 $row['user_agent']
 ]);
 }
 fclose($out);
 exit;
 }

 public function purge()
 {
 $this->postGuard();
 $db = new Database();
 $db->query("DELETE FROM bot_traffic_logs WHERE created_at < NOW() - INTERVAL 30 DAY");
 Session::flash('success', 'تم تنظيف سجلات حركة المرور الأقدم من 30 يوماً بنجاح!');
 return $this->redirect('admin/traffic-radar');
 }
}
