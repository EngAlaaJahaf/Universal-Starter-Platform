<?php
$editing = !empty($article);
$title = $editing ? 'تعديل مقال: ' . ($article['title'] ?? '') : 'كتابة مقال جديد';

// Support auto pre-filling from RSS aggregator
$defTitle = $article['title'] ?? ($_GET['q_title'] ?? '');
$defSourceName = $article['source_name'] ?? ($_GET['q_source'] ?? '');
$defSourceUrl = $article['source_url'] ?? ($_GET['q_url'] ?? '');
$defFeaturedImage = $article['featured_image'] ?? ($_GET['q_img'] ?? '');
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><?= $editing ? 'تعديل مقال' : 'كتابة مقال جديد' ?></h2>
 <p class="text-muted mb-0">أدخل بيانات وتفاصيل المقال والمحتوى التفاعلي والوسائط والمصادر والتحكم في العدادات.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/articles')) ?>" class="btn btn-outline-secondary">
 ← العودة لقائمة المقالات
 </a>
</div>

<!-- ================= SMART TRANSLATION & PREVIEW ASSISTANT BAR ================= -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3 border-start border-primary border-4">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
 <div>
 <div class="d-flex align-items-center gap-2 mb-1">
 <span class="badge bg-primary px-2 py-1"><i class="bi bi-translate me-1"></i>المترجم الصحفي الذكي</span>
 <strong class="text-dark">ترجمة فورية مجانية ومعاينة قبل النشر (Free Instant Translator & Preview)</strong>
 </div>
 <small class="text-muted">يقوم المحرك بصياغة وترجمة العنوان والنص الإنجليزي بالقاموس التقني الرصين ويتيح لك المعاينة قبل الاعتماد.</small>
 </div>

 <div class="d-flex gap-2 flex-wrap">
 <!-- 1. Translate & Preview Button -->
 <button type="button" id="btnTranslatePreview" class="btn btn-outline-primary fw-bold shadow-sm" onclick="runTranslationPreview(false)">
 <i class="bi bi-eye me-1"></i> ترجمة ومعاينة قبل النشر
 </button>

 <!-- 2. Translate & Instant Publish Button -->
 <button type="button" id="btnTranslatePublish" class="btn btn-primary fw-bold shadow-sm" style="background:linear-gradient(135deg,#00f2fe,#9d4edd);border:none" onclick="runTranslationPreview(true)">
 <i class="bi bi-lightning-charge-fill me-1"></i> ترجمة ونشر فوري للعامة
 </button>
 </div>
 </div>
</div>

