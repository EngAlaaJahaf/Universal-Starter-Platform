<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">رادار الزوار وعناكب البحث (Traffic & Bot Radar)</h2>
 <p class="text-muted mb-0">رصد لحظي فائق الدقة لكافة عناكب محركات البحث، زواحف الذكاء الاصطناعي، والزوار في المنصة.</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= admin_e(app_url('admin/traffic-radar/export-csv')) ?>" class="btn btn-outline-success btn-sm fw-bold shadow-sm">
 <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير سجل الزيارات CSV
 </a>
 <button type="button" class="btn btn-outline-danger btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#purgeTrafficModal">
 <i class="bi bi-trash3 me-1"></i> تنظيف السجلات (>30 يوم)
 </button>
 </div>
</div>

<!-- ================= LIVE TRAFFIC METRICS (LAST 24 HOURS) ================= -->
<div class="row g-3 mb-4">
 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-primary border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-primary-subtle text-primary" style="font-size:1.5rem">
 <i class="bi bi-activity"></i>
 </div>
 <div>
 <span class="text-muted small d-block">إجمالي الطلبات والزيارات (24h)</span>
 <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalHits24h) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-success border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-success-subtle text-success" style="font-size:1.5rem">
 <i class="bi bi-google"></i>
 </div>
 <div>
 <span class="text-muted small d-block">عناكب محركات البحث (Google/Bing)</span>
 <h4 class="fw-bold mb-0 text-success"><?= number_format($searchEngineHits) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-info border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-info-subtle text-info" style="font-size:1.5rem">
 <i class="bi bi-robot"></i>
 </div>
 <div>
 <span class="text-muted small d-block">زواحف الذكاء الاصطناعي (OpenAI/Claude)</span>
 <h4 class="fw-bold mb-0 text-info"><?= number_format($aiCrawlerHits) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-warning border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-warning-subtle text-warning" style="font-size:1.5rem">
 <i class="bi bi-person-workspace"></i>
 </div>
 <div>
 <span class="text-muted small d-block">زيارات الزوار الحقيقيين (Humans)</span>
 <h4 class="fw-bold mb-0 text-dark"><?= number_format($humanHits) ?></h4>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- ================= TOP SPIDERS & TOP PAGES CARDS ================= -->
<div class="row g-4 mb-4">
 <!-- Top Bots Active -->
 <div class="col-lg-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h6 class="mb-0 fw-bold">أكثر العناكب والروبوتات نشاطاً في الموقع</h6>
 <span class="badge bg-primary-subtle text-primary">آخر 24 ساعة</span>
 </div>
 <div class="card-body p-3">
 <?php if (empty($topBots)): ?>
 <p class="text-muted small text-center my-3">لا توجد زيارات لعناكب البحث مسجلة في آخر 24 ساعة.</p>
 <?php else: ?>
 <div class="list-group list-group-flush">
 <?php foreach ($topBots as $b): ?>
 <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
 <div>
 <strong class="text-dark d-block"><?= admin_e($b['bot_name']) ?></strong>
 <span class="badge bg-light text-muted border font-monospace" style="font-size:0.7rem"><?= admin_e($b['visitor_type']) ?></span>
 </div>
 <span class="badge bg-primary rounded-pill px-3 py-2"><?= number_format($b['cnt']) ?> طلب زحف</span>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>

 <!-- Top Visited URLs -->
 <div class="col-lg-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h6 class="mb-0 fw-bold">أكثر الصفحات التي تمت زيارتها أو فهرستها</h6>
 <span class="badge bg-success-subtle text-success">الأكثر طلباً</span>
 </div>
 <div class="card-body p-3">
 <?php if (empty($topPages)): ?>
 <p class="text-muted small text-center my-3">لا توجد صفحات مسجلة حتى الآن.</p>
 <?php else: ?>
 <div class="list-group list-group-flush">
 <?php foreach ($topPages as $p): ?>
 <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
 <div class="text-truncate me-2" style="max-width:340px">
 <code class="text-dark fw-bold"><?= admin_e($p['request_uri']) ?></code>
 <small class="text-muted d-block" style="font-size:0.75rem">متوسط السرعة: <?= round((float) $p['avg_speed'], 1) ?> ms</small>
 </div>
 <span class="badge bg-dark rounded-pill px-3 py-2"><?= number_format($p['cnt']) ?> زيارة</span>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>
