<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">التنبيهات الأمنية ورصد الاختراقات (Security IDS Radar)</h2>
 <p class="text-muted mb-0">نظام الكشف الآلي عن هجمات الحقن، فحص الملفات الحساسة، والتصدي للأنشطة المشبوهة فوراً.</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <form method="post" action="<?= admin_e(app_url('admin/security-alerts/clear-all')) ?>" onsubmit="return confirm('هل أنت متأكد من رغبتك في مسح كافة التنبيهات المعالجة؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-outline-danger btn-sm fw-bold shadow-sm">
 <i class="bi bi-check2-all me-1"></i> مسح التنبيهات المعالجة
 </button>
 </form>
 </div>
</div>

<!-- ================= SECURITY METRICS ================= -->
<div class="row g-3 mb-4">
 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-danger border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-danger-subtle text-danger" style="font-size:1.5rem">
 <i class="bi bi-shield-slash-fill"></i>
 </div>
 <div>
 <span class="text-muted small d-block">إجمالي التهديدات المرصودة</span>
 <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalAlerts) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-warning border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-warning-subtle text-warning" style="font-size:1.5rem">
 <i class="bi bi-exclamation-triangle-fill"></i>
 </div>
 <div>
 <span class="text-muted small d-block">تنبيهات تحتاج مراجعة</span>
 <h4 class="fw-bold mb-0 text-warning"><?= number_format($unresolvedCount) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-danger border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-danger text-white" style="font-size:1.5rem">
 <i class="bi bi-radioactive"></i>
 </div>
 <div>
 <span class="text-muted small d-block">تهديدات عالية الخطورة (Critical)</span>
 <h4 class="fw-bold mb-0 text-danger"><?= number_format($criticalCount) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100 border-start border-success border-4">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center bg-success-subtle text-success" style="font-size:1.5rem">
 <i class="bi bi-shield-check"></i>
 </div>
 <div>
 <span class="text-muted small d-block">طلبات تم حظرها تلقائياً (403)</span>
 <h4 class="fw-bold mb-0 text-success"><?= number_format($blockedCount) ?></h4>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- ================= INSTANT SECURITY FILTERS ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-body p-3">
 <div class="d-flex gap-2 flex-wrap" id="securityFilterGroup">
 <button type="button" data-filter="all" class="btn btn-sm sec-filter-btn <?= empty($severity) && empty($status) ? 'btn-dark' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 كافة التنبيهات
 </button>
 <button type="button" data-filter="unresolved" class="btn btn-sm sec-filter-btn <?= $status === 'unresolved' ? 'btn-warning text-dark fw-bold' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 غير معالجة (<?= $unresolvedCount ?>)
 </button>
 <button type="button" data-filter="critical" class="btn btn-sm sec-filter-btn <?= $severity === 'critical' ? 'btn-danger fw-bold' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 حرج / خطير (Critical)
 </button>
 <button type="button" data-filter="resolved" class="btn btn-sm sec-filter-btn <?= $status === 'resolved' ? 'btn-success fw-bold' : 'btn-light border' ?> rounded-pill px-3 fw-semibold">
 تمت المعالجة
 </button>
 </div>
 </div>
</div>

