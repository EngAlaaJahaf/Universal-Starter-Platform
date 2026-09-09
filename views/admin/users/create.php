<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">إضافة مستخدم جديد</h2>
 </div>
 <a href="<?= app_url('admin/users') ?>" class="btn btn-outline-secondary">← العودة للقائمة</a>
</div>

<?php if ($msg = Session::getFlash('error')): ?>
 <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
 <div class="card-body p-4">
 <form action="<?= app_url('admin/users/store') ?>" method="post">
 <?= CSRF::field() ?>

 <div class="row g-3">
 <div class="col-md-6">
 <label class="form-label fw-bold">اسم المستخدم</label>
 <input type="text" name="username" class="form-control" required>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-bold">البريد الإلكتروني</label>
 <input type="email" name="email" class="form-control" required>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-bold">كلمة المرور</label>
 <div class="password-input-wrap">
 <input type="password" id="admin_create_pass" name="password" class="form-control" required>
 <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('admin_create_pass', this)" title="إظهار / إخفاء كلمة المرور">
 <i class="bi bi-eye"></i>
 </button>
 </div>
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">الدور والصلاحيات</label>
 <select name="role_id" class="form-select">
 <?php foreach ($roles as $r): ?>
 <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name_ar'] ?: $r['name']) ?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">الحالة</label>
 <select name="status" class="form-select">
 <option value="active">نشط (Active)</option>
 <option value="pending">معلق (Pending)</option>
 <option value="banned">محظور (Banned)</option>
 </select>
 </div>
 </div>

 <hr class="my-4">
 <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">حفظ وإنشاء المستخدم</button>
 </form>
 </div>
</div>
