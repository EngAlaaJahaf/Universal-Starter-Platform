<?php
$activeTab = $_GET['tab'] ?? 'subscribers';

$smtpHost = Settings::get('smtp_host', 'smtp.gmail.com');
$smtpPort = Settings::get('smtp_port', '587');
$smtpUsername = Settings::get('smtp_username', 'your-email@gmail.com');
$smtpPassword = Settings::get('smtp_password', '');
$brevoApiKey = Settings::get('brevo_api_key', '');
$smtpEncryption = Settings::get('smtp_encryption', 'tls');
$mailFromAddress = Settings::get('mail_from_address', 'news@yourdomain.com');
$mailFromName = Settings::get('mail_from_name', 'منصة الأخبار التقنية');
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">إدارة النشرة البريدية والمشتركين</h2>
 <p class="text-muted mb-0">إدارة قاعدة بيانات المشتركين، إنشاء الحملات، وضبط إعدادات خادم البريد (SMTP).</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <button type="button" class="btn btn-outline-dark fw-bold" data-bs-toggle="modal" data-bs-target="#smtpSettingsModal">
 <i class="bi bi-gear-fill me-1"></i> إعدادات SMTP
 </button>
 <button type="button" class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addSubscriberModal">
 <i class="bi bi-person-plus-fill me-1"></i> + إضافة مشترك جديد
 </button>
 <a href="<?= admin_e(app_url('admin/newsletter/create')) ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-envelope-plus-fill me-1"></i> + إنشاء حملة بريدية
 </a>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm">
 <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<?php if ($err = Session::getFlash('error')): ?>
 <div class="alert alert-danger alert-dismissible fade show shadow-sm">
 <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($err) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- Quick Stats Summary -->
<div class="row g-3 mb-4">
 <div class="col-sm-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
 <i class="bi bi-people-fill fs-4"></i>
 </div>
 <div>
 <small class="text-muted d-block">المشتركون النشطون</small>
 <h3 class="fw-bold mb-0 text-dark"><?= number_format($activeCount ?? 0) ?></h3>
 </div>
 </div>
 </div>
 </div>
 <div class="col-sm-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
 <i class="bi bi-person-x-fill fs-4"></i>
 </div>
 <div>
 <small class="text-muted d-block">إلغاءات الاشتراك</small>
 <h3 class="fw-bold mb-0 text-dark"><?= number_format($unsubCount ?? 0) ?></h3>
 </div>
 </div>
 </div>
 </div>
 <div class="col-sm-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
 <i class="bi bi-send-check-fill fs-4"></i>
 </div>
 <div>
 <small class="text-muted d-block">إجمالي الحملات</small>
 <h3 class="fw-bold mb-0 text-dark"><?= number_format(count($campaigns ?? [])) ?></h3>
 </div>
 </div>
 </div>
 </div>
 <div class="col-sm-6 col-lg-3">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
 <div class="d-flex align-items-center gap-3">
 <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px">
 <i class="bi bi-hdd-network-fill fs-4"></i>
 </div>
 <div>
 <small class="text-muted d-block">خادم SMTP</small>
 <h6 class="fw-bold mb-0 text-dark font-monospace text-truncate" style="max-width:140px"><?= admin_e($smtpHost) ?></h6>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- Nav Tabs -->
<ul class="nav nav-pills mb-3 gap-2 flex-wrap" role="tablist">
 <li class="nav-item">
 <a class="nav-link <?= $activeTab === 'subscribers' ? 'active fw-bold' : 'bg-white shadow-sm text-dark' ?>" 
 href="<?= admin_e(app_url('admin/newsletter?tab=subscribers')) ?>">
 المشتركون المسجلون (<?= count($subscribers ?? []) ?>)
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $activeTab === 'campaigns' ? 'active fw-bold' : 'bg-white shadow-sm text-dark' ?>" 
 href="<?= admin_e(app_url('admin/newsletter?tab=campaigns')) ?>">
 الحملات البريدية (<?= count($campaigns ?? []) ?>)
 </a>
 </li>
 <li class="nav-item">
 <a class="nav-link <?= $activeTab === 'smtp' ? 'active fw-bold' : 'bg-white shadow-sm text-dark' ?>" 
 href="<?= admin_e(app_url('admin/newsletter?tab=smtp')) ?>">
 إعدادات خادم البريد (SMTP)
 </a>
 </li>
