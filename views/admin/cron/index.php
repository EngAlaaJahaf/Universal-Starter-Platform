<?php
$allCategoriesList = (new Database())->fetchAll("SELECT id, name, slug FROM categories ORDER BY id ASC");
$statusFile = dirname(dirname(dirname(__FILE__))) . '/storage/cron_status.json';
$initialStatus = file_exists($statusFile) ? (@json_decode(file_get_contents($statusFile), true) ?: []) : [];
$isInitialRunning = !empty($initialStatus['is_running']);
$initialState = $initialStatus['state'] ?? 'idle';
?>

<!-- ================= HEADER & CONTROLS ================= -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
 <div>
 <h2 class="h3 fw-bold mb-1 d-flex align-items-center flex-wrap gap-2">
 <span><i class="bi bi-clock-history text-primary me-1"></i> النشر التلقائي ومراقبة العمليات</span>
 <span class="badge bg-primary-subtle text-primary fs-6 fw-normal px-2 py-1">Live Engine</span>
 </h2>
 <p class="text-muted small mb-0">نظام الجلب الذكي والترجمة والتصنيف مع لوحة تحكم تفاعلية متجاوبة لحظة بلحظة.</p>
 </div>

 <div class="d-flex gap-2 flex-wrap align-items-center" id="cronControlsBar">
 <!-- Custom Sources Modal Trigger Button -->
 <button type="button" class="btn btn-outline-primary fw-bold shadow-sm px-3 d-inline-flex align-items-center gap-2 <?= ($isInitialRunning || $initialState === 'paused') ? 'd-none' : '' ?>" id="btnOpenSourceModal" data-bs-toggle="modal" data-bs-target="#customSourcesModal" title="اختيار مصادر محددة لجلب الأخبار منها">
 <i class="bi bi-sliders fs-6"></i> <span>تخصيص المصادر (<?= count($allSources ?? []) ?>)</span>
 </button>

 <!-- New Cycle Form (All Sources) -->
 <form method="POST" action="<?= admin_e(app_url('admin/cron')) ?>" class="d-inline <?= ($isInitialRunning || $initialState === 'paused') ? 'd-none' : '' ?>" id="formRunNow">
 <?= CSRF::field() ?>
 <input type="hidden" name="action" value="run_now">
 <button type="submit" class="btn btn-success fw-bold shadow-sm px-3 d-inline-flex align-items-center gap-2">
 <i class="bi bi-play-circle-fill fs-6"></i> <span>بدء دورة جديدة (الكل)</span>
 </button>
 </form>

 <!-- Pause Form -->
 <form method="POST" action="<?= admin_e(app_url('admin/cron/pause')) ?>" class="d-inline <?= ($isInitialRunning) ? '' : 'd-none' ?>" id="formPause">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-warning fw-bold shadow-sm text-dark px-3 d-inline-flex align-items-center gap-2">
 <i class="bi bi-pause-circle-fill fs-6"></i> <span>إيقاف مؤقت</span>
 </button>
 </form>

 <!-- Resume Form -->
 <form method="POST" action="<?= admin_e(app_url('admin/cron/resume')) ?>" class="d-inline <?= ($initialState === 'paused') ? '' : 'd-none' ?>" id="formResume">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-primary fw-bold shadow-sm px-3 d-inline-flex align-items-center gap-2">
 <i class="bi bi-play-fill fs-6"></i> <span>استئناف النشر ▶</span>
 </button>
 </form>

 <!-- Stop Form -->
 <form method="POST" action="<?= admin_e(app_url('admin/cron/stop')) ?>" class="d-inline <?= ($isInitialRunning || $initialState === 'paused') ? '' : 'd-none' ?>" id="formStop">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-outline-danger fw-bold shadow-sm px-3 d-inline-flex align-items-center gap-2">
 <i class="bi bi-stop-circle-fill fs-6"></i> <span>إيقاف نهائي</span>
 </button>
 </form>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3">
 <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- ================= LIVE REAL-TIME PROGRESS MONITOR HERO CARD ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-4" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.04), rgba(16, 185, 129, 0.04)); border: 1px solid rgba(13, 110, 253, 0.15) !important;">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
 <div class="d-flex align-items-center gap-2 flex-wrap">
 <?php
 $initialBadgeClass = 'bg-secondary-subtle text-secondary';
 $initialStatusText = ' خامل (في انتظار الدورة القادمة)';
 if ($isInitialRunning) {
 $initialBadgeClass = 'bg-success-subtle text-success';
 $initialStatusText = 'جاري الجلب والترجمة والنشر الآن...';
 } elseif ($initialState === 'paused') {
 $initialBadgeClass = 'bg-warning-subtle text-dark';
 $initialStatusText = ' متوقف مؤقتاً (جاهز للاستئناف)';
 } elseif ($initialState === 'stopped') {
 $initialBadgeClass = 'bg-danger-subtle text-danger';
 $initialStatusText = ' تم إيقاف الدورة';
 }
 ?>
 <span id="liveStatusBadge" class="badge <?= $initialBadgeClass ?> px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-2">
 <span class="spinner-grow spinner-grow-sm <?= $isInitialRunning ? '' : 'd-none' ?>" id="liveSpinner" role="status"></span>
 <span id="liveStatusText"><?= $initialStatusText ?></span>
 </span>
 <span class="text-muted small d-none d-sm-inline" id="liveLastUpdate">آخر فحص: منذ لحظات</span>
 </div>

 <div class="d-flex align-items-center gap-2 gap-sm-3 flex-wrap ms-auto">
 <div class="form-check form-switch mb-0">
 <input class="form-check-input cursor-pointer" type="checkbox" id="autoPollToggle" checked>
 <label class="form-check-label small text-muted cursor-pointer user-select-none" for="autoPollToggle">تحديث حي (3ث)</label>
 </div>
 <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnManualRefresh" onclick="fetchLiveStatus()">
 <i class="bi bi-arrow-clockwise me-1"></i> تحديث
 </button>
 </div>
 </div>

 <!-- Progress Bar -->
 <div class="mb-3">
 <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
 <div class="small text-truncate" style="max-width: 80%;" id="currentSourceLabel">
 <span class="text-muted">المصدر الحالي:</span> 
 <strong class="text-primary font-monospace ms-1 text-truncate" id="currentSourceName"><?= admin_e($initialStatus['current_source'] ?? 'في انتظار بدء الدورة') ?></strong>
 </div>
 <strong class="text-primary fs-5 fw-bold font-monospace" id="progressPercentText"><?= (int)($initialStatus['progress_percent'] ?? 0) ?>%</strong>
 </div>
 <div class="progress rounded-pill overflow-hidden shadow-inner" style="height: 14px; background: #e2e8f0;">
 <div id="liveProgressBar" class="progress-bar <?= $isInitialRunning ? 'progress-bar-striped progress-bar-animated bg-success' : ($initialState === 'paused' ? 'bg-warning' : 'bg-primary') ?>" 
 role="progressbar" style="width: <?= (int)($initialStatus['progress_percent'] ?? 0) ?>%; transition: width 0.6s ease;"></div>
 </div>
 </div>

 <!-- Sub-metrics Grid (Responsive Cards) -->
 <div class="row g-2 g-md-3 pt-2 border-top">
 <div class="col-6 col-md-3">
 <div class="p-2 p-md-3 rounded-3 bg-white border border-light-subtle text-center text-md-start">
 <small class="text-muted d-block mb-1 text-truncate"><i class="bi bi-diagram-3 me-1 text-primary"></i> المصادر المنجزة</small>
 <strong class="fs-6 fs-md-5 text-dark font-monospace" id="statSourcesProcessed"><?= (int)($initialStatus['sources_processed'] ?? 0) ?> / <?= (int)($initialStatus['total_sources'] ?? 31) ?></strong>
 </div>
 </div>
 <div class="col-6 col-md-3">
 <div class="p-2 p-md-3 rounded-3 bg-white border border-light-subtle text-center text-md-start">
 <small class="text-muted d-block mb-1 text-truncate"><i class="bi bi-check2-circle me-1 text-success"></i> مقالات نُشرت</small>
 <strong class="fs-6 fs-md-5 text-success font-monospace" id="statArticlesPublished"><?= (int)($initialStatus['articles_published'] ?? 0) ?> مقال</strong>
 </div>
 </div>
 <div class="col-6 col-md-3">
 <div class="p-2 p-md-3 rounded-3 bg-white border border-light-subtle text-center text-md-start">
 <small class="text-muted d-block mb-1 text-truncate"><i class="bi bi-skip-forward me-1 text-warning"></i> مقالات مكررة</small>
 <strong class="fs-6 fs-md-5 text-warning font-monospace" id="statArticlesSkipped"><?= (int)($initialStatus['articles_skipped'] ?? 0) ?></strong>
 </div>
 </div>
 <div class="col-6 col-md-3">
 <div class="p-2 p-md-3 rounded-3 bg-white border border-light-subtle text-center text-md-start">
 <small class="text-muted d-block mb-1 text-truncate"><i class="bi bi-cpu me-1 text-secondary"></i> حالة الـ PID</small>
 <strong class="fs-6 fs-md-5 text-secondary font-monospace" id="statPid"><?= !empty($initialStatus['pid']) ? ('PID: ' . $initialStatus['pid']) : 'PID: -' ?></strong>
 </div>
 </div>
 </div>
