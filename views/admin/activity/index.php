<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">سجل النشاط والأمان (Audit Log)</h2>
 <p class="text-muted mb-0">تتبع ومراقبة العمليات الحساسة، التعديلات، وكشف الأنشطة المشبوهة في المنصة.</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <!-- Export Actions -->
 <a href="<?= admin_e(app_url('admin/activity-log/export/csv')) ?>" class="btn btn-outline-success btn-sm fw-bold shadow-sm">
 <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
 </a>
 <a href="<?= admin_e(app_url('admin/activity-log/export/json')) ?>" class="btn btn-outline-primary btn-sm fw-bold shadow-sm">
 <i class="bi bi-filetype-json me-1"></i> تصدير JSON
 </a>
 
 <!-- Cleanup Modal Trigger -->
 <button type="button" class="btn btn-outline-danger btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#cleanupLogsModal">
 <i class="bi bi-trash3 me-1"></i> أرشفة وتنظيف (>90 يوم)
 </button>
 </div>
</div>

<!-- ================= SECURITY & ACTIVITY KPI CARDS ================= -->
<div class="row g-3 mb-4">
 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center" style="background:#e0f2fe;color:#0284c7;font-size:1.5rem">
 <i class="bi bi-journal-text"></i>
 </div>
 <div>
 <span class="text-muted small d-block">إجمالي العمليات المسجلة</span>
 <h4 class="fw-bold mb-0 text-dark"><?= number_format($totalLogs) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center <?= $criticalCount24h> 0 ? 'bg-danger text-white' : 'bg-danger-subtle text-danger' ?>" style="font-size:1.5rem">
 <i class="bi bi-shield-exclamation"></i>
 </div>
 <div>
 <span class="text-muted small d-block">عمليات حرجة (آخر 24 ساعة)</span>
 <h4 class="fw-bold mb-0 <?= $criticalCount24h> 0 ? 'text-danger' : 'text-dark' ?>">
 <?= number_format($criticalCount24h) ?>
 <?php if ($criticalCount24h> 0): ?>
 <span class="badge bg-danger ms-1" style="font-size:0.65rem">تحذير أمني</span>
 <?php endif; ?>
 </h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center" style="background:#fef3c7;color:#d97706;font-size:1.5rem">
 <i class="bi bi-people"></i>
 </div>
 <div>
 <span class="text-muted small d-block">المدراء والفاعلون النشطون</span>
 <h4 class="fw-bold mb-0 text-dark"><?= number_format($uniqueActorsCount) ?></h4>
 </div>
 </div>
 </div>
 </div>

 <div class="col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3 h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-4 p-3 d-grid place-items-center" style="background:#dcfce7;color:#16a34a;font-size:1.5rem">
 <i class="bi bi-clock-history"></i>
 </div>
 <div>
 <span class="text-muted small d-block">نشاط آخر 24 ساعة</span>
 <h4 class="fw-bold mb-0 text-dark"><?= number_format($total24h) ?></h4>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- ================= CRITICAL ACTIVITY ALERT BANNER ================= -->
<?php if ($criticalCount24h> 0): ?>
 <div class="alert alert-danger border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
 <div class="d-flex align-items-center gap-2">
 <span class="badge bg-danger p-2"><i class="bi bi-shield-lock-fill fs-6"></i></span>
 <div>
 <strong class="text-danger">تنبيه حماية المنصة:</strong>
 <span class="text-dark small">تم رصد (<strong><?= $criticalCount24h ?></strong>) عملية حساسة (مثل الحذف أو تجديد المفاتيح أو الحظر) خلال الـ 24 ساعة الماضية. يمكنك تصفيتها أدناه.</span>
 </div>
 </div>
 <a href="<?= admin_e(app_url('admin/activity-log?severity=critical')) ?>" class="btn btn-sm btn-danger fw-bold px-3">
 عرض العمليات الحرجة فقط ←
 </a>
 </div>
<?php endif; ?>

