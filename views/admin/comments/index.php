<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-chat-dots text-primary me-2"></i>إدارة ومراجعة التعليقات</h2>
 <p class="text-muted mb-0">مراجعة تعليقات الزوار والأعضاء واعتمادها أو تصنيفها كـ سبام.</p>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm">
 <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- Status Filter Tabs -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-2">
 <ul class="nav nav-pills flex-wrap gap-2">
 <li class="nav-item">
 <a class="nav-link <?= $status === 'all' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/comments?status=all') ?>">
 كافة التعليقات
 <span class="badge rounded-pill bg-secondary ms-1"><?= $counts['all'] ?? 0 ?></span>
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $status === 'pending' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/comments?status=pending') ?>">
 بانتظار المراجعة
 <?php if (($counts['pending'] ?? 0)> 0): ?>
 <span class="badge rounded-pill bg-warning text-dark ms-1"><?= $counts['pending'] ?></span>
 <?php else: ?>
 <span class="badge rounded-pill bg-light text-muted ms-1">0</span>
 <?php endif; ?>
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $status === 'approved' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/comments?status=approved') ?>">
 المعتمدة
 <span class="badge rounded-pill bg-success ms-1"><?= $counts['approved'] ?? 0 ?></span>
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $status === 'spam' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/comments?status=spam') ?>">
 سبام
 <span class="badge rounded-pill bg-dark ms-1"><?= $counts['spam'] ?? 0 ?></span>
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $status === 'rejected' ? 'active fw-bold' : 'text-dark' ?>" href="<?= app_url('admin/comments?status=rejected') ?>">
 المرفوضة
 <span class="badge rounded-pill bg-danger ms-1"><?= $counts['rejected'] ?? 0 ?></span>
 </a>
 </li>
 </ul>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:220px">المعلق / المقال</th>
 <th>نص التعليق</th>
 <th style="width:110px">الحالة</th>
 <th style="width:130px">التاريخ</th>
 <th class="text-end" style="width:160px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($comments)): ?>
 <tr>
 <td colspan="5" class="text-center py-5 text-muted">
 <i class="bi bi-chat-square-text d-block mb-2" style="font-size:2rem"></i>
 لا توجد تعليقات في هذا القسم حالياً.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($comments as $c): ?>
 <tr>
 <td>
 <strong class="text-dark d-block"><?= htmlspecialchars($c['username'] ?: $c['guest_name'] ?: 'زائر تقني') ?></strong>
 <small class="text-muted text-truncate d-block" style="max-width:200px">
 المقال: <a href="<?= app_url('article/' . $c['article_slug']) ?>" target="_blank" class="text-decoration-none text-primary"><?= htmlspecialchars($c['article_title']) ?></a>
 </small>
 </td>
 <td>
 <p class="mb-0 text-dark" style="font-size:0.92rem;line-height:1.6">
 <?= nl2br(htmlspecialchars($c['content'])) ?>
 </p>
 </td>
 <td>
 <?php if ($c['status'] === 'approved'): ?>
 <span class="badge bg-success-subtle text-success border border-success-subtle">معتمد</span>
 <?php elseif ($c['status'] === 'pending'): ?>
 <span class="badge bg-warning-subtle text-warning border border-warning-subtle">قيد المراجعة</span>
 <?php elseif ($c['status'] === 'spam'): ?>
 <span class="badge bg-dark-subtle text-dark border border-dark-subtle">سبام</span>
 <?php else: ?>
 <span class="badge bg-danger-subtle text-danger border border-danger-subtle">مرفوض</span>
 <?php endif; ?>
 </td>
 <td><small class="text-muted"><?= htmlspecialchars(date('Y-m-d H:i', strtotime($c['created_at']))) ?></small></td>
 <td class="text-end">
 <div class="d-inline-flex gap-1">
 <?php if ($c['status'] !== 'approved'): ?>
 <form action="<?= app_url('admin/comments/approve/' . $c['id']) ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-activate" title="اعتماد ونشر">
 <i class="bi bi-check-lg"></i>
 </button>
 </form>
 <?php endif; ?>

 <?php if ($c['status'] !== 'rejected'): ?>
 <form action="<?= app_url('admin/comments/reject/' . $c['id']) ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-ban" title="رفض التعليق">
 <i class="bi bi-x-lg"></i>
 </button>
 </form>
 <?php endif; ?>

 <?php if ($c['status'] !== 'spam'): ?>
 <form action="<?= app_url('admin/comments/spam/' . $c['id']) ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-view" title="تصنيف كـ سبام">
 <i class="bi bi-shield-slash"></i>
 </button>
 </form>
 <?php endif; ?>

 <form action="<?= app_url('admin/comments/' . $c['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا التعليق نهائياً؟')">
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
