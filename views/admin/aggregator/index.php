<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>استيراد ونشر الأخبار فائق السرعة</h2>
 <p class="text-muted mb-0">سحب ومتابعة أحدث الأخبار العالمية والعربية من خلاصات RSS أو صفحات الويب مباشرة وإعادة نشرها بنقرة واحدة.</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <!-- Dedicated Manage Sources CRUD Button -->
 <a href="<?= admin_e(app_url('admin/rss-sources')) ?>" class="btn btn-dark fw-bold shadow-sm">
 <i class="bi bi-gear-fill me-1"></i> إدارة وتعديل المصادر (CRUD)
 </a>

 <!-- Custom Scraper Toggle Button -->
 <button class="btn btn-outline-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#customScraperCollapse" title="سحب الأخبار من أي رابط مخصص">
 <i class="bi bi-magic me-1"></i> سحب من رابط مخصص
 </button>

 <!-- Export Feeds Dropdown -->
 <div class="dropdown">
 <button class="btn btn-outline-secondary dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown">
 <i class="bi bi-cloud-arrow-down me-1"></i> تصدير المصادر
 </button>
 <ul class="dropdown-menu dropdown-menu-end shadow-sm">
 <li><a class="dropdown-item" href="<?= admin_e(app_url('admin/news-feeds/export/opml')) ?>"><i class="bi bi-file-earmark-code me-2"></i>تصدير بتنسيق OPML (Feedly / Inoreader)</a></li>
 <li><a class="dropdown-item" href="<?= admin_e(app_url('admin/news-feeds/export/json')) ?>"><i class="bi bi-file-earmark-text me-2"></i>تصدير بتنسيق JSON</a></li>
 </ul>
 </div>

 <!-- Import Feeds Button -->
 <button class="btn btn-outline-secondary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#importFeedsBox">
 <i class="bi bi-cloud-arrow-up me-1"></i> استيراد خلاصات
 </button>

 <!-- Add Single Source Button -->
 <a href="<?= admin_e(app_url('admin/rss-sources/create')) ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-plus-lg me-1"></i> إضافة مصدر جديد
 </a>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm">
 <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<?php if ($err = Session::getFlash('error') ?? $error): ?>
 <div class="alert alert-danger alert-dismissible fade show shadow-sm">
 <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($err) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- Quick Custom Web Link Scraper (Collapsed by default for clean UX) -->
<div class="collapse mb-4 <?= !empty($customFeedUrl) ? 'show' : '' ?>" id="customScraperCollapse">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 border-start border-primary border-4" style="background:linear-gradient(135deg, rgba(0, 242, 254, 0.05), rgba(157, 78, 221, 0.05))">
 <div class="row align-items-center g-3">
 <div class="col-md-5">
 <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-magic text-primary me-2"></i>المستخرج التلقائي من الروابط</h6>
 <p class="text-muted small mb-0">الصق رابط أي موقع أو صفحة أخبار عادية لتحليلها واستخراج الأخبار فوراً.</p>
 </div>
 <div class="col-md-7">
 <form method="get" action="<?= admin_e(app_url('admin/news-feeds')) ?>" class="d-flex gap-2">
 <div class="input-group shadow-sm rounded-3 overflow-hidden w-100">
 <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-link-45deg text-primary"></i></span>
 <input type="url" name="custom_url" class="form-control border-start-0 font-monospace small" placeholder="https://example.com/news..." value="<?= admin_e($customFeedUrl) ?>" required>
 <button type="submit" class="btn btn-primary px-3 fw-bold text-nowrap" style="background:linear-gradient(135deg,#00f2fe,#9d4edd);border:none">
 سحب الأخبار
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
</div>

