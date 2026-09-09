<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-diagram-3-fill text-primary me-2"></i>إدارة مصطلحات التصنيف الذكي</h2>
 <p class="text-muted mb-0">تحكم بقاموس الكلمات المفتاحية والمصطلحات التقنية التي يعتمد عليها محرك الأوزان لتصنيف الأخبار تلقائياً وبأعلى دقة.</p>
 </div>
 <div class="d-flex gap-2">
 <form method="post" action="<?= admin_e(app_url('admin/classifier-rules/reclassify-all')) ?>" onsubmit="return confirm('هل تريد إعادة فحص وتصنيف جميع المقالات الموجودة في قاعدة البيانات وفقاً للمصطلحات المحدثة؟');">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-outline-primary fw-bold shadow-sm">
 <i class="bi bi-arrow-repeat me-1"></i> إعادة تصنيف كل المقالات
 </button>
 </form>
 <button type="button" class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addKeywordModal">
 <i class="bi bi-plus-lg me-1"></i> إضافة مصطلح جديد
 </button>
 </div>
</div>

<?php if (!empty($warning)): ?>
 <div class="alert alert-warning border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4">
 <i class="bi bi-exclamation-triangle-fill fs-5"></i>
 <div><?= admin_e($warning) ?></div>
 </div>
<?php endif; ?>

<?php if (!empty($allConflicts)): ?>
 <div class="card border-warning bg-warning-subtle shadow-sm rounded-4 mb-4">
 <div class="card-body p-3">
 <div class="d-flex align-items-center gap-2 mb-2 text-warning-emphasis fw-bold">
 <i class="bi bi-exclamation-octagon-fill fs-5"></i>
 <span>تنبيه محرك فحص التعارضات: تم رصد مصطلحات مكررة في أكثر من تصنيف!</span>
 </div>
 <p class="text-muted small mb-2">المصطلحات التالية موجودة في تصنيفين مختلفين، مما قد يؤثر على توازن الأوزان الدلالية:</p>
 <div class="d-flex flex-wrap gap-2">
 <?php foreach ($allConflicts as $kw => $cats): ?>
 <span class="badge bg-white text-dark border border-warning shadow-sm py-2 px-3">
 <strong class="text-danger"><?= admin_e($kw) ?></strong>
 <span class="text-muted ms-1">في: (<?= admin_e(implode(' و ', array_column($cats, 'name'))) ?>)</span>
 </span>
 <?php endforeach; ?>
 </div>
 </div>
 </div>
<?php endif; ?>

<!-- Stats & Live Search Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-body p-3">
 <div class="row g-3 align-items-center">
 <div class="col-md-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
 <i class="bi bi-journal-code fs-4"></i>
 </div>
 <div>
 <div class="text-muted small">إجمالي المصطلحات النشطة</div>
 <div class="h4 fw-bold mb-0 text-dark"><?= (int) $totalKeywords ?> <span class="fs-6 fw-normal text-muted">مصطلحاً</span></div>
 </div>
 </div>
 </div>
 <div class="col-md-8">
 <div class="input-group">
 <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
 <input type="text" id="keywordSearchInput" class="form-control bg-light border-0" placeholder="ابحث عن مصطلح في القاموس في الوقت الفعلي...">
 </div>
 </div>
 </div>
 </div>
</div>

