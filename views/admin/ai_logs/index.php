<?php
$title = 'سجلات محادثات المرشد (AI Logs)';
if ($_msg = Session::getFlash('success')): ?>
<div class="alert alert-success alert-dismissible fade show shadow-sm"><i class="bi bi-check-circle me-1"></i> <?= admin_e($_msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($_err = Session::getFlash('error')): ?>
<div class="alert alert-danger alert-dismissible fade show shadow-sm"><i class="bi bi-x-circle me-1"></i> <?= admin_e($_err) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-robot text-info me-2"></i>سجلات محادثات المرشد (عصب التقنية)</h2>
 <p class="text-muted mb-0">مراجعة محادثات الأعضاء مع المرشد الذكي، وتصحيح الأخطاء، وإدارة حصص الأسئلة اليومية.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/settings?group=ai_assistant')) ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-gear-fill me-1"></i> إعدادات المرشد
 </a>
</div>

<?php if (!empty($missingTable)): ?>
<div class="alert alert-warning d-flex align-items-start gap-2 rounded-4 shadow-sm">
 <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
 <div>
 <strong>جدول سجلات المحادثات غير منشأ بعد.</strong><br>
 لن تتوفر سجلات المحادثات حتى تنفّذ ملف <code dir="ltr">migrate_ai_conversations.sql</code> من SQL Tab في لوحة استضافة (مرة واحدة). جدول <code dir="ltr">ai_conversations</code> يخزّن كل سؤال وجواب مع المزود والأخطاء تلقائياً.
 </div>
</div>
<?php endif; ?>

<?php if (empty($quotaReady)): ?>
<div class="alert alert-warning d-flex align-items-start gap-2 rounded-4 shadow-sm">
 <i class="bi bi-exclamation-triangle-fill fs-5 mt-1"></i>
 <div>
 <strong>أعمدة الحصص غير مكتملة.</strong><br>
 تأكد من تنفيذ ملف <code dir="ltr">migrate_ai_daily_quota.sql</code> حتى تعمل الحصص اليومية والإضافية وتظهر أزرار إدارتها.
 </div>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
 <div class="col-md-3 col-sm-6">
  <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
   <div class="d-flex align-items-center justify-content-between">
    <div>
     <span class="text-muted small d-block mb-1">إجمالي الرسائل</span>
     <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['total']) ?></h3>
    </div>
    <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-4"><i class="bi bi-chat-dots fs-4"></i></div>
   </div>
  </div>
 </div>
 <div class="col-md-3 col-sm-6">
  <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
   <div class="d-flex align-items-center justify-content-between">
    <div>
     <span class="text-muted small d-block mb-1">إجابات ناجحة</span>
     <h3 class="fw-bold mb-0 text-success"><?= number_format($stats['ok']) ?></h3>
    </div>
    <div class="p-3 bg-success bg-opacity-10 text-success rounded-4"><i class="bi bi-check-circle fs-4"></i></div>
   </div>
  </div>
 </div>
 <div class="col-md-3 col-sm-6">
  <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
   <div class="d-flex align-items-center justify-content-between">
    <div>
     <span class="text-muted small d-block mb-1">أخطاء المزودين</span>
     <h3 class="fw-bold mb-0 text-danger"><?= number_format($stats['error']) ?></h3>
    </div>
    <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-4"><i class="bi bi-bug-fill fs-4"></i></div>
   </div>
  </div>
 </div>
 <div class="col-md-3 col-sm-6">
  <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
   <div class="d-flex align-items-center justify-content-between">
    <div>
     <span class="text-muted small d-block mb-1">مستخدمون تواصلوا</span>
     <h3 class="fw-bold mb-0 text-warning"><?= number_format($stats['users']) ?></h3>
    </div>
    <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-4"><i class="bi bi-people fs-4"></i></div>
   </div>
  </div>
 </div>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-body p-3">
  <form method="get" action="<?= admin_e(app_url('admin/ai-logs')) ?>" class="row g-2 align-items-center">
   <div class="col-md-8">
    <input type="text" name="q" class="form-control" placeholder="ابحث باسم المستخدم أو البريد الإلكتروني..." value="<?= admin_e($search) ?>">
   </div>
   <div class="col-md-2">
    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-search me-1"></i> بحث</button>
   </div>
   <?php if ($search !== ''): ?>
   <div class="col-md-2">
    <a href="<?= admin_e(app_url('admin/ai-logs')) ?>" class="btn btn-outline-secondary w-100">إلغاء</a>
   </div>
   <?php endif; ?>
  </form>
 </div>
</div>