<!-- Import Feeds Collapse Box -->
<div class="collapse mb-4" id="importFeedsBox">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4 border-start border-primary border-4">
 <h6 class="fw-bold mb-2"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>استيراد حزمة مصادر RSS جديدة (OPML / JSON / نصي)</h6>
 <p class="text-muted small mb-3">يمكنك رفع ملف OPML من قارئ الأخبار المفضل لديك أو لصق قائمة بروابط الـ RSS (كل رابط في سطر منفصل).</p>
 <form method="post" action="<?= admin_e(app_url('admin/news-feeds/import')) ?>" enctype="multipart/form-data">
 <?= CSRF::field() ?>
 <div class="row g-3">
 <div class="col-md-5">
 <label class="form-label small fw-bold">رفع ملف (OPML / JSON / XML)</label>
 <input type="file" name="import_file" class="form-control" accept=".opml,.json,.xml,.txt">
 </div>
 <div class="col-md-7">
 <label class="form-label small fw-bold">أو الصق روابط الخلاصات والمواقع مباشرة</label>
 <textarea name="bulk_urls" class="form-control font-monospace" rows="2" placeholder="https://example.com/feed/&#10;https://another.com/news"></textarea>
 </div>
 <div class="col-12 text-end">
 <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">بدء الاستيراد الفوري</button>
 </div>
 </div>
 </form>
 </div>
</div>

<!-- ================= ADVANCED SOURCE HUB & PINNING RADAR ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
 
 <!-- Pinned Sources Top Section (Favorites) -->
 <div id="pinnedSourcesSection" class="p-2 mb-3 rounded-3 pinned-sources-box">
 <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
 <div class="d-flex align-items-center gap-2">
 <span class="badge bg-warning text-dark p-1 px-2 fw-bold"><i class="bi bi-pin-angle-fill me-1"></i>المصادر المثبتة المفضلة</span>
 <small class="text-muted" style="font-size:0.75rem">المصادر التي قمت بتثبيتها للوصول السريع</small>
 </div>
 <small class="text-muted" style="font-size:0.75rem">انقر على لإلغاء التثبيت</small>
 </div>
 <div id="pinnedSourcesList" class="d-flex align-items-center gap-2 flex-wrap">
 <span id="noPinnedNotice" class="text-muted small py-1 px-2 fst-italic">لم تقم بتثبيت أي مصدر بعد. انقر على أيقونة الدبوس بجانب أي مصدر لتثبيته هنا!</span>
 </div>
 </div>

 <!-- Search & Category Filters Bar -->
 <div class="row g-2 align-items-center justify-content-between mb-3">
 <!-- Live Search Input -->
 <div class="col-md-4">
 <div class="input-group input-group-sm">
 <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
 <input type="text" id="sourceSearchInput" class="form-control border-start-0 bg-light" placeholder="بحث سريع في المصادر الـ (<?= count($sources) ?>)..." oninput="filterSourcesLive()" onkeyup="filterSourcesLive()" onsearch="filterSourcesLive()">
 <button type="button" class="btn btn-outline-secondary border-start-0" onclick="clearSourceSearch()" title="مسح"><i class="bi bi-x-lg"></i></button>
 </div>
 </div>

 <!-- Quick Category Filter Pills -->
 <div class="col-md-8">
 <div class="d-flex gap-1 flex-wrap justify-content-md-end" id="sourceCategoryFilters">
 <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 active-category-btn" onclick="filterSourceCategory('all', this)">
 الكل (<?= count($sources) ?>)
 </button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" onclick="filterSourceCategory('arabic', this)">
 مصادر عربية
 </button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" onclick="filterSourceCategory('ai', this)">
 ذكاء اصطناعي
 </button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" onclick="filterSourceCategory('security', this)">
 أمن سيبراني
 </button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" onclick="filterSourceCategory('dev', this)">
 برمجة ومطورين
 </button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1" onclick="filterSourceCategory('hardware', this)">
 عتاد وأجهزة
 </button>
 </div>
 </div>
 </div>

 <!-- All Sources Chips Grid -->
 <div id="sourcesChipsGrid" class="d-flex align-items-center gap-2 flex-wrap" style="max-height:220px;overflow-y:auto;padding:6px 2px">
 <?php foreach ($sources as $src): ?>
 <?php 
 $sName = $src['name'];
 $sCatName = $src['category_name'] ?? '';
 $sId = (int) $src['id'];
 $isActive = ((int) $selectedSourceId === $sId && empty($customFeedUrl));
 
 // Detect Category Tag
 $tag = 'general';
 if (preg_match('/(عالم التقنية|التقنية بلا حدود|أردرويد|البوابة العربية|أخبار التطبيقات)/u', $sName)) {
 $tag = 'arabic';
 } elseif (preg_match('/(OpenAI|Nvidia|AI|Google|ذكاء|Google The Keyword)/i', $sName)) {
 $tag = 'ai';
 } elseif (preg_match('/(Security|Krebs|Dark Reading|قرصنة|دفاع)/i', $sName)) {
 $tag = 'security';
 } elseif (preg_match('/(Dev|GitHub|Smashing|InfoQ|Hacker News|مطورين|برمجية)/i', $sName)) {
 $tag = 'dev';
 } elseif (preg_match('/(GSMArena|Hardware|Wccftech|Tom|هواتف|عتاد)/i', $sName)) {
 $tag = 'hardware';
 }
 ?>
 <div class="source-chip-wrapper d-inline-flex align-items-center rounded-pill border <?= $isActive ? 'is-active bg-dark text-white border-dark shadow-sm' : 'bg-white text-dark shadow-xs' ?> p-1 pe-2" 
 data-source-id="<?= $sId ?>"
 data-source-name="<?= admin_e(mb_strtolower($sName, 'UTF-8')) ?>"
 data-source-cat="<?= $tag ?>"
 data-source-url="<?= admin_e(app_url('admin/news-feeds?source_id=' . $sId)) ?>"
 data-source-title="<?= admin_e($sName) ?>"
 style="transition:all 0.18s ease;font-size:0.84rem">
 
 <!-- Pin Toggle Button -->
 <button type="button" 
 class="btn-pin-source border-0 bg-transparent p-0 px-1 me-1 text-muted" 
 onclick="togglePinSource(<?= $sId ?>, '<?= admin_e(addslashes($sName)) ?>', '<?= admin_e(app_url('admin/news-feeds?source_id=' . $sId)) ?>', event)" 
 title="تثبيت / إلغاء تثبيت بالمفضلة">
 <i class="bi bi-pin-angle" id="pinIcon_<?= $sId ?>" style="font-size:0.9rem"></i>
 </button>

 <!-- Source Link -->
 <a href="<?= admin_e(app_url('admin/news-feeds?source_id=' . $sId)) ?>" 
 class="source-chip-link text-decoration-none <?= $isActive ? 'text-cyan fw-bold' : 'text-dark' ?> d-flex align-items-center gap-1">
 <?php if ($tag === 'arabic'): ?>
 <span></span>
 <?php elseif ($tag === 'ai'): ?>
 <span></span>
 <?php elseif ($tag === 'security'): ?>
 <span></span>
 <?php elseif ($tag === 'dev'): ?>
 <span></span>
 <?php elseif ($tag === 'hardware'): ?>
 <span></span>
 <?php else: ?>
 <span></span>
 <?php endif; ?>
 <span><?= admin_e($sName) ?></span>
 </a>
 </div>
 <?php endforeach; ?>
 </div>
