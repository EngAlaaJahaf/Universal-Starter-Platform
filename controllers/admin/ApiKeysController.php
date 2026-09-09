<?php

class ApiKeysController extends AdminController
{
 public function index()
 {
 $this->guardAdmin();
 $db = new Database();

 $keys = $db->fetchAll("SELECT * FROM api_keys ORDER BY id DESC");

 $allScopes = [
 'all' => 'صلاحيات تحكم شاملة (Full Control)',
 'articles:read' => 'قراءة المقالات (Articles Read)',
 'articles:write' => 'إنشاء وتعديل المقالات والنشر الذكي (Articles Write & AI Publish)',
 'articles:delete' => 'حذف المقالات (Articles Delete)',
 'rss:read' => 'قراءة وسحب خلاصات الـ RSS (RSS Feeds Read & Live Pull)',
 'rss:manage' => 'إدارة مصادر الـ RSS (RSS Sources Manage)',
 'categories:read' => 'قراءة التصنيفات (Categories Read)',
 'categories:manage' => 'إدارة التصنيفات (Categories Manage)',
 'ai:translate' => 'استدعاء محرك الترجمة الذكية والمصطلحات (AI Translation Engine)',
 'stats:read' => 'قراءة الإحصاءات ومؤشرات الأداء (Analytics Read)',
 ];

 $this->view('admin/api_keys/index', [
 'keys' => $keys,
 'allScopes' => $allScopes,
 ]);
 }

 public function store()
 {
 $this->postGuard();
 $data = Sanitizer::cleanArray($_POST);

 $name = trim($data['name'] ?? '');
 if (empty($name)) {
 Session::flash('error', 'اسم المفتاح / نقطة النهاية مطلوب.');
 return $this->redirect('admin/api-keys');
 }

 $scopes = $_POST['scopes'] ?? ['all'];
 if (!is_array($scopes) || empty($scopes)) {
 $scopes = ['all'];
 }

 $rateLimit = max(10, (int) ($data['rate_limit_per_minute'] ?? 60));
 $expiresDays = (int) ($data['expires_days'] ?? 0);
 $expiresAt = $expiresDays > 0 ? date('Y-m-d H:i:s', strtotime("+{$expiresDays} days")) : null;

 // Generate raw key: tnp_live_<random32>
 $randomHex = bin2hex(random_bytes(16));
 $rawToken = 'tnp_live_' . $randomHex;
 $keyPrefix = substr($rawToken, 0, 14) . '...';
 $keyHash = hash('sha256', $rawToken);

 $db = new Database();
 $db->query("
 INSERT INTO api_keys 
 (name, key_prefix, secret_token, key_hash, scopes, rate_limit_per_minute, expires_at, is_active, created_at)
 VALUES 
 (:name, :prefix, :secret, :hash, :scopes, :rate_limit, :expires_at, 1, CURRENT_TIMESTAMP)
 ", [
 ':name' => $name,
 ':prefix' => $keyPrefix,
 ':secret' => $rawToken,
 ':hash' => $keyHash,
 ':scopes' => json_encode(array_values($scopes)),
 ':rate_limit' => $rateLimit,
 ':expires_at' => $expiresAt,
 ]);

 $newId = $db->lastInsertId();
 $this->audit('create', 'api_key', $newId, null, ['name' => $name, 'scopes' => $scopes]);

 Session::flash('success_token', $rawToken);
 Session::flash('success_name', $name);
 return $this->redirect('admin/api-keys');
 }

 public function update($id)
 {
 $this->postGuard();
 $db = new Database();
 $old = $db->fetch("SELECT * FROM api_keys WHERE id = :id", [':id' => (int) $id]);
 if (!$old) {
 Session::flash('error', 'المفتاح غير موجود.');
 return $this->redirect('admin/api-keys');
 }

 $data = Sanitizer::cleanArray($_POST);
 $name = trim($data['name'] ?? $old['name']);
 $scopes = $_POST['scopes'] ?? json_decode($old['scopes'], true);
 if (!is_array($scopes) || empty($scopes)) {
 $scopes = ['all'];
 }

 $rateLimit = max(10, (int) ($data['rate_limit_per_minute'] ?? $old['rate_limit_per_minute']));
 $expiresAt = $old['expires_at'];
 if (isset($data['expires_days'])) {
 $days = (int) $data['expires_days'];
 $expiresAt = $days > 0 ? date('Y-m-d H:i:s', strtotime("+{$days} days")) : null;
 }

 $isActive = isset($_POST['is_active']) ? 1 : 0;

 $db->query("
 UPDATE api_keys SET 
 name = :name, scopes = :scopes, rate_limit_per_minute = :rate_limit, 
 expires_at = :expires_at, is_active = :is_active 
 WHERE id = :id
 ", [
 ':name' => $name,
 ':scopes' => json_encode(array_values($scopes)),
 ':rate_limit' => $rateLimit,
 ':expires_at' => $expiresAt,
 ':is_active' => $isActive,
 ':id' => (int) $id
 ]);

 $this->audit('update', 'api_key', $id, $old, $data);
 Session::flash('success', "تم تحديث بيانات وصلاحيات المفتاح \"{$name}\" بنجاح.");
 return $this->redirect('admin/api-keys');
 }

 public function regenerate($id)
 {
 $this->postGuard();
 $db = new Database();
 $old = $db->fetch("SELECT * FROM api_keys WHERE id = :id", [':id' => (int) $id]);
 if (!$old) {
 Session::flash('error', 'المفتاح غير موجود.');
 return $this->redirect('admin/api-keys');
 }

 $randomHex = bin2hex(random_bytes(16));
 $newRawToken = 'tnp_live_' . $randomHex;
 $keyPrefix = substr($newRawToken, 0, 14) . '...';
 $keyHash = hash('sha256', $newRawToken);

 $db->query("
 UPDATE api_keys SET 
 secret_token = :secret, key_prefix = :prefix, key_hash = :hash 
 WHERE id = :id
 ", [
 ':secret' => $newRawToken,
 ':prefix' => $keyPrefix,
 ':hash' => $keyHash,
 ':id' => (int) $id
 ]);

 $this->audit('regenerate', 'api_key', $id, null, ['old_prefix' => $old['key_prefix']]);
 Session::flash('success_token', $newRawToken);
 Session::flash('success_name', $old['name'] . ' (تمت إعادة التوليد)');
 return $this->redirect('admin/api-keys');
 }

 public function toggle($id)
 {
 $this->postGuard();
 $db = new Database();
 $key = $db->fetch("SELECT * FROM api_keys WHERE id = :id", [':id' => (int) $id]);
 if ($key) {
 $newStatus = empty($key['is_active']) ? 1 : 0;
 $db->query("UPDATE api_keys SET is_active = :status WHERE id = :id", [':status' => $newStatus, ':id' => (int) $id]);
 Session::flash('success', 'تم تغيير حالة المفتاح بنجاح.');
 }
 return $this->redirect('admin/api-keys');
 }

 public function delete($id)
 {
 $this->postGuard();
 $db = new Database();
 $db->query("DELETE FROM api_keys WHERE id = :id", [':id' => (int) $id]);
 Session::flash('success', 'تم حذف مفتاح الـ API بنجاح.');
 return $this->redirect('admin/api-keys');
 }
}
