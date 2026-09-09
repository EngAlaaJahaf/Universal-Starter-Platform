<?php
$title = 'سجلات وأخطاء الترجمة الذكية';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-translate text-info me-2"></i>سجلات وأخطاء الترجمة والذكاء الاصطناعي</h2>
 <p class="text-muted mb-0">مراقبة استجابات المزودين (Omniroute, Gemini, Groq, DeepSeek, OpenAI) واختبار جودة وسرعة الترجمة الفورية.</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= admin_e(app_url('admin/settings?group=ai_translation')) ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-gear-fill me-1"></i> إعدادات الذكاء الاصطناعي والمزودين
 </a>
 <form method="post" action="<?= admin_e(app_url('admin/translation-logs/clear')) ?>" onsubmit="return confirm('هل أنت متأكد من رغبتك في تنظيف السجلات؟');" class="d-inline-flex gap-2">
 <?= CSRF::getField() ?>
 <select name="filter" class="form-select form-select-sm" style="width: 170px;">
 <option value="failed">مسح الأخطاء فقط</option>
 <option value="success">مسح الناجحة فقط</option>
 <option value="fallback">مسح الاحتياطية فقط</option>
 <option value="all">مسح كافة السجلات</option>
 </select>
 <button type="submit" class="btn btn-sm btn-outline-danger">
 <i class="bi bi-trash3 me-1"></i> تنظيف السجلات
 </button>
 </form>
 </div>
</div>

<!-- Live AI Providers Testing Control Center -->
<div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
 <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
 <div>
 <h5 class="fw-bold mb-1 text-dark">
 <i class="bi bi-cpu-fill text-primary me-2"></i>مركز الفحص والاختبار المباشر لمزودي الترجمة
 </h5>
 <small class="text-muted">اضغط على زر أي مزود لإرسال نص تقني تجريبي واختبار سرعة الاستجابة وجودة الصياغة العربية فوراً.</small>
 </div>
 <a href="<?= admin_e(app_url('admin/settings?group=ai_translation')) ?>" class="btn btn-sm btn-outline-primary fw-bold">
 تعديل المفاتيح والروابط
 </a>
 </div>

 <div class="d-flex gap-2 flex-wrap mb-3">
 <button type="button" class="btn btn-dark fw-bold btn-test-provider" data-provider="omniroute" style="background:#0f172a;border-color:#00d2ff">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 نفق Omniroute المباشر
 </button>
 <button type="button" class="btn btn-outline-primary fw-bold btn-test-provider" data-provider="gemini">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 Google Gemini
 </button>
 <button type="button" class="btn btn-outline-success fw-bold btn-test-provider" data-provider="groq">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 Groq Cloud (Llama 3.3)
 </button>
 <button type="button" class="btn btn-outline-info fw-bold btn-test-provider" data-provider="deepseek">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 DeepSeek V3
 </button>
 <button type="button" class="btn btn-outline-secondary fw-bold btn-test-provider" data-provider="openai">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 OpenAI (GPT-4o)
 </button>
 <button type="button" class="btn btn-outline-dark fw-bold btn-test-provider" data-provider="custom_api">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 Custom API
 </button>
 <button type="button" class="btn btn-outline-warning text-dark fw-bold btn-test-provider" data-provider="mymemory">
 <span class="provider-spinner spinner-border spinner-border-sm me-1 d-none"></span>
 المترجم المجاني
 </button>
 </div>

 <!-- Live Test Result Output Box -->
 <div id="testResultBox" class="p-3 rounded-4 bg-light border d-none">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <div id="testResultHeader" class="d-flex align-items-center gap-2">
 <!-- Dynamically populated -->
 </div>
 <button type="button" class="btn-close btn-sm" onclick="document.getElementById('testResultBox').classList.add('d-none')"></button>
 </div>
 <div id="testResultBody" class="small">
 <!-- Dynamically populated -->
 </div>
 </div>
</div>

