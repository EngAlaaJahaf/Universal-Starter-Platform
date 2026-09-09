<?php
$title = 'إدارة المقالات';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-journal-richtext text-primary me-2"></i>إدارة المقالات</h2>
 <p class="text-muted mb-0">عرض وتعديل وتصفية كافة الأخبار والتحليلات في المنصة.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/articles/create')) ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-plus-lg me-1"></i> كتابة مقال جديد
 </a>
</div>

<!-- Search & Filters -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-body p-3">
 <form method="get" action="<?= admin_e(app_url('admin/articles')) ?>" class="row g-2 align-items-center">
 <div class="col-md-5">
 <input type="text" class="form-control" name="q" placeholder="ابحث بالعنوان أو الرابط..." value="<?= admin_e($q ?? '') ?>">
 </div>
 <div class="col-md-3">
 <select class="form-select" name="status">
 <option value="">جميع الحالات</option>
 <option value="published" <?= ($status ?? '') === 'published' ? 'selected' : '' ?>>منشور</option>
 <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>>مسودة</option>
 <option value="scheduled" <?= ($status ?? '') === 'scheduled' ? 'selected' : '' ?>>مجدول</option>
 <option value="archived" <?= ($status ?? '') === 'archived' ? 'selected' : '' ?>>مؤرشف</option>
 </select>
 </div>
 <div class="col-md-2">
 <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> تصفية</button>
 </div>
 <?php if (!empty($q) || !empty($status)): ?>
 <div class="col-md-2">
 <a href="<?= admin_e(app_url('admin/articles')) ?>" class="btn btn-outline-secondary w-100">إلغاء الفلتر</a>
 </div>
 <?php endif; ?>
 </form>
 </div>
</div>

<!-- Bulk Cleanup of Old News -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
 <i class="bi bi-clock-history text-warning fs-5"></i>
 <strong>معالجة الأخبار القديمة (أرشفة / حذف مجمّع)</strong>
 <span class="badge bg-light text-dark border ms-2" style="font-weight:400">أرّخ أو احذف دفعة واحدة وفق نطاق تاريخ</span>
 </div>
 <div class="card-body p-3">
 <form method="post" action="<?= admin_e(app_url('admin/articles/bulk-expire')) ?>" class="row g-2 align-items-end" onsubmit="return confirm('هل تريد بالتأكيد تنفيذ هذه العملية على نطاق التاريخ المحدد؟')">
 <?= CSRF::field() ?>
 <div class="col-md-2">
 <label class="form-label small text-muted mb-1">من تاريخ (اختياري)</label>
 <input type="date" class="form-control form-control-sm" name="date_from">
 </div>
 <div class="col-md-2">
 <label class="form-label small text-muted mb-1">إلى تاريخ (اختياري)</label>
 <input type="date" class="form-control form-control-sm" name="date_to">
 </div>
 <div class="col-md-3">
 <label class="form-label small text-muted mb-1">النطاق</label>
 <select class="form-select form-select-sm" name="scope">
 <option value="all">كل المحتوى المؤهل</option>
 <option value="news">الأخبار فقط (عاجلة / قياسية / فيديو)</option>
 </select>
 </div>
 <div class="col-md-5 d-flex gap-2">
 <button type="submit" name="action" value="bulk-date-archive" class="btn btn-warning btn-sm fw-bold flex-fill">
 <i class="bi bi-archive me-1"></i> أرشفة القديمة
 </button>
 <button type="submit" name="action" value="bulk-date-delete" class="btn btn-danger btn-sm fw-bold flex-fill" onclick="return confirm('تحذير: سيتم حذف المقالات نهائياً مع تفاعلاتها وتعليقاتها. لا يمكن التراجع. هل أنت متأكد؟')">
 <i class="bi bi-trash3 me-1"></i> حذف نهائي
 </button>
 </div>
 </form>
 <small class="d-block text-muted mt-2" style="font-size:0.75rem">
 <i class="bi bi-info-circle me-1"></i> يجب تحديد تاريخ (من) أو (إلى) على الأقل. الأرشفة تحوّل المقالات لصفحة الأرشيف؛ الحذف النهائي يزيلها من قاعدة البيانات نهائياً.
 </small>
 </div>
</div>

