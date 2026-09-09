<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-key-fill text-warning me-2"></i>نقاط النهاية والتحكم البرمجي للذكاء الاصطناعي (REST API & AI Endpoints)</h2>
 <p class="text-muted mb-0">توليد مفاتيح مخصصة بنطاق صلاحيات محدد وتعديلها والاطلاع على تفاصيلها الكاملة للتحكم في الموقع برمجياً.</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <a href="<?= admin_e(app_url('api/v1/schema')) ?>" target="_blank" class="btn btn-outline-dark fw-bold">
 <i class="bi bi-file-earmark-code me-1"></i> مخطط OpenAPI 3.0 (JSON)
 </a>
 <button class="btn btn-primary fw-bold shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#createKeyBox">
 <i class="bi bi-plus-lg me-1"></i> توليد مفتاح / نقطة نهاية جديدة
 </button>
 </div>
</div>

<!-- Newly Generated / Regenerated Token Alert Banner -->
<?php if ($newToken = Session::getFlash('success_token')): ?>
 <div class="alert alert-success border-2 rounded-4 shadow-sm p-4 mb-4">
 <div class="d-flex align-items-center gap-3 mb-2">
 <span class="badge bg-success p-2 rounded-circle"><i class="bi bi-check-lg fs-5"></i></span>
 <div>
 <h5 class="fw-bold mb-0">تم توليد / تحديث رمز الـ API بنجاح: "<?= htmlspecialchars(Session::getFlash('success_name') ?? '') ?>"</h5>
 <small class="text-muted">انسخ المفتاح السري أدناه واحتفظ به في مكان آمن:</small>
 </div>
 </div>
 <div class="input-group mt-3">
 <input type="text" id="newTokenInput" class="form-control form-control-lg font-monospace bg-white fw-bold text-primary" value="<?= htmlspecialchars($newToken) ?>" readonly>
 <button class="btn btn-dark px-4 fw-bold" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newTokenInput').value); alert('تم نسخ المفتاح بنجاح!');">
 <i class="bi bi-clipboard me-1"></i> نسخ المفتاح
 </button>
 </div>
 </div>
<?php endif; ?>

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

<!-- Create Key Form Collapse -->
<div class="collapse mb-4" id="createKeyBox">
 <div class="card border-0 shadow-sm rounded-4 bg-white p-4 border-start border-primary border-4">
 <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock-fill text-primary me-2"></i>توليد نقطة نهاية ومفتاح API مخصص مع تحديد الصلاحيات</h6>
 <form method="post" action="<?= admin_e(app_url('admin/api-keys/store')) ?>">
 <?= CSRF::field() ?>
 <div class="row g-3 mb-3">
 <div class="col-md-6">
 <label class="form-label fw-bold">اسم المفتاح / الغرض منه <span class="text-danger">*</span></label>
 <input type="text" name="name" class="form-control" placeholder="مثال: AI Auto-Publisher Bot / Mobile App Agent" required>
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">حد الطلبات (Rate Limit / دقيقة)</label>
 <input type="number" name="rate_limit_per_minute" class="form-control" value="60" min="10" max="1000">
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">تاريخ الانتهاء</label>
 <select name="expires_days" class="form-select">
 <option value="0">دائم (بدون انتهاء)</option>
 <option value="30">بعد 30 يوماً</option>
 <option value="90">بعد 90 يوماً</option>
 <option value="365">بعد سنة واحدة</option>
 </select>
 </div>
 </div>

 <div class="mb-4">
 <label class="form-label fw-bold d-block mb-2">تحديد صلاحيات نقطة النهاية (Scopes):</label>
 <div class="row g-2 p-3 bg-light rounded-3 border">
 <?php foreach ($allScopes as $scopeKey => $scopeLabel): ?>
 <div class="col-md-6">
 <div class="form-check">
 <input class="form-check-input" type="checkbox" name="scopes[]" value="<?= $scopeKey ?>" id="scope_<?= $scopeKey ?>" <?= $scopeKey === 'all' ? 'checked' : '' ?>>
 <label class="form-check-label small fw-semibold" for="scope_<?= $scopeKey ?>">
 <?= admin_e($scopeLabel) ?>
 </label>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
 </div>

 <div class="text-end">
 <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">توليد وتفعيل المفتاح الآن</button>
 </div>
 </form>
 </div>
</div>