<!-- Stats Counter Cards -->
<div class="row g-3 mb-4">
 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small d-block mb-1">إجمالي العمليات</span>
 <h3 class="fw-bold mb-0 text-dark"><?= number_format($total) ?></h3>
 </div>
 <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4">
 <i class="bi bi-activity fs-4"></i>
 </div>
 </div>
 </div>
 </div>
 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small d-block mb-1">العمليات الناجحة</span>
 <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['success'] ?? 0) ?></h3>
 </div>
 <div class="p-3 bg-success bg-opacity-10 text-success rounded-4">
 <i class="bi bi-check-circle fs-4"></i>
 </div>
 </div>
 </div>
 </div>
 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small d-block mb-1">فشل وأخطاء المزودين</span>
 <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['failed'] ?? 0) ?></h3>
 </div>
 <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-4">
 <i class="bi bi-exclamation-triangle fs-4"></i>
 </div>
 </div>
 </div>
 </div>
 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small d-block mb-1">تحويل للاحتياطي (Fallback)</span>
 <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['fallback'] ?? 0) ?></h3>
 </div>
 <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4">
 <i class="bi bi-arrow-repeat fs-4"></i>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-body p-3">
 <form method="get" action="<?= admin_e(app_url('admin/translation-logs')) ?>" class="row g-2 align-items-center">
 <div class="col-md-4">
 <select class="form-select" name="provider">
 <option value="">جميع المزودين (All Providers)</option>
 <option value="openai" <?= ($provider ?? '') === 'openai' ? 'selected' : '' ?>>OpenAI (GPT-4o)</option>
 <option value="gemini" <?= ($provider ?? '') === 'gemini' ? 'selected' : '' ?>>Google Gemini</option>
 <option value="mymemory" <?= ($provider ?? '') === 'mymemory' ? 'selected' : '' ?>>MyMemory API</option>
 <option value="gtx" <?= ($provider ?? '') === 'gtx' ? 'selected' : '' ?>>Google Translate (GTx)</option>
 </select>
 </div>
 <div class="col-md-4">
 <select class="form-select" name="status">
 <option value="">جميع الحالات (All Statuses)</option>
 <option value="failed" <?= ($status ?? '') === 'failed' ? 'selected' : '' ?>>فشل (Failed / Error)</option>
 <option value="success" <?= ($status ?? '') === 'success' ? 'selected' : '' ?>>ناجح (Success)</option>
 <option value="fallback" <?= ($status ?? '') === 'fallback' ? 'selected' : '' ?>>تحويل احتياطي (Fallback)</option>
 </select>
 </div>
 <div class="col-md-2">
 <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> تصفية</button>
 </div>
 <?php if (!empty($provider) || !empty($status)): ?>
 <div class="col-md-2">
 <a href="<?= admin_e(app_url('admin/translation-logs')) ?>" class="btn btn-outline-secondary w-100">إلغاء الفلتر</a>
 </div>
 <?php endif; ?>
 </form>
 </div>
</div>

