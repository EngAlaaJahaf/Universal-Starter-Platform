<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"> مكتبة الوسائط والملفات</h2>
 <p class="text-muted mb-0">رفع وإدارة صور المقالات، الشعارات، والمستندات التقنية.</p>
 </div>
</div>

<!-- Upload Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold">رفع ملف جديد إلى المكتبة</h6>
 </div>
 <div class="card-body p-4">
 <form method="post" enctype="multipart/form-data" action="<?= admin_e(app_url('admin/media/upload')) ?>" class="row g-3 align-items-center">
 <?= CSRF::field() ?>
 <div class="col-md-7">
 <input type="file" name="file" class="form-control form-control-lg" accept="image/*,.pdf" required>
 </div>
 <div class="col-md-3">
 <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
 بدء الرفع
 </button>
 </div>
 <div class="col-md-2">
 <small class="text-muted d-block" style="font-size:0.75rem">الحد الأقصى: 10MB<br>الامتدادات: JPG, PNG, WebP, GIF, PDF</small>
 </div>
 </form>
 </div>
</div>

<!-- Media Items Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:70px">معاينة</th>
 <th>اسم الملف</th>
 <th>النوع</th>
 <th>الحجم</th>
 <th>تاريخ الرفع</th>
 <th class="text-end">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($media)): ?>
 <tr>
 <td colspan="6" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2rem"></span>
 لا توجد ملفات وسائط مرفوعة بعد.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($media as $item): ?>
 <tr>
 <td>
 <?php if (strpos($item['mime_type'] ?? '', 'image') !== false): ?>
 <img src="<?= admin_e(app_url($item['file_path'])) ?>" class="rounded border" style="width:48px;height:48px;object-fit:cover" alt="معاينة">
 <?php else: ?>
 <div class="bg-light rounded border d-grid place-items-center text-muted" style="width:48px;height:48px;font-size:1.2rem"></div>
 <?php endif; ?>
 </td>
 <td>
 <a target="_blank" href="<?= admin_e(app_url($item['file_path'])) ?>" class="fw-bold text-dark text-decoration-none">
 <?= admin_e($item['original_name'] ?: basename($item['file_path'])) ?>
 </a>
 <small class="text-muted d-block font-monospace" style="font-size:0.75rem"><?= admin_e($item['file_path']) ?></small>
 </td>
 <td><span class="badge bg-light text-dark border"><?= admin_e($item['mime_type'] ?? 'file') ?></span></td>
 <td><small class="text-muted"><?= number_format(((int) ($item['file_size'] ?? 0)) / 1024, 1) ?> KB</small></td>
 <td><small class="text-muted"><?= admin_e(fmt_date($item['created_at'], 'Y-m-d H:i')) ?></small></td>
 <td class="text-end">
 <div class="btn-group btn-group-sm">
 <a target="_blank" href="<?= admin_e(app_url($item['file_path'])) ?>" class="btn btn-outline-dark" title="عرض ومعاينة">
 <i class="bi bi-eye"></i>
 </a>
 <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('<?= admin_e(app_url($item['file_path'])) ?>');alert('تم نسخ رابط الملف بنجاح!');" title="نسخ الرابط">
 <i class="bi bi-clipboard"></i>
 </button>
 <form method="post" action="<?= admin_e(app_url('admin/media/' . $item['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف نهائياً؟')">
 <?= CSRF::field() ?>
 <button class="btn btn-outline-danger" title="حذف">
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
