<?php
$title = $title ?? 'إدارة العناصر';
$search = $search ?? '';
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalItems = $totalItems ?? count($rows ?? []);
?>

<!-- Header & Actions -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="h3 fw-bold mb-1"><i class="bi bi-folder2-open text-primary me-2"></i><?= admin_e($title) ?></h2>
        <p class="text-muted mb-0">إدارة وعرض وتعديل عناصر <?= admin_e($title) ?> (إجمالي: <?= number_format($totalItems) ?> سجل).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= admin_e(app_url('admin/' . $resource . '/create')) ?>" class="btn btn-primary fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> إضافة جديد
        </a>
    </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($err = Session::getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $err ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Search Bar & Filters Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3">
    <form method="get" action="<?= admin_e(app_url('admin/' . $resource)) ?>" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" value="<?= admin_e($search) ?>" class="form-control border-start-0" placeholder="بحث سريع في السجلات...">
                <?php if (!empty($search)): ?>
                    <a href="<?= admin_e(app_url('admin/' . $resource)) ?>" class="btn btn-outline-secondary" title="إلغاء التصفية"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-dark fw-bold px-3">بحث</button>
        </div>
    </form>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">#</th>
                    <?php foreach ($fields as $field => $meta): ?>
                        <th><?= admin_e($meta['label'] ?? $field) ?></th>
                    <?php endforeach; ?>
                    <th class="text-end no-sort" style="width:130px">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="<?= count($fields) + 2 ?>" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox d-block mb-2" style="font-size:2.5rem;color:#cbd5e1"></i>
                            <div class="fw-semibold">لا توجد بيانات مسجلة حالياً.</div>
                            <small class="text-muted">انقر على "إضافة جديد" لإدراج أول سجل.</small>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td><span class="text-muted small">#<?= (int) $row['id'] ?></span></td>
                            <?php foreach ($fields as $field => $meta): 
                                $type = $meta['type'] ?? 'text';
                                $val = $row[$field] ?? '';
                            ?>
                                <td>
                                    <?php if ($type === 'image' && !empty($val)): ?>
                                        <img src="<?= admin_e(str_starts_with($val, 'http') ? $val : app_url($val)) ?>" alt="Thumbnail" class="rounded-3 shadow-sm border" style="width:44px;height:44px;object-fit:cover">
                                    
                                    <?php elseif ($type === 'switch' || $type === 'boolean'): ?>
                                        <span class="badge bg-<?= !empty($val) ? 'success' : 'secondary' ?>-subtle text-<?= !empty($val) ? 'success' : 'secondary' ?>">
                                            <?= !empty($val) ? 'مفعل / نعم' : 'معطل / لا' ?>
                                        </span>

                                    <?php elseif ($type === 'relation' && isset($relationData[$field][$val])): ?>
                                        <span class="badge bg-primary-subtle text-primary fw-normal"><?= admin_e($relationData[$field][$val]) ?></span>

                                    <?php elseif ($field === 'status' || $type === 'select'): 
                                        $label = $meta['options'][$val] ?? $val;
                                        $badgeClass = in_array($val, ['published', 'active', 'approved', 'success']) ? 'success' : (in_array($val, ['draft', 'pending', 'inactive']) ? 'warning' : 'secondary');
                                    ?>
                                        <span class="badge bg-<?= $badgeClass ?>-subtle text-<?= $badgeClass ?>">
                                            <?= admin_e($label) ?>
                                        </span>

                                    <?php elseif ($type === 'datetime' || $type === 'date'): ?>
                                        <span class="small font-monospace"><?= admin_e(function_exists('fmt_date') ? fmt_date($val) : $val) ?></span>

                                    <?php else: ?>
                                        <span><?= admin_e(mb_strimwidth((string)$val, 0, 75, '…', 'UTF-8')) ?></span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a class="btn-action-icon btn-action-edit" href="<?= admin_e(app_url('admin/' . $resource . '/' . $row['id'] . '/edit')) ?>" title="تعديل">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="<?= admin_e(app_url('admin/' . $resource . '/' . $row['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل نهائياً؟')">
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

    <!-- Pagination Footer -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-transparent py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">الصفحة <?= (int)$page ?> من <?= (int)$totalPages ?> (إجمالي <?= number_format($totalItems) ?>)</span>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= admin_e(app_url('admin/' . $resource . '?page=' . ($page - 1) . (!empty($search) ? '&q=' . urlencode($search) : ''))) ?>">السابق</a>
                </li>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === (int)$page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= admin_e(app_url('admin/' . $resource . '?page=' . $i . (!empty($search) ? '&q=' . urlencode($search) : ''))) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= admin_e(app_url('admin/' . $resource . '?page=' . ($page + 1) . (!empty($search) ? '&q=' . urlencode($search) : ''))) ?>">التالي</a>
                </li>
            </ul>
        </div>
    <?php endif; ?>
</div>