<!-- Logs Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:60px">#</th>
 <th>المزود (Provider)</th>
 <th>الحالة ورمز HTTP</th>
 <th>العنوان / الطلب</th>
 <th>رسالة الخطأ / التفاصيل</th>
 <th>المدة</th>
 <th>التاريخ والوقت</th>
 <th class="text-end" style="width:120px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($logs)): ?>
 <tr>
 <td colspan="8" class="text-center py-5 text-muted">
 <i class="bi bi-journal-check fs-1 d-block mb-2 text-secondary"></i>
 لا توجد سجلات ترجمة مسجلة حالياً تطابق الشروط المحددة.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($logs as $log): ?>
 <tr>
 <td class="text-muted small">#<?= (int)$log['id'] ?></td>
 <td>
 <?php
 $provBadge = match($log['provider']) {
 'openai' => '<span class="badge bg-dark border text-light"><i class="bi bi-cpu me-1"></i>OpenAI</span>',
 'gemini' => '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle"><i class="bi bi-stars me-1"></i>Gemini</span>',
 'mymemory' => '<span class="badge bg-info bg-opacity-10 text-info border border-info-subtle"><i class="bi bi-globe me-1"></i>MyMemory</span>',
 'gtx' => '<span class="badge bg-secondary bg-opacity-10 text-secondary border"><i class="bi bi-google me-1"></i>Google GTx</span>',
 default => '<span class="badge bg-light text-dark border">' . admin_e($log['provider']) . '</span>',
 };
 echo $provBadge;
 ?>
 </td>
 <td>
 <?php if ($log['status'] === 'success'): ?>
 <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">
 <i class="bi bi-check-circle-fill me-1"></i> ناجح <?= $log['http_code'] ? '(' . (int)$log['http_code'] . ')' : '' ?>
 </span>
 <?php elseif ($log['status'] === 'fallback'): ?>
 <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1">
 <i class="bi bi-arrow-repeat me-1"></i> تحويل احتياطي
 </span>
 <?php else: ?>
 <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-2 py-1">
 <i class="bi bi-x-circle-fill me-1"></i> فشل <?= $log['http_code'] ? '(' . (int)$log['http_code'] . ')' : '' ?>
 </span>
 <?php endif; ?>
 </td>
 <td>
 <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;" title="<?= admin_e($log['article_title_en']) ?>">
 <?= admin_e($log['article_title_en'] ?: '(بدون عنوان)') ?>
 </div>
 </td>
 <td>
 <?php if (!empty($log['error_summary'])): ?>
 <div class="text-danger small font-monospace text-truncate" style="max-width: 280px;" title="<?= admin_e($log['error_summary']) ?>">
 <i class="bi bi-bug-fill me-1"></i> <?= admin_e($log['error_summary']) ?>
 </div>
 <?php elseif (!empty($log['response_preview'])): ?>
 <div class="text-muted small text-truncate" style="max-width: 280px;" title="<?= admin_e($log['response_preview']) ?>">
 <?= admin_e($log['response_preview']) ?>
 </div>
 <?php else: ?>
 <span class="text-muted small">-</span>
 <?php endif; ?>
 </td>
 <td class="text-muted small">
 <?= $log['duration_ms'] ? number_format($log['duration_ms']) . ' ms' : '-' ?>
 </td>
 <td class="text-muted small" dir="ltr">
 <?= admin_e($log['created_at']) ?>
 </td>
 <td class="text-end">
 <div class="d-inline-flex gap-1">
 <a href="<?= admin_e(app_url('admin/translation-logs/show?id=' . $log['id'])) ?>" class="btn-action-icon btn-action-view" title="عرض التفاصيل والأخطاء الكاملة">
 <i class="bi bi-eye"></i>
 </a>
 <form method="post" action="<?= admin_e(app_url('admin/translation-logs/delete')) ?>" class="d-inline" onsubmit="return confirm('حذف هذا السجل؟');">
 <?= CSRF::getField() ?>
 <input type="hidden" name="id" value="<?= (int)$log['id'] ?>">
 <button type="submit" class="btn-action-icon btn-action-delete" title="حذف السجل">
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

 <!-- Pagination -->
 <?php if ($pages> 1): ?>
 <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
 <span class="small text-muted">صفحة <?= $page ?> من <?= $pages ?></span>
 <ul class="pagination pagination-sm mb-0 gap-1">
 <?php for ($p = 1; $p <= $pages; $p++): ?>
 <li class="page-item <?= $p === $page ? 'active' : '' ?>">
 <a class="page-link rounded-2" href="<?= admin_e(app_url('admin/translation-logs?page=' . $p . ($provider ? '&provider=' . urlencode($provider) : '') . ($status ? '&status=' . urlencode($status) : ''))) ?>">
 <?= $p ?>
 </a>
 </li>
 <?php endfor; ?>
 </ul>
 </div>
 <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
 const testBtns = document.querySelectorAll('.btn-test-provider');
 const resultBox = document.getElementById('testResultBox');
 const resultHeader = document.getElementById('testResultHeader');
 const resultBody = document.getElementById('testResultBody');

 testBtns.forEach(btn => {
 btn.addEventListener('click', async () => {
 const provider = btn.getAttribute('data-provider');
 const spinner = btn.querySelector('.provider-spinner');

 // Disable buttons and show loading
 testBtns.forEach(b => b.disabled = true);
 if (spinner) spinner.classList.remove('d-none');

 resultBox.classList.remove('d-none');
 resultHeader.innerHTML = `<span class="badge bg-secondary">جاري الفحص...</span> <span class="fw-bold">جاري إرسال طلب تجريبي إلى (${provider})...</span>`;
 resultBody.innerHTML = `<div class="text-muted p-2"><div class="spinner-border spinner-border-sm me-2"></div>جاري إرسال النص التقني التجريبي واستلام الرد المترجم...</div>`;

 try {
 const response = await fetch('<?= admin_e(app_url('admin/ai/test-provider')) ?>', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/x-www-form-urlencoded',
 },
 body: new URLSearchParams({
 provider: provider,
 _csrf: '<?= CSRF::getToken() ?>'
 })
 });

 const data = await response.json();

 if (data.ok) {
 resultHeader.innerHTML = `
 <span class="badge bg-success">اتصال ناجح (200 OK)</span>
 <span class="fw-bold text-dark">المزود: ${provider.toUpperCase()}</span>
 <span class="badge bg-light text-dark border font-monospace">${data.duration_ms} ms</span>
 `;
 const resData = data.data || {};
 resultBody.innerHTML = `
 <div class="p-3 bg-white rounded-3 border">
 <div class="fw-bold text-primary mb-1">العنوان المترجم:</div>
 <div class="mb-2 fs-6 fw-bold text-dark">${escapeHtml(resData.title_ar || 'بدون عنوان')}</div>
 ${resData.excerpt ? `<div class="text-muted mb-2"><strong>المقدمة:</strong> ${escapeHtml(resData.excerpt)}</div>` : ''}
 ${resData.mode ? `<div class="badge bg-info-subtle text-info font-monospace">${escapeHtml(resData.mode)}</div>` : ''}
 </div>
 `;
 } else {
 resultHeader.innerHTML = `
 <span class="badge bg-danger">فشل الاتصال</span>
 <span class="fw-bold text-dark">المزود: ${provider.toUpperCase()}</span>
 <span class="badge bg-light text-dark border font-monospace">${data.duration_ms || '--'} ms</span>
 `;
 resultBody.innerHTML = `
 <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3 border border-danger-subtle">
 <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon me-1"></i>تفاصيل الخطأ:</div>
 <div class="font-monospace small">${escapeHtml(data.error || 'حدث خطأ غير معروف أثناء الاتصال بالمزود.')}</div>
 </div>
 `;
 }
 } catch (err) {
 resultHeader.innerHTML = `<span class="badge bg-danger">خطأ شبكة</span> <span class="fw-bold">فشل إرسال الطلب</span>`;
 resultBody.innerHTML = `<div class="p-2 text-danger">تعذر إتمام طلب الفحص: ${escapeHtml(err.message)}</div>`;
 } finally {
 testBtns.forEach(b => b.disabled = false);
 if (spinner) spinner.classList.add('d-none');
 }
 });
 });

 function escapeHtml(str) {
 return (str || '').toString().replace(/[&<>"']/g, m => ({
 '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
 }[m]));
 }
});
</script>