<!-- API Keys Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h6 class="mb-0 fw-bold">المفاتيح ونقاط النهاية الفعالة (<?= count($keys) ?>)</h6>
 </div>
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th>اسم المفتاح</th>
 <th>بادئة المفتاح (Prefix)</th>
 <th>الصلاحيات الممنوحة (Scopes)</th>
 <th>عدد الطلبات</th>
 <th>آخر استخدام</th>
 <th>الحالة</th>
 <th class="text-end" style="min-width:180px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($keys)): ?>
 <tr>
 <td colspan="7" class="text-center py-5 text-muted">
 <i class="bi bi-key d-block mb-2" style="font-size:2.5rem;color:#cbd5e1"></i>
 لا توجد مفاتيح API حتى الآن. انقر على "توليد مفتاح جديد" للبدء.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($keys as $k): ?>
 <?php $scopes = json_decode($k['scopes'] ?? '[]', true) ?: []; ?>
 <tr>
 <td>
 <strong class="text-dark d-block"><?= admin_e($k['name']) ?></strong>
 <small class="text-muted">تم الإنشاء: <?= admin_e(date('Y-m-d', strtotime($k['created_at']))) ?></small>
 </td>
 <td>
 <code class="small bg-light px-2 py-1 border rounded text-primary font-monospace"><?= admin_e($k['key_prefix']) ?></code>
 </td>
 <td>
 <div class="d-flex flex-wrap gap-1" style="max-width:300px">
 <?php foreach ($scopes as $sc): ?>
 <span class="badge bg-<?= $sc === 'all' ? 'primary' : 'secondary-subtle text-dark border' ?> px-2 py-1" style="font-size:0.7rem">
 <?= admin_e($sc) ?>
 </span>
 <?php endforeach; ?>
 </div>
 </td>
 <td>
 <span class="badge bg-light text-dark border fw-bold px-2 py-1">
 <i class="bi bi-activity text-success me-1"></i> <?= number_format((int) $k['requests_count']) ?>
 </span>
 </td>
 <td>
 <small class="text-muted"><?= !empty($k['last_used_at']) ? admin_e(date('Y-m-d H:i', strtotime($k['last_used_at']))) : 'لم يستخدم بعد' ?></small>
 </td>
 <td>
 <span class="badge bg-<?= !empty($k['is_active']) ? 'success' : 'danger' ?>-subtle text-<?= !empty($k['is_active']) ? 'success' : 'danger' ?>">
 <?= !empty($k['is_active']) ? 'فعال' : 'معطل' ?>
 </span>
 </td>
 <td class="text-end">
 <div class="d-inline-flex gap-1">
 <!-- View Details Button -->
 <button type="button" class="btn-action-icon btn-action-view" data-bs-toggle="modal" data-bs-target="#keyDetailsModal_<?= $k['id'] ?>" title="عرض التفاصيل والأكواد الكاملة">
 <i class="bi bi-eye"></i>
 </button>

 <!-- Edit Key Button -->
 <button type="button" class="btn-action-icon btn-action-edit" data-bs-toggle="modal" data-bs-target="#editKeyModal_<?= $k['id'] ?>" title="تعديل الصلاحيات والبيانات">
 <i class="bi bi-pencil"></i>
 </button>

 <!-- Regenerate Token Button -->
 <form method="post" action="<?= admin_e(app_url('admin/api-keys/' . $k['id'] . '/regenerate')) ?>" class="d-inline" onsubmit="return confirm('إعادة توليد الرمز السري لهذا المفتاح ستبطل الرمز القديم فوراً. هل تريد المتابعة؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon" style="color:#6366f1;border-color:#e0e7ff" title="إعادة توليد المفتاح السري">
 <i class="bi bi-arrow-repeat"></i>
 </button>
 </form>

 <!-- Toggle Active/Inactive Button -->
 <form method="post" action="<?= admin_e(app_url('admin/api-keys/' . $k['id'] . '/toggle')) ?>" class="d-inline">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon <?= !empty($k['is_active']) ? 'btn-action-ban' : 'btn-action-activate' ?>" title="<?= !empty($k['is_active']) ? 'تعطيل المفتاح' : 'تفعيل المفتاح' ?>">
 <i class="bi bi-<?= !empty($k['is_active']) ? 'pause-fill' : 'play-fill' ?>"></i>
 </button>
 </form>

 <!-- Delete Key Button -->
 <form method="post" action="<?= admin_e(app_url('admin/api-keys/' . $k['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المفتاح نهائياً؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-delete" title="حذف المفتاح">
 <i class="bi bi-trash3"></i>
 </button>
 </form>
 </div>
 </td>
 </tr>

 <!-- ================= MODAL: KEY DETAILS & SNIPPETS ================= -->
 <div class="modal fade" id="keyDetailsModal_<?= $k['id'] ?>" tabindex="-1">
 <div class="modal-dialog modal-lg modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header bg-light border-bottom py-3">
 <h6 class="modal-title fw-bold text-dark">
 <i class="bi bi-key-fill text-primary me-2"></i>تفاصيل نقطة النهاية: <?= admin_e($k['name']) ?>
 </h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body p-4 text-start" dir="ltr">
 <div class="row g-3 mb-3 text-end" dir="rtl">
 <div class="col-md-6">
 <small class="text-muted d-block">اسم المفتاح</small>
 <strong><?= admin_e($k['name']) ?></strong>
 </div>
 <div class="col-md-3">
 <small class="text-muted d-block">حد الطلبات</small>
 <strong><?= (int) $k['rate_limit_per_minute'] ?> طلب / دقيقة</strong>
 </div>
 <div class="col-md-3">
 <small class="text-muted d-block">إجمالي الطلبات</small>
 <strong class="text-primary"><?= number_format((int) $k['requests_count']) ?> طلب</strong>
 </div>
 </div>

 <!-- Secret Token Copy Row -->
 <div class="mb-3 text-end" dir="rtl">
 <label class="form-label small fw-bold">الرمز السري الكامل (Secret Token):</label>
 <div class="input-group" dir="ltr">
 <input type="text" id="tokenField_<?= $k['id'] ?>" class="form-control font-monospace bg-light" value="<?= admin_e($k['secret_token']) ?>" readonly>
 <button class="btn btn-primary fw-bold" type="button" onclick="navigator.clipboard.writeText(document.getElementById('tokenField_<?= $k['id'] ?>').value); alert('تم نسخ الرمز السري بنجاح!');">
 <i class="bi bi-clipboard me-1"></i> نسخ
 </button>
 </div>
 </div>

 <!-- Granted Scopes -->
 <div class="mb-3 text-end" dir="rtl">
 <label class="form-label small fw-bold">الصلاحيات الممنوحة:</label>
 <div class="d-flex flex-wrap gap-2">
 <?php foreach ($scopes as $sc): ?>
 <span class="badge bg-primary-subtle text-primary border p-2">
 <i class="bi bi-check-circle-fill me-1"></i> <?= admin_e($allScopes[$sc] ?? $sc) ?>
 </span>
 <?php endforeach; ?>
 </div>
 </div>

 <!-- Code Snippets for AI Integration -->
 <div class="p-3 bg-dark text-white rounded-3 mt-3">
 <small class="text-light text-opacity-75 d-block mb-1">cURL Command (Direct Test):</small>
 <pre class="small text-warning mb-0 font-monospace" style="overflow-x:auto"><code>curl -X GET "<?= admin_e(rtrim(app_url(), '/')) ?>/api/v1/stats" \
 -H "Authorization: Bearer <?= admin_e($k['secret_token']) ?>" \
 -H "Accept: application/json"</code></pre>
 </div>
 </div>
 <div class="modal-footer bg-light border-top">
 <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إغلاق</button>
 </div>
 </div>
 </div>
 </div>

 <!-- ================= MODAL: EDIT KEY ================= -->
 <div class="modal fade" id="editKeyModal_<?= $k['id'] ?>" tabindex="-1">
 <div class="modal-dialog modal-lg modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow">
 <div class="modal-header bg-light border-bottom py-3">
 <h6 class="modal-title fw-bold text-dark">
 <i class="bi bi-pencil-square text-primary me-2"></i>تعديل المفتاح: <?= admin_e($k['name']) ?>
 </h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <form method="post" action="<?= admin_e(app_url('admin/api-keys/' . $k['id'] . '/update')) ?>">
 <?= CSRF::field() ?>
 <div class="modal-body p-4">
 <div class="row g-3 mb-3">
 <div class="col-md-6">
 <label class="form-label fw-bold">اسم المفتاح <span class="text-danger">*</span></label>
 <input type="text" name="name" class="form-control" value="<?= admin_e($k['name']) ?>" required>
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">حد الطلبات / دقيقة</label>
 <input type="number" name="rate_limit_per_minute" class="form-control" value="<?= (int) $k['rate_limit_per_minute'] ?>" min="10" max="1000">
 </div>
 <div class="col-md-3">
 <label class="form-label fw-bold">تمديد الانتهاء</label>
 <select name="expires_days" class="form-select">
 <option value="0">دائم (بدون انتهاء)</option>
 <option value="30">30 يوماً إضافية</option>
 <option value="90">90 يوماً</option>
 <option value="365">سنة كاملة</option>
 </select>
 </div>
 </div>

 <div class="mb-3">
 <label class="form-label fw-bold d-block mb-2">تعديل الصلاحيات (Scopes):</label>
 <div class="row g-2 p-3 bg-light rounded-3 border" style="max-height:220px;overflow-y:auto">
 <?php foreach ($allScopes as $scopeKey => $scopeLabel): ?>
 <div class="col-md-6">
 <div class="form-check">
 <input class="form-check-input" type="checkbox" name="scopes[]" value="<?= $scopeKey ?>" id="edit_scope_<?= $k['id'] ?>_<?= $scopeKey ?>" <?= in_array($scopeKey, $scopes) ? 'checked' : '' ?>>
 <label class="form-check-label small fw-semibold" for="edit_scope_<?= $k['id'] ?>_<?= $scopeKey ?>">
 <?= admin_e($scopeLabel) ?>
 </label>
 </div>
 </div>
 <?php endforeach; ?>
 </div>
 </div>

 <div class="p-3 bg-light rounded-3 border">
 <div class="form-check form-switch mb-0">
 <input class="form-check-input" type="checkbox" name="is_active" id="active_<?= $k['id'] ?>" value="1" <?= !empty($k['is_active']) ? 'checked' : '' ?>>
 <label class="form-check-label fw-bold" for="active_<?= $k['id'] ?>">المفتاح فعال ويقبل الطلبات</label>
 </div>
 </div>
 </div>
 <div class="modal-footer bg-light border-top">
 <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">حفظ التعديلات</button>
 </div>
 </form>
 </div>
 </div>
 </div>

 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