<form id="articleForm" method="post" action="<?= admin_e(app_url('admin/articles/' . ($editing ? $article['id'] . '/update' : 'store'))) ?>">
 <?= CSRF::field() ?>

 <div class="row g-4">
 
 <!-- Main Article Content (Left/Center Column) -->
 <div class="col-lg-8">
 <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-body p-4">
 
 <div class="mb-3">
 <label class="form-label fw-bold" for="title">عنوان المقال الأساسي <span class="text-danger">*</span></label>
 <input type="text" class="form-control form-control-lg" id="title" name="title" required value="<?= admin_e($defTitle) ?>" placeholder="اكتب عنواناً جذاباً ومختصراً...">
 </div>

 <div class="row g-3 mb-3">
 <div class="col-md-6">
 <label class="form-label small text-muted" for="title_ar">العنوان العربي</label>
 <input type="text" class="form-control" id="title_ar" name="title_ar" value="<?= admin_e($article['title_ar'] ?? $defTitle) ?>" placeholder="العنوان باللغة العربية">
 </div>
 <div class="col-md-6">
 <label class="form-label small text-muted" for="title_en">العنوان الإنجليزي (English Title)</label>
 <input type="text" class="form-control" id="title_en" name="title_en" value="<?= admin_e($article['title_en'] ?? '') ?>" placeholder="English title...">
 </div>
 </div>

 <!-- Source Attribution Fields -->
 <div class="row g-3 mb-3 p-3 rounded-3 bg-light border">
 <div class="col-md-5">
 <label class="form-label small fw-bold" for="source_name">اسم المصدر الأصلي</label>
 <input type="text" class="form-control form-control-sm" id="source_name" name="source_name" value="<?= admin_e($defSourceName) ?>" placeholder="مثال: The Verge / TechCrunch">
 </div>
 <div class="col-md-7">
 <label class="form-label small fw-bold" for="source_url">رابط الخبر في المصدر (Source URL)</label>
 <input type="url" class="form-control form-control-sm font-monospace" id="source_url" name="source_url" value="<?= admin_e($defSourceUrl) ?>" placeholder="https://www.theverge.com/...">
 </div>
 </div>

 <div class="mb-3">
 <label class="form-label small text-muted" for="slug">الرابط الدائم (Slug)</label>
 <input type="text" class="form-control font-monospace" id="slug" name="slug" value="<?= admin_e($article['slug'] ?? '') ?>" placeholder="article-url-slug">
 <div class="form-text">اتركه فارغاً ليتم توليده تلقائياً من العنوان.</div>
 </div>

 <div class="mb-3">
 <label class="form-label fw-bold" for="excerpt">الملخص والتمهيد (Excerpt)</label>
 <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="ملخص سريع من سطرين يوضح زبدة الخبر وماذا يعني للقارئ..."><?= admin_e($article['excerpt'] ?? '') ?></textarea>
 </div>

 <div class="mb-3">
 <label class="form-label fw-bold" for="content">محتوى المقال الكامل <span class="text-danger">*</span></label>
 <textarea class="form-control font-monospace" id="content" name="content" rows="14" required placeholder="اكتب فقرات المقال هنا (يدعم وسوم HTML الأساسية مثل <p>, <h2>, <blockquote>, <ul>)..."><?= admin_e($article['content'] ?? '') ?></textarea>
 </div>

 </div>
 </div>
 </div>

 <!-- Sidebar / Settings Column (Right Column) -->
 <div class="col-lg-4">
 
 <!-- Publish Controls Card -->
 <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold">إعدادات النشر</h6>
 </div>
 <div class="card-body p-3">
 
 <div class="mb-3">
 <label class="form-label small fw-semibold" for="status">حالة المقال</label>
 <select class="form-select" id="status" name="status">
 <?php foreach (['draft' => 'مسودة', 'published' => 'منشور للعامة', 'scheduled' => 'مجدول', 'archived' => 'مؤرشف'] as $k => $lbl): ?>
 <option value="<?= $k ?>" <?= ($article['status'] ?? 'draft') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
 <?php endforeach; ?>
 </select>
 </div>

 <div class="mb-3">
 <label class="form-label small fw-semibold" for="article_type">نوع المحتوى (Article Type)</label>
 <select class="form-select" id="article_type" name="article_type">
 <?php foreach ([
 'standard' => 'مقال وتقرير قياسي (Standard)',
 'breaking' => 'خبر عاجل وتغطية فورية (Breaking)',
 'analysis' => 'تحليل تقني معمق (Analysis)',
 'review' => 'مراجعة أجهزة أو تطبيقات (Review)',
 'tutorial' => 'دليل وشرح تقني (How-To / Guide)',
 'video' => 'تغطية فيديو وبودكاست (Video)'
 ] as $typeKey => $typeLabel): ?>
 <option value="<?= $typeKey ?>" <?= ($article['article_type'] ?? 'standard') === $typeKey ? 'selected' : '' ?>>
 <?= $typeLabel ?>
 </option>
 <?php endforeach; ?>
 </select>
 </div>

 <div class="mb-3">
 <label class="form-label small fw-semibold" for="category_id">التصنيف الأساسي</label>
 <select class="form-select" id="category_id" name="category_id">
 <option value="">تلقائي (تقنية عامة)</option>
 <?php foreach ($categories as $cat): ?>
 <option value="<?= (int) $cat['id'] ?>" <?= (int) ($article['category_id'] ?? 0) === (int) $cat['id'] ? 'selected' : '' ?>>
 <?= admin_e($cat['name']) ?>
 </option>
 <?php endforeach; ?>
 </select>
 <div class="form-text" style="font-size:0.75rem">إذا لم تحدد تصنيفاً، سيتم إدراجه تلقائياً تحت تصنيف "تقنية عامة".</div>
 </div>

 <div class="mb-3">
 <label class="form-label small fw-semibold" for="published_at">تاريخ ووقت النشر</label>
 <input type="datetime-local" class="form-control" id="published_at" name="published_at" value="<?= !empty($article['published_at']) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : '' ?>">
 </div>

 <hr class="my-3">

 <div class="form-check form-switch mb-2">
 <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" <?= !empty($article['is_featured']) ? 'checked' : '' ?>>
 <label class="form-check-label small" for="is_featured">مقال مميز (Hero / اختيار المحرر)</label>
 </div>

 <div class="form-check form-switch mb-2">
 <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" value="1" <?= !isset($article['allow_comments']) || $article['allow_comments'] ? 'checked' : '' ?>>
 <label class="form-check-label small" for="allow_comments">السماح بالتعليقات والنقاش</label>
 </div>

 <div class="form-check form-switch mb-3">
 <input class="form-check-input" type="checkbox" id="is_premium" name="is_premium" value="1" <?= !empty($article['is_premium']) ? 'checked' : '' ?>>
 <label class="form-check-label small" for="is_premium">محتوى حصري للمشتركين</label>
 </div>

 <button type="submit" id="btnSubmitForm" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
 <?= $editing ? 'حفظ التعديلات' : 'نشر المقال الآن' ?>
 </button>

 </div>
 </div>

 <!-- Stats & Engagement Controls (Direct Admin Control) -->
 <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h6 class="mb-0 fw-bold">التحكم في إحصائيات المقال</h6>
 <span class="badge bg-primary-subtle text-primary">تحكم يدوي</span>
 </div>
 <div class="card-body p-3">
 <div class="mb-3">
 <label class="form-label small fw-semibold" for="views_count">
 <i class="bi bi-eye text-primary me-1"></i> عدد المشاهدات (Views Count)
 </label>
 <input type="number" min="0" class="form-control" id="views_count" name="views_count" value="<?= (int) ($article['views_count'] ?? 0) ?>">
 <small class="text-muted" style="font-size:0.75rem">يتم زيادته تلقائياً مع كل قراءة، ويمكنك تعديله هنا يدوياً.</small>
 </div>

 <div class="mb-3">
 <label class="form-label small fw-semibold" for="shares_count">
 <i class="bi bi-share text-success me-1"></i> عدد المشاركات (Shares)
 </label>
 <input type="number" min="0" class="form-control" id="shares_count" name="shares_count" value="<?= (int) ($article['shares_count'] ?? 0) ?>">
 </div>

 <div class="mb-0">
 <label class="form-label small fw-semibold" for="reading_time_minutes">
 <i class="bi bi-clock text-warning me-1"></i> وقت القراءة التقديري (بالدقائق) <span class="badge bg-light text-muted border fw-normal" style="font-size:0.7rem">اختياري</span>
 </label>
 <input type="number" min="1" class="form-control" id="reading_time_minutes" name="reading_time_minutes" value="<?= isset($article['reading_time_minutes']) && (int) $article['reading_time_minutes']> 0 ? (int) $article['reading_time_minutes'] : '' ?>" placeholder="تلقائي (يُحسب حسب طول المقال)...">
 <div class="form-text" style="font-size:0.75rem">اتركه فارغاً ليقوم النظام بحسابه تلقائياً بناءً على عدد كلمات المقال.</div>
 </div>
 </div>
 </div>

 <!-- Featured Image Card -->
 <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold"> الصورة البارزة (Featured Image)</h6>
 </div>
 <div class="card-body p-3">
 <div class="mb-2">
 <input type="text" class="form-control form-control-sm" id="featured_image_input" name="featured_image" placeholder="رابط الصورة أو مسارها..." value="<?= admin_e($defFeaturedImage) ?>">
 </div>
 <?php if (!empty($defFeaturedImage)): ?>
 <div class="mt-2 border rounded p-1 text-center bg-light">
 <img src="<?= admin_e(app_url($defFeaturedImage)) ?>" class="img-fluid rounded" style="max-height:160px;object-fit:cover" alt="معاينة" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
 </div>
 <?php endif; ?>
 </div>
 </div>

 </div>

 </div>