<?php
$allCategoriesList = (new Database())->fetchAll("SELECT id, name, slug FROM categories ORDER BY id ASC");
?>
<!-- Articles Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:60px">#</th>
 <th>عنوان المقال</th>
 <th>التصنيف</th>
 <th>الكاتب</th>
 <th>الحالة</th>
 <th>المشاهدات</th>
 <th>تاريخ النشر</th>
 <th class="text-end" style="width:140px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($articles)): ?>
 <tr>
 <td colspan="8" class="text-center py-5 text-muted">
 <i class="bi bi-journal-x d-block mb-2" style="font-size:2rem"></i>
 لا توجد مقالات مطابقة للمعايير المحددة.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($articles as $art): ?>
 <tr>
 <td><span class="text-muted small">#<?= (int) $art['id'] ?></span></td>
 <td>
 <strong class="d-block text-dark"><?= admin_e($art['title']) ?></strong>
 <?php if (!empty($art['is_featured'])): ?>
 <span class="badge bg-warning text-dark me-1" style="font-size:0.7rem">مميز</span>
 <?php endif; ?>
 <?php if (!empty($art['is_premium'])): ?>
 <span class="badge bg-purple text-white me-1" style="font-size:0.7rem;background:#9d4edd">حصري</span>
 <?php endif; ?>
 </td>
 <td>
 <?php
 $cSlug = $art['category_slug'] ?? '';
 $catBadgeStyle = 'bg-primary-subtle text-primary';
 if ($cSlug === 'artificial-intelligence') $catBadgeStyle = 'bg-purple-subtle text-purple';
 elseif ($cSlug === 'cybersecurity') $catBadgeStyle = 'bg-danger-subtle text-danger';
 elseif ($cSlug === 'hardware-devices') $catBadgeStyle = 'bg-info-subtle text-info';
 elseif ($cSlug === 'software-development') $catBadgeStyle = 'bg-success-subtle text-success';
 ?>
 <div class="dropdown d-inline-block">
 <button class="badge <?= $catBadgeStyle ?> border-0 px-2 py-1 dropdown-toggle cursor-pointer shadow-none quick-cat-btn" 
 id="catBadge_<?= $art['id'] ?>" 
 data-bs-toggle="dropdown" 
 aria-expanded="false" 
 style="cursor:pointer;" 
 title="انقر لتغيير تصنيف المقال فوراً">
 <?= admin_e($art['category_name'] ?: 'عام') ?>
 </button>
 <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1" aria-labelledby="catBadge_<?= $art['id'] ?>">
 <li class="dropdown-header text-muted small py-1">اختر التصنيف الجديد:</li>
 <?php foreach ($allCategoriesList as $catItem): ?>
 <li>
 <a class="dropdown-item py-1 px-3 small d-flex align-items-center justify-content-between <?= ((int)$art['category_id'] === (int)$catItem['id']) ? 'active fw-bold' : '' ?>" 
 href="javascript:void(0)" 
 onclick="quickChangeCategory(<?= (int)$art['id'] ?>, <?= (int)$catItem['id'] ?>, this)">
 <span><?= admin_e($catItem['name']) ?></span>
 <?php if ((int)$art['category_id'] === (int)$catItem['id']): ?>
 <i class="bi bi-check2 ms-2"></i>
 <?php endif; ?>
 </a>
 </li>
 <?php endforeach; ?>
 </ul>
 </div>
 </td>
 <td><small class="text-muted"><?= admin_e($art['author_name'] ?: 'المدير') ?></small></td>
 <td>
 <?php
 $badgeClass = match ($art['status']) {
 'published' => 'bg-success-subtle text-success',
 'draft' => 'bg-secondary-subtle text-secondary',
 'scheduled' => 'bg-info-subtle text-info',
 'archived' => 'bg-danger-subtle text-danger',
 default => 'bg-light text-dark'
 };
 $statusLabel = match ($art['status']) {
 'published' => 'منشور',
 'draft' => 'مسودة',
 'scheduled' => 'مجدول',
 'archived' => 'مؤرشف',
 default => $art['status']
 };
 ?>
 <span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
 </td>
 <td><strong><?= number_format((int) ($art['views_count'] ?? 0)) ?></strong></td>
 <td><small class="text-muted"><?= admin_e(date('Y-m-d', strtotime($art['created_at']))) ?></small></td>
 <td class="text-end">
 <div class="d-inline-flex gap-1">
 <a href="<?= admin_e(app_url('article/' . $art['slug'])) ?>" target="_blank" class="btn-action-icon btn-action-view" title="معاينة المقال في الموقع">
 <i class="bi bi-eye"></i>
 </a>
<a href="<?= admin_e(app_url('admin/articles/' . $art['id'] . '/edit')) ?>" class="btn-action-icon btn-action-edit" title="تعديل المقال">
 <i class="bi bi-pencil-square"></i>
 </a>
 <form method="post" action="<?= admin_e(app_url('admin/articles/' . $art['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل تريد بالتأكيد أرشفة هذا المقال؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-delete" title="أرشفة / حذف">
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

<script>
function quickChangeCategory(articleId, categoryId, clickedEl) {
 const dropdown = clickedEl.closest('.dropdown');
 const badgeBtn = dropdown.querySelector('.quick-cat-btn');
 const prevHtml = badgeBtn.innerHTML;
 badgeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:10px;height:10px;"></span> جاري الحفظ...';

 const formData = new FormData();
 formData.append('article_id', articleId);
 formData.append('category_id', categoryId);
 formData.append('_csrf_token', '<?= CSRF::generate() ?>');

 fetch('<?= app_url("admin/articles/quick-update-category") ?>', {
 method: 'POST',
 headers: { 'X-Requested-With': 'XMLHttpRequest' },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 if (data.ok) {
 badgeBtn.className = 'badge ' + data.badge_class + ' border-0 px-2 py-1 dropdown-toggle cursor-pointer shadow-none quick-cat-btn';
 badgeBtn.innerHTML = data.category_name;

 // Update active state in dropdown items
 dropdown.querySelectorAll('.dropdown-item').forEach(item => {
 item.classList.remove('active', 'fw-bold');
 const check = item.querySelector('.bi-check2');
 if (check) check.remove();
 });
 clickedEl.classList.add('active', 'fw-bold');
 clickedEl.insertAdjacentHTML('beforeend', '<i class="bi bi-check2 ms-2"></i>');

 // Quick green flash
 badgeBtn.style.transition = 'all 0.3s ease';
 badgeBtn.style.transform = 'scale(1.1)';
 setTimeout(() => badgeBtn.style.transform = 'scale(1)', 300);
 } else {
 badgeBtn.innerHTML = prevHtml;
 alert(data.error || 'حدث خطأ أثناء تحديث التصنيف.');
 }
 })
 .catch(() => {
 badgeBtn.innerHTML = prevHtml;
 alert('حدث خطأ في الاتصال بالخادم.');
 });
}
</script>
