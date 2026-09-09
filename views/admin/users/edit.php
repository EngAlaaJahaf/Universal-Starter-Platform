<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">تعديل بيانات المستخدم: <?= htmlspecialchars($user['username']) ?></h2>
 </div>
 <a href="<?= app_url('admin/users') ?>" class="btn btn-outline-secondary">← العودة للقائمة</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
 <div class="card-body p-4">
 <form action="<?= app_url('admin/users/' . $user['id'] . '/update') ?>" method="post">
 <?= CSRF::field() ?>

 <div class="row g-3">
 <div class="col-md-6">
 <label class="form-label fw-bold">اسم المستخدم</label>
 <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-bold">البريد الإلكتروني</label>
 <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-bold">كلمة المرور الجديدة (اتركها فارغة إذا لم ترغب بالتغيير)</label>
 <div class="password-input-wrap">
 <input type="password" id="admin_edit_pass" name="password" class="form-control" placeholder="••••••••">
 <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('admin_edit_pass', this)" title="إظهار / إخفاء كلمة المرور">
 <i class="bi bi-eye"></i>
 </button>
 </div>
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">الدور والصلاحيات</label>
 <select name="role_id" class="form-select">
 <?php foreach ($roles as $r): ?>
 <option value="<?= $r['id'] ?>" <?= $user['role_id'] == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name_ar'] ?: $r['name']) ?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">الحالة</label>
 <select name="status" class="form-select">
 <option value="active" <?= $user['status'] === 'active' ? 'selected' : '' ?>>نشط (Active)</option>
 <option value="pending" <?= $user['status'] === 'pending' ? 'selected' : '' ?>>معلق (Pending)</option>
 <option value="banned" <?= $user['status'] === 'banned' ? 'selected' : '' ?>>محظور (Banned)</option>
 </select>
 </div>
 </div>

 <hr class="my-4">
 <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">تحديث البيانات</button>
 </form>
 </div>
</div>