<!-- ================= ADVANCED MULTI-FILTER BAR ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-body p-3">
 <form method="get" action="<?= admin_e(app_url('admin/activity-log')) ?>">
 <div class="row g-2 align-items-end">
 
 <!-- Search Query -->
 <div class="col-md-3">
 <label class="form-label small fw-bold mb-1">بحث سريع</label>
 <input type="text" class="form-control form-control-sm" name="q" value="<?= admin_e($q) ?>" placeholder="معرف، وصف، IP، مستخدم...">
 </div>

 <!-- Severity Filter -->
 <div class="col-md-2">
 <label class="form-label small fw-bold mb-1">مستوى الخطورة</label>
 <select class="form-select form-select-sm" name="severity">
 <option value="">كافة المستويات</option>
 <option value="critical" <?= $severity === 'critical' ? 'selected' : '' ?>>عالي الخطورة / حرج</option>
 <option value="warning" <?= $severity === 'warning' ? 'selected' : '' ?>>متوسط / تعديلات</option>
 <option value="success" <?= $severity === 'success' ? 'selected' : '' ?>>عادي / إنشاء ونشر</option>
 </select>
 </div>

 <!-- Action Filter -->
 <div class="col-md-2">
 <label class="form-label small fw-bold mb-1">نوع الإجراء</label>
 <select class="form-select form-select-sm" name="action">
 <option value="">كافة الإجراءات</option>
 <?php foreach ($availableActions as $act): ?>
 <option value="<?= admin_e($act) ?>" <?= $action === $act ? 'selected' : '' ?>><?= admin_e($act) ?></option>
 <?php endforeach; ?>
 </select>
 </div>

 <!-- Entity Filter -->
 <div class="col-md-2">
 <label class="form-label small fw-bold mb-1">القسم / الكيان</label>
 <select class="form-select form-select-sm" name="entity_type">
 <option value="">كافة الكيانات</option>
 <?php foreach ($availableEntities as $ent): ?>
 <option value="<?= admin_e($ent) ?>" <?= $entityType === $ent ? 'selected' : '' ?>><?= admin_e($ent) ?></option>
 <?php endforeach; ?>
 </select>
 </div>

 <!-- Actor Filter -->
 <div class="col-md-2">
 <label class="form-label small fw-bold mb-1">المستخدم</label>
 <select class="form-select form-select-sm" name="user_id">
 <option value="">كافة المستخدمين</option>
 <?php foreach ($availableUsers as $usr): ?>
 <option value="<?= (int) $usr['id'] ?>" <?= $userId === (int) $usr['id'] ? 'selected' : '' ?>><?= admin_e($usr['username']) ?></option>
 <?php endforeach; ?>
 </select>
 </div>

 <!-- Per Page & Submit -->
 <div class="col-md-1 d-flex gap-1">
 <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold" title="تطبيق الفلترة">
 <i class="bi bi-funnel-fill"></i>
 </button>
 <?php if ($q || $action || $entityType || $userId || $severity || $dateFrom || $dateTo): ?>
 <a href="<?= admin_e(app_url('admin/activity-log')) ?>" class="btn btn-outline-secondary btn-sm" title="إعادة تعيين">
 <i class="bi bi-x-lg"></i>
 </a>
 <?php endif; ?>
 </div>

 </div>

 <!-- Date Range Collapsible Filters -->
 <div class="row g-2 mt-2 pt-2 border-top">
 <div class="col-md-3">
 <div class="input-group input-group-sm">
 <span class="input-group-text small bg-light">من:</span>
 <input type="date" class="form-control" name="date_from" value="<?= admin_e($dateFrom) ?>">
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-sm">
 <span class="input-group-text small bg-light">إلى:</span>
 <input type="date" class="form-control" name="date_to" value="<?= admin_e($dateTo) ?>">
 </div>
 </div>
 <div class="col-md-3">
 <div class="input-group input-group-sm">
 <span class="input-group-text small bg-light">العناصر بالصفحة:</span>
 <select class="form-select" name="per_page" onchange="this.form.submit()">
 <?php foreach ([25, 50, 100, 200] as $cnt): ?>
 <option value="<?= $cnt ?>" <?= $perPage === $cnt ? 'selected' : '' ?>><?= $cnt ?> سجل</option>
 <?php endforeach; ?>
 </select>
 </div>
 </div>
 <div class="col-md-3 text-end">
 <small class="text-muted">النتائج المطابقة: <strong><?= number_format($totalLogs) ?></strong> عملية</small>
 </div>
 </div>
 </form>
 </div>
