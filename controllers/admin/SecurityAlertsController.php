<?php

class SecurityAlertsController extends AdminController
{
 public function index()
 {
 $this->guardAdmin();
 $db = new Database();

 $severity = trim((string) ($_GET['severity'] ?? ''));
 $type = trim((string) ($_GET['type'] ?? ''));
 $status = trim((string) ($_GET['status'] ?? ''));

 $where = [];
 $params = [];

 if ($severity !== '') {
 $where[] = "severity = :severity";
 $params[':severity'] = $severity;
 }

 if ($type !== '') {
 $where[] = "alert_type = :type";
 $params[':type'] = $type;
 }

 if ($status === 'resolved') {
 $where[] = "is_resolved = 1";
 } elseif ($status === 'unresolved') {
 $where[] = "is_resolved = 0";
 }

 $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

 $alerts = $db->fetchAll("
 SELECT * FROM security_alerts 
 {$whereSql} 
 ORDER BY created_at DESC 
 LIMIT 100
 ", $params);

 // Security Incident Metrics
 $totalAlerts = (int) ($db->fetch("SELECT COUNT(*) as cnt FROM security_alerts")['cnt'] ?? 0);
 $unresolvedCount = (int) ($db->fetch("SELECT COUNT(*) as cnt FROM security_alerts WHERE is_resolved = 0")['cnt'] ?? 0);
 $criticalCount = (int) ($db->fetch("SELECT COUNT(*) as cnt FROM security_alerts WHERE severity = 'critical'")['cnt'] ?? 0);
 $blockedCount = (int) ($db->fetch("SELECT COUNT(*) as cnt FROM security_alerts WHERE is_blocked = 1")['cnt'] ?? 0);

 $this->view('admin/security/index', [
 'alerts' => $alerts,
 'totalAlerts' => $totalAlerts,
 'unresolvedCount' => $unresolvedCount,
 'criticalCount' => $criticalCount,
 'blockedCount' => $blockedCount,
 'severity' => $severity,
 'type' => $type,
 'status' => $status,
 ]);
 }

 public function resolve($id)
 {
 $this->postGuard();
 $db = new Database();
 $db->query("UPDATE security_alerts SET is_resolved = 1 WHERE id = :id", [':id' => (int) $id]);
 Session::flash('success', "تم تعليم التنبيه الأمني (#{$id}) كتمت معالجته بنجاح!");
 return $this->redirect('admin/security-alerts');
 }

 public function delete($id)
 {
 $this->postGuard();
 $db = new Database();
 $db->query("DELETE FROM security_alerts WHERE id = :id", [':id' => (int) $id]);
 Session::flash('success', "تم حذف سجل التنبيه الأمني (#{$id}) بنجاح!");
 return $this->redirect('admin/security-alerts');
 }

 public function clearAll()
 {
 $this->postGuard();
 $db = new Database();
 $db->query("DELETE FROM security_alerts WHERE is_resolved = 1 OR created_at < NOW() - INTERVAL 60 DAY");
 Session::flash('success', "تم مسح كافة التنبيهات الأمنية المعالجة بنجاح!");
 return $this->redirect('admin/security-alerts');
 }
}