</div>

<!-- ================= STATS CARDS ================= -->
<div class="row g-3 g-md-4 mb-4">
 <div class="col-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-right: 4px solid #0d6efd !important;">
 <div class="d-flex align-items-center gap-2 gap-md-3">
 <div class="rounded-circle bg-primary-subtle text-primary p-2 p-md-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
 <i class="bi bi-newspaper fs-5 fs-md-4"></i>
 </div>
 <div class="min-w-0">
 <small class="text-muted d-block text-truncate">إجمالي المنشور تلقائياً</small>
 <h4 class="fw-bold mb-0 text-dark font-monospace" id="statTotalAuto"><?= number_format($autoTotal) ?></h4>
 </div>
 </div>
 </div>
 </div>
 <div class="col-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-right: 4px solid #198754 !important;">
 <div class="d-flex align-items-center gap-2 gap-md-3">
 <div class="rounded-circle bg-success-subtle text-success p-2 p-md-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
 <i class="bi bi-check2-circle fs-5 fs-md-4"></i>
 </div>
 <div class="min-w-0">
 <small class="text-muted d-block text-truncate">نُشر اليوم تلقائياً</small>
 <h4 class="fw-bold mb-0 text-dark font-monospace" id="statTodayPublished"><?= (int)($todayStats['total_published'] ?? 0) ?></h4>
 </div>
 </div>
 </div>
 </div>
 <div class="col-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-right: 4px solid #fd7e14 !important;">
 <div class="d-flex align-items-center gap-2 gap-md-3">
 <div class="rounded-circle bg-warning-subtle text-warning p-2 p-md-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px">
 <i class="bi bi-skip-forward fs-5 fs-md-4"></i>
 </div>
 <div class="min-w-0">
 <small class="text-muted d-block text-truncate">تخطي (مكرر) اليوم</small>
 <h4 class="fw-bold mb-0 text-dark font-monospace"><?= (int)($todayStats['total_skipped'] ?? 0) ?></h4>
 </div>
 </div>
 </div>
 </div>
 <div class="col-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100" style="border-right: 4px solid #6f42c1 !important;">
 <div class="d-flex align-items-center gap-2 gap-md-3">
 <div class="rounded-circle p-2 p-md-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;background:rgba(111,66,193,0.1);color:#6f42c1">
 <i class="bi bi-repeat fs-5 fs-md-4"></i>
 </div>
 <div class="min-w-0">
 <small class="text-muted d-block text-truncate">مرات التشغيل اليوم</small>
 <h4 class="fw-bold mb-0 text-dark font-monospace"><?= (int)($todayStats['total_runs'] ?? 0) ?></h4>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- ================= LIVE FEED: RECENTLY PUBLISHED ARTICLES STREAM ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <div>
 <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
 <span>بث الأخبار المنشورة لحظة بلحظة</span>
 <span class="badge bg-success-subtle text-success small fw-normal">Live Stream</span>
 </h5>
 <p class="text-muted small mb-0">أحدث المقالات التي تم جلبها وترجمتها وتصنيفها مع إمكانية تعديل التصنيف فوراً.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/articles')) ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">
 <i class="bi bi-grid me-1"></i> كل المقالات (<?= number_format($autoTotal) ?>)
 </a>
 </div>

 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0" id="liveArticlesTable">
 <thead class="table-light">
 <tr class="text-nowrap small text-muted">
 <th style="width:50px">#</th>
 <th style="min-width:240px">عنوان المقال</th>
 <th style="min-width:110px">المصدر الإخباري</th>
 <th style="min-width:140px">التصنيف الذكي</th>
 <th style="min-width:130px">وقت النشر</th>
 <th class="text-end" style="width:90px">إجراءات</th>
 </tr>
 </thead>
 <tbody id="liveArticlesBody">
 <?php if (empty($recentAutoArticles)): ?>
 <tr>
 <td colspan="6" class="text-center py-4 text-muted">
 <i class="bi bi-inbox fs-4 d-block mb-1 text-secondary"></i>
 لا توجد مقالات منشورة بعد في هذه الدورة.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($recentAutoArticles as $art): ?>
 <tr>
 <td class="text-nowrap"><span class="text-muted small font-monospace">#<?= (int) $art['id'] ?></span></td>
 <td>
 <a href="<?= admin_e(app_url('articles/' . ($art['slug'] ?? $art['id']))) ?>" target="_blank" class="fw-bold text-dark text-decoration-none d-block line-clamp-2">
 <?= admin_e($art['title_ar'] ?: $art['title']) ?>
 </a>
 </td>
 <td class="text-nowrap">
 <span class="badge bg-light text-dark border font-monospace small">
 <?= admin_e($art['source_name'] ?: 'مصدر تقني') ?>
 </span>
 </td>
 <td>
 <?php
 $catName = $art['category_name'] ?? 'تقنية عامة';
 $catSlug = $art['category_slug'] ?? '';
 $badgeClass = 'bg-primary-subtle text-primary';
 if ($catSlug === 'artificial-intelligence') $badgeClass = 'bg-purple-subtle text-purple';
 elseif ($catSlug === 'cybersecurity') $badgeClass = 'bg-danger-subtle text-danger';
 elseif ($catSlug === 'hardware-devices') $badgeClass = 'bg-info-subtle text-info';
 elseif ($catSlug === 'software-development') $badgeClass = 'bg-success-subtle text-success';
 ?>
 <div class="dropdown d-inline-block">
 <button class="badge <?= $badgeClass ?> border-0 px-2 py-1 dropdown-toggle cursor-pointer shadow-none quick-cat-btn" 
 id="cronCatBadge_<?= $art['id'] ?>" 
 data-bs-toggle="dropdown" 
 aria-expanded="false" 
 style="cursor:pointer;" 
 title="انقر لتغيير تصنيف المقال فوراً">
 <?= admin_e($catName) ?>
 </button>
 <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-1" aria-labelledby="cronCatBadge_<?= $art['id'] ?>" style="z-index: 1060;">
 <li class="dropdown-header text-muted small py-1">اختر التصنيف الجديد:</li>
 <?php foreach ($allCategoriesList as $catItem): ?>
 <li>
 <a class="dropdown-item py-1 px-3 small d-flex align-items-center justify-content-between <?= ((int)$art['category_id'] === (int)$catItem['id']) ? 'active fw-bold' : '' ?>" 
 href="javascript:void(0)" 
 onclick="quickChangeCategory(<?= (int)$art['id'] ?>, <?= (int)$catItem['id'] ?>, this)">
 <span><?= admin_e($catItem['name']) ?></span>
 <?php if ((int)$art['category_id'] === (int)$catItem['id']): ?>
 <i class="bi bi-check2 ms-2"></i>
 <?php endif; ?>
 </a>
 </li>
 <?php endforeach; ?>
 </ul>
 </div>
 </td>
 <td class="text-nowrap"><small class="text-muted font-monospace"><?= admin_e(fmt_date($art['created_at'], 'H:i:s Y-m-d')) ?></small></td>
 <td class="text-end text-nowrap">
 <a href="<?= admin_e(app_url('admin/articles/' . $art['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" title="تعديل المقال والتصنيف">
 <i class="bi bi-pencil"></i>
 </a>
 <a href="<?= admin_e(app_url('articles/' . ($art['slug'] ?? $art['id']))) ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="عرض في الموقع">
 <i class="bi bi-box-arrow-up-right"></i>
 </a>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>