</div>

<style>
.text-cyan { color: #00f2fe !important; }
.pinned-sources-box {
 background: #fffbeb;
 border: 1px dashed #fde68a;
 transition: all 0.2s ease;
}
.pinned-source-chip {
 background: #ffffff;
 border: 1px solid #f59e0b;
 color: #1e293b;
 transition: all 0.15s ease;
}
.pinned-source-chip a {
 color: #1e293b;
}
.source-chip-wrapper:hover {
 transform: translateY(-1px);
 border-color: #00f2fe !important;
 box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.btn-pin-source:hover i {
 color: #f59e0b !important;
 transform: scale(1.2);
}
.btn-pin-source.is-pinned i {
 color: #f59e0b !important;
}

/* Dark Mode Overrides for Feeds Aggregator */
html.admin-dark .pinned-sources-box {
 background: rgba(245, 158, 11, 0.08) !important;
 border: 1px dashed rgba(245, 158, 11, 0.35) !important;
}
html.admin-dark .pinned-source-chip {
 background: #161f30 !important;
 border: 1px solid rgba(245, 158, 11, 0.5) !important;
}
html.admin-dark .pinned-source-chip a {
 color: #f1f5f9 !important;
}
html.admin-dark .pinned-source-chip a:hover {
 color: #00f2fe !important;
}
html.admin-dark .source-chip-wrapper {
 background-color: #161f30 !important;
 border-color: rgba(255, 255, 255, 0.12) !important;
 color: #f1f5f9 !important;
}
html.admin-dark .source-chip-wrapper.is-active {
 background-color: #034b63 !important;
 border-color: #00f2fe !important;
}
html.admin-dark .source-chip-link {
 color: #cbd5e1 !important;
}
html.admin-dark .source-chip-wrapper.is-active .source-chip-link {
 color: #00f2fe !important;
}

/* High contrast search inputs in Dark Mode */
html.admin-dark #sourceSearchInput,
html.admin-dark #newsSearchInput {
 background-color: #0d1117 !important;
 border-color: rgba(255, 255, 255, 0.18) !important;
 color: #f1f5f9 !important;
}
html.admin-dark #sourceSearchInput::placeholder,
html.admin-dark #newsSearchInput::placeholder {
 color: #94a3b8 !important;
 opacity: 1 !important;
}
html.admin-dark .input-group-text {
 background-color: #161f30 !important;
 border-color: rgba(255, 255, 255, 0.18) !important;
 color: #00f2fe !important;
}
</style>

