<?php
$editing = !empty($category);
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><?= $editing ? 'تعديل التصنيف' : 'إضافة تصنيف جديد' ?></h2>
 <p class="text-muted mb-0">أدخل بيانات التصنيف وخصائص العرض.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/categories')) ?>" class="btn btn-outline-secondary">
 ← العودة لقائمة التصنيفات
 </a>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold"><?= $editing ? 'تعديل بيانات: ' . admin_e($category['name']) : 'بيانات التصنيف الجديد' ?></h6>
 </div>
 <div class="card-body p-4">
 <form method="post" action="<?= admin_e(app_url('admin/categories/' . ($editing ? $category['id'] . '/update' : 'store'))) ?>">
 <?= CSRF::field() ?>

 <div class="row g-4">
 <div class="col-md-6">
 <label class="form-label fw-bold" for="name">اسم التصنيف <span class="text-danger">*</span></label>
 <input type="text" class="form-control" id="name" name="name" required value="<?= admin_e($category['name'] ?? '') ?>" placeholder="مثال: ذكاء اصطناعي">
 </div>

 <div class="col-md-6">
 <label class="form-label fw-bold" for="slug">الرابط الدائم (Slug)</label>
 <input type="text" class="form-control font-monospace" id="slug" name="slug" value="<?= admin_e($category['slug'] ?? '') ?>" placeholder="artificial-intelligence">
 </div>

 <div class="col-md-6">
 <label class="form-label small text-muted" for="name_ar">الاسم بالعربية</label>
 <input type="text" class="form-control" id="name_ar" name="name_ar" value="<?= admin_e($category['name_ar'] ?? ($category['name'] ?? '')) ?>" placeholder="ذكاء اصطناعي">
 </div>

 <div class="col-md-6">
 <label class="form-label small text-muted" for="name_en">الاسم بالإنجليزية (English Name)</label>
 <input type="text" class="form-control" id="name_en" name="name_en" value="<?= admin_e($category['name_en'] ?? '') ?>" placeholder="Artificial Intelligence">
 </div>

 <div class="col-md-4">
 <label class="form-label small text-muted" for="color">لون التصنيف المميز</label>
 <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="<?= admin_e($category['color'] ?? '#00f2fe') ?>">
 </div>

 <div class="col-md-4">
 <label class="form-label small text-muted" for="parent_id">التصنيف الأب (Parent)</label>
 <select class="form-select" id="parent_id" name="parent_id">
 <option value="">تصنيف رئيسي (بدون أب)</option>
 <?php foreach ($parents as $parent): ?>
 <option value="<?= (int) $parent['id'] ?>" <?= (int) ($category['parent_id'] ?? 0) === (int) $parent['id'] ? 'selected' : '' ?>>
 <?= admin_e($parent['name']) ?>
 </option>
 <?php endforeach; ?>
 </select>
 </div>

 <div class="col-md-4">
 <label class="form-label small text-muted" for="sort_order">ترتيب الظهور</label>
 <input type="number" class="form-control" id="sort_order" name="sort_order" value="<?= (int) ($category['sort_order'] ?? 0) ?>">
 </div>

 <div class="col-12">
 <label class="form-label small text-muted" for="description_ar">الوصف والتعريف</label>
 <textarea class="form-control" id="description_ar" name="description_ar" rows="3" placeholder="وصف موجز للمواضيع المندرجة تحت هذا القسم..."><?= admin_e($category['description_ar'] ?? ($category['description'] ?? '')) ?></textarea>
 </div>
 </div>

 <hr class="my-4">
 <div class="d-flex justify-content-end gap-2">
 <a href="<?= admin_e(app_url('admin/categories')) ?>" class="btn btn-light px-4">إلغاء</a>
 <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
 <?= $editing ? 'حفظ التعديلات' : 'إنشاء التصنيف الآن' ?>
 </button>
 </div>
 </form>
 </div>
</div>