</div>

<!-- ================= LIVE TRAFFIC FILTER PILLS ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-body p-3">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
 
 <!-- Filter Pills (Instant Zero-Reload) -->
 <div class="d-flex gap-2 flex-wrap" id="trafficFilterGroup">
 <button type="button" data-type="all" class="btn btn-sm traffic-filter-btn <?= empty($type) ? 'btn-dark' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 كافة الزيارات (الكل)
 </button>
 <button type="button" data-type="search_engine" class="btn btn-sm traffic-filter-btn <?= $type === 'search_engine' ? 'btn-success' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 عناكب البحث (Google/Bing)
 </button>
 <button type="button" data-type="ai_crawler" class="btn btn-sm traffic-filter-btn <?= $type === 'ai_crawler' ? 'btn-info text-dark fw-bold' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 زواحف الذكاء الاصطناعي (GPT/Claude)
 </button>
 <button type="button" data-type="human_visitor" class="btn btn-sm traffic-filter-btn <?= $type === 'human_visitor' ? 'btn-primary' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 زوار حقيقيون (Humans)
 </button>
 <button type="button" data-type="security_scanner" class="btn btn-sm traffic-filter-btn <?= $type === 'security_scanner' ? 'btn-danger' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 أدوات فحص ومسح أمني
 </button>
 </div>

 <!-- Instant Live Search Box -->
 <div class="d-flex gap-2" style="min-width:280px">
 <div class="input-group input-group-sm">
 <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
 <input type="text" id="trafficLiveSearch" class="form-control border-start-0" placeholder="بحث فوري بـ IP أو اسم العنكبوت أو الرابط..." value="<?= admin_e($q) ?>">
 </div>
 <button type="button" id="trafficResetBtn" class="btn btn-outline-secondary btn-sm" style="<?= empty($q) && empty($type) ? 'display:none' : '' ?>">إلغاء</button>
 </div>

 </div>
 </div>
</div>