<script>
// ================= CLIENT-SIDE PINNING & SOURCE FILTER ENGINE =================
const PINNED_STORAGE_KEY = 'pinned_feed_sources_v1';

function getPinnedSources() {
 try {
 return JSON.parse(localStorage.getItem(PINNED_STORAGE_KEY)) || [];
 } catch (e) {
 return [];
 }
}

function savePinnedSources(list) {
 localStorage.setItem(PINNED_STORAGE_KEY, JSON.stringify(list));
 renderPinnedSources();
}

function togglePinSource(id, name, url, event) {
 if (event) {
 event.preventDefault();
 event.stopPropagation();
 }
 let list = getPinnedSources();
 const existingIndex = list.findIndex(item => item.id === id);
 
 if (existingIndex> -1) {
 list.splice(existingIndex, 1);
 } else {
 list.push({ id: id, name: name, url: url });
 }
 savePinnedSources(list);
}

function renderPinnedSources() {
 const list = getPinnedSources();
 const container = document.getElementById('pinnedSourcesList');
 const notice = document.getElementById('noPinnedNotice');
 
 // Reset all pin icons
 document.querySelectorAll('.btn-pin-source').forEach(btn => {
 btn.classList.remove('is-pinned');
 const icon = btn.querySelector('i');
 if (icon) {
 icon.className = 'bi bi-pin-angle';
 }
 });

 if (list.length === 0) {
 container.innerHTML = '<span id="noPinnedNotice" class="text-muted small py-1 px-2 fst-italic">لم تقم بتثبيت أي مصدر بعد. انقر على أيقونة الدبوس بجانب أي مصدر لتثبيته هنا!</span>';
 return;
 }

 let html = '';
 list.forEach(item => {
 // Highlight corresponding chip pin icon
 const pinBtn = document.querySelector(`.source-chip-wrapper[data-source-id="${item.id}"] .btn-pin-source`);
 if (pinBtn) {
 pinBtn.classList.add('is-pinned');
 const icon = pinBtn.querySelector('i');
 if (icon) icon.className = 'bi bi-pin-angle-fill text-warning';
 }

 html += `
 <div class="pinned-source-chip d-inline-flex align-items-center rounded-pill px-2 py-1 shadow-xs me-1 mb-1">
 <a href="${item.url}" class="text-decoration-none fw-bold small me-1">
 ${item.name}
 </a>
 <button type="button" class="btn btn-link text-danger p-0 ms-1 text-decoration-none" onclick="togglePinSource(${item.id}, '', '', event)" title="إلغاء التثبيت" style="font-size:0.75rem">
 <i class="bi bi-x-circle-fill"></i>
 </button>
 </div>
 `;
 });

 container.innerHTML = html;
}

// Search text normalizer (handles Arabic letters, diacritics & accents)
function normalizeSearchText(txt) {
 if (!txt) return '';
 return txt.toString().toLowerCase().trim()
 .replace(/[أإآ]/g, 'ا')
 .replace(/ة/g, 'ه')
 .replace(/ى/g, 'ي')
 .replace(/[\u064B-\u065F]/g, '');
}

