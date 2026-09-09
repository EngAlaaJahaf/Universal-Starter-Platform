<?php
$editing = !empty($row);
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><?= admin_e($title) ?></h2>
 <p class="text-muted mb-0">أدخل وتعديل بيانات النموذج بدقة وسهولة.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/' . $resource)) ?>" class="btn btn-outline-secondary">
 ← العودة للقائمة
 </a>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold"><?= $editing ? 'تعديل السجل' : 'إضافة عنصر جديد' ?></h6>
 </div>
 <div class="card-body p-4">
 <form method="post" action="<?= admin_e(app_url('admin/' . $resource . '/' . ($editing ? $row['id'] . '/update' : 'store'))) ?>">
 <?= CSRF::field() ?>

 <div class="row g-4">
 <?php foreach ($fields as $field => $meta): 
 $type = $meta['type'] ?? 'text';
 $value = $row[$field] ?? '';
 ?>
 <div class="<?= in_array($type, ['textarea', 'json']) ? 'col-12' : 'col-md-6' ?>">
 <label class="form-label fw-bold" for="input_<?= admin_e($field) ?>">
 <?= admin_e($meta['label'] ?? $field) ?>
 </label>

 <?php if ($type === 'textarea'): ?>
 <textarea class="form-control font-monospace" 
 id="input_<?= admin_e($field) ?>" 
 name="<?= admin_e($field) ?>" 
 rows="6"><?= admin_e($value) ?></textarea>

 <?php elseif ($type === 'select'): ?>
 <select class="form-select" id="input_<?= admin_e($field) ?>" name="<?= admin_e($field) ?>">
 <?php foreach (($meta['options'] ?? []) as $option => $label): ?>
 <option value="<?= admin_e($option) ?>" <?= (string) $value === (string) $option ? 'selected' : '' ?>>
 <?= admin_e($label) ?>
 </option>
 <?php endforeach; ?>
 </select>

 <?php else: ?>
 <input type="<?= admin_e($type) ?>" 
 class="form-control" 
 id="input_<?= admin_e($field) ?>" 
 name="<?= admin_e($field) ?>" 
 value="<?= admin_e($value) ?>">
 <?php endif; ?>
 </div>
 <?php endforeach; ?>
 </div>

 <hr class="my-4">
 <div class="d-flex justify-content-end gap-2">
 <a href="<?= admin_e(app_url('admin/' . $resource)) ?>" class="btn btn-light px-4">إلغاء</a>
 <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
 <?= $editing ? 'حفظ التعديلات' : 'إضافة الآن' ?>
 </button>
 </div>
 </form>
 </div>
</div>
