<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <a href="<?= app_url('admin/messages') ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
 <i class="bi bi-arrow-right me-1"></i> العودة لصندوق الرسائل
 </a>
 <h2 class="h3 fw-bold mb-0"><i class="bi bi-envelope-open text-primary me-2"></i>تفاصيل الرسالة الواردة</h2>
 </div>
 <div class="d-flex gap-2">
 <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=<?= rawurlencode('رد: ' . $message['subject']) ?>" class="btn btn-primary shadow-sm" target="_blank">
 <i class="bi bi-reply-fill me-1"></i> الرد عبر البريد الإلكتروني
 </a>
 <?php if ($message['status'] !== 'replied'): ?>
 <form action="<?= app_url('admin/messages/' . $message['id'] . '/mark-replied') ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-outline-success">
 <i class="bi bi-check2-all me-1"></i> تعيين كـ تم الرد
 </button>
 </form>
 <?php endif; ?>
 <form action="<?= app_url('admin/messages/' . $message['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-outline-danger">
 <i class="bi bi-trash3 me-1"></i> حذف الرسالة
 </button>
 </form>
 </div>
</div>

<div class="row g-4">
 <!-- Message Content Card -->
 <div class="col-lg-8">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
 <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
 <h4 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($message['subject']) ?></h4>
 <?php if ($message['status'] === 'unread'): ?>
 <span class="badge bg-danger-subtle text-danger border border-danger-subtle">غير مقروء</span>
 <?php elseif ($message['status'] === 'read'): ?>
 <span class="badge bg-info-subtle text-info border border-info-subtle">تم الاطلاع</span>
 <?php elseif ($message['status'] === 'replied'): ?>
 <span class="badge bg-success-subtle text-success border border-success-subtle">تم الرد عليها</span>
 <?php endif; ?>
 </div>

 <div class="mb-4 text-dark" style="font-size:1.05rem;line-height:1.8;white-space:pre-wrap;background:#f8fafc;padding:24px;border-radius:16px;border:1px solid #e2e8f0"><?= htmlspecialchars($message['message']) ?></div>

 <div class="d-flex justify-content-between align-items-center text-muted small pt-2">
 <span>تاريخ وتوقيت الاستلام: <?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($message['created_at']))) ?></span>
 <?php if (!empty($message['replied_at'])): ?>
 <span class="text-success">تاريخ الرد: <?= htmlspecialchars(date('Y-m-d H:i', strtotime($message['replied_at']))) ?></span>
 <?php endif; ?>
 </div>
 </div>
 </div>

 <!-- Sender Details Card -->
 <div class="col-lg-4">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
 <h5 class="fw-bold mb-3"><i class="bi bi-person-badge text-primary me-2"></i>بيانات المرسل</h5>
 <div class="d-flex align-items-center gap-3 mb-3">
 <div style="width:48px;height:48px;border-radius:50%;background:#0284c7;color:#fff;display:grid;place-items:center;font-size:1.2rem;font-weight:bold">
 <?= mb_substr($message['name'], 0, 1) ?>
 </div>
 <div>
 <h6 class="mb-0 fw-bold"><?= htmlspecialchars($message['name']) ?></h6>
 <a href="mailto:<?= htmlspecialchars($message['email']) ?>" class="text-primary text-decoration-none small">
 <?= htmlspecialchars($message['email']) ?>
 </a>
 </div>
 </div>

 <div class="mt-4 pt-3 border-top">
 <h6 class="fw-bold mb-2">إجراء سريع:</h6>
 <a href="mailto:<?= htmlspecialchars($message['email']) ?>?subject=<?= rawurlencode('رد: ' . $message['subject']) ?>" class="btn btn-light border w-100 mb-2 text-start">
 <i class="bi bi-envelope-at me-2 text-primary"></i> فتح عميل البريد
 </a>
 <form action="<?= app_url('admin/messages/' . $message['id'] . '/toggle-read') ?>" method="post">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-light border w-100 text-start">
 <i class="bi bi-arrow-repeat me-2 text-secondary"></i> تغيير حالة القراءة
 </button>
 </form>
 </div>
 </div>
 </div>
</div>
