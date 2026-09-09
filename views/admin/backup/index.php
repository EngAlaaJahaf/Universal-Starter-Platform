<?php
$title = 'مركز النسخ الاحتياطي والاستيراد والتصدير';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h1 class="h3 fw-bold mb-1">مركز النسخ الاحتياطي والاستيراد والتصدير الشامل</h1>
 <p class="text-muted mb-0">إدارة حفظ البيانات، ترحيل المحتوى، استعادة قاعدة البيانات، ونقل الإعدادات واستطلاعات الرأي والمشتركين.</p>
 </div>
 <div class="d-flex gap-2">
 <a href="<?= app_url('admin/backup/export-db') ?>" class="btn btn-primary shadow-sm">
 <i class="bi bi-download me-1"></i> تحميل نسخة SQL كاملة الآن
 </a>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
 <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<?php if ($err = Session::getFlash('error')): ?>
 <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
 <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($err) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- System Data Stats -->
<div class="row g-3 mb-4">
 <div class="col-6 col-md-3">
 <div class="card border-0 shadow-sm p-3 bg-white h-100" style="border-radius:14px">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <small class="text-muted d-block fw-semibold">حجم قاعدة البيانات</small>
 <span class="h4 fw-bold mb-0 text-primary"><?= $dbSizeMb ?> MB</span>
 </div>
 <div class="p-3 bg-light rounded-4 text-primary"><i class="bi bi-database fs-4"></i></div>
 </div>
 </div>
 </div>
 <div class="col-6 col-md-3">
 <div class="card border-0 shadow-sm p-3 bg-white h-100" style="border-radius:14px">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <small class="text-muted d-block fw-semibold">المقالات والتحليلات</small>
 <span class="h4 fw-bold mb-0 text-success"><?= number_format($articlesCount) ?> مقال</span>
 </div>
 <div class="p-3 bg-light rounded-4 text-success"><i class="bi bi-newspaper fs-4"></i></div>
 </div>
 </div>
 </div>
 <div class="col-6 col-md-3">
 <div class="card border-0 shadow-sm p-3 bg-white h-100" style="border-radius:14px">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <small class="text-muted d-block fw-semibold">إعدادات المنصة</small>
 <span class="h4 fw-bold mb-0 text-info"><?= number_format($settingsCount) ?> إعداد</span>
 </div>
 <div class="p-3 bg-light rounded-4 text-info"><i class="bi bi-sliders fs-4"></i></div>
 </div>
 </div>
 </div>
 <div class="col-6 col-md-3">
 <div class="card border-0 shadow-sm p-3 bg-white h-100" style="border-radius:14px">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <small class="text-muted d-block fw-semibold">مشتركو النشرة</small>
 <span class="h4 fw-bold mb-0 text-warning"><?= number_format($subscribersCount) ?> مشترك</span>
 </div>
 <div class="p-3 bg-light rounded-4 text-warning"><i class="bi bi-envelope-check fs-4"></i></div>
 </div>
 </div>
 </div>
</div>