</ul>

<?php if ($activeTab === 'subscribers'): ?>
 <!-- TAB 1: SUBSCRIBERS LIST -->
 <div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent border-0 pt-3 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <h5 class="fw-bold mb-0">قائمة البريد الإلكتروني للمشتركين</h5>
 <div class="d-flex gap-2">
 <a href="<?= admin_e(app_url('admin/backup/export-subscribers-csv')) ?>" class="btn btn-sm btn-outline-success">
 <i class="bi bi-file-earmark-spreadsheet me-1"></i> تصدير المشتركين CSV
 </a>
 </div>
 </div>
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:60px">#</th>
 <th>البريد الإلكتروني</th>
 <th>الاسم</th>
 <th>الحالة</th>
 <th>تاريخ الاشتراك</th>
 <th>تاريخ الإلغاء</th>
 <th class="text-end">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($subscribers)): ?>
 <tr>
 <td colspan="7" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2rem"></span>
 لا يوجد مشتركون مسجلون حالياً.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($subscribers as $s): ?>
 <tr>
 <td><span class="text-muted small">#<?= (int) $s['id'] ?></span></td>
 <td>
 <strong class="text-dark font-monospace"><?= admin_e($s['email']) ?></strong>
 </td>
 <td><?= !empty($s['name']) ? admin_e($s['name']) : '<span class="text-muted small">غير محدد</span>' ?></td>
 <td>
 <?php if ($s['status'] === 'active'): ?>
 <span class="badge bg-success-subtle text-success">نشط </span>
 <?php else: ?>
 <span class="badge bg-secondary-subtle text-secondary">ملغي </span>
 <?php endif; ?>
 </td>
 <td><small class="text-muted"><?= admin_e(date('Y-m-d H:i', strtotime($s['subscribed_at']))) ?></small></td>
 <td>
 <?php if (!empty($s['unsubscribed_at'])): ?>
 <small class="text-danger"><?= admin_e(date('Y-m-d H:i', strtotime($s['unsubscribed_at']))) ?></small>
 <?php else: ?>
 <span class="text-muted small">-</span>
 <?php endif; ?>
 </td>
 <td class="text-end">
 <form method="post" action="<?= admin_e(app_url('admin/newsletter/subscribers/' . $s['id'] . '/toggle')) ?>" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm btn-outline-<?= $s['status'] === 'active' ? 'warning' : 'success' ?> py-0 px-2" title="<?= $s['status'] === 'active' ? 'تعطيل الاشتراك' : 'تفعيل الاشتراك' ?>">
 <?= $s['status'] === 'active' ? 'إلغاء' : 'تفعيل' ?>
 </button>
 </form>
 <form method="post" action="<?= admin_e(app_url('admin/newsletter/subscribers/' . $s['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل تريد بالتأكيد حذف هذا المشترك نهائياً؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="حذف">
 <i class="bi bi-trash"></i>
 </button>
 </form>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>