<!-- ================= ALERTS TABLE ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th>مستوى الخطورة</th>
 <th>نوع التهديد والهجوم</th>
 <th>المسار المستهدف (Target URI)</th>
 <th>عنوان IP المهاجم</th>
 <th>عينة الحمولة الخبيثة (Payload)</th>
 <th>حالة الحظر</th>
 <th>التاريخ والوقت</th>
 <th class="text-center">إجراءات</th>
 </tr>
 </thead>
 <tbody>
 <tr id="secNoResultsRow" style="display:none">
 <td colspan="8" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2.5rem"></span>
 <h6 class="fw-bold">لا توجد تنبيهات تطابق هذا الفلتر.</h6>
 </td>
 </tr>
 <?php if (empty($alerts)): ?>
 <tr>
 <td colspan="8" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2.5rem"></span>
 <h6 class="fw-bold text-success">المنظومة في أمان تام ولا توجد تنبيهات أمنية حالياً.</h6>
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($alerts as $a): ?>
 <tr class="sec-alert-row <?= empty($a['is_resolved']) ? 'table-warning-subtle' : '' ?>"
 data-resolved="<?= !empty($a['is_resolved']) ? '1' : '0' ?>"
 data-severity="<?= admin_e($a['severity']) ?>">
 <!-- Severity -->
 <td>
 <?php if ($a['severity'] === 'critical'): ?>
 <span class="badge bg-danger px-2 py-1"><i class="bi bi-exclamation-octagon-fill me-1"></i>حرج / Critical</span>
 <?php elseif ($a['severity'] === 'high'): ?>
 <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>عالي الخطورة</span>
 <?php else: ?>
 <span class="badge bg-info-subtle text-info border px-2 py-1"><?= admin_e($a['severity']) ?></span>
 <?php endif; ?>
 </td>

 <!-- Alert Type -->
 <td>
 <strong class="text-dark d-block font-monospace"><?= admin_e($a['alert_type']) ?></strong>
 </td>

 <!-- Target URI -->
 <td>
 <code class="text-danger small font-monospace d-inline-block text-truncate" style="max-width:200px">
 <?= admin_e($a['target_uri']) ?>
 </code>
 </td>

 <!-- IP -->
 <td>
 <span class="badge bg-dark font-monospace"><?= admin_e($a['ip_address']) ?></span>
 </td>

 <!-- Payload -->
 <td>
 <code class="small text-muted d-inline-block text-truncate" style="max-width:220px;background:#f8fafc;padding:2px 6px;border-radius:4px">
 <?= admin_e($a['payload_sample'] ?: '—') ?>
 </code>
 </td>

 <!-- Is Blocked -->
 <td>
 <?php if (!empty($a['is_blocked'])): ?>
 <span class="badge bg-success-subtle text-success border"><i class="bi bi-shield-check me-1"></i>محظور تلقائياً 403</span>
 <?php else: ?>
 <span class="badge bg-secondary-subtle text-secondary">مرصود</span>
 <?php endif; ?>
 </td>

 <!-- Time -->
 <td>
 <small class="text-muted"><?= admin_e(fmt_date($a['created_at'], 'Y-m-d H:i:s')) ?></small>
 </td>

 <!-- Actions -->
 <td class="text-center">
 <div class="d-flex gap-1 justify-content-center">
 <?php if (empty($a['is_resolved'])): ?>
 <form method="post" action="<?= admin_e(app_url('admin/security-alerts/' . $a['id'] . '/resolve')) ?>" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm btn-success p-1 px-2" title="تعليم كتمت المعالجة">
 <i class="bi bi-check-lg"></i>
 </button>
 </form>
 <?php endif; ?>

 <form method="post" action="<?= admin_e(app_url('admin/security-alerts/' . $a['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('حذف هذا التنبيه؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm btn-outline-danger p-1 px-2" title="حذف">
 <i class="bi bi-trash"></i>
 </button>
 </form>
 </div>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
 const secBtns = document.querySelectorAll('.sec-filter-btn');
 const secRows = document.querySelectorAll('.sec-alert-row');
 const secNoResultsRow = document.getElementById('secNoResultsRow');

 let currentSecFilter = 'all';

 function applySecFilter() {
 let visibleCount = 0;

 secRows.forEach(function (row) {
 const isResolved = row.getAttribute('data-resolved') === '1';
 const severity = row.getAttribute('data-severity');

 let match = true;
 if (currentSecFilter === 'unresolved') {
 match = !isResolved;
 } else if (currentSecFilter === 'resolved') {
 match = isResolved;
 } else if (currentSecFilter === 'critical') {
 match = (severity === 'critical');
 }

 if (match) {
 row.style.display = '';
 visibleCount++;
 } else {
 row.style.display = 'none';
 }
 });

 if (secRows.length> 0 && secNoResultsRow) {
 secNoResultsRow.style.display = visibleCount === 0 ? '' : 'none';
 }
 }

 secBtns.forEach(function (btn) {
 btn.addEventListener('click', function (e) {
 e.preventDefault();
 const filter = this.getAttribute('data-filter') || 'all';
 currentSecFilter = filter;

 secBtns.forEach(function (b) {
 b.classList.remove('btn-dark', 'btn-warning', 'btn-danger', 'btn-success', 'text-dark', 'fw-bold');
 b.classList.add('btn-light', 'border');
 });

 this.classList.remove('btn-light', 'border');
 if (filter === 'unresolved') {
 this.classList.add('btn-warning', 'text-dark', 'fw-bold');
 } else if (filter === 'critical') {
 this.classList.add('btn-danger', 'fw-bold');
 } else if (filter === 'resolved') {
 this.classList.add('btn-success', 'fw-bold');
 } else {
 this.classList.add('btn-dark');
 }

 // Update URL without reload
 const currentUrl = new URL(window.location.href);
 currentUrl.searchParams.delete('status');
 currentUrl.searchParams.delete('severity');
 if (filter === 'unresolved') currentUrl.searchParams.set('status', 'unresolved');
 else if (filter === 'resolved') currentUrl.searchParams.set('status', 'resolved');
 else if (filter === 'critical') currentUrl.searchParams.set('severity', 'critical');
 window.history.replaceState({}, '', currentUrl.toString());

 applySecFilter();
 });
 });
});
</script>
