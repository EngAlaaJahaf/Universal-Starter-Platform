<?php
/**
 * Admin Tutorials Visual Step-by-Step Builder Form View
 */
$isEdit = !empty($tutorial);
$actionUrl = $isEdit ? app_url("admin/tutorials/{$tutorial['id']}/update") : app_url('admin/tutorials/store');
$existingSteps = !empty($steps) ? $steps : [
 [
 'title' => 'الخطوة الأولى: إعداد البيئة والمتطلبات',
 'image_url' => '',
 'image_caption' => '',
 'content' => 'ابدأ بتحميل المتطلبات الأساسية وتجهيز بيئة العمل للبدء في التطبيق.',
 'code_snippet' => '',
 'code_language' => 'bash',
 'callout_type' => 'tip',
 'callout_text' => 'تأكد من تشغيل الطرفية بصلاحيات المشرف لتفادي مشاكل الأذونات.',
 'sort_order' => 10
 ]
];
?>
<style>
.step-dropzone {
 border: 2px dashed #cbd5e1;
 border-radius: 12px;
 background: #ffffff;
 cursor: pointer;
 transition: all 0.2s ease-in-out;
 position: relative;
 min-height: 95px;
 display: flex;
 flex-direction: column;
 align-items: center;
 justify-content: center;
}
.step-dropzone:hover, .step-dropzone:focus, .step-dropzone.dragover {
 border-color: #00f2fe !important;
 background: rgba(0, 242, 254, 0.05) !important;
 box-shadow: 0 0 12px rgba(0, 242, 254, 0.2);
 outline: none;
}
.step-dropzone.is-uploading {
 border-color: #3b82f6 !important;
 background: rgba(59, 130, 246, 0.05) !important;
 pointer-events: none;
}
</style>
<div class="container-fluid p-0">

 <!-- Header Navigation -->
 <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <div class="d-flex align-items-center gap-2 mb-1">
 <a href="<?= admin_e(app_url('admin/tutorials')) ?>" class="text-decoration-none text-muted small">
 <i class="bi bi-arrow-right"></i> العودة لقائمة الشروحات
 </a>
 <span class="text-muted">/</span>
 <span class="badge bg-primary bg-opacity-10 text-primary small fw-bold">استوديو الدروس المصورة</span>
 </div>
 <h4 class="fw-bold mb-0 text-dark"><?= $isEdit ? 'تعديل الشرح المتسلسل' : 'إنشاء شرح مصور جديد' ?></h4>
 </div>
 <div class="d-flex gap-2">
 <?php if ($isEdit): ?>
 <a href="<?= admin_e(app_url("tutorial/{$tutorial['slug']}")) ?>" target="_blank" class="btn btn-outline-info rounded-pill px-3 shadow-sm">
 <i class="bi bi-eye me-1"></i> معاينة الشرح بالموقع
 </a>
 <?php endif; ?>
 <button type="submit" form="tutorialForm" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
 <i class="bi bi-check-lg me-1"></i> <?= $isEdit ? 'حفظ التعديلات' : 'نشر الشرح الآن' ?>
 </button>
 </div>
 </div>

 <form id="tutorialForm" action="<?= admin_e($actionUrl) ?>" method="post" enctype="multipart/form-data">
 <?= CSRF::field() ?>

 <div class="row g-4">
 
 <!-- Main Builder Column -->
 <div class="col-lg-8">
 
 <!-- 1. Basic Metadata Card -->
 <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-white border-bottom py-3 px-4">
 <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-primary me-2"></i> معلومات الشرح الأساسية</h6>
 </div>
 <div class="card-body p-4">
 
 <!-- Title -->
 <div class="mb-3">
 <label class="form-label fw-bold text-dark small">عنوان الشرح / الدليل <span class="text-danger">*</span></label>
 <input type="text" name="title" required class="form-control form-control-lg fs-6 fw-bold" placeholder="مثال: الدليل الشامل لتثبيت وتخصيص Docker على خوادم Ubuntu خطوة بخطوة..." value="<?= admin_e($tutorial['title'] ?? '') ?>">
 </div>

 <!-- Summary / Overview -->
 <div class="mb-3">
 <label class="form-label fw-bold text-dark small">المقدمة والهدف من الشرح (Overview)</label>
 <textarea name="summary" rows="3" class="form-control" placeholder="نبذة توضح للمستخدم ما الذي سيتعلمه ويطبقه في نهاية هذا الشرح..."><?= admin_e($tutorial['summary'] ?? '') ?></textarea>
 </div>

 <!-- Row: Category & Difficulty & Time -->
 <div class="row g-3">
 <div class="col-md-4">
 <label class="form-label fw-bold text-dark small">التصنيف المرتبط</label>
 <select name="category_id" class="form-select">
 <option value="">بدون تصنيف (عام)</option>
 <?php foreach ($categories as $cat): ?>
 <option value="<?= $cat['id'] ?>" <?= ($tutorial['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
 <?= admin_e($cat['name']) ?>
 </option>
 <?php endforeach; ?>
 </select>
 </div>

 <div class="col-md-4">
 <label class="form-label fw-bold text-dark small">مستوى الصعوبة</label>
 <select name="difficulty" class="form-select">
 <option value="beginner" <?= ($tutorial['difficulty'] ?? '') === 'beginner' ? 'selected' : '' ?>>مبتدئ (Beginner)</option>
 <option value="intermediate" <?= ($tutorial['difficulty'] ?? '') === 'intermediate' ? 'selected' : '' ?>>متوسط (Intermediate)</option>
 <option value="advanced" <?= ($tutorial['difficulty'] ?? '') === 'advanced' ? 'selected' : '' ?>>متقدم / خبير (Advanced)</option>
 </select>
 </div>

 <div class="col-md-4">
 <label class="form-label fw-bold text-dark small">الوقت التقديري (بالدقائق)</label>
 <input type="number" name="estimated_minutes" class="form-control" min="1" max="600" value="<?= (int)($tutorial['estimated_minutes'] ?? 10) ?>">
 </div>
 </div>

 </div>
 </div>

 <!-- 2. Interactive Steps Builder Container -->
 <div class="d-flex justify-content-between align-items-center mb-3">
 <div>
 <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-diagram-3-fill text-primary me-2"></i> خطوات الشرح المصورة (Step-by-Step Flow)</h5>
 <p class="text-muted small mb-0">أرفق الصورة في مكانها واكتب الشرح المباشر والأوامر البرمجية تحتها</p>
 </div>
 <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-bold" onclick="addNewStep()">
 <i class="bi bi-plus-circle me-1"></i> إضافة خطوة تالية
 </button>
 </div>

 <div id="stepsContainer" class="d-flex flex-direction-column flex-column gap-4">
 <!-- Dynamic Steps Rendered via PHP & JS -->
 </div>

 <!-- Bottom Add Step Button -->
 <div class="text-center mt-4 mb-5">
 <button type="button" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm fw-bold" onclick="addNewStep()">
 <i class="bi bi-plus-circle me-2"></i> + إضافة خطوة تالية في الشرح
 </button>
 </div>

 </div>

 <!-- Sidebar Column -->
 <div class="col-lg-4">
 
 <!-- Publishing Status Card -->
 <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-white border-bottom py-3 px-4">
 <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-send-check text-success me-2"></i> خيارات النشر</h6>
 </div>
 <div class="card-body p-4">
 <div class="mb-3">
 <label class="form-label fw-bold text-dark small">حالة الشرح</label>
 <select name="status" class="form-select">
 <option value="published" <?= ($tutorial['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>منشور للعامة في الموقع</option>
 <option value="draft" <?= ($tutorial['status'] ?? '') === 'draft' ? 'selected' : '' ?>>مسودة (حفظ فقط بدون نشر)</option>
 </select>
 </div>

 <div class="d-grid gap-2 mt-4">
 <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold shadow-sm">
 <i class="bi bi-check-circle me-1"></i> <?= $isEdit ? 'حفظ وتحديث الشرح' : 'نشر الشرح الآن' ?>
 </button>
 </div>
 </div>
 </div>

 <!-- Featured Image Card -->
 <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-white border-bottom py-3 px-4">
 <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-image text-info me-2"></i> الصورة البارزة للدرس</h6>
 </div>
 <div class="card-body p-4">
 
 <!-- Image Preview -->
 <div class="mb-3 text-center">
 <img id="featuredImagePreview" src="<?= !empty($tutorial['featured_image']) ? admin_e(app_url($tutorial['featured_image'])) : 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80' ?>" class="rounded-4 w-100 shadow-sm border" style="max-height:180px;object-fit:cover" alt="Featured Image Preview">
 </div>

 <!-- Direct File Upload -->
 <div class="mb-3">
 <label class="form-label small fw-bold">رفع صورة من الجهاز</label>
 <input type="file" name="featured_image_file" class="form-control form-control-sm" accept="image/*" onchange="previewLocalImage(this, 'featuredImagePreview')">
 </div>

 <!-- Image URL fallback -->
 <div>
 <label class="form-label small fw-bold">أو رابط صورة مباشر (URL)</label>
 <input type="text" name="featured_image" id="featuredImageUrl" class="form-control form-control-sm" placeholder="https://..." value="<?= admin_e($tutorial['featured_image'] ?? '') ?>" oninput="document.getElementById('featuredImagePreview').src = this.value || 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=500&q=80'">
 </div>

 </div>
 </div>

 <!-- Quick Help / Tips Card -->
 <div class="card border-0 shadow-sm rounded-4 bg-light border p-4">
 <h6 class="fw-bold text-dark mb-2"><i class="bi bi-lightbulb-fill text-warning me-2"></i> نصائح لكتابة شروحات مميزة:</h6>
 <ul class="text-muted small ps-3 mb-0" style="line-height:1.7">
 <li>ضع صورة واضحة لكل خطوة تبين النقر أو النتيجة المطلوبة.</li>
 <li>استخدم صندوق الأوامر لوضع الأكواد حتى يسهل نسخها بنقرة واحدة.</li>
 <li>أضف تنبيهات الملاحظات والتحذيرات لتنبيه القارئ للأخطاء الشائعة.</li>
 <li>يمكنك تقديم أو تأخير الخطوات في أي وقت باستخدام أزرار الأسهم.</li>
 </ul>
 </div>

 </div>

 </div>
 </form>

</div>

<!-- Template for Step Card -->
<template id="stepTemplate">
 <div class="card border shadow-sm rounded-4 bg-white step-card mb-3 position-relative" data-step-index="{INDEX}">
 
 <!-- Step Header Bar -->
 <div class="card-header bg-light bg-opacity-75 border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <div class="d-flex align-items-center gap-2">
 <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold step-badge-num">الخطوة {NUM}</span>
 <span class="text-muted small fw-bold step-title-preview">{PREVIEW_TITLE}</span>
 </div>
 
 <!-- Step Controls: Move Up, Move Down, Duplicate, Delete -->
 <div class="d-flex align-items-center gap-1">
 <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 p-1" title="نقل لأعلى" onclick="moveStep(this, -1)">
 <i class="bi bi-arrow-up"></i>
 </button>
 <button type="button" class="btn btn-sm btn-outline-secondary rounded-2 p-1" title="نقل لأسفل" onclick="moveStep(this, 1)">
 <i class="bi bi-arrow-down"></i>
 </button>
 <button type="button" class="btn btn-sm btn-outline-info rounded-2 p-1" title="تكرار الخطوة" onclick="duplicateStep(this)">
 <i class="bi bi-copy"></i>
 </button>
 <button type="button" class="btn btn-sm btn-outline-danger rounded-2 p-1" title="حذف الخطوة" onclick="deleteStep(this)">
 <i class="bi bi-trash3"></i>
 </button>
 </div>
 </div>

 <div class="card-body p-4">
 
 <!-- Step Title -->
 <div class="mb-3">
 <label class="form-label fw-bold small text-dark">عنوان الخطوة <span class="text-danger">*</span></label>
 <input type="text" name="steps[{INDEX}][title]" class="form-control step-title-input fw-bold" placeholder="مثال: تثبيت حزم Docker والتأكد من تشغيل الخدمة..." value="{TITLE}" oninput="updateStepTitlePreview(this)">
 </div>

 <!-- ================= FLEXIBLE STEP IMAGE PLACEMENT (DRAG & DROP + CLIPBOARD PASTE) ================= -->
 <div class="p-3 rounded-4 mb-3 border bg-light bg-opacity-50">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <label class="form-label fw-bold small mb-0 text-dark"><i class="bi bi-image-fill text-info me-1"></i> صورة توضيحية للخطوة (Screenshot / Diagram)</label>
 <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:0.72rem"> يدعم اللصق (Ctrl+V) أو السحب والإفلات</span>
 </div>

 <div class="row g-3 align-items-center">
 <div class="col-md-7">
 <div class="d-flex gap-2 mb-2">
 <!-- Direct AJAX Upload -->
 <label class="btn btn-sm btn-outline-primary rounded-pill px-3 mb-0" style="cursor:pointer">
 <i class="bi bi-cloud-arrow-up-fill me-1"></i> رفع صورة
 <input type="file" class="d-none step-file-uploader" accept="image/*" onchange="uploadStepImage(this)">
 </label>
 
 <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="clearStepImage(this)">
 <i class="bi bi-x-circle me-1"></i> إزالة الصورة
 </button>
 </div>

 <!-- Image URL Input -->
 <div class="input-group input-group-sm mb-2">
 <span class="input-group-text bg-white">رابط الصورة</span>
 <input type="text" name="steps[{INDEX}][image_url]" class="form-control step-img-url" placeholder="مسار أو رابط الصورة... أو اضغط هنا والصق صورة (Ctrl+V)" value="{IMAGE_URL}" oninput="updateStepImgPreview(this)">
 </div>

 <!-- Image Caption -->
 <input type="text" name="steps[{INDEX}][image_caption]" class="form-control form-control-sm" placeholder="تعليق توضيحي أسفل الصورة (مثال: نافذة موجه الأوامر بعد إتمام التثبيت)..." value="{IMAGE_CAPTION}">
 </div>

 <!-- Step Image Dropzone & Preview Box -->
 <div class="col-md-5 text-center">
 <div class="step-dropzone rounded-3 p-2 text-center" 
 onclick="triggerStepFileInput(this)"
 ondragover="handleStepDragOver(event, this)" 
 ondragleave="handleStepDragLeave(event, this)" 
 ondrop="handleStepDrop(event, this)"
 tabindex="0"
 title="اسحب وأفلت صورة هنا، أو انقر للاختيار، أو اضغط Ctrl+V للصق من الحافظة">
 <img src="{IMAGE_URL_SRC}" class="img-fluid rounded-2 step-img-tag" style="max-height:115px;object-fit:contain;{IMAGE_DISPLAY}" alt="معاينة صورة الخطوة" onerror="this.style.display='none';">
 <div class="empty-img-placeholder" style="{PLACEHOLDER_DISPLAY}">
 <div style="font-size:1.5rem;margin-bottom:2px"></div>
 <div class="fw-bold text-dark" style="font-size:0.8rem">اسحب وأفلت الصورة هنا</div>
 <div class="text-muted" style="font-size:0.7rem">أو الصق من الحافظة (<kbd>Ctrl+V</kbd>)</div>
 </div>
 <div class="upload-spinner-overlay d-none py-2">
 <span class="spinner-border spinner-border-sm text-primary"></span>
 <span class="small fw-bold text-primary ms-1">جارٍ رفع الصورة...</span>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Step Detailed Content / Instructions -->
 <div class="mb-3">
 <label class="form-label fw-bold small text-dark"><i class="bi bi-card-text text-primary me-1"></i> الشرح والتوجيهات التفصيلية للخطوة</label>
 <textarea name="steps[{INDEX}][content]" rows="3" class="form-control step-content-text" placeholder="اكتب هنا الشرح الواضح والدقيق للخطوة وما يجب على القارئ فعله..."></textarea>
 </div>

 <!-- Step Code Snippet / Commands (Optional) -->
 <div class="p-3 rounded-4 mb-3 border bg-dark text-light">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <label class="form-label fw-bold small mb-0 text-white"><i class="bi bi-terminal-fill text-warning me-1"></i> أمر طرفي / كود برمجي خاص بالخطوة (Terminal / Code Snippet)</label>
 <select name="steps[{INDEX}][code_language]" class="form-select form-select-sm bg-secondary text-white border-0 py-0 step-code-lang" style="width:auto">
 <option value="bash">Terminal / Bash</option>
 <option value="php">PHP</option>
 <option value="python">Python</option>
 <option value="javascript">JavaScript</option>
 <option value="html">HTML / CSS</option>
 <option value="sql">SQL</option>
 <option value="json">JSON / YAML</option>
 </select>
 </div>
 <textarea name="steps[{INDEX}][code_snippet]" rows="2" class="form-control font-monospace bg-black text-info border-secondary small step-code-snippet" placeholder="مثال: sudo apt update && sudo apt install docker-ce -y"></textarea>
 </div>

 <!-- Step Callout / Note / Tip -->
 <div class="row g-2 align-items-center">
 <div class="col-md-4">
 <select name="steps[{INDEX}][callout_type]" class="form-select form-select-sm step-callout-type">
 <option value="none">بدون تنبيه إضافي</option>
 <option value="tip">نصيحة ذهبية (Pro Tip)</option>
 <option value="warning">تحذير وانتباه (Warning)</option>
 <option value="important">نقطة جوهرية (Important)</option>
 <option value="note">ℹ ملاحظة إضافية (Note)</option>
 </select>
 </div>
 <div class="col-md-8">
 <input type="text" name="steps[{INDEX}][callout_text]" class="form-control form-control-sm step-callout-text" placeholder="نص التنبيه أو الملاحظة الخاصة بالخطوة..." value="">
 </div>
 </div>

 <input type="hidden" name="steps[{INDEX}][sort_order]" class="step-sort-order" value="{SORT_ORDER}">

 </div>
 </div>
</template>

<script>
const INITIAL_STEPS = <?= json_encode($existingSteps, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function () {
 if (INITIAL_STEPS && INITIAL_STEPS.length> 0) {
 INITIAL_STEPS.forEach((step, idx) => {
 renderStepCard(step, idx);
 });
 } else {
 addNewStep();
 }
});

let stepCounter = 0;

function renderStepCard(data = {}, index = null) {
 const container = document.getElementById('stepsContainer');
 const template = document.getElementById('stepTemplate').innerHTML;

 const idx = index !== null ? index : stepCounter;
 const num = idx + 1;
 const title = data.title || '';
 const previewTitle = title ? title : 'بدون عنوان';
 const content = data.content || '';
 const imageUrl = data.image_url || '';
 const imageCaption = data.image_caption || '';
 const codeSnippet = data.code_snippet || '';
 const codeLang = data.code_language || 'bash';
 const calloutType = data.callout_type || 'none';
 const calloutText = data.callout_text || '';
 const sortOrder = data.sort_order !== undefined ? data.sort_order : (idx * 10);

 const hasImg = imageUrl.trim().length> 0;

 let html = template
 .replaceAll('{INDEX}', idx)
 .replaceAll('{NUM}', num)
 .replaceAll('{SORT_ORDER}', sortOrder)
 .replaceAll('{IMAGE_URL_SRC}', hasImg ? imageUrl : '')
 .replaceAll('{IMAGE_DISPLAY}', hasImg ? 'display:block;' : 'display:none;')
 .replaceAll('{PLACEHOLDER_DISPLAY}', hasImg ? 'display:none;' : 'display:block;');

 const tempDiv = document.createElement('div');
 tempDiv.innerHTML = html;
 const cardEl = tempDiv.firstElementChild;

 // Direct Exact DOM property assignment (guarantees 100% preservation of all fields and selects)
 if (cardEl.querySelector('.step-title-input')) cardEl.querySelector('.step-title-input').value = title;
 if (cardEl.querySelector('.step-title-preview')) cardEl.querySelector('.step-title-preview').textContent = previewTitle;
 if (cardEl.querySelector('.step-content-text')) cardEl.querySelector('.step-content-text').value = content;
 if (cardEl.querySelector('.step-img-url')) cardEl.querySelector('.step-img-url').value = imageUrl;
 if (cardEl.querySelector('input[name*="[image_caption]"]')) cardEl.querySelector('input[name*="[image_caption]"]').value = imageCaption;
 if (cardEl.querySelector('.step-code-snippet')) cardEl.querySelector('.step-code-snippet').value = codeSnippet;
 if (cardEl.querySelector('.step-code-lang')) cardEl.querySelector('.step-code-lang').value = codeLang;
 if (cardEl.querySelector('.step-callout-type')) cardEl.querySelector('.step-callout-type').value = calloutType;
 if (cardEl.querySelector('.step-callout-text')) cardEl.querySelector('.step-callout-text').value = calloutText;

 container.appendChild(cardEl);

 stepCounter = Math.max(stepCounter + 1, idx + 1);
 reindexSteps();
}

function addNewStep() {
 renderStepCard({
 title: '',
 content: '',
 image_url: '',
 image_caption: '',
 code_snippet: '',
 code_language: 'bash',
 callout_type: 'none',
 callout_text: ''
 });
}

function deleteStep(btn) {
 const card = btn.closest('.step-card');
 const container = document.getElementById('stepsContainer');
 if (container.querySelectorAll('.step-card').length <= 1) {
 alert('يجب أن يحتوي الشرح على خطوة واحدة على الأقل.');
 return;
 }
 if (confirm('هل أنت متأكد من حذف هذه الخطوة؟')) {
 card.remove();
 reindexSteps();
 }
}

function duplicateStep(btn) {
 const card = btn.closest('.step-card');
 renderStepCard({
 title: (card.querySelector('.step-title-input')?.value || '') + ' (نسخة)',
 content: card.querySelector('.step-content-text')?.value || '',
 image_url: card.querySelector('.step-img-url')?.value || '',
 image_caption: card.querySelector('input[name*="[image_caption]"]')?.value || '',
 code_snippet: card.querySelector('.step-code-snippet')?.value || '',
 code_language: card.querySelector('.step-code-lang')?.value || 'bash',
 callout_type: card.querySelector('.step-callout-type')?.value || 'none',
 callout_text: card.querySelector('.step-callout-text')?.value || ''
 });
}

function moveStep(btn, direction) {
 const card = btn.closest('.step-card');
 if (direction === -1 && card.previousElementSibling) {
 card.parentNode.insertBefore(card, card.previousElementSibling);
 } else if (direction === 1 && card.nextElementSibling) {
 card.parentNode.insertBefore(card.nextElementSibling, card);
 }
 reindexSteps();
}

function reindexSteps() {
 const cards = document.querySelectorAll('#stepsContainer .step-card');
 cards.forEach((card, i) => {
 const num = i + 1;
 card.querySelector('.step-badge-num').textContent = `الخطوة ${num}`;
 const sortInput = card.querySelector('.step-sort-order');
 if (sortInput) sortInput.value = i * 10;
 });
}

function updateStepTitlePreview(input) {
 const card = input.closest('.step-card');
 const preview = card.querySelector('.step-title-preview');
 preview.textContent = input.value.trim() ? input.value.trim() : 'بدون عنوان';
}

function updateStepImgPreview(input) {
 const card = input.closest('.step-card');
 const imgTag = card.querySelector('.step-img-tag');
 const placeholder = card.querySelector('.empty-img-placeholder');
 const url = input.value.trim();

 if (url) {
 imgTag.src = url;
 imgTag.style.display = 'block';
 placeholder.style.display = 'none';
 } else {
 imgTag.src = '';
 imgTag.style.display = 'none';
 placeholder.style.display = 'block';
 }
}

function clearStepImage(btn) {
 const card = btn.closest('.step-card');
 const urlInput = card.querySelector('.step-img-url');
 urlInput.value = '';
 updateStepImgPreview(urlInput);
}

function triggerStepFileInput(dropzone) {
 const card = dropzone.closest('.step-card');
 const fileInput = card.querySelector('.step-file-uploader');
 if (fileInput) fileInput.click();
}

function handleStepDragOver(e, dropzone) {
 e.preventDefault();
 e.stopPropagation();
 dropzone.classList.add('dragover');
}

function handleStepDragLeave(e, dropzone) {
 e.preventDefault();
 e.stopPropagation();
 dropzone.classList.remove('dragover');
}

function handleStepDrop(e, dropzone) {
 e.preventDefault();
 e.stopPropagation();
 dropzone.classList.remove('dragover');

 const dt = e.dataTransfer;
 if (dt && dt.files && dt.files.length> 0) {
 const file = dt.files[0];
 if (file.type.indexOf('image') !== -1) {
 const card = dropzone.closest('.step-card');
 uploadBlobToCard(file, card);
 } else {
 alert('يرجى إفلات ملف صورة صالح (PNG, JPG, WEBP, GIF, SVG).');
 }
 }
}

function uploadStepImage(fileInput) {
 if (!fileInput.files || !fileInput.files[0]) return;
 const file = fileInput.files[0];
 const card = fileInput.closest('.step-card');
 uploadBlobToCard(file, card);
 fileInput.value = '';
}

function uploadBlobToCard(fileBlob, card) {
 if (!fileBlob || !card) return;

 const urlInput = card.querySelector('.step-img-url');
 const dropzone = card.querySelector('.step-dropzone');
 const placeholder = card.querySelector('.empty-img-placeholder');
 const spinner = card.querySelector('.upload-spinner-overlay');
 const imgTag = card.querySelector('.step-img-tag');

 const formData = new FormData();
 formData.append('image', fileBlob, fileBlob.name || 'clipboard_screenshot.png');

 const csrfInput = document.querySelector('input[name="_csrf_token"]') || document.querySelector('input[name="_csrf"]');
 if (csrfInput) {
 formData.append('_csrf_token', csrfInput.value);
 formData.append('_csrf', csrfInput.value);
 }

 // UI Loading state
 if (dropzone) dropzone.classList.add('is-uploading');
 if (placeholder) placeholder.style.display = 'none';
 if (spinner) spinner.classList.remove('d-none');

 fetch('<?= admin_e(app_url('admin/tutorials/ajax-upload-image')) ?>', {
 method: 'POST',
 body: formData
 })
 .then(res => {
 if (!res.ok) {
 throw new Error('HTTP Status ' + res.status);
 }
 return res.json();
 })
 .then(data => {
 if (data.success && data.url) {
 urlInput.value = data.url;
 updateStepImgPreview(urlInput);
 if (typeof showToast === 'function') {
 showToast('تم رفع ولصق الصورة بنجاح! ', '');
 }
 } else {
 alert(data.message || 'فشل رفع الصورة.');
 updateStepImgPreview(urlInput);
 }
 })
 .catch(err => {
 console.error(err);
 alert('حدث خطأ أثناء الاتصال بالخادم لرفع الصورة.');
 updateStepImgPreview(urlInput);
 })
 .finally(() => {
 if (dropzone) dropzone.classList.remove('is-uploading');
 if (spinner) spinner.classList.add('d-none');
 });
}

// Global Clipboard Paste Handler (Ctrl + V) for Step Images
document.addEventListener('paste', function(e) {
 const items = (e.clipboardData || e.originalEvent?.clipboardData)?.items;
 if (!items) return;

 let imageFile = null;
 for (let i = 0; i < items.length; i++) {
 if (items[i].type.indexOf('image') !== -1) {
 imageFile = items[i].getAsFile();
 break;
 }
 }

 if (!imageFile) return;

 // Detect target step card: activeElement parent, hovered card, or last created card
 const activeEl = document.activeElement;
 let targetCard = activeEl ? activeEl.closest('.step-card') : null;
 if (!targetCard) {
 targetCard = document.querySelector('.step-card:hover') || document.querySelector('.step-card');
 }

 if (targetCard) {
 e.preventDefault();
 uploadBlobToCard(imageFile, targetCard);
 }
});

function previewLocalImage(input, previewId) {
 if (input.files && input.files[0]) {
 const reader = new FileReader();
 reader.onload = function(e) {
 document.getElementById(previewId).src = e.target.result;
 };
 reader.readAsDataURL(input.files[0]);
 }
}

function escapeHtml(text) {
 if (!text) return '';
 return String(text)
 .replace(/&/g, "&amp;")
 .replace(/</g, "&lt;")
 .replace(/>/g, "&gt;")
 .replace(/"/g, "&quot;")
 .replace(/'/g, "&#039;");
}
</script>