<!-- ================= HISTORICAL CRON LOGS ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-ul text-primary me-2"></i>سجل الدورات السابقة</h5>
 <span class="badge bg-secondary"><?= count($logs) ?> دورة مسجلة</span>
 </div>
 <div class="card-body p-0">
 <?php if (empty($logs)): ?>
 <div class="text-center py-5 text-muted">
 <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
 لا توجد سجلات سابقة بعد.
 </div>
 <?php else: ?>
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr class="text-nowrap small text-muted">
 <th>التاريخ والوقت</th>
 <th>الحالة</th>
 <th>نُشر</th>
 <th>تخطى</th>
 <th>فشل</th>
 <th>مصادر</th>
 <th>وقت التنفيذ</th>
 <th>المُشغِّل</th>
 <th class="text-end">التفاصيل</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($logs as $log): ?>
 <tr>
 <td class="text-muted small font-monospace text-nowrap"><?= htmlspecialchars(fmt_date($log['created_at'], 'Y-m-d H:i:s')) ?></td>
 <td class="text-nowrap">
 <?php if ($log['status'] === 'success'): ?>
 <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i>نجح</span>
 <?php elseif ($log['status'] === 'error'): ?>
 <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle me-1"></i>خطأ</span>
 <?php else: ?>
 <span class="badge bg-secondary-subtle text-secondary">تخطى</span>
 <?php endif; ?>
 </td>
 <td><strong class="text-success font-monospace"><?= (int)$log['articles_published'] ?></strong></td>
 <td><small class="text-muted font-monospace"><?= (int)$log['articles_skipped'] ?></small></td>
 <td>
 <?php if ($log['articles_failed']> 0): ?>
 <strong class="text-danger font-monospace"><?= (int)$log['articles_failed'] ?></strong>
 <?php else: ?>
 <span class="text-muted font-monospace">0</span>
 <?php endif; ?>
 </td>
 <td><span class="font-monospace"><?= (int)$log['sources_processed'] ?></span></td>
 <td class="small text-muted font-monospace text-nowrap"><?= number_format($log['execution_time_ms']) ?>ms</td>
 <td class="text-nowrap">
 <?php if ($log['triggered_by'] === 'manual'): ?>
 <span class="badge bg-primary-subtle text-primary border">يدوي</span>
 <?php else: ?>
 <span class="badge bg-light text-secondary border">Cron</span>
 <?php endif; ?>
 </td>
 <td class="text-end text-nowrap">
 <?php if (!empty($log['message'])): ?>
 <button class="btn btn-sm btn-outline-secondary py-0 px-2" data-bs-toggle="modal" data-bs-target="#logModal<?= $log['id'] ?>" title="عرض السجل الكامل">
 <i class="bi bi-eye"></i>
 </button>
 <!-- Modal -->
 <div class="modal fade" id="logModal<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header border-0 pb-0">
 <h6 class="modal-title fw-bold">سجل التشغيل #<?= $log['id'] ?> (<?= htmlspecialchars(fmt_date($log['created_at'], 'Y-m-d H:i:s')) ?>)</h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body">
 <pre class="bg-dark text-success p-3 rounded-3 small font-monospace mb-0" style="max-height:450px;overflow-y:auto;direction:ltr;text-align:left"><?= htmlspecialchars($log['message'] ?? '') ?></pre>
 </div>
 </div>
 </div>
 </div>
 <?php endif; ?>
 </td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 </div>
 <?php endif; ?>
 </div>
