<?php

class UsersController extends AdminController
{
 public function index()
 {
 $db = Database::getInstance();
 $q = trim($_GET['q'] ?? '');
 $role = trim($_GET['role'] ?? '');
 $status = trim($_GET['status'] ?? '');

 $sql = "
 SELECT u.*, r.name as role_name, r.name_ar as role_name_ar
 FROM users u
 LEFT JOIN roles r ON u.role_id = r.id
 WHERE 1=1
 ";
 $params = [];

 if ($q !== '') {
 $sql .= " AND (u.username LIKE ? OR u.email LIKE ?)";
 $params[] = "%{$q}%";
 $params[] = "%{$q}%";
 }
 if ($role !== '') {
 $sql .= " AND u.role_id = ?";
 $params[] = (int) $role;
 }
 if ($status !== '') {
 $sql .= " AND u.status = ?";
 $params[] = $status;
 }

 $sql .= " ORDER BY u.created_at DESC";
 $stmt = $db->prepare($sql);
 $stmt->execute($params);
 $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

 $rolesStmt = $db->query("SELECT * FROM roles ORDER BY id ASC");
 $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/users/index', [
 'users' => $users,
 'roles' => $roles,
 'q' => $q,
 'role' => $role,
 'status'=> $status
 ]);
 }

 public function create()
 {
 $db = Database::getInstance();
 $roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/users/create', [
 'roles' => $roles
 ]);
 }

 public function store()
 {
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 header('Location: ' . app_url('admin/users'));
 exit;
 }

 CSRF::validate($_POST['_csrf'] ?? '');
 $username = trim($_POST['username'] ?? '');
 $email = trim($_POST['email'] ?? '');
 $password = $_POST['password'] ?? '';
 $roleId = (int) ($_POST['role_id'] ?? 3);
 $status = $_POST['status'] ?? 'active';

 if (empty($username) || empty($email) || empty($password)) {
 Session::flash('error', 'يرجى تعبئة كافة الحقول المطلوبة.');
 header('Location: ' . app_url('admin/users/create'));
 exit;
 }

 $db = Database::getInstance();
 $hash = password_hash($password, PASSWORD_BCRYPT);
 $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?)");
 $stmt->execute([$username, $email, $hash, $roleId, $status]);

 ActivityLogger::log('create', 'user', $db->lastInsertId(), "إنشاء مستخدم جديد: {$username}");
 Session::flash('success', 'تم إنشاء المستخدم بنجاح.');
 header('Location: ' . app_url('admin/users'));
 exit;
 }

 public function edit($id)
 {
 $db = Database::getInstance();
 $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
 $stmt->execute([(int) $id]);
 $user = $stmt->fetch(PDO::FETCH_ASSOC);

 if (!$user) {
 header('Location: ' . app_url('admin/users'));
 exit;
 }

 $roles = $db->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

 $this->renderAdmin('admin/users/edit', [
 'user' => $user,
 'roles' => $roles
 ]);
 }

 public function update($id)
 {
 if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 header('Location: ' . app_url('admin/users'));
 exit;
 }

 CSRF::validate($_POST['_csrf'] ?? '');
 $username = trim($_POST['username'] ?? '');
 $email = trim($_POST['email'] ?? '');
 $roleId = (int) ($_POST['role_id'] ?? 3);
 $status = $_POST['status'] ?? 'active';

 $db = Database::getInstance();
 if (!empty($_POST['password'])) {
 $hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
 $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, password_hash = ?, role_id = ?, status = ? WHERE id = ?");
 $stmt->execute([$username, $email, $hash, $roleId, $status, (int) $id]);
 } else {
 $stmt = $db->prepare("UPDATE users SET username = ?, email = ?, role_id = ?, status = ? WHERE id = ?");
 $stmt->execute([$username, $email, $roleId, $status, (int) $id]);
 }

 ActivityLogger::log('update', 'user', $id, "تعديل بيانات المستخدم: {$username}");
 Session::flash('success', 'تم تحديث بيانات المستخدم بنجاح.');
 header('Location: ' . app_url('admin/users'));
 exit;
 }

 public function ban($id)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('ban', 'user', $id, "حظر المستخدم رقم: {$id}");
 Session::flash('success', 'تم حظر المستخدم بنجاح.');
 header('Location: ' . app_url('admin/users'));
 exit;
 }

 public function activate($id)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('activate', 'user', $id, "تفعيل المستخدم رقم: {$id}");
 Session::flash('success', 'تم تفعيل حساب المستخدم.');
 header('Location: ' . app_url('admin/users'));
 exit;
 }

 public function delete($id)
 {
 CSRF::validate($_POST['_csrf'] ?? '');
 $db = Database::getInstance();
 $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
 $stmt->execute([(int) $id]);

 ActivityLogger::log('delete', 'user', $id, "حذف المستخدم رقم: {$id}");
 Session::flash('success', 'تم حذف المستخدم.');
 header('Location: ' . app_url('admin/users'));
 exit;
 }
}
