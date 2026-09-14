<?php
$editing = !empty($row);
$title = $title ?? ($editing ? 'تعديل السجل' : 'إضافة عنصر جديد');
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="h3 fw-bold mb-1"><?= admin_e($title) ?></h2>
        <p class="text-muted mb-0">أدخل بيانات النموذج بدقة، وسيتم حفظها وحمايتها وتدقيقها تلقائياً.</p>
    </div>
    <a href="<?= admin_e(app_url('admin/' . $resource)) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-right me-1"></i> العودة للقائمة
    </a>
</div>

<?php if ($err = Session::getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $err ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 bg-white">
    <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i><?= $editing ? 'تعديل البيانات' : 'إدخال بيانات جديدة' ?></h6>
        <?php if ($editing): ?>
            <span class="badge bg-secondary-subtle text-secondary small">ID: #<?= (int) $row['id'] ?></span>
        <?php endif; ?>
    </div>
    <div class="card-body p-4">
        <form method="post" action="<?= admin_e(app_url('admin/' . $resource . '/' . ($editing ? $row['id'] . '/update' : 'store'))) ?>" enctype="multipart/form-data">
            <?= CSRF::field() ?>

            <div class="row g-4">
                <?php foreach ($fields as $field => $meta): 
                    $type = $meta['type'] ?? 'text';
                    $label = $meta['label'] ?? $field;
                    $required = !empty($meta['required']);
                    $value = $row[$field] ?? ($meta['default'] ?? '');
                    $help = $meta['help'] ?? '';
                    $colClass = in_array($type, ['textarea', 'rich_text', 'json']) ? 'col-12' : ($meta['col'] ?? 'col-md-6');
                ?>
                    <div class="<?= admin_e($colClass) ?>">
                        <label class="form-label fw-bold" for="input_<?= admin_e($field) ?>">
                            <?= admin_e($label) ?>
                            <?php if ($required): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($type === 'textarea'): ?>
                            <textarea class="form-control" 
                                id="input_<?= admin_e($field) ?>" 
                                name="<?= admin_e($field) ?>" 
                                rows="<?= (int)($meta['rows'] ?? 5) ?>"
                                placeholder="<?= admin_e($meta['placeholder'] ?? '') ?>"
                                <?= $required ? 'required' : '' ?>><?= admin_e($value) ?></textarea>

                        <?php elseif ($type === 'rich_text'): ?>
                            <textarea class="form-control font-monospace" 
                                id="input_<?= admin_e($field) ?>" 
                                name="<?= admin_e($field) ?>" 
                                rows="8"
                                placeholder="<?= admin_e($meta['placeholder'] ?? 'محتوى منسق (يدعم HTML و Markdown)...') ?>"
                                <?= $required ? 'required' : '' ?>><?= admin_e($value) ?></textarea>

                        <?php elseif ($type === 'select'): ?>
                            <select class="form-select" id="input_<?= admin_e($field) ?>" name="<?= admin_e($field) ?>" <?= $required ? 'required' : '' ?>>
                                <?php foreach (($meta['options'] ?? []) as $option => $optLabel): ?>
                                    <option value="<?= admin_e($option) ?>" <?= (string) $value === (string) $option ? 'selected' : '' ?>>
                                        <?= admin_e($optLabel) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($type === 'relation'): ?>
                            <select class="form-select" id="input_<?= admin_e($field) ?>" name="<?= admin_e($field) ?>" <?= $required ? 'required' : '' ?>>
                                <option value="">-- اختر <?= admin_e($label) ?> --</option>
                                <?php foreach (($relationData[$field] ?? []) as $relId => $relName): ?>
                                    <option value="<?= admin_e($relId) ?>" <?= (string)$value === (string)$relId ? 'selected' : '' ?>>
                                        <?= admin_e($relName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($type === 'switch' || $type === 'boolean'): ?>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                    id="input_<?= admin_e($field) ?>" 
                                    name="<?= admin_e($field) ?>" 
                                    value="1" 
                                    <?= !empty($value) ? 'checked' : '' ?>>
                                <label class="form-check-label text-muted" for="input_<?= admin_e($field) ?>">
                                    <?= admin_e($meta['switch_label'] ?? 'تفعيل / تنشيط هذا الخيار') ?>
                                </label>
                            </div>

                        <?php elseif ($type === 'image' || $type === 'file'): ?>
                            <div class="input-group">
                                <input type="file" class="form-control" 
                                    id="input_<?= admin_e($field) ?>" 
                                    name="<?= admin_e($field) ?>" 
                                    accept="<?= $type === 'image' ? 'image/*' : '*/*' ?>">
                            </div>
                            <?php if (!empty($value)): ?>
                                <div class="mt-2 d-flex align-items-center gap-2 p-2 border rounded-3 bg-light">
                                    <?php if ($type === 'image'): ?>
                                        <img src="<?= admin_e(str_starts_with($value, 'http') ? $value : app_url($value)) ?>" alt="Preview" class="rounded-2" style="width:48px;height:48px;object-fit:cover">
                                    <?php endif; ?>
                                    <span class="small text-muted font-monospace"><?= admin_e($value) ?></span>
                                </div>
                            <?php endif; ?>

                        <?php elseif ($type === 'password'): ?>
                            <div class="input-group password-input-wrap">
                                <input type="password" 
                                    class="form-control" 
                                    id="input_<?= admin_e($field) ?>" 
                                    name="<?= admin_e($field) ?>" 
                                    placeholder="<?= $editing ? 'اتركه فارغاً للحفاظ على كلمة المرور الحالية' : '' ?>"
                                    <?= ($required && !$editing) ? 'required' : '' ?>>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('input_<?= admin_e($field) ?>', this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                        <?php elseif ($type === 'datetime'): ?>
                            <input type="datetime-local" 
                                class="form-control" 
                                id="input_<?= admin_e($field) ?>" 
                                name="<?= admin_e($field) ?>" 
                                value="<?= admin_e(function_exists('form_datetime_local') ? form_datetime_local($value) : $value) ?>"
                                <?= $required ? 'required' : '' ?>>

                        <?php elseif ($type === 'date'): ?>
                            <input type="date" 
                                class="form-control" 
                                id="input_<?= admin_e($field) ?>" 
                                name="<?= admin_e($field) ?>" 
                                value="<?= admin_e($value) ?>"
                                <?= $required ? 'required' : '' ?>>

                        <?php else: ?>
                            <input type="<?= admin_e($type) ?>" 
                                class="form-control" 
                                id="input_<?= admin_e($field) ?>" 
                                name="<?= admin_e($field) ?>" 
                                value="<?= admin_e($value) ?>"
                                placeholder="<?= admin_e($meta['placeholder'] ?? '') ?>"
                                <?= !empty($meta['step']) ? 'step="' . admin_e($meta['step']) . '"' : '' ?>
                                <?= $required ? 'required' : '' ?>>
                        <?php endif; ?>

                        <?php if (!empty($help)): ?>
                            <div class="form-text text-muted small"><?= admin_e($help) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= admin_e(app_url('admin/' . $resource)) ?>" class="btn btn-light px-4">إلغاء</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> <?= $editing ? 'حفظ التعديلات' : 'إضافة الآن' ?>
                </button>
            </div>
        </form>
    </div>
</div>