</div>

<!-- ================= RSS SOURCES & INDIVIDUAL SITE TRIGGER ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-transparent border-0 pt-3 px-3 px-md-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <div>
 <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
 <span><i class="bi bi-rss-fill text-warning me-1"></i> مصادر الـ RSS المعتمدة والجلب الفردي</span>
 <span class="badge bg-light text-dark border"><?= count($allSources ?? []) ?> مصدر</span>
 </h5>
 <p class="text-muted small mb-0">يمكنك جلب الأخبار من أي مصدر إخباري محدد بشكل فوري دون الحاجة لتشغيل الدورة بالكامل.</p>
 </div>
 <div class="d-flex gap-2">
 <input type="text" id="filterSourcesInput" class="form-control form-control-sm rounded-pill px-3" placeholder="بحث في المصادر..." style="max-width: 200px;">
 </div>
 </div>
 <div class="card-body p-0">
 <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
 <table class="table table-hover align-middle mb-0" id="tableSourcesList">
 <thead class="table-light sticky-top">
 <tr class="text-nowrap small text-muted">
 <th style="width:50px">#</th>
 <th>اسم المصدر الإخباري</th>
 <th>التصنيف الافتراضي</th>
 <th>رابط الخلاصة (RSS Feed)</th>
 <th>آخر عملية جلب</th>
 <th class="text-end" style="width:130px">إجراء فوري</th>
 </tr>
 </thead>
 <tbody>
 <?php if (!empty($allSources)): ?>
 <?php foreach ($allSources as $src): ?>
 <?php
 $srcCatSlug = $src['category_slug'] ?? '';
 $srcBadgeClass = 'bg-primary-subtle text-primary';
 if ($srcCatSlug === 'artificial-intelligence') $srcBadgeClass = 'bg-purple-subtle text-purple';
 elseif ($srcCatSlug === 'cybersecurity') $srcBadgeClass = 'bg-danger-subtle text-danger';
 elseif ($srcCatSlug === 'hardware-devices') $srcBadgeClass = 'bg-info-subtle text-info';
 elseif ($srcCatSlug === 'software-development') $srcBadgeClass = 'bg-success-subtle text-success';
 ?>
 <tr class="source-row" data-name="<?= htmlspecialchars(mb_strtolower($src['name'] . ' ' . ($src['category_name'] ?? ''))) ?>">
 <td class="text-nowrap"><span class="text-muted small font-monospace">#<?= (int)$src['id'] ?></span></td>
 <td>
 <strong class="text-dark d-block"><?= admin_e($src['name']) ?></strong>
 </td>
 <td>
 <span class="badge <?= $srcBadgeClass ?> px-2 py-1"><?= admin_e($src['category_name'] ?? 'عام') ?></span>
 </td>
 <td>
 <a href="<?= admin_e($src['url']) ?>" target="_blank" class="text-muted small text-decoration-none font-monospace text-truncate d-inline-block" style="max-width: 260px;">
 <i class="bi bi-box-arrow-up-right me-1"></i><?= admin_e($src['url']) ?>
 </a>
 </td>
 <td class="text-nowrap">
 <small class="text-muted font-monospace"><?= !empty($src['last_fetched_at']) ? admin_e(fmt_date($src['last_fetched_at'], 'Y-m-d H:i')) : 'لم يُجلب بعد' ?></small>
 </td>
 <td class="text-end text-nowrap">
 <button type="button" class="btn btn-sm btn-outline-success fw-bold py-1 px-2 btn-fetch-single" 
 onclick="triggerSingleSourceFetch(<?= (int)$src['id'] ?>, '<?= admin_e($src['name']) ?>', this)" 
 title="جلب وترجمة المقالات من <?= admin_e($src['name']) ?> فقط">
 <i class="bi bi-lightning-charge-fill me-1"></i> جلب الآن
 </button>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>