</form>

<!-- ================= MODAL: LIVE TRANSLATION PREVIEW ================= -->
<div class="modal fade" id="translationPreviewModal" tabindex="-1" aria-labelledby="translationPreviewModalLabel" aria-hidden="true">
 <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header bg-light border-bottom py-3">
 <h5 class="modal-title fw-bold text-dark" id="translationPreviewModalLabel">
 <i class="bi bi-translate text-primary me-2"></i>معاينة الترجمة والصياغة الصحفية للمقال
 </h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body p-4">
 <div class="alert alert-info border-0 rounded-3 py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
 <small><i class="bi bi-info-circle-fill me-1"></i> تمت معالجة النص عبر محرك الترجمة المعزز بالقاموس التقني لضبط المصطلحات.</small>
 <span id="transModeBadge" class="badge bg-primary"></span>
 </div>

 <div class="row g-4">
 <!-- Arabic Translated Output (Editable Preview) -->
 <div class="col-lg-7">
 <h6 class="fw-bold text-success mb-2"><i class="bi bi-check2-circle me-1"></i>الترجمة والصياغة العربية المقترحة:</h6>
 
 <div class="mb-3">
 <label class="form-label small fw-bold">العنوان العربي المقترح:</label>
 <input type="text" id="previewTitleAr" class="form-control form-control-lg fw-bold text-dark">
 </div>

 <div class="mb-3">
 <label class="form-label small fw-bold">الملخص التمهيدي (TL;DR):</label>
 <textarea id="previewExcerpt" class="form-control" rows="3"></textarea>
 </div>

 <div class="mb-3">
 <label class="form-label small fw-bold">محتوى المقال الكامل:</label>
 <textarea id="previewContentAr" class="form-control font-monospace" rows="10"></textarea>
 </div>
 </div>

 <!-- Original Source Text Column -->
 <div class="col-lg-5">
 <h6 class="fw-bold text-muted mb-2"><i class="bi bi-file-text me-1"></i>النص الأصلي (Original Text):</h6>
 <div class="p-3 bg-light rounded-3 border h-100" style="max-height:500px;overflow-y:auto">
 <h6 id="originalTitleView" class="fw-bold text-muted mb-3"></h6>
 <div id="originalContentView" class="text-secondary small" style="line-height:1.6"></div>
 </div>
 </div>
 </div>
 </div>

 <div class="modal-footer bg-light border-top d-flex justify-content-between">
 <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إلغاء المعاينة</button>
 <div class="d-flex gap-2">
 <button type="button" class="btn btn-success fw-bold px-4 shadow-sm" onclick="applyTranslationToForm(false)">
 <i class="bi bi-check-lg me-1"></i> موافقة وتطبيق على المحرر
 </button>
 <button type="button" class="btn btn-primary fw-bold px-4 shadow-sm" style="background:linear-gradient(135deg,#00f2fe,#9d4edd);border:none" onclick="applyTranslationToForm(true)">
 <i class="bi bi-rocket-takeoff-fill me-1"></i> موافقة ونشر مباشر للعامة
 </button>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- Rich Interactive Error, Diagnostic & Provider Switcher Modal -->
