<?php
$title = 'الملف الشخصي للمدير';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h1 class="h3 fw-bold mb-1">الملف الشخصي وإعدادات الحساب</h1>
 <p class="text-muted mb-0">إدارة معلوماتك الشخصية، بيانات تسجيل الدخول، وكلمة المرور الخاصة بلوحة التحكم.</p>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
 <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<?php if ($err = Session::getFlash('error')): ?>
 <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
 <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($err) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<div class="row g-4">
 <!-- Left Column: User Profile Info Card -->
 <div class="col-lg-4">
 <div class="card border-0 shadow-sm rounded-4 text-center p-4 bg-white mb-4">
 <div class="position-relative d-inline-block mx-auto mb-3">
 <div style="width:100px;height:100px;background:linear-gradient(135deg,#00f2fe,#4facfe);border-radius:50%;display:grid;place-items:center;color:#041122;font-size:2.5rem;font-weight:800;box-shadow:0 8px 20px rgba(0,242,254,0.3)">
 <?= mb_substr($user['username'], 0, 1) ?>
 </div>
 <span class="position-absolute bottom-0 end-0 p-2 bg-success border border-white rounded-circle"></span>
 </div>
 <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['username']) ?></h4>
 <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
 <span class="badge bg-primary px-3 py-2 rounded-pill mb-3">مدير النظام الأعلى (Super Admin)</span>

 <div class="p-3 bg-light rounded-3 text-start mt-2">
 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted small">تاريخ الانضمام:</span>
 <span class="small fw-semibold"><?= date('Y-m-d', strtotime($user['created_at'] ?? 'now')) ?></span>
 </div>
 <div class="d-flex justify-content-between mb-2">
 <span class="text-muted small">المقالات المنشورة:</span>
 <span class="small fw-bold text-primary"><?= $articlesCount ?> مقال</span>
 </div>
 <div class="d-flex justify-content-between">
 <span class="text-muted small">حالة الحساب:</span>
 <span class="badge bg-success">نشط (Active)</span>
 </div>
 </div>
 </div>

 <!-- Recent Logs Card -->
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
 <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-1 text-primary"></i> آخر نشاطاتك المسجلة</h6>
 <?php if (!empty($recentLogs)): ?>
 <ul class="list-group list-group-flush small">
 <?php foreach ($recentLogs as $log): ?>
 <li class="list-group-item px-0 py-2 border-bottom">
 <div class="d-flex justify-content-between">
 <span class="fw-semibold text-dark"><?= htmlspecialchars($log['action']) ?></span>
 <span class="text-muted" style="font-size:0.75rem"><?= date('H:i', strtotime($log['created_at'])) ?></span>
 </div>
 <span class="text-muted d-block text-truncate" style="max-width:250px"><?= htmlspecialchars($log['entity_type']) ?></span>
 </li>
 <?php endforeach; ?>
 </ul>
 <?php else: ?>
 <p class="text-muted small mb-0">لا توجد نشاطات مسجلة مؤخراً.</p>
 <?php endif; ?>
 </div>
 </div>

 <!-- Right Column: Edit Details & Password -->
 <div class="col-lg-8">
 <!-- Edit Profile Form -->
 <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
 <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-pencil-square text-primary me-1"></i> تعديل البيانات الشخصية</h5>
 <form action="<?= app_url('admin/profile/update') ?>" method="post">
 <?= CSRF::field() ?>
 <div class="row g-3">
 <div class="col-md-6">
 <label class="form-label fw-semibold">اسم المستخدم <span class="text-danger">*</span></label>
 <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
 <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-semibold">اللغة المفضلة</label>
 <select name="preferred_language" class="form-select">
 <option value="ar" <?= ($user['preferred_language'] ?? 'ar') === 'ar' ? 'selected' : '' ?>>العربية (Arabic)</option>
 <option value="en" <?= ($user['preferred_language'] ?? '') === 'en' ? 'selected' : '' ?>>English (الإنجليزية)</option>
 </select>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-semibold">مظهر لوحة التحكم الافتراضي</label>
 <select name="theme_preference" class="form-select">
 <option value="auto" <?= ($user['theme_preference'] ?? 'auto') === 'auto' ? 'selected' : '' ?>>تلقائي (حسب النظام)</option>
 <option value="dark" <?= ($user['theme_preference'] ?? '') === 'dark' ? 'selected' : '' ?>>داكن (Dark Mode)</option>
 <option value="light" <?= ($user['theme_preference'] ?? '') === 'light' ? 'selected' : '' ?>>فاتح (Light Mode)</option>
 </select>
 </div>
 <div class="col-12">
 <label class="form-label fw-semibold">النبذة التعريفية (Bio)</label>
 <textarea name="bio" class="form-control" rows="3" placeholder="اكتب نبذة مختصرة تظهر في مقالاتك..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
 </div>
 <div class="col-12 text-end">
 <button type="submit" class="btn btn-primary px-4">
 <i class="bi bi-check2-circle me-1"></i> حفظ التعديلات
 </button>
 </div>
 </div>
 </form>
 </div>

 <!-- Change Password Form -->
 <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
 <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-key text-warning me-1"></i> تغيير كلمة المرور</h5>
 <form action="<?= app_url('admin/profile/change-password') ?>" method="post">
 <?= CSRF::field() ?>
 <div class="row g-3">
 <div class="col-md-12">
 <label class="form-label fw-semibold">كلمة المرور الحالية <span class="text-danger">*</span></label>
 <div class="password-input-wrap">
 <input type="password" id="admin_curr_pass" name="current_password" class="form-control" placeholder="••••••••" required>
 <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('admin_curr_pass', this)" title="إظهار / إخفاء كلمة المرور">
 <i class="bi bi-eye"></i>
 </button>
 </div>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-semibold">كلمة المرور الجديدة <span class="text-danger">*</span></label>
 <div class="password-input-wrap">
 <input type="password" id="admin_new_pass" name="new_password" class="form-control" placeholder="6 أحرف على الأقل" required minlength="6">
 <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('admin_new_pass', this)" title="إظهار / إخفاء كلمة المرور">
 <i class="bi bi-eye"></i>
 </button>
 </div>
 </div>
 <div class="col-md-6">
 <label class="form-label fw-semibold">تأكيد كلمة المرور الجديدة <span class="text-danger">*</span></label>
 <div class="password-input-wrap">
 <input type="password" id="admin_conf_pass" name="confirm_password" class="form-control" placeholder="أعد إدخال كلمة المرور" required minlength="6">
 <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('admin_conf_pass', this)" title="إظهار / إخفاء كلمة المرور">
 <i class="bi bi-eye"></i>
 </button>
 </div>
 </div>
 <div class="col-12 text-end">
 <button type="submit" class="btn btn-warning text-dark fw-bold px-4">
 <i class="bi bi-shield-lock me-1"></i> تحديث كلمة المرور
 </button>
 </div>
 </div>
 </form>
 </div>
 </div>
</div>