<!-- ================= CUSTOM SOURCES SELECTION MODAL ================= -->
<div class="modal fade" id="customSourcesModal" tabindex="-1" aria-labelledby="customSourcesModalLabel" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header border-0 pb-0">
 <div>
 <h5 class="modal-title fw-bold text-dark" id="customSourcesModalLabel">
 <i class="bi bi-sliders text-primary me-2"></i> تخصيص مصادر الجلب للدورة القادمة
 </h5>
 <p class="text-muted small mb-0">حدد المصادر التي ترغب في معالجتها وترجمتها الآن (اختر مصدراً واحداً أو عدة مصادر).</p>
 </div>
 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
 </div>

 <form id="formCustomSourcesRun" method="POST" action="<?= admin_e(app_url('admin/cron')) ?>">
 <?= CSRF::field() ?>
 <input type="hidden" name="action" value="run_now">

 <div class="modal-body pt-3">
 <!-- Quick Toolbar -->
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 bg-light p-2 rounded-3 border">
 <div class="d-flex gap-2">
 <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllModalSources(true)">
 <i class="bi bi-check-all me-1"></i> تحديد الكل
 </button>
 <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllModalSources(false)">
 <i class="bi bi-x-lg me-1"></i> إلغاء التحديد
 </button>
 </div>
 <div>
 <input type="text" id="modalSourceSearch" class="form-control form-control-sm" placeholder="فلترة المصادر..." oninput="filterModalSources(this.value)">
 </div>
 <div class="small fw-bold text-primary">
 المحدد: <span id="modalSelectedCount"><?= count($allSources ?? []) ?></span> / <?= count($allSources ?? []) ?>
 </div>
 </div>

 <!-- Sources Checkbox Grid -->
 <div class="row g-2" id="modalSourcesGrid">
 <?php if (!empty($allSources)): ?>
 <?php foreach ($allSources as $src): ?>
 <?php
 $srcCatSlug = $src['category_slug'] ?? '';
 $srcBadgeClass = 'bg-primary-subtle text-primary';
 if ($srcCatSlug === 'artificial-intelligence') $srcBadgeClass = 'bg-purple-subtle text-purple';
 elseif ($srcCatSlug === 'cybersecurity') $srcBadgeClass = 'bg-danger-subtle text-danger';
 elseif ($srcCatSlug === 'hardware-devices') $srcBadgeClass = 'bg-info-subtle text-info';
 elseif ($srcCatSlug === 'software-development') $srcBadgeClass = 'bg-success-subtle text-success';
 ?>
 <div class="col-12 col-md-6 modal-source-item" data-search="<?= htmlspecialchars(mb_strtolower($src['name'] . ' ' . ($src['category_name'] ?? ''))) ?>">
 <label class="d-flex align-items-center justify-content-between p-2 rounded-3 border bg-white cursor-pointer hover-shadow transition-all w-100 mb-0">
 <div class="d-flex align-items-center gap-2 text-truncate me-2">
 <input type="checkbox" name="source_ids[]" value="<?= (int)$src['id'] ?>" class="form-check-input modal-source-cb cursor-pointer mt-0 flex-shrink-0" checked onchange="updateModalSelectedCount()">
 <span class="fw-bold text-dark text-truncate small"><?= admin_e($src['name']) ?></span>
 </div>
 <span class="badge <?= $srcBadgeClass ?> small px-2 py-1 flex-shrink-0"><?= admin_e($src['category_name'] ?? 'عام') ?></span>
 </label>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>
 </div>

 <div class="modal-footer border-0 pt-0">
 <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" class="btn btn-success fw-bold px-4" id="btnSubmitCustomRun">
 <i class="bi bi-play-circle-fill me-1"></i> بدء النشر للمصادر المحددة 
 </button>
 </div>
 </form>
 </div>
 </div>
</div>

<!-- ================= REAL-TIME POLLING JAVASCRIPT ENGINE ================= -->
<script>
let pollInterval = null;
const ALL_CATEGORIES = <?= json_encode($allCategoriesList) ?>;

