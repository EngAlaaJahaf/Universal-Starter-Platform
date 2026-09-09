<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">إدارة التغطيات الحية (Live Blogs)</h2>
 <p class="text-muted mb-0">إنشاء ومتابعة البث المباشر والتغطيات النصية للأحداث التقنية.</p>
 </div>
 <a href="<?= app_url('admin/live-blog/create') ?>" class="btn btn-primary fw-bold">+ بدء تغطية حية جديدة</a>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show">
 <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th>عنوان التغطية</th>
 <th>الحالة</th>
 <th>عدد التدوينات</th>
 <th>تاريخ البدء</th>
 <th class="text-end">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($blogs)): ?>
 <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد تغطيات حية مضافة.</td></tr>
 <?php else: ?>
 <?php foreach ($blogs as $b): ?>
 <tr>
 <td>
 <strong><?= htmlspecialchars($b['title_ar']) ?></strong>
 </td>
 <td>
 <span class="badge bg-<?= $b['status'] === 'active' ? 'danger' : 'secondary' ?>">
 <?= $b['status'] === 'active' ? 'مباشر الآن' : 'منتهي' ?>
 </span>
 </td>
 <td><?= (int) $b['entries_count'] ?> تدوينة</td>
 <td><?= htmlspecialchars($b['started_at']) ?></td>
 <td class="text-end">
 <div class="d-inline-flex align-items-center gap-1">
 <a href="<?= app_url('admin/live-blog/' . $b['id'] . '/entries') ?>" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" title="إضافة تدوينات وتحديثات">
 <i class="bi bi-journal-text"></i>
 <span>التدوينات</span>
 </a>
 
 <form action="<?= app_url('admin/live-blog/' . $b['id'] . '/toggle-status') ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm d-inline-flex align-items-center gap-1 <?= $b['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-danger' ?>" title="<?= $b['status'] === 'active' ? 'إنهاء البث وأرشفته' : 'إعادة تفعيل البث المباشر' ?>">
 <?php if ($b['status'] === 'active'): ?>
 <i class="bi bi-stop-circle"></i> <span>إنهاء</span>
 <?php else: ?>
 <i class="bi bi-broadcast"></i> <span>تفعيل</span>
 <?php endif; ?>
 </button>
 </form>

 <a href="<?= app_url('live-blog/' . $b['id']) ?>" target="_blank" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-1" title="معاينة الصفحة للزوار">
 <span>عرض</span> <i class="bi bi-box-arrow-up-right" style="font-size:0.75rem"></i>
 </a>

 <form action="<?= app_url('admin/live-blog/' . $b['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه التغطية وجميع تدويناتها نهائياً؟');">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center" title="حذف" style="padding:5px 8px">
 <i class="bi bi-trash3"></i>
 </button>
 </form>
 </div>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>