<!-- ================= SOURCE-TO-CATEGORY CLASSIFICATION RULES ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white mt-4 mb-4">
 <div class="card-header bg-transparent border-0 pt-3 pb-2 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <div>
 <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
 <span><i class="bi bi-broadcast-pin text-primary me-1"></i> قواعد ربط وتوجيه مصادر الأخبار (Source Mapping Engine)</span>
 <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= count($rssSources ?? []) ?> مصدر</span>
 </h5>
 <p class="text-muted small mb-0">حدد السلوك الترجيحي للمصادر المتخصصة (مثل مواقع الأمن السيبراني والذكاء الاصطناعي) لتوجيه مقالاتها فوراً أو ترجيحها بدقة.</p>
 </div>
 <div>
 <input type="text" id="sourceSearchInput" class="form-control form-control-sm rounded-pill px-3" placeholder="فلترة المصادر..." style="max-width: 220px;">
 </div>
 </div>
 <div class="card-body p-0">
 <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
 <table class="table table-hover align-middle mb-0" id="tableSourceRules">
 <thead class="table-light sticky-top">
 <tr class="text-nowrap small text-muted">
 <th style="width: 50px;">#</th>
 <th>اسم المصدر التقني</th>
 <th>نمط التصنيف (Mode)</th>
 <th>التصنيف المعتمد / المرجّح</th>
 <th>وزن الترجيح</th>
 <th class="text-end" style="width: 120px;">حالة الحفظ</th>
 </tr>
 </thead>
 <tbody>
 <?php if (!empty($rssSources)): ?>
 <?php foreach ($rssSources as $src): ?>
 <?php
 $srcKey = mb_strtolower(trim($src['name']));
 $rule = $sourceRules[$srcKey] ?? [
 'mode' => 'smart',
 'target_slug' => $src['category_slug'] ?? 'general-tech',
 'weight' => 5.0
 ];
 $currentMode = $rule['mode'] ?? 'smart';
 $currentTarget = $rule['target_slug'] ?? ($src['category_slug'] ?? 'general-tech');
 $currentWeight = (float) ($rule['weight'] ?? 5.0);
 ?>
 <tr class="source-rule-row" data-name="<?= htmlspecialchars(mb_strtolower($src['name'] . ' ' . $currentTarget)) ?>">
 <td class="text-nowrap"><span class="text-muted small font-monospace">#<?= (int)$src['id'] ?></span></td>
 <td>
 <strong class="text-dark d-block"><?= admin_e($src['name']) ?></strong>
 <a href="<?= admin_e($src['url']) ?>" target="_blank" class="text-muted small text-decoration-none font-monospace text-truncate d-inline-block" style="max-width: 240px;">
 <?= admin_e($src['url']) ?>
 </a>
 </td>
 <td>
 <select class="form-select form-select-sm rounded-3 shadow-none fw-bold src-mode-select" 
 onchange="saveSourceRuleAjax('<?= admin_e($src['name']) ?>', this)">
 <option value="smart" <?= $currentMode === 'smart' ? 'selected' : '' ?>>ذكي (كلمات مفتاحية + ترجيح)</option>
 <option value="strict" <?= $currentMode === 'strict' ? 'selected' : '' ?>>قطعي (تثبيت تصنيف المصدر دائماً)</option>
 <option value="neutral" <?= $currentMode === 'neutral' ? 'selected' : '' ?>>حيادي (اعتماد الكلمات المفتاحية فقط)</option>
 </select>
 </td>
 <td>
 <select class="form-select form-select-sm rounded-3 shadow-none src-target-select" 
 onchange="saveSourceRuleAjax('<?= admin_e($src['name']) ?>', this)">
 <?php foreach ($categories as $cat): ?>
 <option value="<?= admin_e($cat['slug']) ?>" <?= $currentTarget === $cat['slug'] ? 'selected' : '' ?>>
 <?= admin_e($cat['name']) ?>
 </option>
 <?php endforeach; ?>
 </select>
 </td>
 <td>
 <div class="input-group input-group-sm" style="max-width: 110px;">
 <span class="input-group-text bg-light border-0 py-0"><i class="bi bi-speedometer2 text-muted"></i></span>
 <input type="number" step="0.5" min="0" max="15" value="<?= $currentWeight ?>" 
 class="form-control form-control-sm text-center fw-bold src-weight-input" 
 onchange="saveSourceRuleAjax('<?= admin_e($src['name']) ?>', this)" 
 title="وزن الترجيح للمصدر (0 إلى 15)">
 </div>
 </td>
 <td class="text-end text-nowrap">
 <span class="save-status-badge text-success small fw-bold d-none">
 <i class="bi bi-check-circle-fill me-1"></i> تم الحفظ
 </span>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>