// Live Search Filter (Searches in Sources ONLY - Independent)
function filterSourcesLive() {
 const rawQ = document.getElementById('sourceSearchInput')?.value || '';
 const q = normalizeSearchText(rawQ);
 const chips = document.querySelectorAll('.source-chip-wrapper');
 let visibleSources = 0;
 
 chips.forEach(chip => {
 const name = normalizeSearchText(chip.getAttribute('data-source-name') || '');
 const cat = normalizeSearchText(chip.getAttribute('data-source-cat') || '');
 const url = normalizeSearchText(chip.getAttribute('data-source-url') || '');
 const title = normalizeSearchText(chip.getAttribute('data-source-title') || '');
 const text = normalizeSearchText(chip.innerText || '');
 
 if (!q || name.includes(q) || cat.includes(q) || url.includes(q) || title.includes(q) || text.includes(q)) {
 chip.classList.remove('d-none');
 chip.style.setProperty('display', 'inline-flex', 'important');
 visibleSources++;
 } else {
 chip.classList.add('d-none');
 chip.style.setProperty('display', 'none', 'important');
 }
 });
}

function clearSourceSearch() {
 const input = document.getElementById('sourceSearchInput');
 if (input) input.value = '';
 filterSourcesLive();
}

// Category Filter Tabs
function filterSourceCategory(cat, btn) {
 document.querySelectorAll('#sourceCategoryFilters button').forEach(b => {
 b.className = 'btn btn-sm btn-light border rounded-pill px-3 py-1';
 });
 btn.className = 'btn btn-sm btn-dark rounded-pill px-3 py-1';

 const chips = document.querySelectorAll('.source-chip-wrapper');
 chips.forEach(chip => {
 const cCat = chip.getAttribute('data-source-cat');
 if (cat === 'all' || cCat === cat) {
 chip.classList.remove('d-none');
 chip.style.setProperty('display', 'inline-flex', 'important');
 } else {
 chip.classList.add('d-none');
 chip.style.setProperty('display', 'none', 'important');
 }
 });
}

// Live News Items Filtering Engine
let currentNewsStatusFilter = 'all';

function filterNewsStatus(status, btn) {
 currentNewsStatusFilter = status;
 document.querySelectorAll('#newsStatusFilterBtns button').forEach(b => {
 b.classList.remove('btn-dark', 'text-white');
 b.classList.add('btn-light');
 });
 btn.classList.remove('btn-light');
 btn.classList.add('btn-dark', 'text-white');
 filterNewsLive();
}

function filterNewsLive() {
 const rawQ = document.getElementById('newsSearchInput')?.value || document.getElementById('sourceSearchInput')?.value || '';
 const q = normalizeSearchText(rawQ);
 const cards = document.querySelectorAll('.news-item-col');
 const noResults = document.getElementById('noNewsSearchResults');
 const countBadge = document.getElementById('visibleNewsCount');
 let visibleCount = 0;

 cards.forEach(card => {
 const title = normalizeSearchText(card.getAttribute('data-news-title') || '');
 const excerpt = normalizeSearchText(card.getAttribute('data-news-excerpt') || '');
 const status = card.getAttribute('data-news-status') || 'new';

 const matchesQuery = !q || title.includes(q) || excerpt.includes(q);
 const matchesStatus = (currentNewsStatusFilter === 'all') 
 || (currentNewsStatusFilter === 'new' && (!status || status === 'new'))
 || (status === currentNewsStatusFilter);

 if (matchesQuery && matchesStatus) {
 card.style.display = '';
 visibleCount++;
 } else {
 card.style.display = 'none';
 }
 });

 if (countBadge) {
 countBadge.innerText = visibleCount;
 }
 if (noResults) {
 noResults.style.display = (visibleCount === 0 && cards.length> 0) ? 'block' : 'none';
 }
}

function clearNewsSearch() {
 const newsInput = document.getElementById('newsSearchInput');
 const sourceInput = document.getElementById('sourceSearchInput');
 if (newsInput) newsInput.value = '';
 if (sourceInput) sourceInput.value = '';
 filterSourcesLive();
 filterNewsLive();
}

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', function () {
 renderPinnedSources();
 
 // Attach independent event listeners
 const sInput = document.getElementById('sourceSearchInput');
 if (sInput) {
 sInput.addEventListener('input', filterSourcesLive);
 sInput.addEventListener('keyup', filterSourcesLive);
 }
 const nInput = document.getElementById('newsSearchInput');
 if (nInput) {
 nInput.addEventListener('input', filterNewsLive);
 nInput.addEventListener('keyup', filterNewsLive);
 }
});
</script>