<div class="modal fade" id="appSystemModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg">
 <div class="modal-content rounded-4 border-0 shadow-lg" style="background:var(--bg-surface, #ffffff)">
 <div class="modal-header border-bottom py-3" id="appSystemModalHeader">
 <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="appSystemModalTitle">
 <span id="appSystemModalIcon"></span>
 <span id="appSystemModalTitleText">تنبيه</span>
 </h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
 </div>
 <div class="modal-body p-4">
 <p class="mb-3 fs-6 text-dark" id="appSystemModalMessage" style="line-height:1.6"></p>

 <!-- Interactive Provider Suggestions & Quick Alternative Switcher -->
 <div id="appSystemModalSuggestionsWrap" class="d-none mt-3 p-3 rounded-3 border" style="background:rgba(0, 242, 254, 0.04)">
 <h6 class="fw-bold text-dark d-flex align-items-center gap-2 mb-3">
 <span></span>
 <span>الحلول والبدائل المتاحة للترجمة الفورية:</span>
 </h6>
 <div class="d-flex flex-column gap-2" id="appSystemModalSuggestionsList">
 <!-- Dynamic alternative cards will be injected here -->
 </div>
 </div>
 
 <!-- Expandable Technical Details -->
 <div id="appSystemModalDetailsWrap" class="d-none mt-3">
 <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1 w-100 justify-content-between mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#appSystemModalDetailsCollapse">
 <span>تفاصيل الخطأ والخادم (Technical Details)</span>
 <span>▼</span>
 </button>
 <div class="collapse" id="appSystemModalDetailsCollapse">
 <pre class="p-3 bg-dark text-warning rounded-3 small font-monospace mb-0" id="appSystemModalDetailsText" style="max-height:200px;overflow:auto;white-space:pre-wrap;word-break:break-all"></pre>
 </div>
 </div>
 </div>
 <div class="modal-footer bg-light border-top py-2 d-flex justify-content-between" id="appSystemModalFooter">
 <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">إغلاق</button>
 <div id="appSystemModalCustomActions" class="d-flex gap-2"></div>
 </div>
 </div>
 </div>