<!-- ================= LIVE TRAFFIC STREAM TABLE ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0" id="trafficDataTable">
 <thead class="table-light">
 <tr>
 <th>الكائن / نوع الزائر</th>
 <th>الصفحة المطلوبة (URI)</th>
 <th>طريقة وكود HTTP</th>
 <th>سرعة الاستجابة</th>
 <th>عنوان IP</th>
 <th>التاريخ والتوقيت الدقيق</th>
 <th class="text-center">فحص</th>
 </tr>
 </thead>
 <tbody>
 <tr id="trafficNoResultsRow" style="display:none">
 <td colspan="7" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2.5rem"></span>
 <h6 class="fw-bold">لا توجد زيارات مسجلة تطابق هذا الفلتر أو البحث.</h6>
 </td>
 </tr>
 <?php if (empty($logs)): ?>
 <tr id="trafficInitialEmptyRow">
 <td colspan="7" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2.5rem"></span>
 <h6 class="fw-bold">لا توجد زيارات مسجلة حالياً.</h6>
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($logs as $log): ?>
 <tr class="traffic-data-row" 
 data-visitor-type="<?= admin_e($log['visitor_type']) ?>" 
 data-search-text="<?= strtolower(admin_e($log['bot_name'] . ' ' . $log['ip_address'] . ' ' . $log['request_uri'] . ' ' . $log['visitor_type'] . ' ' . ($log['user_agent'] ?? ''))) ?>">
 <!-- Bot Name & Type -->
 <td>
 <div>
 <strong class="text-dark d-block"><?= admin_e($log['bot_name']) ?></strong>
 <?php if ($log['visitor_type'] === 'search_engine'): ?>
 <span class="badge bg-success-subtle text-success border">عنكبوت بحث</span>
 <?php elseif ($log['visitor_type'] === 'ai_crawler'): ?>
 <span class="badge bg-info-subtle text-info border">زاحف ذكاء اصطناعي</span>
 <?php elseif ($log['visitor_type'] === 'human_visitor'): ?>
 <span class="badge bg-primary-subtle text-primary border">زائر حقيقي</span>
 <?php else: ?>
 <span class="badge bg-danger-subtle text-danger border"><?= admin_e($log['visitor_type']) ?></span>
 <?php endif; ?>
 </div>
 </td>

 <!-- URI -->
 <td>
 <a href="<?= admin_e(app_url(ltrim($log['request_uri'], '/'))) ?>" target="_blank" class="text-dark text-decoration-none font-monospace small hover-primary d-inline-block text-truncate" style="max-width:280px">
 <?= admin_e($log['request_uri']) ?> ↗
 </a>
 </td>

 <!-- Method & Status -->
 <td>
 <span class="badge bg-dark font-monospace"><?= admin_e($log['http_method']) ?></span>
 <span class="badge <?= (int) $log['status_code'] === 200 ? 'bg-success' : ((int) $log['status_code'] === 404 ? 'bg-warning text-dark' : 'bg-danger') ?> font-monospace">
 <?= (int) $log['status_code'] ?>
 </span>
 </td>

 <!-- Response Time -->
 <td>
 <span class="badge bg-light text-secondary border font-monospace">
 <?= (int) $log['response_time_ms'] ?> ms
 </span>
 </td>

 <!-- IP -->
 <td>
 <span class="badge bg-light text-dark border font-monospace">
 <?= admin_e($log['ip_address']) ?>
 </span>
 </td>

 <!-- Timestamp -->
 <td>
 <small class="text-dark d-block fw-semibold"><?= admin_e(date('Y-m-d H:i:s', strtotime($log['created_at']))) ?></small>
 </td>

 <!-- Detail Inspector -->
 <td class="text-center">
 <button type="button" 
 class="btn btn-sm btn-outline-primary p-1 px-2 rounded-circle"
 onclick='openBotInspector(<?= json_encode($log, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
 <i class="bi bi-eye"></i>
 </button>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>

 <!-- Pagination -->
 <?php if ($totalPages> 1): ?>
 <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <small class="text-muted">الصفحة <strong><?= $page ?></strong> من <strong><?= $totalPages ?></strong> (إجمالي <?= number_format($totalLogs) ?> سجل)</small>
 <?php $qParams = $_GET; ?>
 <nav>
 <ul class="pagination pagination-sm mb-0">
 <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): $qParams['page'] = $p; ?>
 <li class="page-item <?= $p === $page ? 'active' : '' ?>">
 <a class="page-link" href="?<?= http_build_query($qParams) ?>"><?= $p ?></a>
 </li>
 <?php endfor; ?>
 </ul>
 </nav>
 </div>
 <?php endif; ?>
</div>

<!-- Modal: Bot Inspector -->
<div class="modal fade" id="botInspectorModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header bg-light border-bottom py-3">
 <h5 class="modal-title fw-bold text-dark">فحص تفاصيل الزيارة والبصمة الرقمية</h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body p-4">
 <div class="mb-3 p-3 bg-light rounded-3 border">
 <div class="row g-2">
 <div class="col-sm-6"><strong>نوع الزائر:</strong> <span id="mBotName" class="badge bg-primary"></span></div>
 <div class="col-sm-6"><strong>عنوان IP:</strong> <code id="mIp"></code></div>
 <div class="col-sm-6"><strong>المسار:</strong> <code id="mUri"></code></div>
 <div class="col-sm-6"><strong>التوقيت:</strong> <span id="mTime"></span></div>
 </div>
 </div>
 <div>
 <label class="form-label small fw-bold">معلومات المتصفح والبصمة (User-Agent):</label>
 <textarea id="mUserAgent" class="form-control font-monospace small bg-light" rows="3" readonly></textarea>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- Modal: Purge Traffic -->
<div class="modal fade" id="purgeTrafficModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header bg-danger text-white py-3">
 <h5 class="modal-title fw-bold">تنظيف سجلات حركة المرور القديمة</h5>
 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
 </div>
 <form method="post" action="<?= admin_e(app_url('admin/traffic-radar/purge')) ?>">
 <?= CSRF::field() ?>
 <div class="modal-body p-4">
 <p class="mb-0">هل ترغب في حذف سجلات الزيارات وعناكب البحث التي مضى عليها أكثر من <strong>30 يوماً</strong>؟</p>
 </div>
 <div class="modal-footer bg-light border-top">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" class="btn btn-danger fw-bold">نعم، تنظيف الآن</button>
 </div>
 </form>
 </div>
 </div>
</div>

