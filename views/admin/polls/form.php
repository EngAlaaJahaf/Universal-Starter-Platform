<?php
$iconsList = [
 'sparkle' => 'شرارة تقنية (Sparkle)',
 'ai' => 'ذكاء اصطناعي (AI)',
 'tutorials' => 'برمجة وتطوير (Code)',
 'security' => 'أمن سيبراني (Security)',
 'flame' => 'تريند شائع (Trending)',
 'mobile' => 'أجهزة وهواتف (Devices)',
 'general' => 'عام وشامل (General)',
 'chart' => 'تحليل وبيانات (Analytics)'
];
?>

<div class="admin-content-wrapper" style="max-width: 920px; margin: 0 auto;">

 <!-- Top Ribbon -->
 <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="fw-bold mb-1" style="color:var(--text-main);font-size:1.5rem">
 <?= $isEdit ? 'تعديل استطلاع الرأي' : 'إنشاء استطلاع رأي جديد' ?>
 </h2>
 <p class="text-muted mb-0" style="font-size:0.88rem">
 قم بتحديد سؤال الاستطلاع وإضافة الخيارات وأيقوناتها وتحديد حالة الظهور.
 </p>
 </div>
 <a href="<?= admin_e(app_url('admin/polls')) ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
 <span>←</span> <span>الرجوع للاستطلاعات</span>
 </a>
 </div>

 <!-- Form Card -->
 <div class="card border-0 shadow-sm p-4" style="background:var(--bg-surface-elevated);border-radius:16px;border:1px solid var(--border-medium)!important">
 <form method="post" action="<?= admin_e($isEdit ? app_url("admin/polls/{$poll['id']}/update") : app_url('admin/polls/store')) ?>" id="pollForm">
 <?= CSRF::getField() ?>

 <!-- 1. Question Input -->
 <div class="mb-3">
 <label class="form-label fw-bold" style="color:var(--text-main)">
 سؤال الاستطلاع <span class="text-danger">*</span>
 </label>
 <input type="text" 
 name="question" 
 class="form-control form-control-lg" 
 placeholder="مثال: ما هي التقنية الأكثر تأثيراً على أعمالك ومشاريعك هذا العام؟" 
 value="<?= admin_e($poll['question'] ?? '') ?>" 
 required 
 style="background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)">
 </div>

 <!-- 2. Description Input -->
 <div class="mb-4">
 <label class="form-label fw-bold" style="color:var(--text-main)">
 وصف أو تفاصيل إضافية (اختياري)
 </label>
 <textarea name="description" 
 class="form-control" 
 rows="2" 
 placeholder="اكتب توضيحاً مقتضباً يظهر للزوار أسفل السؤال..." 
 style="background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)"><?= admin_e($poll['description'] ?? '') ?></textarea>
 </div>

 <!-- 3. Dynamic Options Repeater -->
 <div class="mb-4 p-3 rounded" style="background:var(--bg-surface);border:1px solid var(--border-subtle)">
 <div class="d-flex justify-content-between align-items-center mb-3">
 <h5 class="fw-bold mb-0" style="color:var(--text-main);font-size:1.05rem">
 خيارات التصويت (خيارين على الأقل) <span class="text-danger">*</span>
 </h5>
 <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="btnAddOption">
 <span></span> <span>إضافة خيار جديد</span>
 </button>
 </div>

 <div id="optionsContainer" class="d-flex flex-column gap-2">
 <?php 
 $existingOptions = !empty($poll['options']) ? $poll['options'] : [
 ['id' => 0, 'title' => '', 'icon' => 'ai'],
 ['id' => 0, 'title' => '', 'icon' => 'tutorials'],
 ['id' => 0, 'title' => '', 'icon' => 'security']
 ];
 foreach ($existingOptions as $index => $opt): 
 ?>
 <div class="option-row d-flex align-items-center gap-2 p-2 rounded border" style="background:var(--bg-surface-elevated);border-color:var(--border-subtle)!important">
 <input type="hidden" name="option_ids[]" value="<?= (int)($opt['id'] ?? 0) ?>">
 
 <span class="badge bg-secondary option-index-badge"><?= $index + 1 ?></span>

 <input type="text" 
 name="options[]" 
 class="form-control" 
 placeholder="نص الخيار..." 
 value="<?= admin_e($opt['title'] ?? '') ?>" 
 required 
 style="background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)">

 <select name="option_icons[]" class="form-select" style="max-width:200px;background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)">
 <?php foreach ($iconsList as $icKey => $icLabel): ?>
 <option value="<?= $icKey ?>" <?= ($opt['icon'] ?? '') === $icKey ? 'selected' : '' ?>>
 <?= $icLabel ?>
 </option>
 <?php endforeach; ?>
 </select>

 <button type="button" class="btn btn-outline-danger btn-remove-option" title="حذف الخيار" style="flex-shrink:0">
 &times;
 </button>
 </div>
 <?php endforeach; ?>
 </div>
 </div>

 <!-- 4. Settings & Status -->
 <div class="row g-3 mb-4">
 <div class="col-12 col-md-6">
 <label class="form-label fw-bold" style="color:var(--text-main)">
 حالة الاستطلاع
 </label>
 <select name="status" class="form-select" style="background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)">
 <option value="active" <?= ($poll['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>نشط (يسمح بالتصويت)</option>
 <option value="inactive" <?= ($poll['status'] ?? '') === 'inactive' ? 'selected' : '' ?>> متوقف مؤقتاً</option>
 <option value="archived" <?= ($poll['status'] ?? '') === 'archived' ? 'selected' : '' ?>>مؤرشف (عرض النتائج فقط)</option>
 </select>
 </div>

 <div class="col-12 col-md-6">
 <label class="form-label fw-bold" style="color:var(--text-main)">
 موعد انتهاء الاستطلاع (اختياري)
 </label>
 <input type="datetime-local" 
 name="expires_at" 
 class="form-control" 
 value="<?= !empty($poll['expires_at']) ? date('Y-m-d\TH:i', strtotime($poll['expires_at'])) : '' ?>" 
 style="background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)">
 </div>
 </div>

 <div class="mb-4 form-check form-switch p-0 d-flex align-items-center gap-3">
 <input class="form-check-input ms-0 me-2" 
 type="checkbox" 
 role="switch" 
 name="is_featured" 
 id="is_featured" 
 value="1" 
 <?= !empty($poll['is_featured']) || !$isEdit ? 'checked' : '' ?> 
 style="width:2.5em;height:1.3em;cursor:pointer">
 <label class="form-check-label fw-bold" for="is_featured" style="color:var(--text-main);cursor:pointer">
 تثبيت هذا الاستطلاع كاستطلاع رئيسي نشط على الصفحة الرئيسية
 </label>
 </div>

 <!-- Submit Button -->
 <div class="d-flex gap-2">
 <button type="submit" class="btn btn-primary px-4 py-2 fw-bold shadow-sm">
 <?= $isEdit ? 'حفظ التعديلات' : 'نشر الاستطلاع' ?>
 </button>
 <a href="<?= admin_e(app_url('admin/polls')) ?>" class="btn btn-outline-secondary px-4 py-2">
 إلغاء
 </a>
 </div>
 </form>
 </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
 const container = document.getElementById('optionsContainer');
 const btnAdd = document.getElementById('btnAddOption');

 const iconsOptionsHtml = `
 <option value="sparkle">شرارة تقنية (Sparkle)</option>
 <option value="ai">ذكاء اصطناعي (AI)</option>
 <option value="tutorials">برمجة وتطوير (Code)</option>
 <option value="security">أمن سيبراني (Security)</option>
 <option value="flame">تريند شائع (Trending)</option>
 <option value="mobile">أجهزة وهواتف (Devices)</option>
 <option value="general">عام وشامل (General)</option>
 <option value="chart">تحليل وبيانات (Analytics)</option>
 `;

 function updateBadges() {
 const rows = container.querySelectorAll('.option-row');
 rows.forEach((row, i) => {
 const badge = row.querySelector('.option-index-badge');
 if (badge) badge.textContent = (i + 1);
 });
 }

 btnAdd.addEventListener('click', function() {
 const row = document.createElement('div');
 row.className = 'option-row d-flex align-items-center gap-2 p-2 rounded border';
 row.style.background = 'var(--bg-surface-elevated)';
 row.style.borderColor = 'var(--border-subtle)';

 row.innerHTML = `
 <input type="hidden" name="option_ids[]" value="0">
 <span class="badge bg-secondary option-index-badge">1</span>
 <input type="text" name="options[]" class="form-control" placeholder="نص الخيار..." required style="background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)">
 <select name="option_icons[]" class="form-select" style="max-width:200px;background:var(--bg-surface);color:var(--text-main);border-color:var(--border-subtle)">
 ${iconsOptionsHtml}
 </select>
 <button type="button" class="btn btn-outline-danger btn-remove-option" title="حذف الخيار" style="flex-shrink:0">&times;</button>
 `;

 container.appendChild(row);
 updateBadges();
 });

 container.addEventListener('click', function(e) {
 if (e.target.classList.contains('btn-remove-option')) {
 const rows = container.querySelectorAll('.option-row');
 if (rows.length <= 2) {
 alert('يجب الإبقاء على خيارين على الأقل للاستطلاع.');
 return;
 }
 e.target.closest('.option-row').remove();
 updateBadges();
 }
 });
});
</script>