</div>

<script>
let lastTranslationData = null;

// Global Rich Modal Displayer with Provider Diagnostics
function showAppModal(title, message, type = 'info', details = null, customActions = [], suggestions = null) {
 const modalEl = document.getElementById('appSystemModal');
 const titleText = document.getElementById('appSystemModalTitleText');
 const iconSpan = document.getElementById('appSystemModalIcon');
 const messageP = document.getElementById('appSystemModalMessage');
 const detailsWrap = document.getElementById('appSystemModalDetailsWrap');
 const detailsText = document.getElementById('appSystemModalDetailsText');
 const actionsDiv = document.getElementById('appSystemModalCustomActions');
 const suggestionsWrap = document.getElementById('appSystemModalSuggestionsWrap');
 const suggestionsList = document.getElementById('appSystemModalSuggestionsList');

 titleText.textContent = title;
 messageP.textContent = message;

 if (type === 'error') {
 iconSpan.textContent = '';
 titleText.className = 'text-danger fw-bold';
 } else if (type === 'success') {
 iconSpan.textContent = '';
 titleText.className = 'text-success fw-bold';
 } else if (type === 'warning') {
 iconSpan.textContent = '';
 titleText.className = 'text-warning fw-bold';
 } else {
 iconSpan.textContent = 'ℹ';
 titleText.className = 'text-primary fw-bold';
 }

 if (details) {
 detailsWrap.classList.remove('d-none');
 detailsText.textContent = typeof details === 'object' ? JSON.stringify(details, null, 2) : String(details);
 } else {
 detailsWrap.classList.add('d-none');
 detailsText.textContent = '';
 }

 // Render Provider Alternatives if provided
 if (suggestions && suggestions.providers) {
 suggestionsWrap.classList.remove('d-none');
 suggestionsList.innerHTML = '';

 suggestions.providers.forEach(p => {
 const row = document.createElement('div');
 row.className = 'p-3 bg-white rounded-3 border d-flex justify-content-between align-items-center gap-3 shadow-sm';
 
 const info = document.createElement('div');
 info.innerHTML = `
 <div class="fw-bold text-dark mb-1">${p.name}</div>
 <div class="text-muted small">${p.desc}</div>
 `;
 
 const btnWrap = document.createElement('div');
 if (p.is_current) {
 btnWrap.innerHTML = `<span class="badge bg-secondary px-3 py-2">المزود الحالي</span>`;
 } else if (p.ready) {
 const switchBtn = document.createElement('button');
 switchBtn.type = 'button';
 switchBtn.className = 'btn btn-sm btn-primary fw-bold px-3 shadow-sm text-nowrap';
 switchBtn.innerHTML = 'التبديل والترجمة به الآن';
 switchBtn.onclick = () => switchProviderAndRetry(p.id);
 btnWrap.appendChild(switchBtn);
 } else {
 btnWrap.innerHTML = `
 <a href="<?= app_url('admin/settings?group=ai_translation') ?>" target="_blank" class="btn btn-sm btn-outline-warning text-dark fw-bold text-nowrap">
 إضافة المفتاح
 </a>
 `;
 }

 row.appendChild(info);
 row.appendChild(btnWrap);
 suggestionsList.appendChild(row);
 });
 } else {
 suggestionsWrap.classList.add('d-none');
 suggestionsList.innerHTML = '';
 }

 actionsDiv.innerHTML = '';
 if (Array.isArray(customActions)) {
 customActions.forEach(act => {
 const b = document.createElement('a');
 b.className = act.class || 'btn btn-primary rounded-3';
 b.textContent = act.text;
 if (act.href) {
 b.href = act.href;
 if (act.target) b.target = act.target;
 }
 if (act.onClick) {
 b.addEventListener('click', act.onClick);
 }
 actionsDiv.appendChild(b);
 });
 }

 const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
 modalInstance.show();
}

