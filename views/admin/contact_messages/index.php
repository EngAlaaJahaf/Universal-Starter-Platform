<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-inbox text-primary me-2"></i>صندوق الرسائل والاتصالات الواردة</h2>
 <p class="text-muted mb-0">متابعة رسائل واقتراحات واستفسارات الزوار والأعضاء الواردة عبر صفحة "اتصل بنا".</p>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm">
 <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<?php if ($err = Session::getFlash('error')): ?>
 <div class="alert alert-danger alert-dismissible fade show shadow-sm">
 <i class="bi bi-exclamation-triangle me-2"></i> <?= htmlspecialchars($err) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- Search & Status Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
 <!-- Status Tabs -->
 <ul class="nav nav-pills flex-wrap gap-2 mb-0">
 <li class="nav-item">
 <a class="nav-link <?= $status === 'all' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/messages?status=all' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
 كافة الرسائل
 <span class="badge rounded-pill bg-secondary ms-1"><?= $counts['all'] ?? 0 ?></span>
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $status === 'unread' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/messages?status=unread' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
 غير مقروءة
 <?php if (($counts['unread'] ?? 0)> 0): ?>
 <span class="badge rounded-pill bg-danger ms-1"><?= $counts['unread'] ?></span>
 <?php else: ?>
 <span class="badge rounded-pill bg-light text-muted ms-1">0</span>
 <?php endif; ?>
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $status === 'read' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/messages?status=read' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
 تم الاطلاع
 <span class="badge rounded-pill bg-info text-dark ms-1"><?= $counts['read'] ?? 0 ?></span>
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $status === 'replied' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/messages?status=replied' . (!empty($search) ? '&q=' . urlencode($search) : '')) ?>">
 تم الرد
 <span class="badge rounded-pill bg-success ms-1"><?= $counts['replied'] ?? 0 ?></span>
 </a>
 </li>
 </ul>

 <!-- Quick Search -->
 <form method="get" action="<?= app_url('admin/messages') ?>" class="d-flex gap-2" style="max-width:320px;flex-grow:1">
 <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
 <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" class="form-control form-control-sm" placeholder="بحث بالاسم، الإيميل، أو الموضوع...">
 <button type="submit" class="btn btn-primary btn-sm px-3">بحث</button>
 <?php if (!empty($search)): ?>
 <a href="<?= app_url('admin/messages?status=' . $status) ?>" class="btn btn-outline-secondary btn-sm" title="إلغاء البحث">&times;</a>
 <?php endif; ?>
 </form>
 </div>
</div>

<!-- Messages Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:240px">المرسل والبريد</th>
 <th>الموضوع ومعاينة الرسالة</th>
 <th style="width:120px">الحالة</th>
 <th style="width:140px">التاريخ</th>
 <th class="text-end" style="width:170px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($messages)): ?>
 <tr>
 <td colspan="5" class="text-center py-5 text-muted">
 <i class="bi bi-inbox d-block mb-2" style="font-size:2.5rem;color:var(--border-subtle)"></i>
 لا توجد رسائل واردة في هذا القسم حالياً.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($messages as $m): ?>
 <tr style="<?= $m['status'] === 'unread' ? 'background:rgba(0, 242, 254, 0.03);font-weight:600' : '' ?>">
 <td>
 <div class="d-flex align-items-center gap-2">
 <div style="width:34px;height:34px;border-radius:50%;background:<?= $m['status'] === 'unread' ? '#0284c7' : '#64748b' ?>;color:#fff;display:grid;place-items:center;font-size:0.85rem;font-weight:bold">
 <?= mb_substr($m['name'], 0, 1) ?>
 </div>
 <div>
 <strong class="text-dark d-block"><?= htmlspecialchars($m['name']) ?></strong>
 <a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="text-muted text-decoration-none small">
 <?= htmlspecialchars($m['email']) ?>
 </a>
 </div>
 </div>
 </td>
 <td>
 <a href="<?= app_url('admin/messages/' . $m['id']) ?>" class="text-decoration-none text-dark fw-bold d-block mb-1">
 <?= htmlspecialchars($m['subject']) ?>
 </a>
 <p class="text-muted mb-0 text-truncate" style="max-width:450px;font-size:0.88rem">
 <?= htmlspecialchars($m['message']) ?>
 </p>
 </td>
 <td>
 <?php if ($m['status'] === 'unread'): ?>
 <span class="badge bg-danger-subtle text-danger border border-danger-subtle">غير مقروء</span>
 <?php elseif ($m['status'] === 'read'): ?>
 <span class="badge bg-info-subtle text-info border border-info-subtle">تم الاطلاع</span>
 <?php elseif ($m['status'] === 'replied'): ?>
 <span class="badge bg-success-subtle text-success border border-success-subtle">تم الرد</span>
 <?php endif; ?>
 </td>
 <td>
 <small class="text-muted"><?= htmlspecialchars(fmt_date($m['created_at'], 'Y-m-d H:i')) ?></small>
 </td>
 <td class="text-end">
 <div class="d-inline-flex gap-1">
 <!-- Read Details -->
 <a href="<?= app_url('admin/messages/' . $m['id']) ?>" class="btn-action-icon btn-action-view" title="عرض التفاصيل والرد">
 <i class="bi bi-eye"></i>
 </a>

 <!-- Toggle Read/Unread -->
 <form action="<?= app_url('admin/messages/' . $m['id'] . '/toggle-read') ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-activate" title="<?= $m['status'] === 'unread' ? 'تعيين كمقروء' : 'تعيين كغير مقروء' ?>">
 <i class="bi bi-envelope<?= $m['status'] === 'unread' ? '-open' : '' ?>"></i>
 </button>
 </form>

 <!-- Quick Reply Email -->
 <a href="mailto:<?= htmlspecialchars($m['email']) ?>?subject=<?= rawurlencode('رد: ' . $m['subject']) ?>" class="btn-action-icon btn-action-primary" title="إرسال بريد رد مباشر" target="_blank">
 <i class="bi bi-reply-fill"></i>
 </a>

 <!-- Delete Message -->
 <form action="<?= app_url('admin/messages/' . $m['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة نهائياً؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-delete" title="حذف نهائي">
 <i class="bi bi-trash3"></i>
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