<?php elseif ($activeTab === 'smtp'): ?>
 <!-- TAB 3: SMTP SETTINGS & TEST TOOL -->
 <div class="row g-4">
 <div class="col-lg-8">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
 <h5 class="fw-bold mb-3 text-dark">إعدادات خادم البريد الصادر (SMTP Settings)</h5>
 <p class="text-muted small mb-4">اضبط بيانات خادم البريد لربط الموقع بحسابك في Google Workspace أو Gmail أو SendGrid أو استضافتك.</p>

 <form method="post" action="<?= admin_e(app_url('admin/newsletter/smtp')) ?>">
 <?= CSRF::field() ?>

 <div class="row g-3">
 <div class="col-md-8">
 <label class="form-label fw-bold small text-dark">عنوان خادم SMTP (Host) *</label>
 <input type="text" name="smtp_host" class="form-control" value="<?= admin_e($smtpHost) ?>" required placeholder="smtp.gmail.com">
 <small class="text-muted">مثال: smtp.gmail.com أو smtp.sendgrid.net أو mail.yourdomain.com</small>
 </div>
 <div class="col-md-4">
 <label class="form-label fw-bold small text-dark">المنفذ (Port) *</label>
 <input type="number" name="smtp_port" class="form-control" value="<?= admin_e($smtpPort) ?>" required placeholder="587">
 <small class="text-muted">587 لـ TLS أو 465 لـ SSL</small>
 </div>

 <div class="col-md-6">
 <label class="form-label fw-bold small text-dark">اسم المستخدم / البريد (Username) *</label>
 <input type="text" name="smtp_username" class="form-control font-monospace" value="<?= admin_e($smtpUsername) ?>" required placeholder="your-email@gmail.com">
 </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">كلمة المرور / App Password *</label>
                            <div class="password-input-wrap">
                                <input type="password" id="smtp_pass_input" name="smtp_password" class="form-control font-monospace" value="<?= admin_e($smtpPassword) ?>" placeholder="••••••••••••••••">
                                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('smtp_pass_input', this)" title="إظهار / إخفاء كلمة المرور">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">لحسابات Gmail استخدم (كلمة مرور التطبيقات App Password)</small>
                        </div>

  <div class="col-md-4">
  <label class="form-label fw-bold small text-dark">نوع التشفير (Encryption)</label>
  <select name="smtp_encryption" class="form-select">
  <option value="tls" <?= $smtpEncryption === 'tls' ? 'selected' : '' ?>>TLS (موصى به - منفذ 587)</option>
  <option value="ssl" <?= $smtpEncryption === 'ssl' ? 'selected' : '' ?>>SSL (منفذ 465)</option>
  <option value="none" <?= $smtpEncryption === 'none' ? 'selected' : '' ?>>بدون تشفير</option>
  </select>
  </div>

  <div class="col-md-12">
  <label class="form-label fw-bold small text-dark">مفتاح Brevo API (يُستخدم بدل SMTP — اختياري)</label>
  <div class="password-input-wrap">
  <input type="password" id="brevo_key_input" name="brevo_api_key" class="form-control font-monospace" value="<?= admin_e($brevoApiKey) ?>" placeholder="xkeysib-...">
  <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('brevo_key_input', this)" title="إظهار / إخفاء المفتاح">
  <i class="bi bi-eye"></i>
  </button>
  </div>
  <small class="text-muted">إذا أُدخل سيُرسل البريد عبر Brevo HTTP API (يعمل على الاستضافات التي تحجب منافذ SMTP مثل InfinityFree). اتركه فارغاً لاستخدام SMTP أعلاه.</small>
  </div>

 <div class="col-md-4">
 <label class="form-label fw-bold small text-dark">بريد المرسل (From Address) *</label>
 <input type="email" name="mail_from_address" class="form-control font-monospace" value="<?= admin_e($mailFromAddress) ?>" required placeholder="news@yourdomain.com">
 </div>

 <div class="col-md-4">
 <label class="form-label fw-bold small text-dark">اسم المرسل (From Name) *</label>
 <input type="text" name="mail_from_name" class="form-control" value="<?= admin_e($mailFromName) ?>" required placeholder="منصة الأخبار التقنية">
 </div>
 </div>

 <div class="mt-4 pt-3 border-top d-flex justify-content-end">
 <button type="submit" class="btn btn-primary fw-bold px-4 shadow-sm">
 حفظ إعدادات SMTP
 </button>
 </div>
 </form>
 </div>
 </div>

 <div class="col-lg-4">
 <!-- Test Email Card -->
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
 <h6 class="fw-bold mb-2 text-dark">اختبار اتصال SMTP الفعلي</h6>
 <p class="text-muted small mb-3">أرسل رسالة تجريبية إلى بريدك للتحقق من سلامة وصحة الإعدادات.</p>

 <form method="post" action="<?= admin_e(app_url('admin/newsletter/smtp/test')) ?>">
 <?= CSRF::field() ?>
 <div class="mb-3">
 <label class="form-label small text-muted">أدخل البريد المستلم للتجربة</label>
 <input type="email" name="test_email" class="form-control" required placeholder="mytest@gmail.com" value="<?= admin_e(Auth::user()['email'] ?? '') ?>">
 </div>
 <button type="submit" class="btn btn-success fw-bold w-100 shadow-sm">
 إرسال بريد تجريبي الآن
 </button>
 </form>
 </div>

 <!-- Gmail Setup Help Box -->
 <div class="card border-0 shadow-sm rounded-4 bg-light p-3">
 <h6 class="fw-bold mb-2 text-dark">تلميح لإعداد Gmail:</h6>
 <ol class="small text-muted ps-3 mb-0" style="line-height:1.7">
 <li>فعّل التحقق بخطوتين (2-Step Verification) في حساب Google.</li>
 <li>أنشئ <b>App Password (كلمة مرور التطبيقات)</b> من إعدادات أمان Google.</li>
 <li>انسخ كلمة المرور المكونة من 16 حرفاً وضعها في خانة كلمة المرور هنا.</li>
 </ol>
 </div>
 </div>
 </div>