<script>
function openBotInspector(log) {
 document.getElementById('mBotName').textContent = log.bot_name;
 document.getElementById('mIp').textContent = log.ip_address;
 document.getElementById('mUri').textContent = log.request_uri;
 document.getElementById('mTime').textContent = log.created_at;
 document.getElementById('mUserAgent').value = log.user_agent || 'غير معروف';
 new bootstrap.Modal(document.getElementById('botInspectorModal')).show();
}

// Instant Zero-Reload Live Filter & Live Search Engine
document.addEventListener('DOMContentLoaded', function () {
 const filterButtons = document.querySelectorAll('.traffic-filter-btn');
 const searchInput = document.getElementById('trafficLiveSearch');
 const resetBtn = document.getElementById('trafficResetBtn');
 const tableRows = document.querySelectorAll('.traffic-data-row');
 const noResultsRow = document.getElementById('trafficNoResultsRow');
 const initialEmptyRow = document.getElementById('trafficInitialEmptyRow');

 let activeType = '<?= !empty($type) ? admin_e($type) : 'all' ?>';
 let searchQuery = (searchInput ? searchInput.value : '').trim().toLowerCase();

 function applyFilters() {
 let visibleRows = 0;
 const q = searchQuery.toLowerCase().trim();

 tableRows.forEach(function (row) {
 const rowType = row.getAttribute('data-visitor-type') || '';
 const rowSearchText = row.getAttribute('data-search-text') || '';

 const matchesType = (activeType === 'all' || rowType === activeType);
 const matchesQuery = (!q || rowSearchText.includes(q));

 if (matchesType && matchesQuery) {
 row.style.display = '';
 visibleRows++;
 } else {
 row.style.display = 'none';
 }
 });

 if (tableRows.length> 0) {
 if (noResultsRow) {
 noResultsRow.style.display = (visibleRows === 0) ? '' : 'none';
 }
 }

 if (resetBtn) {
 resetBtn.style.display = (activeType !== 'all' || q !== '') ? 'inline-block' : 'none';
 }
 }

 // Pill Click Handler (Instant Zero Reload)
 filterButtons.forEach(function (btn) {
 btn.addEventListener('click', function (e) {
 e.preventDefault();
 const chosenType = this.getAttribute('data-type') || 'all';
 activeType = chosenType;

 // Update Active UI States
 filterButtons.forEach(function (b) {
 b.classList.remove('btn-dark', 'btn-success', 'btn-info', 'btn-primary', 'btn-danger', 'text-dark', 'fw-bold');
 b.classList.add('btn-light', 'border');
 });

 this.classList.remove('btn-light', 'border');
 if (chosenType === 'search_engine') {
 this.classList.add('btn-success');
 } else if (chosenType === 'ai_crawler') {
 this.classList.add('btn-info', 'text-dark', 'fw-bold');
 } else if (chosenType === 'human_visitor') {
 this.classList.add('btn-primary');
 } else if (chosenType === 'security_scanner') {
 this.classList.add('btn-danger');
 } else {
 this.classList.add('btn-dark');
 }

 // Update browser URL query param seamlessly without reloading page
 const currentUrl = new URL(window.location.href);
 if (chosenType === 'all') {
 currentUrl.searchParams.delete('type');
 } else {
 currentUrl.searchParams.set('type', chosenType);
 }
 window.history.replaceState({}, '', currentUrl.toString());

 applyFilters();
 });
 });

 // Instant Live Search Handler on Keyup
 if (searchInput) {
 searchInput.addEventListener('input', function () {
 searchQuery = this.value;
 
 const currentUrl = new URL(window.location.href);
 if (searchQuery.trim()) {
 currentUrl.searchParams.set('q', searchQuery.trim());
 } else {
 currentUrl.searchParams.delete('q');
 }
 window.history.replaceState({}, '', currentUrl.toString());

 applyFilters();
 });
 }

 // Reset Filter Button Handler
 if (resetBtn) {
 resetBtn.addEventListener('click', function () {
 if (searchInput) searchInput.value = '';
 searchQuery = '';
 
 const allBtn = document.querySelector('.traffic-filter-btn[data-type="all"]');
 if (allBtn) allBtn.click();
 });
 }

 // Apply initial filter state
 if (activeType !== 'all' || searchQuery) {
 applyFilters();
 }
});
</script>