</div>

<!-- ================= AUDIT LOGS DATA TABLE ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:160px">المستخدم المنفذ</th>
 <th style="width:140px">نوع الإجراء والخطورة</th>
 <th style="width:120px">القسم / الكيان</th>
 <th style="width:90px">المعرف</th>
 <th>التفاصيل والبيانات المعدلة</th>
 <th style="width:130px">عنوان IP</th>
 <th style="width:150px">التاريخ والوقت</th>
 <th style="width:70px" class="text-center">فحص</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($logs)): ?>
 <tr>
 <td colspan="8" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2.5rem"></span>
 <h6 class="fw-bold">لا توجد سجلات تطابق معايير الفلترة المحددة.</h6>
 <p class="small mb-0">جرب تعديل خيارات البحث أو إعادة تعيين الفلاتر.</p>
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($logs as $log): ?>
 <tr>
 <!-- User Actor -->
 <td>
 <div class="d-flex align-items-center gap-2">
 <div class="rounded-circle bg-light border p-1 d-grid place-items-center" style="width:32px;height:32px;font-size:0.9rem">
 
 </div>
 <div>
 <strong class="text-dark d-block text-truncate" style="max-width:110px">
 <?= admin_e($log['username'] ?? 'نظام آلي') ?>
 </strong>
 </div>
 </div>
 </td>

 <!-- Action & Severity Badge -->
 <td>
 <span class="badge <?= $log['badge_class'] ?> px-2 py-1 shadow-sm font-monospace" style="font-size:0.75rem">
 <i class="bi <?= $log['icon'] ?> me-1"></i><?= admin_e($log['action']) ?>
 </span>
 </td>

 <!-- Entity -->
 <td>
 <span class="badge bg-light text-dark border px-2 py-1">
 <?= admin_e($log['entity_type']) ?>
 </span>
 </td>

 <!-- Entity ID -->
 <td>
 <?php if (!empty($log['entity_id'])): ?>
 <span class="badge bg-secondary-subtle text-secondary font-monospace">#<?= (int) $log['entity_id'] ?></span>
 <?php else: ?>
 <span class="text-muted small">—</span>
 <?php endif; ?>
 </td>

 <!-- Summary / Clean Payload -->
 <td>
 <?php if (!empty($log['clean_new_values'])): ?>
 <code class="small text-muted d-inline-block text-truncate" style="max-width:280px;background:#f8fafc;padding:2px 6px;border-radius:4px">
 <?= admin_e(is_array($log['clean_new_values']) ? json_encode($log['clean_new_values'], JSON_UNESCAPED_UNICODE) : (string) $log['clean_new_values']) ?>
 </code>
 <?php elseif (!empty($log['description'])): ?>
 <span class="small text-muted"><?= admin_e(mb_strimwidth($log['description'], 0, 60, '…', 'UTF-8')) ?></span>
 <?php else: ?>
 <span class="text-muted small">لا توجد بيانات إضافية</span>
 <?php endif; ?>
 </td>

 <!-- IP Address -->
 <td>
 <?= GeoIp::label($log['ip_address'] ?? '') ?>
 </td>

 <!-- Timestamp -->
 <td>
