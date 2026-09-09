<?php

class AdminProfileController extends AdminController
{
 public function show()
 {
 $this->guardAdmin();
 $user = Auth::user();
 $db = Database::getInstance();

 // Get user stats
 $articlesCount = (int) ($db->fetch("SELECT COUNT(*) AS c FROM articles WHERE author_id = ?", [$user['id']])['c'] ?? 0);
 $recentLogs = $db->fetchAll("SELECT * FROM activity_logs WHERE user_id = ? ORDER BY id DESC LIMIT 5", [$user['id']]);

 $this->renderAdmin('admin/profile/index', [
 'title' => 'الملف الشخصي للمدير',
 'user' => $user,
 'articlesCount' => $articlesCount,
 'recentLogs' => $recentLogs
 ]);
 }

 public function update()
 {
 $this->postGuard();
 $user = Auth::user();
 $data = Sanitizer::cleanArray($_POST);

 $username = trim($data['username'] ?? $user['username']);
 $email = trim($data['email'] ?? $user['email']);
 $bio = trim($data['bio'] ?? '');
 $lang = $data['preferred_language'] ?? 'ar';
 $theme = $data['theme_preference'] ?? 'auto';

 if (empty($username) || empty($email)) {
 Session::flash('error', 'اسم المستخدم والبريد الإلكتروني حقول مطلوبة.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }

 $db = Database::getInstance();
 
 // Check uniqueness if changed
 if ($username !== $user['username']) {
 $exists = $db->fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $user['id']]);
 if ($exists) {
 Session::flash('error', 'اسم المستخدم مستخدم بالفعل من قِبل عضو آخر.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }
 }

 if ($email !== $user['email']) {
 $exists = $db->fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user['id']]);
 if ($exists) {
 Session::flash('error', 'البريد الإلكتروني مسجل بالفعل لحساب آخر.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }
 }

 $db->prepare("
 UPDATE users SET username = ?, email = ?, bio = ?, preferred_language = ?, theme_preference = ? WHERE id = ?
 ")->execute([$username, $email, $bio, $lang, $theme, $user['id']]);

 $this->audit('update', 'admin_profile', $user['id']);
 Session::flash('success', 'تم حفظ وتحديث بيانات الملف الشخصي بنجاح.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }

 public function changePassword()
 {
 $this->postGuard();
 $user = Auth::user();
 $old = $_POST['current_password'] ?? '';
 $new = $_POST['new_password'] ?? '';
 $confirm = $_POST['confirm_password'] ?? '';

 if (!password_verify($old, $user['password_hash'])) {
 Session::flash('error', 'كلمة المرور الحالية غير صحيحة.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }

 if (strlen($new) < 6) {
 Session::flash('error', 'يجب أن لا تقل كلمة المرور الجديدة عن 6 خانات.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }

 if ($new !== $confirm) {
 Session::flash('error', 'كلمة المرور وتأكيدها غير متطابقين.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }

 $db = Database::getInstance();
 $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([
 password_hash($new, PASSWORD_DEFAULT),
 $user['id']
 ]);

 $this->audit('change_password', 'admin_profile', $user['id']);
 Session::flash('success', 'تم تغيير كلمة المرور بنجاح.');
 header('Location: ' . app_url('admin/profile'));
 exit;
 }
}