// Quick Switch Provider API Handler
function switchProviderAndRetry(providerId) {
 const formData = new FormData();
 formData.append('provider', providerId);
 formData.append('_csrf_token', '<?= CSRF::token() ?>');

 const modalEl = document.getElementById('appSystemModal');
 const modalInstance = bootstrap.Modal.getInstance(modalEl);
 if (modalInstance) modalInstance.hide();

 const btnPreview = document.getElementById('btnTranslatePreview');
 if (btnPreview) {
 btnPreview.disabled = true;
 btnPreview.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري تبديل المزود...';
 }

 fetch('<?= app_url('admin/settings/quick-switch-provider') ?>', {
 method: 'POST',
 headers: {
 'X-Requested-With': 'XMLHttpRequest',
 'Accept': 'application/json'
 },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 if (data.success) {
 // Re-run the translation preview immediately with the new provider!
 setTimeout(() => runTranslationPreview(false), 300);
 } else {
 showAppModal('فشل التبديل', data.error || 'تعذر تبديل المزود.', 'error');
 }
 })
 .catch(err => {
 showAppModal('خطأ في الاتصال', err.message, 'error');
 });
}

function runTranslationPreview(directPublish = false) {
 const title = document.getElementById('title').value.trim();
 const content = document.getElementById('content').value.trim();

 if (!title) {
 showAppModal('تنبيه مطلوب', 'يرجى كتابة عنوان المقال أولاً في الحقل المخصص للتمكن من ترجمته وصياغته.', 'warning');
 document.getElementById('title').focus();
 return;
 }

 const btnPreview = document.getElementById('btnTranslatePreview');
 const btnPublish = document.getElementById('btnTranslatePublish');
 const originalPreviewHtml = btnPreview.innerHTML;
 const originalPublishHtml = btnPublish.innerHTML;

 btnPreview.disabled = true;
 btnPublish.disabled = true;
 btnPreview.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الترجمة والصياغة...';
 btnPublish.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري المعالجة...';

 const formData = new FormData();
 formData.append('title', title);
 formData.append('content', content);
 formData.append('_csrf_token', '<?= CSRF::token() ?>');

 fetch('<?= app_url('admin/articles/translate-preview') ?>', {
 method: 'POST',
 headers: {
 'X-Requested-With': 'XMLHttpRequest',
 'Accept': 'application/json'
 },
 body: formData
 })
 .then(async res => {
 const text = await res.text();
 let data;
 try {
 data = JSON.parse(text);
 } catch (e) {
 throw new Error(`تعذر معالجة الاستجابة من الخادم (HTTP ${res.status}):\n${text.substring(0, 300)}`);
 }

 btnPreview.disabled = false;
 btnPublish.disabled = false;
 btnPreview.innerHTML = originalPreviewHtml;
 btnPublish.innerHTML = originalPublishHtml;

 if (res.status === 401 || (data && data.require_login)) {
 showAppModal(
 'انتهت الجلسة',
 'انتهت جلستك الحالية في لوحة التحكم. يمكنك تسجيل الدخول في نافذة جديدة دون فقدان المقال المكتوب هنا.',
 'warning',
 'HTTP 401 Unauthorized - Session Expired',
 [{
 text: 'تسجيل الدخول في نافذة جديدة',
 href: '<?= app_url('login') ?>',
 target: '_blank',
 class: 'btn btn-primary rounded-3'
 }]
);
 return;
 }

 if (data.success && data.data) {
 lastTranslationData = data.data;

 if (directPublish) {
 applyTranslationToForm(true);
 } else {
 document.getElementById('previewTitleAr').value = data.data.title_ar || title;
 document.getElementById('previewExcerpt').value = data.data.excerpt || '';
 document.getElementById('previewContentAr').value = data.data.content_ar || content;
 
 document.getElementById('originalTitleView').textContent = title;
 document.getElementById('originalContentView').textContent = content;

 const modeBadge = document.getElementById('transModeBadge');
 if (data.data.mode === 'openai_gpt4o' || data.data.mode === 'openai_gpt-4o-mini') {
 modeBadge.textContent = 'OpenAI GPT-4o';
 } else if (data.data.mode === 'gemini_flash' || (data.data.mode && data.data.mode.includes('gemini'))) {
 modeBadge.textContent = 'Google Gemini';
 } else {
 modeBadge.textContent = 'Free Translator + Tech Glossary';
 }

 const modal = new bootstrap.Modal(document.getElementById('translationPreviewModal'));
 modal.show();
 }
 } else {
 // Build suggestion payload
 const suggestions = {
 providers: [
 {
 id: 'mymemory',
 name: 'المترجم المجاني الفوري (Google Web + القاموس التقني)',
 desc: 'جاهز ومجاني 100% بدون أي مفاتيح API أو قيود استهلاك.',
 ready: true
 },
 {
 id: 'gemini',
 name: 'Google Gemini (Gemini 1.5 Flash)',
 desc: 'سريع ودقيق مع صياغة صحفية ذكية.',
 ready: true
 },
 {
 id: 'openai',
 name: 'OpenAI (GPT-4o / GPT-4o-mini)',
 desc: 'نموذج الذكاء الاصطناعي الأقوى لصياغة التقارير الإخبارية.',
 ready: true
 }
 ]
 };

 showAppModal(
 'تعذر استكمال الترجمة بالخادم الحالي',
 data.error || 'حدث تعثر أثناء محاولة الترجمة عبر المزود المحدد. يمكنك التبديل إلى أحد الخيارات البديلة أدناه فوراً بنقرة واحدة:',
 'error',
 data.diagnostics || data,
 [{
 text: 'إعدادات الذكاء الاصطناعي',
 href: '<?= app_url('admin/settings?group=ai_translation') ?>',
 target: '_blank',
 class: 'btn btn-outline-secondary rounded-3'
 }],
 suggestions
);
 }
 })
 .catch(err => {
 btnPreview.disabled = false;
 btnPublish.disabled = false;
 btnPreview.innerHTML = originalPreviewHtml;
 btnPublish.innerHTML = originalPublishHtml;

 const defaultSuggestions = {
 providers: [
 {
 id: 'mymemory',
 name: 'المترجم المجاني الفوري (Google Web + القاموس التقني)',
 desc: 'جاهز ومجاني 100% بدون أي مفاتيح API أو قيود استهلاك.',
 ready: true
 },
 {
 id: 'gemini',
 name: 'Google Gemini (Gemini 1.5 Flash)',
 desc: 'سريع ودقيق مع صياغة صحفية ذكية.',
 ready: true
 },
 {
 id: 'openai',
 name: 'OpenAI (GPT-4o / GPT-4o-mini)',
 desc: 'نموذج الذكاء الاصطناعي الأقوى لصياغة التقارير الإخبارية.',
 ready: true
 }
 ]
 };

 showAppModal(
 'فشل الاتصال بخادم الترجمة',
 'حدث تعثر في الاستجابة من مزود الترجمة الحالي. يمكنك التبديل إلى أحد الحلول والبدائل المتاحة أدناه فوراً:',
 'error',
 err.message,
 [{
 text: 'إعدادات الذكاء الاصطناعي',
 href: '<?= app_url('admin/settings?group=ai_translation') ?>',
 target: '_blank',
 class: 'btn btn-outline-secondary rounded-3'
 }],
 defaultSuggestions
);
 });
}

