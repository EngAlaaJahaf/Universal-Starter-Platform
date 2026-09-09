<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-folder2-open text-primary me-2"></i><?= admin_e($title) ?></h2>
 <p class="text-muted mb-0">إدارة وإضافة وتعديل عناصر <?= admin_e($title) ?> في المنصة.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/' . $resource . '/create')) ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-plus-lg me-1"></i> إضافة <?= admin_e($title) ?> جديد
 </a>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:60px">#</th>
 <?php foreach ($fields as $field => $meta): ?>
 <th><?= admin_e($meta['label'] ?? $field) ?></th>
 <?php endforeach; ?>
 <th class="text-end" style="width:120px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($rows)): ?>
 <tr>
 <td colspan="<?= count($fields) + 2 ?>" class="text-center py-5 text-muted">
 <i class="bi bi-inbox d-block mb-2" style="font-size:2rem"></i>
 لا توجد بيانات مسجلة حالياً.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($rows as $row): ?>
 <tr>
 <td><span class="text-muted small">#<?= (int) $row['id'] ?></span></td>
 <?php foreach ($fields as $field => $meta): ?>
 <td>
 <?php 
 $val = (string) ($row[$field] ?? '');
 if ($field === 'status'): 
 ?>
 <span class="badge bg-<?= $val === 'published' || $val === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= $val === 'published' || $val === 'active' ? 'success' : 'secondary' ?>">
 <?= $val === 'published' ? 'منشور' : ($val === 'active' ? 'نشط' : $val) ?>
 </span>
 <?php else: ?>
 <span><?= admin_e(mb_strimwidth($val, 0, 70, '…', 'UTF-8')) ?></span>
 <?php endif; ?>
 </td>
 <?php endforeach; ?>
 <td class="text-end">
 <div class="d-inline-flex gap-1">
 <a class="btn-action-icon btn-action-edit" href="<?= admin_e(app_url('admin/' . $resource . '/' . $row['id'] . '/edit')) ?>" title="تعديل">
 <i class="bi bi-pencil"></i>
 </a>
 <form method="post" action="<?= admin_e(app_url('admin/' . $resource . '/' . $row['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-delete" title="حذف">
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