<!-- Feed Results Header & Stream Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-4">
 <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
 <div class="d-flex align-items-center gap-2 flex-wrap">
 <h5 class="fw-bold mb-0">
 <span>الأخبار الحية من: </span>
 <span class="badge bg-primary-subtle text-primary border py-2 px-3"><?= admin_e($activeSourceName ?: 'المصدر المحدد') ?></span>
 <?php if (!empty($customFeedUrl)): ?>
 <span class="badge bg-info-subtle text-info border py-2 px-3 font-monospace ms-1"><i class="bi bi-link-45deg"></i> <?= admin_e($customFeedUrl) ?></span>
 <?php endif; ?>
 </h5>
 <span class="badge bg-light text-dark border px-2 py-1 small">
 عرض <strong id="visibleNewsCount" class="text-primary"><?= count($items) ?></strong> من <?= count($items) ?> خبر
 </span>
 </div>

 <!-- Quick Status Filter Pills -->
 <div class="d-flex gap-1 flex-wrap" id="newsStatusFilterBtns">
 <button type="button" class="btn btn-sm btn-dark text-white rounded-pill px-3" onclick="filterNewsStatus('all', this)">الكل (<?= count($items) ?>)</button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="filterNewsStatus('new', this)"> غير منشور</button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="filterNewsStatus('published', this)">منشور</button>
 <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" onclick="filterNewsStatus('draft', this)">مسودات</button>
 </div>
 </div>

 <!-- News Live Search Input -->
 <div class="input-group">
 <span class="input-group-text bg-light border-end-0 text-primary"><i class="bi bi-search"></i></span>
 <input type="text" id="newsSearchInput" class="form-control border-start-0 bg-light" placeholder="ابحث في عناوين ونصوص الأخبار المعروضة حالياً (مثل security, AI, آبل, إلخ)..." oninput="filterNewsLive()">
 <button type="button" class="btn btn-outline-secondary border-start-0" onclick="clearNewsSearch()" title="إعادة تعيين وبحث"><i class="bi bi-x-lg"></i> مسح</button>
 </div>
</div>

<!-- No Search Results Notice -->
<div id="noNewsSearchResults" class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center text-muted mb-4" style="display:none">
 <i class="bi bi-search d-block mb-3" style="font-size:2.5rem;color:#cbd5e1"></i>
 <h5 class="fw-bold">لا توجد أخبار تطابق عبارة البحث الحالية</h5>
 <p class="mb-3 text-muted small">جرب كلمة مفتاحية أخرى أو انقر على زر المسح لعرض كافة الأخبار.</p>
 <div>
 <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold" onclick="clearNewsSearch()">إلغاء الفلتر وعرض الكل</button>
 </div>
</div>

<!-- News Feed Grid -->
<?php if (empty($items)): ?>
 <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center text-muted">
 <i class="bi bi-rss d-block mb-3" style="font-size:3rem;color:#cbd5e1"></i>
 <h5 class="fw-bold">لم يتم جلب أخبار من هذا المصدر حالياً</h5>
 <p class="mb-0">اختر مصدراً آخر من القائمة أو الصق رابط أي صفحة ويب في شريط السحب بالأعلى لعرض وتصفح الأخبار الحية.</p>
 </div>
