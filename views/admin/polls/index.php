<div class="admin-content-wrapper">

 <!-- Top Action Ribbon -->
 <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
 <div>
 <h2 class="fw-bold mb-1" style="color:var(--text-main);font-size:1.6rem">
 <i class="bi bi-bar-chart-line-fill text-primary me-2"></i> إدارة استطلاعات الرأي ونبض المجتمع
 </h2>
 <p class="text-muted mb-0" style="font-size:0.9rem">
 تحكم كامل في إنشاء وتعديل ونشر استطلاعات الرأي ومتابعة إحصائيات ونسب التصويت الحية.
 </p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= admin_e(app_url('admin/settings?group=general')) ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
 <i class="bi bi-gear-fill"></i>
 <span>إعدادات الظهور</span>
 </a>
 <a href="<?= admin_e(app_url('admin/polls/create')) ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
 <i class="bi bi-plus-lg"></i>
 <span>إنشاء استطلاع جديد</span>
 </a>
 </div>
 </div>

 <!-- Analytics Metric Cards -->
 <div class="row g-3 mb-4">
 <div class="col-12 col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm p-3 h-100" style="background:var(--bg-surface-elevated);border-radius:14px;border:1px solid var(--border-medium)!important">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">إجمالي الاستطلاعات</span>
 <h3 class="fw-bold my-1" style="color:var(--text-main)"><?= number_format($totalPolls) ?></h3>
 <span class="small text-success">مخزنة بالنظام</span>
 </div>
 <div style="width:48px;height:48px;border-radius:12px;background:rgba(0,242,254,0.12);color:var(--accent-primary);display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-bar-chart-fill"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-12 col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm p-3 h-100" style="background:var(--bg-surface-elevated);border-radius:14px;border:1px solid var(--border-medium)!important">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">الاستطلاعات النشطة</span>
 <h3 class="fw-bold my-1 text-success"><?= number_format($activePolls) ?></h3>
 <span class="small text-muted">متاحة للتصويت</span>
 </div>
 <div style="width:48px;height:48px;border-radius:12px;background:rgba(16,185,129,0.12);color:#10b981;display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-check-circle-fill"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-12 col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm p-3 h-100" style="background:var(--bg-surface-elevated);border-radius:14px;border:1px solid var(--border-medium)!important">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">إجمالي الأصوات المسجلة</span>
 <h3 class="fw-bold my-1" style="color:var(--accent-primary)"><?= number_format($totalVotes) ?></h3>
 <span class="small text-muted">مشاركات حقيقية</span>
 </div>
 <div style="width:48px;height:48px;border-radius:12px;background:rgba(168,85,247,0.12);color:#a855f7;display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-ui-checks"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-12 col-sm-6 col-xl-3">
 <div class="card border-0 shadow-sm p-3 h-100" style="background:var(--bg-surface-elevated);border-radius:14px;border:1px solid var(--border-medium)!important">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">الاستطلاع الرئيسي للواجهة</span>
 <h6 class="fw-bold my-1 text-truncate" style="color:var(--text-main);max-width:160px" title="<?= admin_e($featuredPoll['question'] ?? 'لا يوجد') ?>">
 <?= admin_e($featuredPoll ? mb_substr($featuredPoll['question'], 0, 24) . '...' : 'غير محدد') ?>
 </h6>
 <span class="small text-warning"><i class="bi bi-star-fill text-warning me-1"></i> يظهر في الرئيسية</span>
 </div>
 <div style="width:48px;height:48px;border-radius:12px;background:rgba(250,204,21,0.12);color:#eab308;display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-star-fill"></i>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Polls Cards Grid -->
 <div class="row g-4">
 <?php if (empty($polls)): ?>
 <div class="col-12">
 <div class="card text-center p-5 border-0 shadow-sm" style="background:var(--bg-surface-elevated);border-radius:16px">
 <div style="font-size:3rem;margin-bottom:12px" class="text-primary"><i class="bi bi-bar-chart-line"></i></div>
 <h4 class="fw-bold" style="color:var(--text-main)">لا توجد استطلاعات رأي منشأة حتى الآن</h4>
 <p class="text-muted">ابدأ بإنشاء أول استطلاع رأي تفاعلي لزوار ومتابعي المنصة.</p>
 <div>
 <a href="<?= admin_e(app_url('admin/polls/create')) ?>" class="btn btn-primary px-4 py-2">
 <i class="bi bi-plus-lg me-1"></i> إنشاء استطلاع جديد الآن
 </a>
 </div>
 </div>
 </div>
 <?php else: ?>
 <?php foreach ($polls as $poll): ?>
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm h-100" style="background:var(--bg-surface-elevated);border-radius:16px;border:1px solid <?= !empty($poll['is_featured']) ? 'var(--accent-primary)' : 'var(--border-medium)' ?>!important;transition:all 0.2s ease">
 <div class="card-header bg-transparent d-flex justify-content-between align-items-center pt-3 px-4 border-0">
 <div class="d-flex align-items-center gap-2 flex-wrap">
 <?php if (!empty($poll['is_featured'])): ?>
 <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
 <i class="bi bi-star-fill"></i> <span>الرئيسي النشط</span>
 </span>
 <?php endif; ?>
 
 <?php if ($poll['status'] === 'active'): ?>
 <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i>نشط للتصويت</span>
 <?php elseif ($poll['status'] === 'inactive'): ?>
 <span class="badge bg-secondary"><i class="bi bi-pause-circle me-1"></i>متوقف مؤقتاً</span>
 <?php else: ?>
 <span class="badge bg-dark"><i class="bi bi-archive me-1"></i>مؤرشف</span>
 <?php endif; ?>

 <span class="badge bg-light text-dark border">
 <i class="bi bi-people-fill me-1"></i><?= number_format((int)$poll['total_votes']) ?> صوت
 </span>
 </div>

 <small class="text-muted" style="font-size:0.78rem">
 <?= admin_e(fmt_date($poll['created_at'])) ?>
 </small>
 </div>

 <div class="card-body px-4 pb-2">
 <h4 class="fw-bold mb-2" style="color:var(--text-main);font-size:1.15rem;line-height:1.5">
 <?= admin_e($poll['question']) ?>
 </h4>
 <?php if (!empty($poll['description'])): ?>
 <p class="text-muted small mb-3">
 <?= admin_e($poll['description']) ?>
 </p>
 <?php endif; ?>

 <!-- Options Breakdown -->
 <div class="poll-options-breakdown my-3 d-flex flex-column gap-2">
 <?php foreach ($poll['options'] as $opt): ?>
 <div class="p-2 rounded border" style="background:var(--bg-surface);border-color:var(--border-subtle)!important;position:relative;overflow:hidden">
 <!-- Progress Bar Background Fill -->
 <div style="position:absolute;top:0;bottom:0;right:0;width:<?= (int)$opt['percent'] ?>%;background:rgba(0,242,254,0.12);border-inline-end:2px solid var(--accent-primary);z-index:1;transition:width 0.5s ease"></div>
 
 <div class="d-flex justify-content-between align-items-center position-relative" style="z-index:2">
 <span class="d-inline-flex align-items-center gap-2 small fw-bold" style="color:var(--text-main)">
 <i class="bi bi-circle-fill text-primary" style="font-size:0.5rem"></i>
 <span><?= admin_e($opt['title']) ?></span>
 </span>
 <div class="d-flex align-items-center gap-2">
 <small class="text-muted" style="font-size:0.75rem"><?= number_format((int)$opt['votes_count']) ?> صوت</small>
 <span class="badge bg-dark text-white font-monospace" style="font-size:0.75rem"><?= (int)$opt['percent'] ?>%</span>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
 </div>

 <!-- Actions Toolbar -->
 <div class="card-footer bg-transparent border-top d-flex justify-content-between align-items-center px-4 py-3" style="border-color:var(--border-subtle)!important">
 <div class="d-flex gap-2">
 <a href="<?= admin_e(app_url("admin/polls/{$poll['id']}/edit")) ?>" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
 <i class="bi bi-pencil-square"></i> <span>تعديل</span>
 </a>

 <?php if (empty($poll['is_featured'])): ?>
 <form method="post" action="<?= admin_e(app_url("admin/polls/{$poll['id']}/set-featured")) ?>" class="d-inline">
 <?= CSRF::getField() ?>
 <button type="submit" class="btn btn-sm btn-outline-warning d-inline-flex align-items-center gap-1" title="تعيين كاستطلاع رئيسي على الصفحة الرئيسية">
 <i class="bi bi-star"></i> <span>تثبيت بالرئيسية</span>
 </button>
 </form>
 <?php endif; ?>
 </div>

 <div class="d-flex gap-2">
 <form method="post" action="<?= admin_e(app_url("admin/polls/{$poll['id']}/reset-votes")) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من تصفير كافة الأصوات في هذا الاستطلاع؟')">
 <?= CSRF::getField() ?>
 <button type="submit" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" title="تصفير الأصوات">
 <i class="bi bi-arrow-repeat"></i>
 </button>
 </form>

 <form method="post" action="<?= admin_e(app_url("admin/polls/{$poll['id']}/delete")) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الاستطلاع نهائياً مع كافة أصواته؟')">
 <?= CSRF::getField() ?>
 <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" title="حذف الاستطلاع">
 <i class="bi bi-trash3"></i>
 </button>
 </form>
 </div>
 </div>
 </div>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>

</div>
