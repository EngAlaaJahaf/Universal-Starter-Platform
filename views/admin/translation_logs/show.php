<?php
$title = 'تفاصيل سجل الترجمة #' . $log['id'];
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <a href="<?= admin_e(app_url('admin/translation-logs')) ?>" class="btn btn-sm btn-outline-secondary mb-2">
 <i class="bi bi-arrow-right me-1"></i> العودة لقائمة السجلات
 </a>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-file-earmark-medical text-primary me-2"></i>تفاصيل سجل الترجمة والخطأ #<?= (int)$log['id'] ?></h2>
 <p class="text-muted mb-0">تحليل الاستجابة البرمجية من المزود ومعاينة رسائل الخطأ بدقة.</p>
 </div>
 <div class="d-flex gap-2">
 <form method="post" action="<?= admin_e(app_url('admin/translation-logs/delete')) ?>" onsubmit="return confirm('حذف هذا السجل نهائياً؟');">
 <?= CSRF::getField() ?>
 <input type="hidden" name="id" value="<?= (int)$log['id'] ?>">
 <button type="submit" class="btn btn-outline-danger">
 <i class="bi bi-trash me-1"></i> حذف السجل
 </button>
 </form>
 </div>
</div>

<div class="row g-4">
 <!-- Meta Summary Card -->
 <div class="col-lg-4">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
 <h5 class="fw-bold mb-3"><i class="bi bi-info-circle text-primary me-2"></i>بيانات الطلب</h5>
 
 <ul class="list-group list-group-flush small">
 <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
 <span class="text-muted">المزود (Provider):</span>
 <span class="fw-bold text-uppercase badge bg-dark"><?= admin_e($log['provider']) ?></span>
 </li>
 <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
 <span class="text-muted">حالة العملية:</span>
 <?php if ($log['status'] === 'success'): ?>
 <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>ناجح</span>
 <?php elseif ($log['status'] === 'fallback'): ?>
 <span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>تحويل للاحتياطي</span>
 <?php else: ?>
 <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>فشل خطأ</span>
 <?php endif; ?>
 </li>
 <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
 <span class="text-muted">رمز استجابة HTTP:</span>
 <span class="fw-bold font-monospace"><?= $log['http_code'] ? (int)$log['http_code'] : 'N/A' ?></span>
 </li>
 <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
 <span class="text-muted">زمن التنفيذ:</span>
 <span class="fw-bold font-monospace"><?= $log['duration_ms'] ? number_format($log['duration_ms']) . ' ms' : 'N/A' ?></span>
 </li>
 <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
 <span class="text-muted">تاريخ التسجيل:</span>
 <span class="font-monospace text-muted" dir="ltr"><?= admin_e($log['created_at']) ?></span>
 </li>
 </ul>

 <div class="mt-4 pt-3 border-top">
 <label class="form-label text-muted small fw-bold">عنوان المقال الأصلي:</label>
 <div class="p-3 bg-light rounded-3 text-dark small" style="word-break: break-word;">
 <?= admin_e($log['article_title_en'] ?: '(لم يُحدد عنوان)') ?>
 </div>
 </div>
 </div>
 </div>

 <!-- Error & Response Inspector -->
 <div class="col-lg-8">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
 <div class="d-flex justify-content-between align-items-center mb-3">
 <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-octagon me-2"></i>ملخص ورسالة الخطأ</h5>
 </div>
 
 <?php if (!empty($log['error_summary'])): ?>
 <div class="alert alert-danger border-2 rounded-3 mb-3">
 <div class="fw-bold mb-1"><i class="bi bi-bug me-1"></i> ملخص الخطأ:</div>
 <div class="font-monospace small"><?= admin_e($log['error_summary']) ?></div>
 </div>
 <?php else: ?>
 <div class="alert alert-success border-2 rounded-3 mb-3">
 <i class="bi bi-check-circle-fill me-1"></i> لم تسجل أية أخطاء في هذه العملية.
 </div>
 <?php endif; ?>

 <?php if (!empty($log['error_raw'])): ?>
 <div class="mt-3">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <label class="form-label text-muted small fw-bold mb-0">استجابة الخطأ الخام (Raw Error / Payload):</label>
 <button type="button" class="btn btn-xs btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('rawErrorBox').innerText); alert('تم نسخ الاستجابة الخام!');">
 <i class="bi bi-clipboard me-1"></i> نسخ
 </button>
 </div>
 <pre id="rawErrorBox" class="p-3 bg-dark text-light rounded-3 font-monospace small" style="max-height: 280px; overflow-y: auto; white-space: pre-wrap; direction: ltr;"><?= admin_e($log['error_raw']) ?></pre>
 </div>
 <?php endif; ?>
 </div>

 <?php if (!empty($log['response_preview'])): ?>
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <h5 class="fw-bold mb-0 text-success"><i class="bi bi-check2-square me-2"></i>معاينة الاستجابة (Response Preview)</h5>
 <button type="button" class="btn btn-xs btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('previewBox').innerText); alert('تم نسخ المعاينة!');">
 <i class="bi bi-clipboard me-1"></i> نسخ
 </button>
 </div>
 <pre id="previewBox" class="p-3 bg-light border rounded-3 font-monospace small" style="max-height: 220px; overflow-y: auto; white-space: pre-wrap; direction: auto;"><?= admin_e($log['response_preview']) ?></pre>
 </div>
 <?php endif; ?>
 </div>
</div>