<?php else: ?>
 <!-- TAB 2: CAMPAIGNS LIST -->
 <div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:60px">#</th>
 <th>عنوان الموضوع</th>
 <th>الكاتب</th>
 <th>الحالة</th>
 <th>المستلمون الناجحون</th>
 <th>فشل الإرسال</th>
 <th>تاريخ الإنشاء</th>
 <th class="text-end">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($campaigns)): ?>
 <tr>
 <td colspan="8" class="text-center py-5 text-muted">
 <span class="d-block mb-2" style="font-size:2rem"></span>
 لا توجد حملات بريدية سابقة.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($campaigns as $camp): ?>
 <tr>
 <td><span class="text-muted small">#<?= (int) $camp['id'] ?></span></td>
 <td><strong class="text-dark"><?= admin_e($camp['subject']) ?></strong></td>
 <td><small class="text-muted"><?= admin_e($camp['username'] ?? 'المدير') ?></small></td>
 <td>
 <span class="badge bg-<?= $camp['status'] === 'sent' ? 'success' : 'warning' ?>-subtle text-<?= $camp['status'] === 'sent' ? 'success' : 'warning' ?>">
 <?= $camp['status'] === 'sent' ? 'تم الإرسال' : 'مسودة' ?>
 </span>
 </td>
 <td><strong class="text-success"><?= number_format((int) ($camp['sent_count'] ?? 0)) ?></strong></td>
 <td><small class="text-danger"><?= (int) ($camp['failed_count'] ?? 0) ?></small></td>
 <td><small class="text-muted"><?= admin_e(date('Y-m-d H:i', strtotime($camp['created_at']))) ?></small></td>
 <td class="text-end">
 <?php if ($camp['status'] !== 'sent'): ?>
 <form method="post" action="<?= admin_e(app_url('admin/newsletter/' . $camp['id'] . '/send')) ?>" class="d-inline" onsubmit="return confirm('هل تريد بالتأكيد إرسال هذه النشرة إلى جميع المشتركين الآن؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm btn-success fw-bold shadow-sm">
 إرسال الآن
 </button>
 </form>
 <?php else: ?>
 <span class="badge bg-light text-muted border">مكتملة</span>
 <?php endif; ?>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
<?php endif; ?>

<!-- Modal: Add Subscriber -->
<div class="modal fade" id="addSubscriberModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow">
 <form method="post" action="<?= admin_e(app_url('admin/newsletter/subscribers/add')) ?>">
 <?= CSRF::field() ?>
 <div class="modal-header border-0 pb-0">
 <h5 class="modal-title fw-bold">إضافة مشترك جديد للنشرة</h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body">
 <div class="mb-3">
 <label class="form-label small text-muted">البريد الإلكتروني *</label>
 <input type="email" name="email" class="form-control" required placeholder="user@example.com">
 </div>
 <div class="mb-3">
 <label class="form-label small text-muted">اسم المشترك (اختياري)</label>
 <input type="text" name="name" class="form-control" placeholder="محمد علي">
 </div>
 </div>
 <div class="modal-footer border-0 pt-0">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" class="btn btn-primary fw-bold">حفظ المشترك</button>
 </div>
 </form>
 </div>
 </div>