</div>

<!-- AI Agent & Developer Integration Playground Guide -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4">
 <h5 class="fw-bold mb-3"><i class="bi bi-robot text-primary me-2"></i>دليل تكامل واستخدام الذكاء الاصطناعي (AI Agent Quickstart)</h5>
 <p class="text-muted small mb-4">يمكن لأي نموذج ذكاء اصطناعي (مثل GPT-4, Claude, Gemini, أو سكريبت بايثون مستقل) استخدام نقاط النهاية التالية لجلب الأخبار وترجمتها ونشرها تلقائياً بالكامل.</p>

 <div class="row g-4">
 <!-- 1. Auto Publish Example -->
 <div class="col-lg-6">
 <div class="p-3 bg-dark text-white rounded-3">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="badge bg-success">POST /api/v1/articles/ai-publish</span>
 <small class="text-light text-opacity-75">نشر وترجمة فورية بالذكاء الاصطناعي</small>
 </div>
 <pre class="small text-light mb-0 font-monospace" style="overflow-x:auto;direction:ltr;text-align:left"><code>curl -X POST <?= admin_e(app_url('api/v1/articles/ai-publish')) ?> \
 -H "Authorization: Bearer YOUR_API_KEY" \
 -H "Content-Type: application/json" \
 -d '{
 "title_en": "OpenAI Unveils Breakthrough AI Reasoning Model",
 "content_en": "OpenAI has officially launched its new reasoning model...",
 "source_name": "TechCrunch",
 "source_url": "https://techcrunch.com/...",
 "category_id": 5
 }'</code></pre>
 </div>
 </div>

 <!-- 2. Pull RSS items Example -->
 <div class="col-lg-6">
 <div class="p-3 bg-dark text-white rounded-3">
 <div class="d-flex justify-content-between align-items-center mb-2">
 <span class="badge bg-primary">GET /api/v1/rss-feeds/pull?source_id=1</span>
 <small class="text-light text-opacity-75">سحب الأخبار الحية لتحليلها بالـ AI</small>
 </div>
 <pre class="small text-light mb-0 font-monospace" style="overflow-x:auto;direction:ltr;text-align:left"><code>curl -X GET "<?= admin_e(app_url('api/v1/rss-feeds/pull?source_id=1')) ?>" \
 -H "Authorization: Bearer YOUR_API_KEY"</code></pre>
 </div>
 </div>
 </div>
</div>