<?php else: ?>
 <div class="row g-4" id="newsFeedGrid">
 <?php foreach ($items as $item): ?>
 <div class="col-md-6 col-lg-4 news-item-col" 
 data-news-title="<?= admin_e(mb_strtolower($item['title'], 'UTF-8')) ?>"
 data-news-excerpt="<?= admin_e(mb_strtolower($item['excerpt'], 'UTF-8')) ?>"
 data-news-status="<?= admin_e($item['import_status'] ?? 'new') ?>">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden d-flex flex-column" style="border: 1px solid #edf2f7!important">
 <!-- Image Preview -->
 <div style="height:175px;background:#0f172a;position:relative;overflow:hidden">
 <?php if (!empty($item['featured_image'])): ?>
 <img src="<?= admin_e($item['featured_image']) ?>" 
 class="w-100 h-100" 
 style="object-fit:cover" 
 alt="<?= admin_e($item['title']) ?>"
 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
 <?php else: ?>
 <div class="w-100 h-100 d-grid place-items-center text-white" style="background:linear-gradient(135deg,#1e293b,#0f172a);font-size:2rem"></div>
 <?php endif; ?>

 <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 start-0 m-2" style="font-size:0.75rem">
 <i class="bi bi-clock me-1"></i> <?= admin_e($item['pubDate']) ?>
 </span>

 <?php if (!empty($item['import_status'])): ?>
 <?php if ($item['import_status'] === 'published'): ?>
 <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow-sm">
 <i class="bi bi-check-circle-fill me-1"></i> منشور للعامة
 </span>
 <?php elseif ($item['import_status'] === 'draft'): ?>
 <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2 shadow-sm">
 <i class="bi bi-pencil-square me-1"></i> مسودة قيد التحرير
 </span>
 <?php else: ?>
 <span class="badge bg-secondary position-absolute top-0 end-0 m-2 shadow-sm">
 <?= admin_e($item['import_status']) ?>
 </span>
 <?php endif; ?>
 <?php endif; ?>
 </div>

 <!-- Card Body -->
 <div class="card-body p-4 d-flex flex-column">
 <h6 class="fw-bold mb-2 text-dark" style="line-height:1.5">
 <a href="<?= admin_e($item['link']) ?>" target="_blank" class="text-dark text-decoration-none hover-primary">
 <?= admin_e($item['title']) ?> ↗
 </a>
 </h6>
 <p class="text-muted small mb-3 flex-grow-1" style="line-height:1.6">
 <?= admin_e($item['excerpt']) ?>
 </p>

 <!-- Actions Bar -->
 <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto flex-wrap gap-2">
 <a href="<?= admin_e($item['link']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="زيارة الرابط الأصلي">
 <i class="bi bi-box-arrow-up-right"></i> المصدر
 </a>

 <div class="d-flex gap-1 flex-wrap">
 <!-- 1. Translate & Publish Form (For Foreign News) -->
 <form method="post" action="<?= admin_e(app_url('admin/news-feeds/translate-publish')) ?>" class="d-inline publish-ajax-form" data-action="translate">
 <?= CSRF::field() ?>
 <input type="hidden" name="title" value="<?= admin_e($item['title']) ?>">
 <input type="hidden" name="excerpt" value="<?= admin_e($item['excerpt']) ?>">
 <input type="hidden" name="content" value="<?= admin_e($item['content']) ?>">
 <input type="hidden" name="source_url" value="<?= admin_e($item['link']) ?>">
 <input type="hidden" name="source_name" value="<?= admin_e($activeSourceName) ?>">
 <input type="hidden" name="featured_image" value="<?= admin_e($item['featured_image']) ?>">
 <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm" title="ترجمة فورية بالذكاء الاصطناعي ونشر باللغة العربية">
 ترجمة ونشر
 </button>
 </form>

 <!-- 2. Direct Instant Publish Form (For Arabic/Direct News - No Translation) -->
 <form method="post" action="<?= admin_e(app_url('admin/news-feeds/fast-publish')) ?>" class="d-inline publish-ajax-form" data-action="fast">
 <?= CSRF::field() ?>
 <input type="hidden" name="title" value="<?= admin_e($item['title']) ?>">
 <input type="hidden" name="excerpt" value="<?= admin_e($item['excerpt']) ?>">
 <input type="hidden" name="content" value="<?= admin_e($item['content']) ?>">
 <input type="hidden" name="source_url" value="<?= admin_e($item['link']) ?>">
 <input type="hidden" name="source_name" value="<?= admin_e($activeSourceName) ?>">
 <input type="hidden" name="featured_image" value="<?= admin_e($item['featured_image']) ?>">
 <button type="submit" class="btn btn-sm btn-primary fw-bold shadow-sm" title="نشر مباشر بدون ترجمة (للأخبار العربية)">
 نشر فوري
 </button>
 </form>

 <!-- 3. Draft in Full Editor -->
 <form method="post" action="<?= admin_e(app_url('admin/news-feeds/draft-article')) ?>" class="d-inline publish-ajax-form" data-action="draft">
 <?= CSRF::field() ?>
 <input type="hidden" name="title" value="<?= admin_e($item['title']) ?>">
 <input type="hidden" name="excerpt" value="<?= admin_e($item['excerpt']) ?>">
 <input type="hidden" name="content" value="<?= admin_e($item['content']) ?>">
 <input type="hidden" name="source_url" value="<?= admin_e($item['link']) ?>">
 <input type="hidden" name="source_name" value="<?= admin_e($activeSourceName) ?>">
 <input type="hidden" name="featured_image" value="<?= admin_e($item['featured_image']) ?>">
 <button type="submit" class="btn btn-sm btn-light border fw-semibold" title="تعديل في المحرر الكامل قبل النشر">
 تحرير
 </button>
 </form>
 </div>
 </div>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