<small class="text-dark d-block fw-semibold"><?= admin_e(fmt_date($log['created_at'], 'Y-m-d H:i')) ?></small>
  <small class="text-muted" style="font-size:0.7rem"><?= admin_e(fmt_date($log['created_at'], 's')) ?>s</small>
 </td>

 <!-- View Inspector Button -->
 <td class="text-center">
 <button type="button" 
 class="btn btn-sm btn-outline-primary p-1 px-2 rounded-circle" 
 title="عرض التفاصيل الكاملة"
 onclick='openLogDetailsModal(<?= json_encode([
 'id' => $log['id'],
 'username' => $log['username'] ?? 'نظام آلي',
 'action' => $log['action'],
 'severity' => $log['severity'],
 'entity' => $log['entity_type'],
 'entity_id' => $log['entity_id'],
 'ip' => $log['ip_address'],
 'country_code' => $log['country_code'] ?? '',
 'country_name' => $log['country_name'] ?? '',
 'country_flag' => $log['country_flag'] ?? '',
 'user_agent' => $log['user_agent'],
 'created_at' => fmt_date($log['created_at'], 'Y-m-d H:i:s'),
 'old_values' => $log['clean_old_values'],
 'new_values' => $log['clean_new_values']
 ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>)'>
 <i class="bi bi-eye"></i>
 </button>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>

 <!-- ================= PAGINATION CONTROLS ================= -->
 <?php if ($totalPages> 1): ?>
 <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <small class="text-muted">
 الصفحة <strong><?= $page ?></strong> من <strong><?= $totalPages ?></strong> (إجمالي <?= number_format($totalLogs) ?> سجل)
 </small>

 <?php
 // Build query string for pagination preserving all filters
 $queryParams = $_GET;
 ?>
 <nav aria-label="Page navigation">
 <ul class="pagination pagination-sm mb-0">
 <!-- First & Previous -->
 <?php if ($page> 1): ?>
 <li class="page-item">
 <?php $queryParams['page'] = 1; ?>
 <a class="page-link" href="?<?= http_build_query($queryParams) ?>">« الأولى</a>
 </li>
 <li class="page-item">
 <?php $queryParams['page'] = $page - 1; ?>
 <a class="page-link" href="?<?= http_build_query($queryParams) ?>">‹ السابق</a>
 </li>
 <?php endif; ?>

 <!-- Numeric Window (5 pages) -->
 <?php
 $startPage = max(1, $page - 2);
 $endPage = min($totalPages, $page + 2);
 for ($p = $startPage; $p <= $endPage; $p++):
 $queryParams['page'] = $p;
 ?>
 <li class="page-item <?= $p === $page ? 'active' : '' ?>">
 <a class="page-link" href="?<?= http_build_query($queryParams) ?>"><?= $p ?></a>
 </li>
 <?php endfor; ?>

 <!-- Next & Last -->
 <?php if ($page < $totalPages): ?>
 <li class="page-item">
 <?php $queryParams['page'] = $page + 1; ?>
 <a class="page-link" href="?<?= http_build_query($queryParams) ?>">التالي ›</a>
 </li>
 <li class="page-item">
 <?php $queryParams['page'] = $totalPages; ?>
 <a class="page-link" href="?<?= http_build_query($queryParams) ?>">الأخيرة »</a>
 </li>
 <?php endif; ?>
 </ul>
 </nav>
 </div>
 <?php endif; ?>
</div>

<!-- ================= MODAL: LOG DETAILS INSPECTOR ================= -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header bg-light border-bottom py-3">
 <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
 <span id="modalSeverityBadge" class="badge"></span>
 <span>تفاصيل العملية (<span id="modalActionName"></span>)</span>
 </h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body p-4">
 
 <!-- Metadata Info Grid -->
 <div class="row g-3 mb-4 p-3 bg-light rounded-3 border">
 <div class="col-sm-6 col-md-4">
 <small class="text-muted d-block">المستخدم المنفذ:</small>
 <strong id="modalUsername" class="text-dark"></strong>
 </div>
 <div class="col-sm-6 col-md-4">
 <small class="text-muted d-block">الكيان / المعرف:</small>
 <span id="modalEntity" class="badge bg-secondary"></span>
 </div>
 <div class="col-sm-6 col-md-4">
 <small class="text-muted d-block">التاريخ والتوقيت:</small>
 <strong id="modalCreatedAt" class="text-dark small"></strong>
 </div>
 <div class="col-sm-6 col-md-4">
 <small class="text-muted d-block">عنوان IP:</small>
 <span id="modalIp" class="badge bg-light text-muted border font-monospace"></span>
 <span id="modalIpCountry"></span>
 </div>
 <div class="col-md-8">
 <small class="text-muted d-block">المتصفح والنظام (User Agent):</small>
 <small id="modalUserAgent" class="text-secondary font-monospace text-truncate d-block" style="font-size:0.75rem"></small>
 </div>
 </div>

 <!-- Structured Changes Comparison (Old vs New) -->
 <div class="row g-3">
 <!-- New Values (After) -->
 <div class="col-md-6">
 <h6 class="fw-bold text-success mb-2"><i class="bi bi-plus-circle me-1"></i>البيانات بعد العملية (New Values):</h6>
 <pre id="modalNewValues" class="p-3 bg-white rounded-3 border font-monospace small" style="max-height:280px;overflow-y:auto;line-height:1.5;background:#f8fafc!important"></pre>
 </div>

 <!-- Old Values (Before) -->
 <div class="col-md-6">
 <h6 class="fw-bold text-muted mb-2"><i class="bi bi-dash-circle me-1"></i>البيانات قبل التعديل (Old Values):</h6>
 <pre id="modalOldValues" class="p-3 bg-white rounded-3 border font-monospace small text-muted" style="max-height:280px;overflow-y:auto;line-height:1.5;background:#f8fafc!important"></pre>
 </div>
 </div>

 </div>
 <div class="modal-footer bg-light border-top">
 <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إغلاق</button>
 </div>
 </div>
 </div>