function escapeHtml(str) {
 if (!str) return '';
 return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function getCategoryBadgeClass(slug) {
 switch (slug) {
 case 'artificial-intelligence': return 'bg-purple-subtle text-purple';
 case 'cybersecurity': return 'bg-danger-subtle text-danger';
 case 'hardware-devices': return 'bg-info-subtle text-info';
 case 'software-development': return 'bg-success-subtle text-success';
 default: return 'bg-primary-subtle text-primary';
 }
}

function fetchLiveStatus() {
 fetch('<?= app_url('admin/cron/status-json') ?>', {
 headers: { 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(response => {
 if (!response.ok) throw new Error('Status poll failed');
 return response.json();
 })
 .then(data => {
 // 1. Update Badge, Buttons & Status
 const badge = document.getElementById('liveStatusBadge');
 const text = document.getElementById('liveStatusText');
 const spinner = document.getElementById('liveSpinner');
 const pBar = document.getElementById('liveProgressBar');
 const pText = document.getElementById('progressPercentText');
 const formPause = document.getElementById('formPause');
 const formResume = document.getElementById('formResume');
 const formRunNow = document.getElementById('formRunNow');
 const formStop = document.getElementById('formStop');

 if (data.is_running) {
 badge.className = 'badge bg-success-subtle text-success px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-2';
 text.textContent = 'جاري الجلب والترجمة والنشر الآن...';
 spinner.classList.remove('d-none');
 pBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-success';
 if (formPause) formPause.classList.remove('d-none');
 if (formResume) formResume.classList.add('d-none');
 if (formRunNow) formRunNow.classList.add('d-none');
 if (formStop) formStop.classList.remove('d-none');
 } else if (data.state === 'paused') {
 badge.className = 'badge bg-warning-subtle text-dark px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-2';
 text.textContent = ' متوقف مؤقتاً (جاهز للاستئناف)';
 spinner.classList.add('d-none');
 pBar.className = 'progress-bar bg-warning';
 if (formPause) formPause.classList.add('d-none');
 if (formResume) formResume.classList.remove('d-none');
 if (formRunNow) formRunNow.classList.add('d-none');
 if (formStop) formStop.classList.remove('d-none');
 } else if (data.state === 'stopped') {
 badge.className = 'badge bg-danger-subtle text-danger px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-2';
 text.textContent = ' تم إيقاف الدورة';
 spinner.classList.add('d-none');
 pBar.className = 'progress-bar bg-danger';
 if (formPause) formPause.classList.add('d-none');
 if (formResume) formResume.classList.add('d-none');
 if (formRunNow) formRunNow.classList.remove('d-none');
 if (formStop) formStop.classList.add('d-none');
 } else {
 badge.className = 'badge bg-secondary-subtle text-secondary px-3 py-2 fs-6 rounded-pill d-inline-flex align-items-center gap-2';
 text.textContent = ' خامل (في انتظار الدورة القادمة)';
 spinner.classList.add('d-none');
 pBar.className = 'progress-bar bg-primary';
 if (formPause) formPause.classList.add('d-none');
 if (formResume) formResume.classList.add('d-none');
 if (formRunNow) formRunNow.classList.remove('d-none');
 if (formStop) formStop.classList.add('d-none');
 }

 // 2. Update Progress Bar
 let percent = 0;
 if (data.is_running) {
 percent = Math.max(3, parseInt(data.progress_percent) || 5);
 } else if (data.state === 'paused') {
 percent = parseInt(data.progress_percent) || 0;
 } else if (data.state === 'stopped') {
 percent = 0;
 } else {
 percent = (data.sources_processed> 0 && data.sources_processed>= data.total_sources) ? 100 : (parseInt(data.progress_percent) || 0);
 }

 pBar.style.width = percent + '%';
 pText.textContent = percent + '%';

 // 3. Update Text Labels
 document.getElementById('currentSourceName').textContent = data.current_source || (data.is_running ? 'جاري الفحص...' : (data.state === 'stopped' ? 'متوقف' : 'مكتمل'));
 document.getElementById('statSourcesProcessed').textContent = (data.sources_processed || 0) + ' / ' + (data.total_sources || 31);
 document.getElementById('statArticlesPublished').textContent = (data.articles_published || 0) + ' مقال';
 document.getElementById('statArticlesSkipped').textContent = (data.articles_skipped || 0);
 document.getElementById('statPid').textContent = data.pid ? ('PID: ' + data.pid) : 'PID: -';
 if (data.today_published_total !== undefined) {
 const todayEl = document.getElementById('statTodayPublished');
 if (todayEl) todayEl.textContent = data.today_published_total;
 }

 const lastUpdateEl = document.getElementById('liveLastUpdate');
 if (lastUpdateEl) {
 lastUpdateEl.textContent = 'آخر فحص: ' + new Date().toLocaleTimeString('ar-SA');
 }

 // 4. Update Live Articles Feed Table (only if user is not currently interacting with a dropdown)
 if (!document.querySelector('#liveArticlesBody .dropdown-menu.show') && data.recent_articles && data.recent_articles.length> 0) {
 const tbody = document.getElementById('liveArticlesBody');
 let html = '';
 data.recent_articles.forEach(art => {
 const title = escapeHtml(art.title_ar || art.title);
 const source = escapeHtml(art.source_name || 'مصدر تقني');
 const catName = escapeHtml(art.category_name || 'تقنية عامة');
 const catBadge = getCategoryBadgeClass(art.category_slug);
 const articleUrl = '<?= app_url('articles/') ?>' + encodeURIComponent(art.slug || art.id);
 const editUrl = '<?= app_url('admin/articles/') ?>' + art.id + '/edit';

 let catDropdownItems = '';
 ALL_CATEGORIES.forEach(c => {
 const isActive = (parseInt(art.category_id) === parseInt(c.id));
 catDropdownItems += `
 <li>
 <a class="dropdown-item py-1 px-3 small d-flex align-items-center justify-content-between ${isActive ? 'active fw-bold' : ''}" 
 href="javascript:void(0)" 
 onclick="quickChangeCategory(${art.id}, ${c.id}, this)">
 <span>${escapeHtml(c.name)}</span>
 ${isActive ? '<i class="bi bi-check2 ms-2"></i>' : ''}
 </a>
 </li>
 `;
 });

 html += `
 <tr>
 <td class="text-nowrap"><span class="text-muted small font-monospace">#${art.id}</span></td>
 <td>
 <a href="${articleUrl}" target="_blank" class="fw-bold text-dark text-decoration-none d-block line-clamp-2">
 ${title}
 </a>
 </td>
 <td class="text-nowrap"><span class="badge bg-light text-dark border font-monospace small">${source}</span></td>
 <td>
 <div class="dropdown d-inline-block">
 <button class="badge ${catBadge} border-0 px-2 py-1 dropdown-toggle cursor-pointer shadow-none quick-cat-btn" 
 id="cronCatBadge_${art.id}" 
 data-bs-toggle="dropdown" 
 aria-expanded="false" 
 style="cursor:pointer;" 
 title="انقر لتغيير تصنيف المقال فوراً">
 ${catName}
 </button>
 <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 py-1" aria-labelledby="cronCatBadge_${art.id}" style="z-index: 1060;">
 <li class="dropdown-header text-muted small py-1">اختر التصنيف الجديد:</li>
 ${catDropdownItems}
 </ul>
 </div>
 </td>
 <td class="text-nowrap"><small class="text-muted font-monospace">${art.created_at || ''}</small></td>
 <td class="text-end text-nowrap">
 <a href="${editUrl}" class="btn btn-sm btn-outline-secondary py-0 px-2" title="تعديل">
 <i class="bi bi-pencil"></i>
 </a>
 <a href="${articleUrl}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="عرض">
 <i class="bi bi-box-arrow-up-right"></i>
 </a>
 </td>
 </tr>
 `;
 });
 tbody.innerHTML = html;
 }
 })
 .catch(err => {
 console.warn('Status poll error:', err);
 });
}

function quickChangeCategory(articleId, categoryId, clickedEl) {
 const dropdown = clickedEl.closest('.dropdown');
 const badgeBtn = dropdown.querySelector('.quick-cat-btn');
 const prevHtml = badgeBtn.innerHTML;
 badgeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:10px;height:10px;"></span> جاري الحفظ...';

 const formData = new FormData();
 formData.append('article_id', articleId);
 formData.append('category_id', categoryId);
 formData.append('_csrf_token', '<?= CSRF::generate() ?>');

 fetch('<?= app_url("admin/articles/quick-update-category") ?>', {
 method: 'POST',
 headers: { 'X-Requested-With': 'XMLHttpRequest' },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 if (data.ok) {
 badgeBtn.className = 'badge ' + data.badge_class + ' border-0 px-2 py-1 dropdown-toggle cursor-pointer shadow-none quick-cat-btn';
 badgeBtn.innerHTML = data.category_name;

 // Update active state in dropdown items
 dropdown.querySelectorAll('.dropdown-item').forEach(item => {
 item.classList.remove('active', 'fw-bold');
 const check = item.querySelector('.bi-check2');
 if (check) check.remove();
 });
 clickedEl.classList.add('active', 'fw-bold');
 clickedEl.insertAdjacentHTML('beforeend', '<i class="bi bi-check2 ms-2"></i>');

 // Quick green flash
 badgeBtn.style.transition = 'all 0.3s ease';
 badgeBtn.style.transform = 'scale(1.08)';
 setTimeout(() => badgeBtn.style.transform = 'scale(1)', 300);
 } else {
 badgeBtn.innerHTML = prevHtml;
 alert(data.error || 'حدث خطأ أثناء تحديث التصنيف.');
 }
 })
 .catch(() => {
 badgeBtn.innerHTML = prevHtml;
 alert('حدث خطأ في الاتصال بالخادم.');
 });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
 const toggle = document.getElementById('autoPollToggle');
 
 // Restore saved toggle preference from localStorage
 const savedToggle = localStorage.getItem('cron_auto_poll_enabled');
 if (savedToggle !== null) {
 toggle.checked = (savedToggle === 'true');
 }

 function setupPolling() {
 if (toggle.checked) {
 if (!pollInterval) {
 pollInterval = setInterval(fetchLiveStatus, 3000);
 }
 } else {
 if (pollInterval) {
 clearInterval(pollInterval);
 pollInterval = null;
 }
 }
 }

 toggle.addEventListener('change', function() {
 localStorage.setItem('cron_auto_poll_enabled', this.checked ? 'true' : 'false');
 setupPolling();
 });

 setupPolling();
 fetchLiveStatus();

 // AJAX Handler for Cron Action Buttons
 function bindAjaxForm(formId, confirmMsg = '') {
 const form = document.getElementById(formId);
 if (!form) return;
 form.addEventListener('submit', function(e) {
 e.preventDefault();
 if (confirmMsg && !confirm(confirmMsg)) return;

 const btn = form.querySelector('button[type="submit"]');
 const originalHtml = btn ? btn.innerHTML : '';
 if (btn) {
 btn.disabled = true;
 btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري المعالجة...';
 }

 const formData = new FormData(form);
 const actionUrl = form.getAttribute('action') || window.location.href;
 fetch(actionUrl, {
 method: 'POST',
 headers: { 'X-Requested-With': 'XMLHttpRequest' },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 if (btn) {
 btn.disabled = false;
 btn.innerHTML = originalHtml;
 }
 fetchLiveStatus();
 setTimeout(fetchLiveStatus, 400);
 })
 .catch(() => {
 if (btn) {
 btn.disabled = false;
 btn.innerHTML = originalHtml;
 }
 fetchLiveStatus();
 });
 });
 }

 bindAjaxForm('formRunNow', 'هل تريد بالتأكيد تشغيل دورة جلب ونشر تلقائي جديدة الآن لكافة المصادر؟');
 bindAjaxForm('formPause');
 bindAjaxForm('formResume');
 bindAjaxForm('formStop', 'هل تريد بالتأكيد إيقاف مهمة النشر الحالية نهائياً؟');

 // Handle Custom Sources Modal Submission
 const formCustomSources = document.getElementById('formCustomSourcesRun');
 if (formCustomSources) {
 formCustomSources.addEventListener('submit', function(e) {
 e.preventDefault();
 const checkedBoxes = formCustomSources.querySelectorAll('.modal-source-cb:checked');
 if (checkedBoxes.length === 0) {
 alert('يرجى تحديد مصدر واحد على الأقل للبدء.');
 return;
 }

 const btn = document.getElementById('btnSubmitCustomRun');
 const originalHtml = btn ? btn.innerHTML : '';
 if (btn) {
 btn.disabled = true;
 btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جاري الإطلاق...';
 }

 const formData = new FormData(formCustomSources);
 const actionUrl = formCustomSources.getAttribute('action') || window.location.href;
 fetch(actionUrl, {
 method: 'POST',
 headers: { 'X-Requested-With': 'XMLHttpRequest' },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 if (btn) {
 btn.disabled = false;
 btn.innerHTML = originalHtml;
 }
 // Close modal
 const modalEl = document.getElementById('customSourcesModal');
 const modalInstance = bootstrap.Modal.getInstance(modalEl);
 if (modalInstance) modalInstance.hide();

 fetchLiveStatus();
 setTimeout(fetchLiveStatus, 400);
 })
 .catch(() => {
 if (btn) {
 btn.disabled = false;
 btn.innerHTML = originalHtml;
 }
 fetchLiveStatus();
 });
 });
 }

 // Live search filter in sources table
 const filterInput = document.getElementById('filterSourcesInput');
 if (filterInput) {
 filterInput.addEventListener('input', function() {
 const query = this.value.trim().toLowerCase();
 document.querySelectorAll('#tableSourcesList tbody .source-row').forEach(row => {
 const name = row.getAttribute('data-name') || '';
 row.style.display = name.includes(query) ? '' : 'none';
 });
 });
 }
});

// Single source instant trigger
function triggerSingleSourceFetch(sourceId, sourceName, btnEl) {
 if (!confirm(`هل تريد بدء جلب وترجمة ونشر الأخبار من: "${sourceName}" فقط الآن؟`)) {
 return;
 }

 const prevHtml = btnEl.innerHTML;
 btnEl.disabled = true;
 btnEl.innerHTML = '<span class="spinner-border spinner-border-sm" style="width:10px;height:10px;"></span> جاري البدء...';

 const formData = new FormData();
 formData.append('action', 'run_now');
 formData.append('single_source_id', sourceId);
 formData.append('_csrf_token', '<?= CSRF::generate() ?>');

 fetch('<?= admin_e(app_url("admin/cron")) ?>', {
 method: 'POST',
 headers: { 'X-Requested-With': 'XMLHttpRequest' },
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 btnEl.disabled = false;
 btnEl.innerHTML = '<i class="bi bi-check2"></i> انطلقت!';
 btnEl.classList.remove('btn-outline-success');
 btnEl.classList.add('btn-success');

 setTimeout(() => {
 btnEl.innerHTML = prevHtml;
 btnEl.classList.remove('btn-success');
 btnEl.classList.add('btn-outline-success');
 }, 2000);

 fetchLiveStatus();
 setTimeout(fetchLiveStatus, 400);
 })
 .catch(() => {
 btnEl.disabled = false;
 btnEl.innerHTML = prevHtml;
 alert('حدث خطأ أثناء محاولة بدء جلب المصدر.');
 });
}

// Modal helper functions
function toggleAllModalSources(check) {
 document.querySelectorAll('#modalSourcesGrid .modal-source-cb').forEach(cb => {
 // only toggle visible items if search filter is active
 const parentItem = cb.closest('.modal-source-item');
 if (!parentItem || parentItem.style.display !== 'none') {
 cb.checked = check;
 }
 });
 updateModalSelectedCount();
}

function filterModalSources(query) {
 const q = (query || '').trim().toLowerCase();
 document.querySelectorAll('#modalSourcesGrid .modal-source-item').forEach(item => {
 const text = item.getAttribute('data-search') || '';
 item.style.display = text.includes(q) ? '' : 'none';
 });
}

function updateModalSelectedCount() {
 const total = document.querySelectorAll('#modalSourcesGrid .modal-source-cb').length;
 const checked = document.querySelectorAll('#modalSourcesGrid .modal-source-cb:checked').length;
 const countEl = document.getElementById('modalSelectedCount');
 if (countEl) countEl.textContent = checked;
 const submitBtn = document.getElementById('btnSubmitCustomRun');
 if (submitBtn) {
 submitBtn.disabled = (checked === 0);
 }
}
</script>