<!-- Categories & Keywords Grid -->
<div class="row g-4" id="categoriesGrid">
 <?php
 $catIcons = [
 'artificial-intelligence' => ['icon' => 'bi-robot', 'color' => '#8b5cf6', 'badge' => 'bg-purple-subtle text-purple border-purple'],
 'cybersecurity' => ['icon' => 'bi-shield-lock-fill', 'color' => '#ef4444', 'badge' => 'bg-danger-subtle text-danger border-danger'],
 'hardware-devices' => ['icon' => 'bi-cpu-fill', 'color' => '#06b6d4', 'badge' => 'bg-info-subtle text-info border-info'],
 'software-development' => ['icon' => 'bi-code-slash', 'color' => '#10b981', 'badge' => 'bg-success-subtle text-success border-success'],
 'general-tech' => ['icon' => 'bi-globe-americas', 'color' => '#3b82f6', 'badge' => 'bg-primary-subtle text-primary border-primary'],
 ];
 ?>

 <?php foreach ($rules as $slug => $data): ?>
 <?php
 $info = $catIcons[$slug] ?? ['icon' => 'bi-tag', 'color' => '#64748b', 'badge' => 'bg-secondary-subtle text-secondary'];
 $high = $data['high_priority'] ?? [];
 $med = $data['medium_priority'] ?? [];
 $catTotal = count($high) + count($med);
 ?>
 <div class="col-lg-6 category-card" data-slug="<?= admin_e($slug) ?>">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent border-0 pt-3 pb-2 px-4 d-flex justify-content-between align-items-center">
 <div class="d-flex align-items-center gap-2">
 <div class="rounded-3 p-2 d-flex align-items-center justify-content-center text-white" style="background:<?= $info['color'] ?>; width:36px; height:36px;">
 <i class="bi <?= $info['icon'] ?>"></i>
 </div>
 <div>
 <h5 class="fw-bold mb-0 text-dark"><?= admin_e($data['name_ar'] ?? $slug) ?></h5>
 <span class="text-muted small"><code><?= admin_e($slug) ?></code></span>
 </div>
 </div>
 <div class="d-flex align-items-center gap-2">
 <span class="badge bg-light text-dark border px-2 py-1"><span class="cat-count"><?= $catTotal ?></span> مصطلح</span>
 <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" style="width:30px;height:30px;padding:0" title="إضافة مصطلح لهذا القسم" onclick="openAddModal('<?= admin_e($slug) ?>')">
 <i class="bi bi-plus"></i>
 </button>
 </div>
 </div>

 <div class="card-body px-4 pt-2 pb-4">
 <!-- High Priority -->
 <div class="mb-3">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="small fw-bold text-muted"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>أولوية عالية (وزن 6x)</span>
 <span class="badge bg-light text-muted small"><?= count($high) ?></span>
 </div>
 <div class="d-flex flex-wrap gap-2 keywords-container high-priority-container">
 <?php foreach ($high as $kw): ?>
 <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2 keyword-tag" data-keyword="<?= admin_e($kw) ?>">
 <span class="kw-text"><?= admin_e($kw) ?></span>
 <button type="button" class="btn-close ms-1" style="font-size:0.5rem" title="حذف" onclick="deleteKeyword('<?= admin_e($slug) ?>', '<?= admin_e(addslashes($kw)) ?>', this)"></button>
 </span>
 <?php endforeach; ?>
 </div>
 </div>

 <!-- Medium Priority -->
 <?php if (!empty($med)): ?>
 <div>
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="small fw-bold text-muted"><i class="bi bi-star-half text-info me-1"></i>أولوية متوسطة (وزن 3x)</span>
 <span class="badge bg-light text-muted small"><?= count($med) ?></span>
 </div>
 <div class="d-flex flex-wrap gap-2 keywords-container medium-priority-container">
 <?php foreach ($med as $kw): ?>
 <span class="badge bg-light text-muted border border-dashed d-inline-flex align-items-center gap-1 py-1 px-2 keyword-tag" data-keyword="<?= admin_e($kw) ?>">
 <span class="kw-text"><?= admin_e($kw) ?></span>
 <button type="button" class="btn-close ms-1" style="font-size:0.5rem" title="حذف" onclick="deleteKeyword('<?= admin_e($slug) ?>', '<?= admin_e(addslashes($kw)) ?>', this)"></button>
 </span>
 <?php endforeach; ?>
 </div>
 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
</div>