</div>

<!-- ================= <i class="bi bi-trash"></i> MODAL: CLEANUP OLD LOGS ================= -->
<div class="modal fade" id="cleanupLogsModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header bg-danger text-white py-3">
 <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>أرشفة وتنظيف السجلات القديمة</h5>
 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
 </div>
 <form method="post" action="<?= admin_e(app_url('admin/activity-log/cleanup')) ?>">
 <?= CSRF::field() ?>
 <div class="modal-body p-4">
 <p class="mb-3">هل أنت متأكد من رغبتك في حذف وأرشفة كافة سجلات النشاط التي مضى عليها أكثر من <strong>90 يوماً</strong>؟</p>
 <div class="alert alert-warning border-0 rounded-3 small mb-0">
 <i class="bi bi-info-circle-fill me-1"></i> هذه العملية لا يمكن التراجع عنها وتساعد في الحفاظ على الأداء وسرعة الاستعلامات في قاعدة البيانات.
 </div>
 </div>
 <div class="modal-footer bg-light border-top">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" class="btn btn-danger fw-bold px-4">نعم، نفذ التنظيف الآن</button>
 </div>
 </form>
 </div>
 </div>
</div>

<script>
function openLogDetailsModal(log) {
 document.getElementById('modalActionName').textContent = log.action;
 document.getElementById('modalUsername').textContent = log.username;
 document.getElementById('modalEntity').textContent = log.entity + (log.entity_id ? ' #' + log.entity_id : '');
 document.getElementById('modalCreatedAt').textContent = log.created_at;
 document.getElementById('modalIp').textContent = log.ip || '127.0.0.1';
 const modalIpCountry = document.getElementById('modalIpCountry');
 if (log.country_name) {
 modalIpCountry.innerHTML = '<span class="badge bg-info-subtle text-info border ms-1">' + log.country_flag + ' ' + log.country_name + '</span>';
 } else {
 modalIpCountry.innerHTML = '';
 }
 document.getElementById('modalUserAgent').textContent = log.user_agent || 'غير معروف';

 const severityBadge = document.getElementById('modalSeverityBadge');
 if (log.severity === 'critical') {
 severityBadge.className = 'badge bg-danger text-white';
 severityBadge.textContent = 'حرج / أمان';
 } else if (log.severity === 'warning') {
 severityBadge.className = 'badge bg-warning text-dark';
 severityBadge.textContent = 'متوسط / تحذير';
 } else if (log.severity === 'success') {
 severityBadge.className = 'badge bg-success text-white';
 severityBadge.textContent = 'عادي / ناجح';
 } else {
 severityBadge.className = 'badge bg-info text-white';
 severityBadge.textContent = 'معلومات';
 }

 // Format JSON
 const newValuesPre = document.getElementById('modalNewValues');
 if (log.new_values) {
 newValuesPre.textContent = typeof log.new_values === 'object' ? JSON.stringify(log.new_values, null, 2) : log.new_values;
 } else {
 newValuesPre.textContent = '(لا توجد مدخلات جديدة)';
 }

 const oldValuesPre = document.getElementById('modalOldValues');
 if (log.old_values) {
 oldValuesPre.textContent = typeof log.old_values === 'object' ? JSON.stringify(log.old_values, null, 2) : log.old_values;
 } else {
 oldValuesPre.textContent = '(لا توجد بيانات سابقة)';
 }

 const modal = new bootstrap.Modal(document.getElementById('logDetailsModal'));
 modal.show();
}
</script>
