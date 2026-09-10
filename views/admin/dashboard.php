<?php
$title = 'الرئيسية | لوحة التحكم الشاملة';
$currentUser = Auth::user();
?>

<!-- Header & Welcome -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
 <div>
 <h2 class="h3 fw-bold mb-1">مرحباً بك، <?= htmlspecialchars($currentUser['username'] ?? 'المدير') ?> </h2>
 <p class="text-muted mb-0">مركز القيادة والتحكم الشامل لعصب التقنية.</p>
 </div>
 <div class="d-flex gap-2">
 <a href="<?= app_url('admin/articles/create') ?>" class="btn btn-primary fw-bold shadow-sm">+ كتابة مقال جديد</a>
 <a href="<?= app_url('admin/live-blog/create') ?>" class="btn btn-danger fw-bold shadow-sm">بدء بث حي</a>
 <a href="<?= app_url('admin/settings') ?>" class="btn btn-dark fw-bold shadow-sm">إعدادات الموقع</a>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
 <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- KPI Overview Cards -->
<div class="row g-3 mb-4">
 <div class="col-sm-6 col-xl-2">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-primary border-4">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="text-muted fw-semibold small">المقالات</span>
 <span class="badge bg-primary-subtle text-primary">إجمالي</span>
 </div>
 <h3 class="fw-bold mb-0 text-primary"><?= number_format($stats['articles']) ?></h3>
 <small class="text-muted" style="font-size:0.75rem"><?= number_format($stats['published']) ?> منشور</small>
 </div>
 </div>

 <div class="col-sm-6 col-xl-2">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-info border-4">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="text-muted fw-semibold small">المشاهدات</span>
 <span class="badge bg-info-subtle text-info">تراكمي</span>
 </div>
 <h3 class="fw-bold mb-0 text-info"><?= number_format($stats['views']) ?></h3>
 <small class="text-muted" style="font-size:0.75rem">قراءات وتفاعل</small>
 </div>
 </div>

 <div class="col-sm-6 col-xl-2">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-success border-4">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="text-muted fw-semibold small">المستخدمون</span>
 <span class="badge bg-success-subtle text-success">أعضاء</span>
 </div>
 <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['users']) ?></h3>
 <small class="text-muted" style="font-size:0.75rem">حسابات مسجلة</small>
 </div>
 </div>

 <div class="col-sm-6 col-xl-2">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-warning border-4">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="text-muted fw-semibold small">تعليقات معلقة</span>
 <span class="badge bg-warning-subtle text-warning">مراجعة</span>
 </div>
 <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['pending_comments']) ?></h3>
 <small class="text-muted" style="font-size:0.75rem"><a href="<?= app_url('admin/comments?status=pending') ?>" class="text-decoration-none">فحص ومراجعة ←</a></small>
 </div>
 </div>

 <div class="col-sm-6 col-xl-2">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-danger border-4">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="text-muted fw-semibold small">البث الحي</span>
 <span class="badge bg-danger-subtle text-danger">مباشر</span>
 </div>
 <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['active_live_blogs']) ?></h3>
 <small class="text-muted" style="font-size:0.75rem">تغطيات نشطة</small>
 </div>
 </div>

 <div class="col-sm-6 col-xl-2">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-dark border-4">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="text-muted fw-semibold small">النشرة البريدية</span>
 <span class="badge bg-dark-subtle text-dark">مشتركون</span>
 </div>
 <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['subscribers']) ?></h3>
 <small class="text-muted" style="font-size:0.75rem">مشترك نشط</small>
 </div>
 </div>
</div>

<!-- Quick Control Navigation Hub -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
 <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
 <span class="fw-bold text-dark">وصول سريع لكافة أدوات التحكم:</span>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/settings') ?>" class="btn btn-sm btn-outline-dark">الإعدادات (12 مجموعة)</a>
 <a href="<?= app_url('admin/articles') ?>" class="btn btn-sm btn-outline-primary">المقالات</a>
 <a href="<?= app_url('admin/categories') ?>" class="btn btn-sm btn-outline-info">التصنيفات</a>
 <a href="<?= app_url('admin/users') ?>" class="btn btn-sm btn-outline-success">المستخدمون</a>
 <a href="<?= app_url('admin/comments') ?>" class="btn btn-sm btn-outline-warning">التعليقات</a>
 <a href="<?= app_url('admin/live-blog') ?>" class="btn btn-sm btn-outline-danger">التغطيات الحية</a>
 <a href="<?= app_url('admin/media') ?>" class="btn btn-sm btn-outline-secondary"> الوسائط</a>
 <a href="<?= app_url('admin/newsletter') ?>" class="btn btn-sm btn-outline-dark">النشرات البريدية</a>
 <a href="<?= app_url('admin/activity-log') ?>" class="btn btn-sm btn-outline-secondary">سجل الأمان</a>
 <a href="<?= app_url('admin/diagnostics') ?>" class="btn btn-sm btn-info text-dark fw-bold" style="background:#00d2ff;border-color:#00d2ff"> مركز التشخيص </a>
 </div>
 </div>
</div>