<?php endif; ?>

<script>
// ================= AJAX PUBLISH: نشر بدون إعادة تحميل الصفحة =================
(function () {
 const forms = document.querySelectorAll('.publish-ajax-form');
 if (!forms.length) return;

 let toast = document.getElementById('aggregatorToastBox');
 if (!toast) {
  toast = document.createElement('div');
  toast.id = 'aggregatorToastBox';
  toast.style.cssText = 'position:fixed;top:18px;left:50%;transform:translateX(-50%);z-index:1080;max-width:680px;width:calc(100% - 32px)';
  document.body.appendChild(toast);
 }
 function esc(s) {
  return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
   return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
 }
 function showToast(html, isError) {
  toast.innerHTML = '<div class="alert alert-' + (isError ? 'danger' : 'success') + ' alert-dismissible fade show shadow-lg small mb-0">' + html + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  clearTimeout(toast._timer);
  toast._timer = setTimeout(function () { toast.innerHTML = ''; }, 7000);
 }

 forms.forEach(function (form) {
  form.addEventListener('submit', function (e) {
   e.preventDefault();
   const btn = form.querySelector('button[type=submit]');
   if (!btn || btn.disabled) return;
   const originalHtml = btn.innerHTML;
   const action = form.getAttribute('data-action') || 'publish';
   btn.disabled = true;
   btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جارٍ النشر...';

   fetch(form.action, {
    method: 'POST',
    body: new FormData(form),
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin'
   })
   .then(function (res) {
    return res.text().then(function (t) { return { ok: res.ok, status: res.status, text: t }; });
   })
   .then(function (r) {
    if (!r.text || !r.text.trim()) throw new Error('استجابة الخادم فارغة. حاول مرة أخرى (ربما انتهت مهلة الاستضافة).');
    let data;
    try {
     data = JSON.parse(r.text);
    } catch (e) {
     throw new Error('استجابة غير صالحة من الخادم (HTTP ' + r.status + ').');
    }
    if (!data || !data.success) throw new Error((data && data.error) || 'فشل النشر.');

    if (action === 'draft' && data.edit_url) {
     showToast('<i class="bi bi-check-circle-fill me-1"></i> تم إنشاء المسودة. جارٍ فتح المحرر...');
     window.location.href = data.edit_url;
     return;
    }
    showToast('<i class="bi bi-check-circle-fill me-1"></i> ' + esc(data.message || 'تم النشر بنجاح.'));

    const bar = form.closest('.d-flex.gap-1.flex-wrap');
    if (bar) {
     let link = '';
     if (data.edit_url) link = ' <a class="text-white fw-bold me-2" href="' + esc(data.edit_url) + '">فتح المقال</a>';
     bar.innerHTML = '<span class="badge bg-success px-3 py-2 shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> تم النشر</span>' + link;
    }

    const card = form.closest('.news-item-col');
    const statusBadge = card ? card.querySelector('.badge.position-absolute.top-0.end-0') : null;
    if (statusBadge) {
     statusBadge.className = 'badge bg-success position-absolute top-0 end-0 m-2 shadow-sm';
     statusBadge.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> منشور للعامة';
     card.setAttribute('data-news-status', 'published');
     filterNewsLive();
    }
    if (btn && btn.isConnected) { btn.disabled = false; btn.innerHTML = originalHtml; }
   })
   .catch(function (err) {
    showToast('<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + esc(err.message), true);
    btn.disabled = false;
    btn.innerHTML = originalHtml;
   });
  });
 });
})();
</script>
