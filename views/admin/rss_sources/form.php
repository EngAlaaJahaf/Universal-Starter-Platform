<?php
$editing = !empty($source);
$title = $editing ? 'تعديل مصدر RSS: ' . ($source['name'] ?? '') : 'إضافة مصدر RSS جديد';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><?= $editing ? 'تعديل مصدر RSS' : 'إضافة مصدر RSS جديد' ?></h2>
 <p class="text-muted mb-0">أدخل بيانات وتفاصيل رابط الخلاصة والتصنيف الافتراضي للأخبار المستوردة.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/rss-sources')) ?>" class="btn btn-outline-secondary">
 ← العودة لقائمة المصادر
 </a>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold">بيانات المصدر الإخباري</h6>
 </div>
 <div class="card-body p-4">
 <form method="post" action="<?= admin_e(app_url('admin/rss-sources/' . ($editing ? $source['id'] . '/update' : 'store'))) ?>">
 <?= CSRF::field() ?>

 <div class="row g-3 mb-3">
 <div class="col-md-6">
 <label class="form-label fw-bold" for="name">اسم المصدر الإخباري <span class="text-danger">*</span></label>
 <input type="text" class="form-control form-control-lg" id="name" name="name" required value="<?= admin_e($source['name'] ?? '') ?>" placeholder="مثال: TechCrunch (الشركات الناشئة)">
 </div>
 <div class="col-md-6">
 <label class="form-label fw-bold" for="category_id">التصنيف والفرع الأساسي</label>
 <select class="form-select form-select-lg" id="category_id" name="category_id">
 <option value="">عام / تلقائي</option>
 <?php foreach ($categories as $cat): ?>
 <option value="<?= (int) $cat['id'] ?>" <?= (int) ($source['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
 <?= admin_e($cat['name']) ?>
 </option>
 <?php endforeach; ?>
 </select>
 </div>
 </div>

 <div class="mb-4">
 <label class="form-label fw-bold" for="url">رابط خلاصة الـ RSS أو Atom المباشر <span class="text-danger">*</span></label>
 <input type="url" class="form-control font-monospace" id="url" name="url" required value="<?= admin_e($source['url'] ?? '') ?>" placeholder="https://example.com/feed/">
 <div class="form-text">تأكد من إدخال رابط مباشر صالح لخلاصة XML أو RSS أو Atom.</div>
 </div>

 <div class="mb-4 p-3 rounded-3 bg-light border">
 <div class="form-check form-switch mb-0">
 <input class="form-check-input" type="checkbox" id="auto_fetch" name="auto_fetch" value="1" <?= !isset($source['auto_fetch']) || !empty($source['auto_fetch']) ? 'checked' : '' ?>>
 <label class="form-check-label fw-semibold" for="auto_fetch">تفعيل المصدر في شريط المتابعة السريع</label>
 </div>
 </div>

 <div class="d-flex justify-content-end gap-2">
 <a href="<?= admin_e(app_url('admin/rss-sources')) ?>" class="btn btn-light px-4">إلغاء</a>
 <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
 <?= $editing ? 'حفظ التعديلات' : 'إضافة المصدر الآن' ?>
 </button>
 </div>
 </form>
 </div>
</div>