</div>

<!-- Modal: SMTP Quick Settings -->
<div class="modal fade" id="smtpSettingsModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-lg">
 <div class="modal-content rounded-4 border-0 shadow">
 <form method="post" action="<?= admin_e(app_url('admin/newsletter/smtp')) ?>">
 <?= CSRF::field() ?>
 <div class="modal-header border-0 pb-0">
 <h5 class="modal-title fw-bold">ضبط إعدادات خادم البريد (SMTP)</h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body">
 <div class="row g-3">
 <div class="col-md-8">
 <label class="form-label small fw-bold text-dark">خادم SMTP (Host) *</label>
 <input type="text" name="smtp_host" class="form-control" value="<?= admin_e($smtpHost) ?>" required placeholder="smtp.gmail.com">
 </div>
 <div class="col-md-4">
 <label class="form-label small fw-bold text-dark">المنفذ (Port) *</label>
 <input type="number" name="smtp_port" class="form-control" value="<?= admin_e($smtpPort) ?>" required placeholder="587">
 </div>
 <div class="col-md-6">
 <label class="form-label small fw-bold text-dark">اسم المستخدم (Username) *</label>
 <input type="text" name="smtp_username" class="form-control font-monospace" value="<?= admin_e($smtpUsername) ?>" required placeholder="your-email@gmail.com">
 </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">كلمة المرور (App Password) *</label>
                            <div class="password-input-wrap">
                                <input type="password" id="smtp_modal_pass_input" name="smtp_password" class="form-control font-monospace" value="<?= admin_e($smtpPassword) ?>" placeholder="••••••••••••••••">
                                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('smtp_modal_pass_input', this)" title="إظهار / إخفاء كلمة المرور">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
  <div class="col-md-4">
  <label class="form-label small fw-bold text-dark">التشفير (Encryption)</label>
  <select name="smtp_encryption" class="form-select">
  <option value="tls" <?= $smtpEncryption === 'tls' ? 'selected' : '' ?>>TLS (منفذ 587)</option>
  <option value="ssl" <?= $smtpEncryption === 'ssl' ? 'selected' : '' ?>>SSL (منفذ 465)</option>
  <option value="none" <?= $smtpEncryption === 'none' ? 'selected' : '' ?>>بدون تشفير</option>
  </select>
  </div>
  <div class="col-md-12">
  <label class="form-label small fw-bold text-dark">مفتاح Brevo API (يُستخدم بدل SMTP — اختياري)</label>
  <div class="password-input-wrap">
  <input type="password" id="brevo_modal_key_input" name="brevo_api_key" class="form-control font-monospace" value="<?= admin_e($brevoApiKey) ?>" placeholder="xkeysib-...">
  <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('brevo_modal_key_input', this)" title="إظهار / إخفاء المفتاح">
  <i class="bi bi-eye"></i>
  </button>
  </div>
  <small class="text-muted">يعمل على الاستضافات التي تحجب منافذ SMTP. اتركه فارغاً لاستخدام SMTP.</small>
  </div>
 <div class="col-md-4">
 <label class="form-label small fw-bold text-dark">بريد المرسل (From Address) *</label>
 <input type="email" name="mail_from_address" class="form-control font-monospace" value="<?= admin_e($mailFromAddress) ?>" required placeholder="news@yourdomain.com">
 </div>
 <div class="col-md-4">
 <label class="form-label small fw-bold text-dark">اسم المرسل (From Name) *</label>
 <input type="text" name="mail_from_name" class="form-control" value="<?= admin_e($mailFromName) ?>" required placeholder="منصة الأخبار التقنية">
 </div>
 </div>
 </div>
 <div class="modal-footer border-0 pt-0">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" class="btn btn-primary fw-bold px-4">حفظ الإعدادات</button>
 </div>
 </form>
 </div>
 </div>
</div>