<!-- Users table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
  <table class="table table-hover align-middle mb-0">
   <thead class="table-light">
    <tr>
     <th>المستخدم</th>
     <th>الحصة اليومية</th>
     <th>المستخدم اليوم</th>
     <th>إضافية</th>
     <th>الرسائل</th>
     <th>الأخطاء</th>
     <th>آخر نشاط</th>
     <th class="text-end" style="width:210px">الإجراءات</th>
    </tr>
   </thead>
   <tbody>
   <?php if (empty($users)): ?>
    <tr>
     <td colspan="8" class="text-center py-5 text-muted">
      <i class="bi bi-robot fs-1 d-block mb-2 text-secondary"></i>
      لا يوجد مستخدمون بعد<?= $search !== '' ? ' يطابقون البحث المحدد' : '' ?>.
     </td>
    </tr>
   <?php else: ?>
    <?php foreach ($users as $u): ?>
     <tr>
      <td>
       <div class="fw-semibold text-dark"><?= admin_e($u['username']) ?></div>
       <div class="text-muted small" dir="ltr"><?= admin_e($u['email']) ?></div>
      </td>
      <td>
       <?php if ($u['ai_quota_daily'] !== null): ?>
        <span class="badge bg-primary bg-opacity-10 text-primary border"><?= (int) $u['ai_quota_daily'] === 0 ? 'غير محدود' : (int) $u['ai_quota_daily'] . ' سؤال' ?></span>
       <?php else: ?>
        <span class="text-muted small">الافتراضي (من الإعدادات)</span>
       <?php endif; ?>
      </td>
      <td><span class="badge bg-secondary bg-opacity-10 text-secondary border"><?= (int) $u['ai_quota_used'] ?></span></td>
      <td>
       <?php if ((int) $u['ai_quota_boost'] > 0): ?>
        <span class="badge bg-success bg-opacity-10 text-success border">+<?= (int) $u['ai_quota_boost'] ?></span>
       <?php else: ?>
        <span class="text-muted small">-</span>
       <?php endif; ?>
      </td>
      <td class="fw-semibold"><?= number_format((int) $u['total_msgs']) ?></td>
      <td>
       <?php if ((int) $u['err_msgs'] > 0): ?>
        <span class="badge bg-danger bg-opacity-10 text-danger border"><?= (int) $u['err_msgs'] ?></span>
       <?php else: ?>
        <span class="text-muted small">-</span>
       <?php endif; ?>
      </td>
      <td class="text-muted small" dir="ltr">
       <?= $u['last_at'] ? admin_e(fmt_date($u['last_at'], 'Y-m-d H:i')) : '<span class="text-muted">—</span>' ?>
      </td>
      <td class="text-end">
       <div class="d-inline-flex gap-1">
        <a href="<?= admin_e(app_url('admin/ai-logs/conversation?user_id=' . (int) $u['id'])) ?>" class="btn btn-sm btn-outline-info fw-bold">
         <i class="bi bi-chat-left-text me-1"></i> المحادثة
        </a>
        <details class="position-relative">
         <summary class="btn btn-sm btn-outline-warning fw-bold"><i class="bi bi-sliders me-1"></i> الحصة</summary>
         <div class="card border-0 shadow-lg rounded-3 p-3" style="width:300px; position:absolute; left:0; top:100%; z-index:1050; margin-top:6px;">
          <div class="fw-bold text-dark mb-2">إدارة حصة: <?= admin_e($u['username']) ?></div>
          <form method="post" action="<?= admin_e(app_url('admin/ai-logs/reset')) ?>" class="mb-3" onsubmit="return confirm('تصفير العداد اليومي لهذا المستخدم؟');">
           <?= CSRF::getField() ?>
           <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
           <button type="submit" class="btn btn-sm btn-danger w-100">
            <i class="bi bi-arrow-counterclockwise me-1"></i> تصفير العداد اليومي
           </button>
          </form>
          <form method="post" action="<?= admin_e(app_url('admin/ai-logs/boost')) ?>" class="border-top pt-3">
           <?= CSRF::getField() ?>
           <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
           <label class="form-label small text-muted mb-1">منح رسائل</label>
           <div class="input-group input-group-sm mb-2">
            <input type="number" name="amount" class="form-control" min="1" max="1000" value="5" required>
            <span class="input-group-text">رسالة</span>
           </div>
           <div class="form-check">
            <input class="form-check-input" type="radio" name="kind" id="kindExtra<?= (int) $u['id'] ?>" value="extra" checked>
            <label class="form-check-label small" for="kindExtra<?= (int) $u['id'] ?>">إضافية مؤقتة (تُستهلك مرة واحدة)</label>
           </div>
           <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="kind" id="kindDaily<?= (int) $u['id'] ?>" value="daily">
            <label class="form-check-label small" for="kindDaily<?= (int) $u['id'] ?>">تعديل دائم — تصبح حصته اليومية هذا الرقم</label>
           </div>
           <button type="submit" class="btn btn-sm btn-success w-100">
            <i class="bi bi-plus-lg me-1"></i> تنفيذ
           </button>
          </form>
         </div>
        </details>
       </div>
      </td>
     </tr>
    <?php endforeach; ?>
   <?php endif; ?>
   </tbody>
  </table>
 </div>
 <div class="card-footer bg-white border-0 py-3 small text-muted">
  تُحتسب الحصة اليومية لكل مستخدم بمنطقة الموقع الزمنية وتُصفَّر تلقائياً منتصف الليل. الرسائل الإضافية المؤقتة تُستهلك قبل رصيد اليوم ولا تصبح إعداداً دائماً.
 </div>
</div>