function applyTranslationToForm(andSubmit = false) {
 const transTitle = document.getElementById('previewTitleAr').value || (lastTranslationData ? lastTranslationData.title_ar : '');
 const transExcerpt = document.getElementById('previewExcerpt').value || (lastTranslationData ? lastTranslationData.excerpt : '');
 const transContent = document.getElementById('previewContentAr').value || (lastTranslationData ? lastTranslationData.content_ar : '');

 if (transTitle) {
 document.getElementById('title').value = transTitle;
 document.getElementById('title_ar').value = transTitle;
 }
 if (transExcerpt) {
 document.getElementById('excerpt').value = transExcerpt;
 }
 if (transContent) {
 document.getElementById('content').value = transContent;
 }

 const modalEl = document.getElementById('translationPreviewModal');
 const modalInstance = bootstrap.Modal.getInstance(modalEl);
 if (modalInstance) {
 modalInstance.hide();
 }

 if (andSubmit) {
 const statusSelect = document.getElementById('status');
 if (statusSelect) {
 statusSelect.value = 'published';
 }
 document.getElementById('articleForm').submit();
 } else {
 showAppModal(
 'تم التطبيق بنجاح',
 'تم تطبيق الترجمة والصياغة العربية على حقول المقال في المحرر بنجاح! يمكنك الآن مراجعتها وحفظ التعديلات.',
 'success'
);
 }
}
</script>