<!-- Modal: Add Keyword with Live Conflict Checker -->
<div class="modal fade" id="addKeywordModal" tabindex="-1" aria-labelledby="addKeywordModalLabel" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content border-0 shadow-lg rounded-4">
 <div class="modal-header border-0 pb-0">
 <h5 class="modal-title fw-bold" id="addKeywordModalLabel">
 <i class="bi bi-plus-circle-fill text-primary me-2"></i>إضافة مصطلح إلى محرك التصنيف
 </h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 </div>
 <form id="addKeywordForm" method="post" action="<?= admin_e(app_url('admin/classifier-rules/add')) ?>">
 <?= CSRF::field() ?>
 <div class="modal-body py-4">
 <div class="mb-3">
 <label class="form-label fw-bold text-dark">المصطلح أو الكلمة المفتاحية (عربي أو إنجليزي) <span class="text-danger">*</span></label>
 <input type="text" name="keyword" id="modalKeywordInput" class="form-control form-control-lg rounded-3" placeholder="مثال: deepseek, ثغرة أمنية, kubernetes..." required autocomplete="off">
 </div>

 <div class="mb-3">
 <label class="form-label fw-bold text-dark">التصنيف المستهدف <span class="text-danger">*</span></label>
 <select name="category_slug" id="modalCategorySelect" class="form-select rounded-3" required>
 <?php foreach ($rules as $slug => $data): ?>
 <option value="<?= admin_e($slug) ?>"><?= admin_e($data['name_ar'] ?? $slug) ?> (<?= admin_e($slug) ?>)</option>
 <?php endforeach; ?>
 </select>
 </div>

 <div class="mb-3">
 <label class="form-label fw-bold text-dark">مستوى الأولوية في الأوزان</label>
 <select name="priority" id="modalPrioritySelect" class="form-select rounded-3">
 <option value="high" selected>أولوية عالية (وزن 6x في العنوان - مفاهيم أساسية حاسمة)</option>
 <option value="medium">أولوية متوسطة (وزن 3x - مصطلحات مساعدة وسياقية)</option>
 </select>
 </div>

 <!-- Live Conflict Alert Box -->
 <div id="conflictAlertBox" class="alert alert-warning border-0 rounded-3 d-none mb-0">
 <div class="d-flex align-items-start gap-2">
 <i class="bi bi-exclamation-triangle-fill text-warning fs-5 mt-1"></i>
 <div>
 <strong class="text-warning-emphasis d-block mb-1">تنبيه محرك فحص التعارضات:</strong>
 <span id="conflictAlertText" class="small text-dark"></span>
 <div class="form-check mt-2">
 <input class="form-check-input" type="checkbox" name="force" value="1" id="forceAddCheck">
 <label class="form-check-label small fw-bold text-dark" for="forceAddCheck">
 تأكيد إضافة المصطلح في هذا القسم رغم التعارض
 </label>
 </div>
 </div>
 </div>
 </div>
 </div>

 <div class="modal-footer border-0 pt-0">
 <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" id="saveKeywordBtn" class="btn btn-primary fw-bold px-4">
 <i class="bi bi-check-lg me-1"></i> حفظ المصطلح
 </button>
 </div>
 </form>
 </div>
 </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
 const searchInput = document.getElementById('keywordSearchInput');
 const modalKeywordInput = document.getElementById('modalKeywordInput');
 const modalCategorySelect = document.getElementById('modalCategorySelect');
 const conflictAlertBox = document.getElementById('conflictAlertBox');
 const conflictAlertText = document.getElementById('conflictAlertText');
 const forceAddCheck = document.getElementById('forceAddCheck');
 const addKeywordForm = document.getElementById('addKeywordForm');

 // 1. Live Search in Keywords Grid
 searchInput.addEventListener('input', function() {
 const query = this.value.toLowerCase().trim();
 const tags = document.querySelectorAll('.keyword-tag');
 
 tags.forEach(tag => {
 const text = tag.getAttribute('data-keyword').toLowerCase();
 if (query === '' || text.includes(query)) {
 tag.classList.remove('d-none');
 tag.style.display = '';
 } else {
 tag.classList.add('d-none');
 tag.style.display = 'none';
 }
 });
 });

 // 2. Live Conflict Checking in Modal
 let conflictTimer = null;
 function checkConflict() {
 const keyword = modalKeywordInput.value.trim();
 const categorySlug = modalCategorySelect.value;

 if (keyword.length < 2) {
 conflictAlertBox.classList.add('d-none');
 return;
 }

 fetch('<?= app_url("admin/classifier-rules/check-conflict") ?>?keyword=' + encodeURIComponent(keyword) + '&category_slug=' + encodeURIComponent(categorySlug), {
 headers: { 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(res => res.json())
 .then(data => {
 if (data.has_conflict) {
 const names = data.conflicts.map(c => `<strong>${c.category_name} (${c.priority})</strong>`).join(' و ');
 conflictAlertText.innerHTML = `المصطلح "<strong>${keyword}</strong>" مضاف بالفعل في قسم: ${names}. هل ترغب في إضافته هنا أيضاً؟`;
 conflictAlertBox.classList.remove('d-none');
 } else {
 conflictAlertBox.classList.add('d-none');
 forceAddCheck.checked = false;
 }
 })
 .catch(() => conflictAlertBox.classList.add('d-none'));
 }

 modalKeywordInput.addEventListener('input', function() {
 clearTimeout(conflictTimer);
 conflictTimer = setTimeout(checkConflict, 300);
 });

 // 3. Live Search in Sources Table
 const sourceSearchInput = document.getElementById('sourceSearchInput');
 if (sourceSearchInput) {
 sourceSearchInput.addEventListener('input', function() {
 const query = this.value.toLowerCase().trim();
 document.querySelectorAll('#tableSourceRules tbody .source-rule-row').forEach(row => {
 const name = (row.getAttribute('data-name') || '').toLowerCase();
 row.style.display = (query === '' || name.includes(query)) ? '' : 'none';
 });
 });
 }
});

function saveSourceRuleAjax(sourceName, changedEl) {
 const row = changedEl.closest('tr');
 const modeSelect = row.querySelector('.src-mode-select');
 const targetSelect = row.querySelector('.src-target-select');
 const weightInput = row.querySelector('.src-weight-input');
 const statusBadge = row.querySelector('.save-status-badge');

 const formData = new FormData();
 formData.append('source_name', sourceName);
 formData.append('mode', modeSelect.value);
 formData.append('target_slug', targetSelect.value);
 formData.append('weight', weightInput.value);
 formData.append('_csrf_token', '<?= CSRF::generate() ?>');

 fetch('<?= app_url("admin/classifier-rules/update-source-rule") ?>', {
 method: 'POST',
 headers: { 'X-Requested-With': 'XMLHttpRequest' },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 if (data.ok) {
 if (statusBadge) {
 statusBadge.classList.remove('d-none');
 statusBadge.style.opacity = '1';
 setTimeout(() => {
 statusBadge.style.transition = 'opacity 0.5s ease';
 statusBadge.style.opacity = '0';
 setTimeout(() => statusBadge.classList.add('d-none'), 500);
 }, 1500);
 }
 } else {
 alert(data.error || 'تعذر حفظ إعدادات المصدر.');
 }
 })
 .catch(() => alert('حدث خطأ في الاتصال بالخادم.'));
}

function openAddModal(categorySlug) {
 const select = document.getElementById('modalCategorySelect');
 if (select) select.value = categorySlug;
 const modal = new bootstrap.Modal(document.getElementById('addKeywordModal'));
 modal.show();
 setTimeout(() => document.getElementById('modalKeywordInput').focus(), 400);
}

function deleteKeyword(categorySlug, keyword, btnElement) {
 if (!confirm(`هل تريد بالتأكيد حذف المصطلح "${keyword}" من محرك التصنيف؟`)) return;

 const formData = new FormData();
 formData.append('category_slug', categorySlug);
 formData.append('keyword', keyword);
 formData.append('_csrf_token', '<?= CSRF::generate() ?>');

 fetch('<?= app_url("admin/classifier-rules/delete") ?>', {
 method: 'POST',
 headers: { 'X-Requested-With': 'XMLHttpRequest' },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 if (data.ok) {
 const tag = btnElement.closest('.keyword-tag');
 if (tag) {
 tag.style.opacity = '0';
 tag.style.transform = 'scale(0.8)';
 tag.style.transition = 'all 0.2s ease';
 setTimeout(() => tag.remove(), 200);
 }
 } else {
 alert('تعذر حذف المصطلح.');
 }
 })
 .catch(() => alert('حدث خطأ في الاتصال بالخادم.'));
}
</script>
