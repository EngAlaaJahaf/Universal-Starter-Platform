<?php
$title = 'محادثة المستخدم مع المرشد';
?>
<?php if ($msg = Session::getFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-1"></i> <?= admin_e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($err = Session::getFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-x-circle me-1"></i> <?= admin_e($err) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
  <a href="<?= admin_e(app_url('admin/ai-logs')) ?>" class="btn btn-sm btn-outline-secondary mb-2"><i class="bi bi-arrow-right me-1"></i> العودة للسجلات</a>
  <h2 class="h3 fw-bold mb-1"><i class="bi bi-chat-dots text-info me-2"></i>محادثة: <?= admin_e($user['username']) ?></h2>
  <p class="text-muted mb-0" dir="ltr"><?= admin_e($user['email']) ?> — <?= count($rows) ?> رسالة</p>
 </div>
</div>

<?php if (empty($rows)): ?>
<div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center">
 <i class="bi bi-chat-square-text fs-1 d-block mb-2 text-secondary"></i>
 <p class="text-muted mb-0">لا توجد محادثات مسجلة لهذا المستخدم بعد.</p>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-body py-4">
  <?php foreach ($rows as $row): ?>
   <!-- User question -->
   <div class="d-flex justify-content-end mb-2">
    <div class="rounded-4 px-3 py-2" style="max-width:75%; background:#eef2ff; border-end-end-radius:4px;">
     <div class="small text-muted mb-1"><i class="bi bi-person me-1"></i><?= admin_e($user['username']) ?> <span class="text-secondary" dir="ltr"><?= admin_e(fmt_date($row['created_at'], 'Y-m-d H:i:s')) ?></span></div>
     <div class="text-dark"><?= nl2br(admin_e($row['question'])) ?></div>
    </div>
   </div>
   <!-- Assistant answer -->
   <div class="d-flex justify-content-start mb-4">
    <div class="rounded-4 px-3 py-2" style="max-width:80%; background:#f8f9fa; border-start-start-radius:4px; width:100%;">
     <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
      <span class="badge bg-info-subtle text-info"><i class="bi bi-robot me-1"></i><?= admin_e($row['provider'] ?: 'أساسي') ?></span>
      <?php if ($row['status'] === 'error'): ?>
       <span class="badge bg-danger bg-opacity-10 text-danger border"><i class="bi bi-x-circle me-1"></i> فشل / خطأ مزوّد</span>
      <?php else: ?>
       <span class="badge bg-success bg-opacity-10 text-success border"><i class="bi bi-check-circle me-1"></i> نجاح</span>
      <?php endif; ?>
      <?php if (!empty($row['page_slug'])): ?>
       <span class="badge bg-light text-dark border"><i class="bi bi-file-text me-1"></i> الصفحة: <?= admin_e($row['page_slug']) ?></span>
      <?php endif; ?>
     </div>
     <?php if ($row['status'] === 'error'): ?>
      <div class="p-2 bg-danger bg-opacity-10 rounded-3 text-danger font-monospace small"><?= nl2br(admin_e($row['error'] ?: 'تعذر الحصول على إجابة.')) ?></div>
     <?php else: ?>
      <div class="text-dark text-start" dir="auto" style="white-space:pre-wrap;"><?= admin_e($row['answer']) ?></div>
      <?php if (!empty($row['sources_list'])): ?>
       <div class="mt-2 small">
        <span class="text-muted">المصادر:</span>
        <?php foreach ($row['sources_list'] as $si => $s): ?>
         <a href="<?= admin_e(is_array($s) && isset($s['url']) ? $s['url'] : '#') ?>" target="_blank" rel="noopener noreferrer" class="me-2 text-decoration-underline"><?= ($si + 1) ?>. <?= admin_e(is_array($s) ? ($s['title'] ?? $s['url']) : $s) ?></a>
        <?php endforeach; ?>
       </div>
      <?php endif; ?>
     <?php endif; ?>
    </div>
   </div>
  <?php endforeach; ?>
 </div>
 <div class="card-footer bg-white border-0 py-3 small text-muted">
  الرسالة الأولى أعلى الصفحة والأحدث في الأسفل — تسلسل زمني كامل للسؤال/الجواب لكل مستخدم.
 </div>
</div>
<?php endif; ?>