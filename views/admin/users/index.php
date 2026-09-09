<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-people text-primary me-2"></i>إدارة المستخدمين والصلاحيات</h2>
 <p class="text-muted mb-0">عرض وتعديل والتحكم في حسابات الأعضاء والكتّاب والمشرفين.</p>
 </div>
 <a href="<?= app_url('admin/users/create') ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-person-plus me-1"></i> إضافة مستخدم جديد
 </a>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm">
 <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- Filter Box -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-body p-3">
 <form method="get" action="<?= app_url('admin/users') ?>" class="row g-2 align-items-center">
 <div class="col-md-5">
 <input type="text" name="q" value="<?= htmlspecialchars($q ?? '') ?>" class="form-control" placeholder="بحث بالاسم أو البريد الإلكتروني...">
 </div>
 <div class="col-md-3">
 <select name="role" class="form-select">
 <option value="">جميع الأدوار</option>
 <?php foreach ($roles as $r): ?>
 <option value="<?= $r['id'] ?>" <?= ($role ?? '') == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['name_ar'] ?: $r['name']) ?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-md-2">
 <select name="status" class="form-select">
 <option value="">جميع الحالات</option>
 <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>نشط</option>
 <option value="banned" <?= ($status ?? '') === 'banned' ? 'selected' : '' ?>>محظور</option>
 <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>معلق</option>
 </select>
 </div>
 <div class="col-md-2">
 <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> فلترة</button>
 </div>
 </form>
 </div>
</div>

<!-- Users Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:60px">#</th>
 <th>المستخدم</th>
 <th>البريد الإلكتروني</th>
 <th>الدور والصلاحية</th>
 <th>الحالة</th>
 <th>تاريخ التسجيل</th>
 <th class="text-end" style="width:140px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($users)): ?>
 <tr>
 <td colspan="7" class="text-center py-5 text-muted">
 <i class="bi bi-people d-block mb-2" style="font-size:2rem"></i>
 لا يوجد مستخدمون مطابقون لبحثك.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($users as $u): ?>
 <tr>
 <td><span class="text-muted small">#<?= (int) $u['id'] ?></span></td>
 <td>
 <div class="d-flex align-items-center gap-2">
 <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#00f2fe,#9d4edd);color:#fff;display:grid;place-items:center;font-weight:700;font-size:0.85rem">
 <?= mb_substr($u['username'], 0, 1, 'UTF-8') ?>
 </div>
 <strong class="text-dark"><?= htmlspecialchars($u['username']) ?></strong>
 </div>
 </td>
 <td><span class="text-muted"><?= htmlspecialchars($u['email']) ?></span></td>
 <td>
 <span class="badge bg-dark-subtle text-dark border">
 <?= htmlspecialchars($u['role_name_ar'] ?: $u['role_name'] ?: 'قارئ مسجل') ?>
 </span>
 </td>
 <td>
 <span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>-subtle text-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>">
 <?= $u['status'] === 'active' ? 'نشط' : ($u['status'] === 'banned' ? 'محظور' : 'معلق') ?>
 </span>
 </td>
 <td><small class="text-muted"><?= htmlspecialchars(date('Y-m-d', strtotime($u['created_at']))) ?></small></td>
 <td class="text-end">
 <div class="d-inline-flex gap-1">
 <a class="btn-action-icon btn-action-edit" href="<?= app_url('admin/users/' . $u['id'] . '/edit') ?>" title="تعديل المستخدم">
 <i class="bi bi-pencil"></i>
 </a>

 <?php if ($u['status'] === 'active'): ?>
 <form method="post" action="<?= app_url('admin/users/' . $u['id'] . '/ban') ?>" class="d-inline" onsubmit="return confirm('هل تريد حظر هذا المستخدم؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-ban" title="حظر الحساب">
 <i class="bi bi-slash-circle"></i>
 </button>
 </form>
 <?php else: ?>
 <form method="post" action="<?= app_url('admin/users/' . $u['id'] . '/activate') ?>" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-activate" title="تفعيل الحساب">
 <i class="bi bi-check-circle"></i>
 </button>
 </form>
 <?php endif; ?>

 <?php if ((int)$u['id'] !== (int)Auth::user()['id']): ?>
 <form method="post" action="<?= app_url('admin/users/' . $u['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-delete" title="حذف المستخدم">
 <i class="bi bi-trash3"></i>
 </button>
 </form>
 <?php endif; ?>
 </div>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>
