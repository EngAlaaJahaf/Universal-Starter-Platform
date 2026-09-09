<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-list-nested text-primary me-2"></i>عناصر قائمة: <?= admin_e($menu['name'] ?? '') ?></h2>
 <p class="text-muted mb-0">إدارة الروابط الداخلية والخارجية والترتيب لهذه القائمة.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/menus')) ?>" class="btn btn-outline-secondary">
 ← العودة لقوائم الموقع
 </a>
</div>

<!-- Add Item Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold">إضافة رابط جديد للقائمة</h6>
 </div>
 <div class="card-body p-4">
 <form method="post" action="<?= admin_e(app_url('admin/menus/items/' . $menu['id'] . '/store')) ?>" class="row g-3">
 <?= CSRF::field() ?>

 <div class="col-md-3">
 <label class="form-label small fw-bold">العنوان بالعربية <span class="text-danger">*</span></label>
 <input type="text" class="form-control" name="title_ar" required placeholder="مثال: الذكاء الاصطناعي">
 </div>

 <div class="col-md-3">
 <label class="form-label small text-muted">العنوان بالإنجليزية</label>
 <input type="text" class="form-control" name="title_en" placeholder="AI">
 </div>

 <div class="col-md-4">
 <label class="form-label small fw-bold">الرابط الموجه (URL) <span class="text-danger">*</span></label>
 <input type="text" class="form-control font-monospace" name="url" required placeholder="/category/ai أو https://...">
 </div>

 <div class="col-md-2">
 <label class="form-label small text-muted">الترتيب</label>
 <input type="number" class="form-control" name="sort_order" value="0">
 </div>

 <div class="col-12 text-end mt-3">
 <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
 + إضافة الرابط للقائمة
 </button>
 </div>
 </form>
 </div>
</div>

<!-- Existing Menu Items Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th>العنوان بالعربية</th>
 <th>العنوان بالإنجليزية</th>
 <th>الرابط (URL)</th>
 <th>الترتيب</th>
 <th class="text-end" style="width:100px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($items)): ?>
 <tr>
 <td colspan="5" class="text-center py-5 text-muted">
 <i class="bi bi-link-45deg d-block mb-2" style="font-size:2rem"></i>
 لا توجد عناصر مضافة لهذه القائمة بعد.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($items as $item): ?>
 <tr>
 <td><strong class="text-dark"><?= admin_e($item['title_ar']) ?></strong></td>
 <td><span class="text-muted small"><?= admin_e($item['title_en'] ?: '-') ?></span></td>
 <td><code class="text-primary font-monospace"><?= admin_e($item['url']) ?></code></td>
 <td><span class="badge bg-light text-dark border"><?= (int) $item['sort_order'] ?></span></td>
 <td class="text-end">
 <form method="post" action="<?= admin_e(app_url('admin/menus/items/' . $item['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا العنصر؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-delete" title="حذف العنصر">
 <i class="bi bi-trash3"></i>
 </button>
 </form>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>
