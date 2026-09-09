<?php

class ContactMessagesController extends AdminController
{
 public function index()
 {
 $db = Database::getInstance();

 // 1. Calculate status counts
 $countsStmt = $db->query("
 SELECT status, COUNT(*) as total 
 FROM contact_messages 
 GROUP BY status
 ");
 $rawCounts = $countsStmt->fetchAll(PDO::FETCH_KEY_PAIR);
 $counts = [
 'all' => array_sum($rawCounts),
 'unread' => (int) ($rawCounts['unread'] ?? 0),
 'read' => (int) ($rawCounts['read'] ?? 0),
 'replied' => (int) ($rawCounts['replied'] ?? 0),
 ];

 // 2. Active status tab
 if (isset($_GET['status'])) {
 $status = $_GET['status'];
 } else {
 $status = ($counts['unread'] > 0) ? 'unread' : 'all';
 }

 $search = trim($_GET['q'] ?? '');

 // 3. Build query
 $where = [];
 $params = [];

 if ($status !== 'all') {
 $where[] = "status = ?";
 $params[] = $status;
 }

 if (!empty($search)) {
 $where[] = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
 $searchTerm = "%{$search}%";
 $params[] = $searchTerm;
 $params[] = $searchTerm;
 $params[] = $searchTerm;
 $params[] = $searchTerm;
 }

 $sql = "SELECT * FROM contact_messages";
 if (!empty($where)) {
 $sql .= " WHERE " . implode(" AND ", $where);
 }
 $sql .= " ORDER BY created_at DESC";

 $stmt = $db->prepare($sql);
 $stmt->execute($params);
 $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/contact_messages/index', [
 'messages' => $messages,
 'status' => $status,
 'counts' => $counts,
 'search' => $search
 ]);
 }

 public function show($id)
 {
 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
 $stmt->execute([(int) $id]);
 $message = $stmt->fetch(PDO::FETCH_ASSOC);

 if (!$message) {
 Session::flash('error', 'الرسالة غير موجودة.');
 header('Location: ' . app_url('admin/messages'));
 exit;
 }

 // Auto mark as read if unread
 if ($message['status'] === 'unread') {
 $up = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
 $up->execute([(int) $id]);
 $message['status'] = 'read';
 }

 $this->renderAdmin('admin/contact_messages/show', [
 'message' => $message
 ]);
 }

 public function toggleRead($id)
 {
 $this->postGuard();
 $db = Database::getInstance();
 
 $stmt = $db->prepare("SELECT status FROM contact_messages WHERE id = ?");
 $stmt->execute([(int) $id]);
 $msg = $stmt->fetch(PDO::FETCH_ASSOC);

 if ($msg) {
 $newStatus = ($msg['status'] === 'unread') ? 'read' : 'unread';
 $up = $db->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
 $up->execute([$newStatus, (int) $id]);
 ActivityLogger::log('update', 'contact_message', $id, "تغيير حالة الرسالة إلى: {$newStatus}");
 Session::flash('success', 'تم تحديث حالة الرسالة بنجاح.');
 }

 header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url('admin/messages')));
 exit;
 }

 public function markReplied($id)
 {
 $this->postGuard();
 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE contact_messages SET status = 'replied', replied_at = NOW() WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('reply', 'contact_message', $id, "تم تعيين الرسالة كـ تم الرد عليها");
 Session::flash('success', 'تم تعيين الرسالة كـ (تم الرد عليها).');
 header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? app_url('admin/messages')));
 exit;
 }

 public function delete($id)
 {
 $this->postGuard();
 $db = Database::getInstance();
 $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('delete', 'contact_message', $id, "حذف رسالة التواصل رقم: {$id}");
 Session::flash('success', 'تم حذف الرسالة بنجاح.');
 header('Location: ' . app_url('admin/messages'));
 exit;
 }
}