<!-- Main Management Sections Grid -->
<div class="row g-4 mb-4">
 
 <!-- Left Column: Recent Articles -->
 <div class="col-lg-7">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h5 class="card-title mb-0 fw-bold">أحدث المقالات والتحليلات</h5>
 <a href="<?= app_url('admin/articles') ?>" class="btn btn-sm btn-outline-primary">عرض الكل ↗</a>
 </div>
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th>المقال</th>
 <th>التصنيف</th>
 <th>المشاهدات</th>
 <th class="text-end">إجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($recentArticles)): ?>
 <tr><td colspan="4" class="text-center py-4 text-muted">لا توجد مقالات بعد.</td></tr>
 <?php else: ?>
 <?php foreach ($recentArticles as $art): ?>
 <tr>
 <td>
 <strong class="d-block text-truncate" style="max-width:280px"><?= htmlspecialchars($art['title']) ?></strong>
 <small class="text-muted"><?= htmlspecialchars(fmt_date($art['created_at'], 'Y-m-d')) ?></small>
 </td>
 <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($art['category_name'] ?: 'عام') ?></span></td>
 <td><span class="fw-semibold"><?= number_format((int) $art['views_count']) ?></span></td>
 <td class="text-end">
 <a href="<?= app_url('admin/articles/' . $art['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">تعديل</a>
 <a href="<?= app_url('article/' . $art['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-dark">معاينة</a>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <!-- Right Column: Pending Comments -->
 <div class="col-lg-5">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h5 class="card-title mb-0 fw-bold">تعليقات بانتظار الاعتماد</h5>
 <a href="<?= app_url('admin/comments?status=pending') ?>" class="btn btn-sm btn-outline-warning">إدارة التعليقات</a>
 </div>
 <div class="card-body p-3">
 <?php if (empty($recentComments)): ?>
 <div class="text-center py-4 text-muted">
 <span class="d-block mb-1" style="font-size:1.5rem"></span>
 جميع التعليقات معتمدة ولا توجد تعليقات معلقة!
 </div>
 <?php else: ?>
 <div class="d-flex flex-column gap-3">
 <?php foreach ($recentComments as $c): ?>
 <div class="p-3 bg-light rounded-3 border">
 <div class="d-flex justify-content-between align-items-center mb-1">
 <strong class="text-primary small"><?= htmlspecialchars($c['username'] ?: $c['guest_name'] ?: 'زائر') ?></strong>
 <small class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars(fmt_date($c['created_at'], 'm-d H:i')) ?></small>
 </div>
 <p class="small text-dark mb-2 text-truncate" style="max-width:320px"><?= htmlspecialchars($c['content']) ?></p>
 <div class="d-flex gap-1 justify-content-end">
 <form action="<?= app_url('admin/comments/approve/' . $c['id']) ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button class="btn btn-sm btn-success py-0 px-2" style="font-size:0.78rem">اعتماد </button>
 </form>
 <form action="<?= app_url('admin/comments/reject/' . $c['id']) ?>" method="post" class="d-inline">
 <?= CSRF::field() ?>
 <button class="btn btn-sm btn-danger py-0 px-2" style="font-size:0.78rem">رفض </button>
 </form>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>

</div>

<!-- Bottom Row: Users & Activity Logs -->
<div class="row g-4">
 
 <!-- Users -->
 <div class="col-lg-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h5 class="card-title mb-0 fw-bold">أحدث الأعضاء المسجلين</h5>
 <a href="<?= app_url('admin/users') ?>" class="btn btn-sm btn-outline-success">إدارة المستخدمين ↗</a>
 </div>
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th>المستخدم</th>
 <th>البريد</th>
 <th>الدور</th>
 <th>الحالة</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($recentUsers as $u): ?>
 <tr>
 <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
 <td><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small></td>
 <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role_name'] ?: 'قارئ') ?></span></td>
 <td><span class="badge bg-<?= $u['status'] === 'active' ? 'success' : 'danger' ?>"><?= htmlspecialchars($u['status']) ?></span></td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 </div>
 </div>
 </div>

 <!-- Security & Activity Log -->
 <div class="col-lg-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h5 class="card-title mb-0 fw-bold">سجل العمليات والأمان (Audit Log)</h5>
 <a href="<?= app_url('admin/activity-log') ?>" class="btn btn-sm btn-outline-secondary">سجل النشاط ↗</a>
 </div>
 <div class="card-body p-3">
 <?php if (empty($recentLogs)): ?>
 <p class="text-muted text-center py-3">لا توجد سجلات نشاط حديثة.</p>
 <?php else: ?>
 <div class="d-flex flex-column gap-2">
 <?php foreach ($recentLogs as $log): ?>
 <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light border-bottom">
 <div>
 <span class="badge bg-dark-subtle text-dark"><?= htmlspecialchars($log['action']) ?></span>
 <span class="small fw-semibold ms-1"><?= htmlspecialchars($log['description'] ?: ($log['entity_type'] ?? '')) ?></span>
 </div>
 <small class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars(fmt_date($log['created_at'], 'm-d H:i')) ?></small>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>

</div>