<div class="row g-4">

 <!-- 1. Full Database Backup & Restore -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-server text-primary me-1"></i> النسخة الاحتياطية لقاعدة البيانات (SQL)</h5>
 <span class="badge bg-primary">Full Dump</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير كامل بنية وبيانات جميع جداول المنصة في ملف <code>.sql</code> جاهز للاستيراد والاستعادة الفورية في أي وقت.
 </p>
 <div class="p-3 bg-light rounded-3 mb-3 border">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
 <span class="small fw-semibold">تصدير فوري مضغوط ومؤمن</span>
 <a href="<?= app_url('admin/backup/export-db') ?>" class="btn btn-sm btn-primary">
 <i class="bi bi-cloud-arrow-down-fill me-1"></i> تصدير SQL (.sql)
 </a>
 </div>
 </div>
 </div>

 <hr class="my-3">

 <!-- Restore Form -->
 <div>
 <label class="form-label fw-bold text-danger small"><i class="bi bi-exclamation-triangle me-1"></i> استعادة قاعدة البيانات من ملف SQL:</label>
 <form action="<?= app_url('admin/backup/import-db') ?>" method="post" enctype="multipart/form-data" onsubmit="return confirm('تنبيه هام: استيراد ملف SQL سيقوم باستبدال الجداول الحالية. هل أنت متأكد من المتابعة؟');">
 <?= CSRF::field() ?>
 <div class="input-group">
 <input type="file" name="sql_file" class="form-control form-control-sm" accept=".sql" required>
 <button type="submit" class="btn btn-sm btn-danger">استعادة الآن </button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 <!-- 2. Articles & Content Import / Export -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-file-earmark-text text-success me-1"></i> تصدير واستيراد المقالات والمحتوى</h5>
 <span class="badge bg-success">JSON & CSV</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير أرشيف الأخبار والتحليلات بهيكلية متكاملة (العناوين، المحتوى، التصنيفات، والمؤلفين) للترحيل أو التغذية الخارجية.
 </p>
 <div class="d-flex gap-2 mb-3 flex-wrap">
 <a href="<?= app_url('admin/backup/export-articles-json') ?>" class="btn btn-sm btn-outline-success flex-grow-1">
 <i class="bi bi-filetype-json me-1"></i> تصدير JSON
 </a>
 <a href="<?= app_url('admin/backup/export-articles-csv') ?>" class="btn btn-sm btn-outline-success flex-grow-1">
 <i class="bi bi-filetype-csv me-1"></i> تصدير CSV (Excel)
 </a>
 </div>
 </div>

 <hr class="my-3">

 <!-- Import Articles -->
 <div>
 <label class="form-label fw-bold text-dark small"><i class="bi bi-cloud-arrow-up-fill text-success me-1"></i> استيراد مقالات مجمعة من ملف JSON:</label>
 <form action="<?= app_url('admin/backup/import-articles-json') ?>" method="post" enctype="multipart/form-data">
 <?= CSRF::field() ?>
 <div class="input-group">
 <input type="file" name="articles_json_file" class="form-control form-control-sm" accept=".json" required>
 <button type="submit" class="btn btn-sm btn-success">استيراد المقالات</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 <!-- 3. Polls & Voting Analytics Export (NEW) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-bar-chart-line-fill text-primary me-1"></i> استطلاعات الرأي ونبض المجتمع</h5>
 <span class="badge bg-primary">Polls Analytics</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير كافة استطلاعات الرأي مع خياراتها وتفاصيل الأصوات والنسب المئوية المسجلة لإعداد التقارير الدورية وتحليل اهتمامات المتابعين.
 </p>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/backup/export-polls-json') ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
 <i class="bi bi-filetype-json me-1"></i> تقرير JSON تفصيلي
 </a>
 <a href="<?= app_url('admin/backup/export-polls-csv') ?>" class="btn btn-sm btn-outline-primary flex-grow-1">
 <i class="bi bi-filetype-csv me-1"></i> جدول Excel (CSV)
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- 4. Tutorials Studio Archive (NEW) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-journal-code text-info me-1"></i> استوديو الشروحات والدروس المصورة</h5>
 <span class="badge bg-info">Tutorials JSON</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير أرشيف الدروس والشروحات البرمجية التقنية مع كافة الخطوات والأكواد والشروحات التوضيحية لنقلها أو أرشفتها.
 </p>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/backup/export-tutorials-json') ?>" class="btn btn-sm btn-outline-info flex-grow-1">
 <i class="bi bi-download me-1"></i> تصدير حزمة الدروس (.json)
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- 5. OPML & RSS Feeds Export (NEW) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-rss-fill text-warning me-1"></i> مصادر الأخبار وخلاصات التغذية (OPML)</h5>
 <span class="badge bg-warning text-dark">OPML Standard</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير كافة مصادر الأخبار والـ RSS بتنسيق <code>OPML</code> القياسي المعتمد في قارئات الأخبار والتطبيقات العالمية (Feedly, Inoreader).
 </p>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/backup/export-rss-opml') ?>" class="btn btn-sm btn-outline-warning flex-grow-1">
 <i class="bi bi-file-earmark-code me-1"></i> تصدير ملف OPML (.opml)
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- 6. AI Classifier Rules Package (NEW) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-diagram-3-fill text-danger me-1"></i> مصطلحات وقواعد التصنيف الذكي (AI Rules)</h5>
 <span class="badge bg-danger">Classifier JSON</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير معجم الكلمات المفتاحية وقواعد الذكاء الاصطناعي المسؤولة عن التصنيف التلقائي لمقالات وأخبار المنصة.
 </p>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/backup/export-classifier-rules-json') ?>" class="btn btn-sm btn-outline-danger flex-grow-1">
 <i class="bi bi-download me-1"></i> تحميل حزمة القواعد (.json)
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- 7. Contact Messages Archive (NEW) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-chat-left-text text-secondary me-1"></i> رسائل واستفسارات اتصل بنا</h5>
 <span class="badge bg-secondary">Inbox CSV</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير كافة رسائل واستفسارات الزوار والطلبات الإعلانية والشراكات بتنسيق Excel CSV للمتابعة والأرشفة.
 </p>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/backup/export-contact-messages-csv') ?>" class="btn btn-sm btn-outline-secondary flex-grow-1">
 <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير سجل الرسائل (.csv)
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- 8. Live Blogs Archive (NEW) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-broadcast text-danger me-1"></i> أرشيف التغطيات المباشرة (Live Blog)</h5>
 <span class="badge bg-danger">Live Timeline JSON</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير التغطيات الحية للأحداث والمؤتمرات العالمية مع كامل التدوينات اللحظية والوسائط المرفقة للتوثيق والأرشفة.
 </p>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/backup/export-live-blogs-json') ?>" class="btn btn-sm btn-outline-danger flex-grow-1">
 <i class="bi bi-download me-1"></i> تصدير التغطيات (.json)
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- 9. Site Settings Package (JSON) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-sliders2 text-info me-1"></i> حزمة إعدادات المنصة (Settings Package)</h5>
 <span class="badge bg-info">Config JSON</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير كافة إعدادات المظهر، الذكاء الاصطناعي، الأمان، والتخصيصات في حزمة برمجية واحدة لنسخها إلى خادم آخر أو حفظها.
 </p>
 <div class="p-3 bg-light rounded-3 mb-3 border">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
 <span class="small fw-semibold">تصدير ملف الإعدادات الكامل</span>
 <a href="<?= app_url('admin/backup/export-settings-json') ?>" class="btn btn-sm btn-outline-info">
 <i class="bi bi-download me-1"></i> تحميل Package (.json)
 </a>
 </div>
 </div>
 </div>

 <hr class="my-3">

 <!-- Import Settings -->
 <div>
 <label class="form-label fw-bold text-dark small"><i class="bi bi-cloud-arrow-up-fill text-info me-1"></i> استيراد حزمة إعدادات وتطبيقها فوراً:</label>
 <form action="<?= app_url('admin/backup/import-settings-json') ?>" method="post" enctype="multipart/form-data">
 <?= CSRF::field() ?>
 <div class="input-group">
 <input type="file" name="settings_json_file" class="form-control form-control-sm" accept=".json" required>
 <button type="submit" class="btn btn-sm btn-info text-white">تطبيق الإعدادات</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 <!-- 10. Newsletter Subscribers (CSV) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-envelope-paper text-warning me-1"></i> مشتركو النشرة البريدية (Audience)</h5>
 <span class="badge bg-warning text-dark">CSV Format</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير قائمة الإيميلات المسجلة بالنشرة البريدية لمزامنتها مع منصات التسويق والحملات أو استيراد قائمة جديدة.
 </p>
 <div class="p-3 bg-light rounded-3 mb-3 border">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
 <span class="small fw-semibold">تصدير قائمة المشتركين الحالية</span>
 <a href="<?= app_url('admin/backup/export-subscribers-csv') ?>" class="btn btn-sm btn-outline-warning">
 <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير CSV
 </a>
 </div>
 </div>
 </div>

 <hr class="my-3">

 <!-- Import Subscribers -->
 <div>
 <label class="form-label fw-bold text-dark small"><i class="bi bi-cloud-arrow-up-fill text-warning me-1"></i> استيراد إيميلات مشتركي النشرة من CSV:</label>
 <form action="<?= app_url('admin/backup/import-subscribers-csv') ?>" method="post" enctype="multipart/form-data">
 <?= CSRF::field() ?>
 <div class="input-group">
 <input type="file" name="subscribers_csv_file" class="form-control form-control-sm" accept=".csv,.txt" required>
 <button type="submit" class="btn btn-sm btn-warning">إضافة المشتركين</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>

 <!-- 11. Security Audit Logs (NEW) -->
 <div class="col-12 col-xl-6">
 <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom d-flex align-items-center justify-content-between">
 <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shield-lock-fill text-dark me-1"></i> سجلات الأمان وتدقيق العمليات (Audit Logs)</h5>
 <span class="badge bg-dark">Security CSV</span>
 </div>
 <div class="card-body p-4 d-flex flex-column justify-content-between">
 <div>
 <p class="text-muted small">
 تصدير سجل النشاط الإداري والعمليات ومحاولات التعديل وعناوين الـ IP لأغراض التدقيق الأمني والامتثال.
 </p>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= app_url('admin/backup/export-activity-logs-csv') ?>" class="btn btn-sm btn-outline-dark flex-grow-1">
 <i class="bi bi-shield-check me-1"></i> تصدير سجلات الأمان (.csv)
 </a>
 </div>
 </div>
 </div>
 </div>
 </div>

</div>
