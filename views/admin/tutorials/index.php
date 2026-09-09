<?php
/**
 * Admin Tutorials & Step-by-Step Guides Index View
 */
?>
<div class="container-fluid p-0">

 <!-- Header & Actions -->
 <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h4 class="fw-bold mb-1 text-dark">استوديو الشروحات والدروس المصورة</h4>
 <p class="text-muted small mb-0">إنشاء وإدارة الشروحات التقنية المتسلسلة والمدعمة بالصور والأكواد خطوة بخطوة</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= admin_e(app_url('tutorials')) ?>" target="_blank" class="btn btn-outline-info rounded-pill px-3 shadow-sm">
 <i class="bi bi-box-arrow-up-right me-1"></i> تصفح الشروحات بالموقع
 </a>
 <a href="<?= admin_e(app_url('admin/tutorials/create')) ?>" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
 <i class="bi bi-plus-circle me-1"></i> إنشاء شرح مصور جديد
 </a>
 </div>
 </div>

 <!-- Summary KPI Cards -->
 <div class="row g-3 mb-4">
 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">إجمالي الشروحات</span>
 <h3 class="fw-bold text-dark mb-0 mt-1"><?= count($tutorials) ?></h3>
 </div>
 <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary fs-4">
 <i class="bi bi-journal-code"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">شروحات منشورة</span>
 <h3 class="fw-bold text-success mb-0 mt-1">
 <?= count(array_filter($tutorials, fn($t) => $t['status'] === 'published')) ?>
 </h3>
 </div>
 <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success fs-4">
 <i class="bi bi-check-circle-fill"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">مسودات قيد التحرير</span>
 <h3 class="fw-bold text-warning mb-0 mt-1">
 <?= count(array_filter($tutorials, fn($t) => $t['status'] === 'draft')) ?>
 </h3>
 </div>
 <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning fs-4">
 <i class="bi bi-pencil-square"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3 col-sm-6">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
 <div class="d-flex align-items-center justify-content-between">
 <div>
 <span class="text-muted small fw-bold">إجمالي الخطوات المصورة</span>
 <h3 class="fw-bold text-info mb-0 mt-1">
 <?= array_sum(array_column($tutorials, 'steps_count')) ?>
 </h3>
 </div>
 <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info fs-4">
 <i class="bi bi-images"></i>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Tutorials Table Card -->
 <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
 <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
 <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-stars text-primary me-2"></i> قائمة الشروحات والدروس المصورة</h6>
 <span class="badge bg-light text-dark border px-3 py-2">عدد الدروس: <?= count($tutorials) ?></span>
 </div>
 <div class="card-body p-0">
 <?php if (empty($tutorials)): ?>
 <div class="text-center py-5">
 <div class="display-4 text-muted mb-3"></div>
 <h5 class="fw-bold text-secondary">لا توجد شروحات أو دروس مصورة مضافة حتى الآن</h5>
 <p class="text-muted small">ابدأ الآن بإنشاء أول شرح مصور خطوة بخطوة مع إرفاق الصور والأكواد والتنبيهات بكل سهولة.</p>
 <a href="<?= admin_e(app_url('admin/tutorials/create')) ?>" class="btn btn-primary rounded-pill px-4 shadow-sm mt-2">
 <i class="bi bi-plus-circle me-1"></i> إنشاء أول شرح مصور
 </a>
 </div>
 <?php else: ?>
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="bg-light text-muted small">
 <tr>
 <th class="ps-4">الشرح والدروس</th>
 <th>المستوى</th>
 <th>الخطوات المصورة</th>
 <th>الوقت التقديري</th>
 <th>المشاهدات</th>
 <th>الحالة</th>
 <th class="text-end pe-4">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($tutorials as $t): ?>
 <tr>
 <td class="ps-4">
 <div class="d-flex align-items-center gap-3">
 <?php if (!empty($t['featured_image'])): ?>
 <img src="<?= admin_e(app_url($t['featured_image'])) ?>" class="rounded-3 shadow-sm" style="width:54px;height:42px;object-fit:cover" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=100&q=70';">
 <?php else: ?>
 <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-grid place-items-center fw-bold" style="width:54px;height:42px;font-size:1.3rem">
 
 </div>
 <?php endif; ?>
 <div>
 <a href="<?= admin_e(app_url("admin/tutorials/{$t['id']}/edit")) ?>" class="fw-bold text-dark text-decoration-none d-block mb-1">
 <?= admin_e($t['title']) ?>
 </a>
 <div class="d-flex align-items-center gap-2 text-muted small">
 <?php if ($t['category_name']): ?>
 <span class="badge bg-light text-secondary border"><?= admin_e($t['category_name']) ?></span>
 <?php endif; ?>
 <span> <?= fmt_date($t['created_at'], 'Y-m-d') ?></span>
 </div>
 </div>
 </div>
 </td>
 <td>
 <?php if ($t['difficulty'] === 'beginner'): ?>
 <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">مبتدئ</span>
 <?php elseif ($t['difficulty'] === 'intermediate'): ?>
 <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1">متوسط</span>
 <?php else: ?>
 <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">متقدم / خبير</span>
 <?php endif; ?>
 </td>
 <td>
 <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 fw-bold">
 <?= (int)$t['steps_count'] ?> خطوات
 </span>
 </td>
 <td>
 <span class="text-muted small"><?= (int)$t['estimated_minutes'] ?> دقيقة</span>
 </td>
 <td>
 <span class="text-muted small"><?= number_format((int)$t['views_count']) ?></span>
 </td>
 <td>
 <?php if ($t['status'] === 'published'): ?>
 <span class="badge bg-success px-2 py-1">منشور للعامة</span>
 <?php else: ?>
 <span class="badge bg-secondary px-2 py-1">مسودة</span>
 <?php endif; ?>
 </td>
 <td class="text-end pe-4">
 <div class="d-flex justify-content-end gap-1">
 <a href="<?= admin_e(app_url("tutorial/{$t['slug']}")) ?>" target="_blank" class="btn btn-sm btn-outline-info rounded-3" title="معاينة في الموقع">
 <i class="bi bi-eye"></i>
 </a>
 <a href="<?= admin_e(app_url("admin/tutorials/{$t['id']}/edit")) ?>" class="btn btn-sm btn-outline-primary rounded-3" title="تعديل الشرح والخطوات">
 <i class="bi bi-pencil-square"></i>
 </a>
 <form action="<?= admin_e(app_url("admin/tutorials/{$t['id']}/delete")) ?>" method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا الشرح وكافة خطواته المصورة نهائياً؟');" style="display:inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="حذف">
 <i class="bi bi-trash3"></i>
 </button>
 </form>
 </div>
 </td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 </div>
 <?php endif; ?>
 </div>
 </div>

</div>
