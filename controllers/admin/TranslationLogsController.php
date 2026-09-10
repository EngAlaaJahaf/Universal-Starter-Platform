<?php

class TranslationLogsController extends AdminController
{
 public function index()
 {
 $db = Database::getInstance();
 $page = max(1, (int) ($_GET['page'] ?? 1));
 $perPage = 30;
 $offset = ($page - 1) * $perPage;

 $provider = trim($_GET['provider'] ?? '');
 $status = trim($_GET['status'] ?? '');

 $where = '1=1';
 $params = [];
 if ($provider) { $where .= ' AND provider = ?'; $params[] = $provider; }
 if ($status) { $where .= ' AND status = ?'; $params[] = $status; }

 $total = (int) ($db->fetch("SELECT COUNT(*) AS cnt FROM translation_logs WHERE {$where}", $params)['cnt'] ?? 0);
 $pages = max(1, (int) ceil($total / $perPage));

 $stmt = $db->prepare("SELECT * FROM translation_logs WHERE {$where} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
 $stmt->execute($params);
 $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

 // Stats
 $stats = [];
 foreach ($db->fetchAll('SELECT status, COUNT(*) AS cnt FROM translation_logs GROUP BY status') as $r) {
 $stats[$r['status']] = (int) $r['cnt'];
 }
 $providerStats = [];
 foreach ($db->fetchAll('SELECT provider, COUNT(*) AS cnt FROM translation_logs GROUP BY provider') as $r) {
 $providerStats[$r['provider']] = (int) $r['cnt'];
 }

 $this->renderAdmin('admin/translation_logs/index', [
 'logs' => $logs,
 'total' => $total,
 'page' => $page,
 'pages' => $pages,
 'provider' => $provider,
 'status' => $status,
 'stats' => $stats,
 'providerStats' => $providerStats,
 ]);
 }

 public function show()
 {
 $id = (int) ($_GET['id'] ?? 0);
 $db = Database::getInstance();
 $log = $db->fetch('SELECT * FROM translation_logs WHERE id = ?', [$id]);
 if (!$log) {
 Session::flash('error', 'السجل غير موجود.');
 header('Location: ' . app_url('admin/translation-logs'));
 exit;
 }
 $this->renderAdmin('admin/translation_logs/show', ['log' => $log]);
 }

 public function delete()
 {
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 header('Location: ' . app_url('admin/translation-logs'));
 exit;
 }
 $id = (int) ($_POST['id'] ?? 0);
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $db->prepare('DELETE FROM translation_logs WHERE id = ?')->execute([$id]);
 ActivityLogger::log('delete', 'translation_logs', $id, 'حذف سجل ترجمة #' . $id);
 Session::flash('success', 'تم حذف السجل بنجاح.');
 header('Location: ' . app_url('admin/translation-logs'));
 exit;
 }

 public function clearAll()
 {
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 header('Location: ' . app_url('admin/translation-logs'));
 exit;
 }
 CSRF::validate($_POST['_csrf'] ?? '');
 $filter = $_POST['filter'] ?? 'failed';
 $db = Database::getInstance();

 if ($filter === 'all') {
 $db->query('TRUNCATE TABLE translation_logs');
 $msg = 'تم مسح جميع سجلات الترجمة.';
 } else {
 $db->prepare('DELETE FROM translation_logs WHERE status = ?')->execute([$filter]);
 $label = ['failed' => 'الفاشلة', 'success' => 'الناجحة', 'fallback' => 'الاحتياطية'][$filter] ?? $filter;
 $msg = "تم مسح السجلات {$label}.";
 }
 ActivityLogger::log('delete', 'translation_logs', null, $msg);
 Session::flash('success', $msg);
 header('Location: ' . app_url('admin/translation-logs'));
 exit;
 }

 public function testProvider()
 {
 $this->guardAdmin();
 $provider = trim($_POST['provider'] ?? $_GET['provider'] ?? 'omniroute');
 $overrides = [];
 if (!empty($_POST['omniroute_endpoint'])) $overrides['omniroute_endpoint'] = trim($_POST['omniroute_endpoint']);
 if (isset($_POST['omniroute_api_key'])) $overrides['omniroute_api_key'] = trim($_POST['omniroute_api_key']);
 if (!empty($_POST['omniroute_model'])) $overrides['omniroute_model'] = trim($_POST['omniroute_model']);
 if (isset($_POST['gemini_api_key'])) $overrides['gemini_api_key'] = trim($_POST['gemini_api_key']);
 if (isset($_POST['groq_api_key'])) $overrides['groq_api_key'] = trim($_POST['groq_api_key']);
 if (isset($_POST['deepseek_api_key'])) $overrides['deepseek_api_key'] = trim($_POST['deepseek_api_key']);
 if (isset($_POST['openai_api_key'])) $overrides['openai_api_key'] = trim($_POST['openai_api_key']);
 if (!empty($_POST['custom_api_endpoint'])) $overrides['custom_api_endpoint'] = trim($_POST['custom_api_endpoint']);
 if (!empty($_POST['opencode_model'])) $overrides['opencode_model'] = trim($_POST['opencode_model']);

 $res = AiTranslator::testProvider($provider, $overrides);

 header('Content-Type: application/json; charset=utf-8');
 echo json_encode($res, JSON_UNESCAPED_UNICODE);
 exit;
 }

 public function getModels()
 {
 $this->guardAdmin();
 $provider = trim($_GET['provider'] ?? $_POST['provider'] ?? 'omniroute');
 $overrides = [];
 if (!empty($_POST['omniroute_endpoint'])) $overrides['omniroute_endpoint'] = trim($_POST['omniroute_endpoint']);
 if (isset($_POST['omniroute_api_key'])) $overrides['omniroute_api_key'] = trim($_POST['omniroute_api_key']);
 if (isset($_GET['omniroute_endpoint'])) $overrides['omniroute_endpoint'] = trim($_GET['omniroute_endpoint']);
 if (isset($_GET['omniroute_api_key'])) $overrides['omniroute_api_key'] = trim($_GET['omniroute_api_key']);

 $res = AiTranslator::getProviderModelsWithHealth($provider, $overrides);

 header('Content-Type: application/json; charset=utf-8');
 echo json_encode($res, JSON_UNESCAPED_UNICODE);
 exit;
 }
}

