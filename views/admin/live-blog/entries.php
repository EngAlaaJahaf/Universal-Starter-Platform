<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">إدارة تدوينات البث: <?= htmlspecialchars($blog['title_ar']) ?></h2>
 <p class="text-muted mb-0">نشر التحديثات اللحظية، رفع الصور والفيديوهات، والتحكم الفوري في التعديل والتثبيت.</p>
 </div>
 <div class="d-flex gap-2">
 <a href="<?= app_url('live-blog/' . $blog['id']) ?>" target="_blank" class="btn btn-outline-dark fw-bold d-inline-flex align-items-center gap-1">
 <?= ui_icon('live', '', 16) ?>
 <span>معاينة البث المباشر للزوار ↗</span>
 </a>
 <a href="<?= app_url('admin/live-blog') ?>" class="btn btn-outline-secondary">← العودة لقائمة التغطيات</a>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" id="flash-alert-box">
 <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>
<?php if ($err = Session::getFlash('error')): ?>
 <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm">
 <?= htmlspecialchars($err) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- Quick Publish Box with Clipboard & Media Upload -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background:var(--bg-surface, #ffffff);border:1px solid rgba(0,0,0,0.06)">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h5 class="card-title mb-0 fw-bold text-danger d-flex align-items-center gap-2">
 <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;box-shadow:0 0 8px #ef4444"></span>
 <span>إضافة تدوينة سريعة للبث المباشر</span>
 </h5>
 <span class="badge bg-light text-muted border">دعم اللصق المباشر (Ctrl+V)</span>
 </div>
 <div class="card-body p-4">
 <form action="<?= app_url('admin/live-blog/' . $blog['id'] . '/entry/store') ?>" method="post" id="liveEntryCreateForm">
 <?= CSRF::field() ?>

 <div class="row g-3">
 <div class="col-12">
 <label class="form-label fw-bold">نص التدوينة أو التحديث اللحظي</label>
 <textarea name="content_ar" id="live_entry_content" class="form-control" rows="3" placeholder="اكتب الخبر أو التحديث هنا... (يمكنك الضغط على Ctrl+V في أي وقت للصق صورة من الحافظة مباشرة)" required></textarea>
 </div>

 <div class="col-md-3">
 <label class="form-label fw-bold">نوع التدوينة</label>
 <select name="entry_type" id="live_entry_type" class="form-select">
 <option value="text">نص فقط (Text)</option>
 <option value="image">صورة (Image)</option>
 <option value="video">فيديو (Video)</option>
 <option value="tweet">تغريدة / منشور (Social)</option>
 </select>
 </div>

 <div class="col-md-6">
 <label class="form-label fw-bold">رابط الوسائط أو الملف المرفوع</label>
 <div class="input-group">
 <input type="text" name="media_url" id="live_entry_media_url" class="form-control" placeholder="رابط صورة / فيديو أو ارفع من جهازك مباشرة...">
 <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1" id="btn-trigger-file-upload" title="اختر ملف من جهازك">
 <?= ui_icon('upload', '', 15) ?>
 <span>رفع ملف</span>
 </button>
 </div>
 <input type="file" id="media-file-input" class="d-none" accept="image/*,video/*">
 </div>

 <div class="col-md-3 d-flex align-items-end">
 <div class="p-2 px-3 bg-light rounded-3 border w-100 d-flex align-items-center justify-content-between" style="min-height:38px">
 <label class="form-check-label fw-bold mb-0 cursor-pointer d-inline-flex align-items-center gap-1" for="is_pinned">
 <?= ui_icon('pin', 'text-primary', 15) ?>
 <span>تثبيت في الأعلى</span>
 </label>
 <input class="form-check-input mt-0" type="checkbox" name="is_pinned" id="is_pinned" value="1" style="width:20px;height:20px;cursor:pointer">
 </div>
 </div>

 <!-- Media Preview Box -->
 <div class="col-12 d-none" id="media-preview-container">
 <div class="p-3 bg-light rounded-3 border position-relative d-inline-block">
 <span class="badge bg-dark mb-2" id="media-preview-badge">معاينة المرفق</span>
 <div id="media-preview-target"></div>
 <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle d-flex align-items-center justify-content-center" id="btn-remove-preview" title="إزالة المرفق" style="width:28px;height:28px;padding:0">
 <?= ui_icon('close', '', 14) ?>
 </button>
 </div>
 </div>

 <!-- Drop & Paste Zone -->
 <div class="col-12">
 <div id="media-drop-zone" class="p-3 border border-2 border-dashed rounded-3 text-center text-muted" style="cursor:pointer;background:#fafafa;transition:all 0.2s ease">
 <div style="margin-bottom:6px">
 <?= ui_icon('upload', 'text-primary', 26) ?>
 </div>
 <span class="small fw-bold">اسحب وأفلت صورة أو فيديو هنا، أو الصق من الحافظة (Ctrl+V) لرفعها فوراً</span>
 <div id="upload-progress-bar" class="progress mt-2 d-none" style="height:5px">
 <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" style="width: 100%"></div>
 </div>
 </div>
 </div>
 </div>

 <hr class="my-3">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
 <button type="submit" class="btn btn-danger btn-lg fw-bold px-4 shadow-sm d-inline-flex align-items-center gap-2">
 <?= ui_icon('flame', '', 18) ?>
 <span>نشر التدوينة فوراً في البث</span>
 </button>
 <small class="text-muted">ستظهر التدوينة للزوار في ثوانٍ دون الحاجة لتحديث الصفحة.</small>
 </div>
 </form>
 </div>
</div>

<!-- Published Entries Management List -->
<div class="card border-0 shadow-sm rounded-4">
 <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
 <h5 class="card-title mb-0 fw-bold">
 التدوينات المنشورة في هذا البث (<span id="entries-count-badge"><?= count($entries) ?></span>)
 </h5>
 <div class="text-muted small">
 الترتيب: المثبتة أولاً، ثم الأحدث نزولاً
 </div>
 </div>
 <div class="card-body p-4">
 <?php if (empty($entries)): ?>
 <div id="no-entries-admin-msg" class="text-center py-5 text-muted">
 <div style="margin-bottom:10px">
 <?= ui_icon('live', 'text-muted', 32) ?>
 </div>
 <h6>لا توجد تدوينات بعد في هذا البث.</h6>
 <p class="small">ابدأ بكتابة أول خبر أو إعلان من النموذج أعلاه.</p>
 </div>
 <?php else: ?>
 <div class="d-flex flex-column gap-3" id="admin-entries-container">
 <?php foreach ($entries as $e): ?>
 <div class="p-3 bg-white rounded-3 border shadow-sm position-relative entry-card-item <?= $e['is_pinned'] ? 'border-primary border-2 is-pinned-card' : '' ?>" id="entry-card-<?= $e['id'] ?>">
 <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
 <div class="d-flex align-items-center gap-2 flex-wrap">
 <span class="badge bg-dark font-monospace"><?= htmlspecialchars(fmt_date($e['created_at'], 'H:i:s')) ?></span>
 <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1">
 <?php if ($e['entry_type'] === 'image'): ?>
 <?= ui_icon('image', 'text-primary', 13) ?> <span>صورة</span>
 <?php elseif ($e['entry_type'] === 'video'): ?>
 <?= ui_icon('video', 'text-danger', 13) ?> <span>فيديو</span>
 <?php elseif ($e['entry_type'] === 'tweet'): ?>
 <?= ui_icon('twitter', 'text-info', 13) ?> <span>تغريدة</span>
 <?php else: ?>
 <?= ui_icon('file', 'text-muted', 13) ?> <span>نص</span>
 <?php endif; ?>
 </span>
 <span class="badge bg-primary pin-status-badge <?= $e['is_pinned'] ? '' : 'd-none' ?>">
 <?= ui_icon('pin', '', 12) ?> <span>مثبت في الأعلى</span>
 </span>
 <small class="text-muted">بواسطة: <?= htmlspecialchars($e['author_name'] ?: 'محرر البث') ?></small>
 </div>

 <!-- Unified Action Buttons with SVG Icons -->
 <div class="d-flex align-items-center gap-1">
 <!-- AJAX Pin Button -->
 <button type="button" class="btn btn-sm btn-ajax-pin d-inline-flex align-items-center gap-1 <?= $e['is_pinned'] ? 'btn-primary' : 'btn-outline-secondary' ?>" 
 data-id="<?= $e['id'] ?>"
 title="<?= $e['is_pinned'] ? 'إلغاء التثبيت' : 'تثبيت في أعلى البث' ?>"
 style="font-size:0.82rem;padding:4px 10px;border-radius:6px;font-weight:600">
 <?= ui_icon('pin', '', 13) ?>
 <span class="pin-btn-label"><?= $e['is_pinned'] ? 'مثبت' : 'تثبيت' ?></span>
 </button>

 <!-- Edit Modal Trigger -->
 <button type="button" class="btn btn-sm btn-outline-warning btn-edit-entry d-inline-flex align-items-center gap-1" 
 data-id="<?= $e['id'] ?>"
 data-content="<?= htmlspecialchars($e['content_ar'], ENT_QUOTES) ?>"
 data-type="<?= htmlspecialchars($e['entry_type']) ?>"
 data-media="<?= htmlspecialchars($e['media_url'] ?? '') ?>"
 data-pinned="<?= (int)$e['is_pinned'] ?>"
 style="font-size:0.82rem;padding:4px 10px;border-radius:6px;font-weight:600"
 title="تعديل التدوينة">
 <?= ui_icon('edit', '', 13) ?>
 <span>تعديل</span>
 </button>

 <!-- AJAX Delete Button -->
 <button type="button" class="btn btn-sm btn-outline-danger btn-ajax-delete d-inline-flex align-items-center justify-content-center" 
 data-id="<?= $e['id'] ?>"
 style="font-size:0.82rem;padding:5px 8px;border-radius:6px"
 title="حذف التدوينة">
 <?= ui_icon('trash', '', 14) ?>
 </button>
 </div>
 </div>

 <p class="mb-2 entry-content-text" style="font-size:1rem;line-height:1.7;color:#1e293b"><?= nl2br(htmlspecialchars($e['content_ar'])) ?></p>

 <?php if (!empty($e['media_url'])): ?>
 <div class="mt-2 entry-media-preview" style="max-width:360px">
 <?php if ($e['entry_type'] === 'video' || preg_match('/\.(mp4|webm|ogv)$/i', $e['media_url'])): ?>
 <video src="<?= htmlspecialchars(str_starts_with($e['media_url'], 'http') ? $e['media_url'] : app_url($e['media_url'])) ?>" controls style="max-width:100%;border-radius:8px;max-height:220px"></video>
 <?php else: ?>
 <img src="<?= htmlspecialchars(str_starts_with($e['media_url'], 'http') ? $e['media_url'] : app_url($e['media_url'])) ?>" alt="مرفق التغطية" style="max-width:100%;border-radius:8px;max-height:200px;object-fit:cover">
 <?php endif; ?>
 </div>
 <?php endif; ?>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
 </div>
</div>

<!-- Live Reactions & Chat Moderation Card -->
<div class="row g-4 mb-4">
 <!-- Live Reactions Statistics -->
 <div class="col-lg-4">
 <div class="card border-0 shadow-sm rounded-4 h-100" style="background:var(--bg-surface, #ffffff);border:1px solid rgba(0,0,0,0.06)">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
 <?= ui_icon('sparkle', 'text-warning', 18) ?>
 <span>إحصائيات تفاعلات البث</span>
 </h5>
 </div>
 <div class="card-body p-4">
 <div class="d-flex flex-column gap-3">
 <?php
 $rxIcons = [
 'fire' => ['icon' => '', 'label' => 'حماسي (Fire)'],
 'clap' => ['icon' => '', 'label' => 'مذهل (Clap)'],
 'mindblown' => ['icon' => '', 'label' => 'صادم (Mindblown)'],
 'lightbulb' => ['icon' => '', 'label' => 'عبقري (Idea)'],
 'heart' => ['icon' => '', 'label' => 'أحبه (Heart)']
 ];
 $rxStatMap = [];
 foreach (($reactionStats ?? []) as $st) {
 $rxStatMap[$st['reaction_type']] = (int) $st['total'];
 }
 foreach ($rxIcons as $k => $info):
 $val = $rxStatMap[$k] ?? 0;
 ?>
 <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border">
 <span class="d-flex align-items-center gap-2">
 <span style="font-size:1.2rem"><?= $info['icon'] ?></span>
 <strong class="small"><?= $info['label'] ?></strong>
 </span>
 <span class="badge bg-dark font-monospace px-3 py-2" style="font-size:0.88rem"><?= $val ?></span>
 </div>
 <?php endforeach; ?>
 </div>
 </div>
 </div>
 </div>

 <!-- Live Stream Chat Moderation -->
 <div class="col-lg-8">
 <div class="card border-0 shadow-sm rounded-4 h-100" style="background:var(--bg-surface, #ffffff);border:1px solid rgba(0,0,0,0.06)">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
 <h5 class="card-title mb-0 fw-bold text-dark d-flex align-items-center gap-2">
 <?= ui_icon('chat', 'text-primary', 18) ?>
 <span>رسائل دردشة البث المباشر (إشراف فوري)</span>
 </h5>
 <span class="badge bg-light text-muted border">آخر 50 رسالة</span>
 </div>
 <div class="card-body p-3" style="max-height:460px;overflow-y:auto">
 <?php if (empty($chats)): ?>
 <div class="text-center py-5 text-muted">
 <div class="mb-2" style="font-size:2rem"></div>
 <p class="mb-0 small">لا توجد رسائل دردشة في هذا البث حتى الآن.</p>
 </div>
 <?php else: ?>
 <div class="d-flex flex-column gap-2" id="admin-chat-list">
 <?php foreach ($chats as $chat): ?>
 <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-start gap-3 chat-admin-item" id="admin-chat-msg-<?= $chat['id'] ?>">
 <div class="flex-grow-1">
 <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
 <strong class="text-primary small"><?= htmlspecialchars($chat['sender_name']) ?></strong>
 <small class="text-muted font-monospace" style="font-size:0.75rem"><?= fmt_date($chat['created_at'], 'H:i:s Y-m-d') ?></small>
 <?php if (!empty($chat['ip_address'])): ?>
 <span class="badge bg-secondary font-monospace" style="font-size:0.7rem"><?= htmlspecialchars($chat['ip_address']) ?></span>
 <?php endif; ?>
 <?php if (!empty($chat['entry_id'])): ?>
 <a href="#entry-card-<?= $chat['entry_id'] ?>" class="badge bg-primary text-dark text-decoration-none fw-bold" style="font-size:0.72rem">
 ↩ رد على التدوينة #<?= $chat['entry_id'] ?>
 </a>
 <?php else: ?>
 <span class="badge bg-light text-muted border" style="font-size:0.7rem">رسالة عامة للبث</span>
 <?php endif; ?>
 </div>

 <?php if (!empty($chat['entry_id']) && !empty($chat['reply_snippet'])): ?>
 <div class="mb-2 p-2 rounded-2" style="background:rgba(0,242,254,0.08);border-inline-start:3px solid #00f2fe;font-size:0.78rem">
 <span class="text-primary fw-bold">نص الفقرة المردود عليها:</span>
 <span class="text-muted">"<?= htmlspecialchars($chat['reply_snippet']) ?>..."</span>
 </div>
 <?php endif; ?>

 <div class="text-dark small" style="line-height:1.5">
 <?= htmlspecialchars($chat['message']) ?>
 </div>
 </div>
 <button type="button" class="btn btn-sm btn-outline-danger btn-admin-delete-chat flex-shrink-0 d-inline-flex align-items-center gap-1" data-id="<?= $chat['id'] ?>" title="حذف الرسالة">
 <?= ui_icon('trash', '', 13) ?>
 <span>حذف</span>
 </button>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
 </div>
 </div>
 </div>
</div>

<!-- Enhanced Edit Entry Bootstrap Modal with Interactive Media & Paste Support -->
<div class="modal fade" id="editEntryModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered">
 <div class="modal-content rounded-4 border-0 shadow">
 <form id="editEntryForm" action="" method="post">
 <?= CSRF::field() ?>
 <div class="modal-header border-bottom py-3">
 <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
 <?= ui_icon('edit', 'text-primary', 18) ?>
 <span>تعديل تدوينة البث المباشر</span>
 </h5>
 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
 </div>
 <div class="modal-body p-4">
 <div class="mb-3">
 <label class="form-label fw-bold">محتوى التدوينة</label>
 <textarea name="content_ar" id="modal_edit_content" class="form-control" rows="4" placeholder="اكتب التحديث المعدل هنا... (يمكنك الضغط على Ctrl+V للصق صورة جديدة)" required></textarea>
 </div>

 <div class="row g-3 mb-3">
 <div class="col-md-4">
 <label class="form-label fw-bold">نوع التدوينة</label>
 <select name="entry_type" id="modal_edit_type" class="form-select">
 <option value="text">نص فقط</option>
 <option value="image">صورة</option>
 <option value="video">فيديو</option>
 <option value="tweet">تغريدة / منشور</option>
 </select>
 </div>
 <div class="col-md-8">
 <label class="form-label fw-bold">رابط الوسائط المرفقة</label>
 <div class="input-group">
 <input type="text" name="media_url" id="modal_edit_media" class="form-control" placeholder="رابط صورة / فيديو أو ارفع بديل...">
 <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-1" id="btn-modal-trigger-upload" title="رفع ملف بديل">
 <?= ui_icon('upload', '', 14) ?>
 <span>رفع بديل</span>
 </button>
 </div>
 <input type="file" id="modal-file-input" class="d-none" accept="image/*,video/*">
 </div>
 </div>

 <!-- Modal Media Interactive Preview Box with Delete/Replace Button -->
 <div class="mb-3 d-none" id="modal-preview-container">
 <label class="form-label fw-bold">المرفق الحالي</label>
 <div class="p-3 bg-light rounded-3 border position-relative d-inline-block w-100" style="max-width:420px">
 <div id="modal-preview-target"></div>
 <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 rounded-circle d-flex align-items-center justify-content-center shadow-sm" id="btn-modal-remove-media" title="إزالة المرفق" style="width:28px;height:28px;padding:0">
 <?= ui_icon('close', '', 14) ?>
 </button>
 </div>
 </div>

 <!-- Modal Paste/Drop Hint -->
 <div class="p-2 border border-dashed rounded-3 text-center text-muted mb-3 bg-light small d-flex align-items-center justify-content-center gap-2" id="modal-drop-zone">
 <?= ui_icon('image', 'text-primary', 16) ?>
 <span>يمكنك لصق صورة جديدة مباشرة عبر <strong>Ctrl+V</strong> لاستبدال المرفق الحالي فوراً</span>
 </div>

 <!-- Fixed RTL Pin Checkbox Container -->
 <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
 <label class="form-check-label fw-bold mb-0 cursor-pointer d-inline-flex align-items-center gap-2" for="modal_edit_pinned">
 <?= ui_icon('pin', 'text-primary', 16) ?>
 <span>تثبيت في أعلى البث المباشر</span>
 </label>
 <input class="form-check-input mt-0" type="checkbox" name="is_pinned" id="modal_edit_pinned" value="1" style="width:22px;height:22px;cursor:pointer">
 </div>
 </div>
 <div class="modal-footer border-top py-3">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
 <button type="submit" class="btn btn-primary fw-bold px-4 d-inline-flex align-items-center gap-1">
 <?= ui_icon('save', '', 15) ?>
 <span>حفظ التعديلات فوراً</span>
 </button>
 </div>
 </form>
 </div>
 </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
 const csrfToken = document.querySelector('input[name="_csrf"]').value;

 // --- Main Create Form Elements ---
 const mediaInput = document.getElementById('live_entry_media_url');
 const typeSelect = document.getElementById('live_entry_type');
 const previewContainer = document.getElementById('media-preview-container');
 const previewTarget = document.getElementById('media-preview-target');
 const removePreviewBtn = document.getElementById('btn-remove-preview');
 const dropZone = document.getElementById('media-drop-zone');
 const fileInput = document.getElementById('media-file-input');
 const triggerFileBtn = document.getElementById('btn-trigger-file-upload');
 const progressBar = document.getElementById('upload-progress-bar');

 // Trigger file dialog
 triggerFileBtn.addEventListener('click', () => fileInput.click());
 dropZone.addEventListener('click', (e) => {
 if (e.target !== removePreviewBtn) fileInput.click();
 });

 fileInput.addEventListener('change', () => {
 if (fileInput.files && fileInput.files[0]) {
 uploadFile(fileInput.files[0], (url, fullUrl, type) => {
 setMainMedia(url, fullUrl, type);
 });
 }
 });

 // Main Drag & Drop
 ['dragenter', 'dragover'].forEach(eventName => {
 dropZone.addEventListener(eventName, (e) => {
 e.preventDefault();
 e.stopPropagation();
 dropZone.classList.add('border-danger', 'bg-white');
 }, false);
 });

 ['dragleave', 'drop'].forEach(eventName => {
 dropZone.addEventListener(eventName, (e) => {
 e.preventDefault();
 e.stopPropagation();
 dropZone.classList.remove('border-danger', 'bg-white');
 }, false);
 });

 dropZone.addEventListener('drop', (e) => {
 const dt = e.dataTransfer;
 const files = dt.files;
 if (files && files.length> 0) {
 uploadFile(files[0], (url, fullUrl, type) => {
 setMainMedia(url, fullUrl, type);
 });
 }
 });

 // --- Modal Elements ---
 const editModalEl = document.getElementById('editEntryModal');
 const editModal = new bootstrap.Modal(editModalEl);
 const editForm = document.getElementById('editEntryForm');
 const modalContent = document.getElementById('modal_edit_content');
 const modalType = document.getElementById('modal_edit_type');
 const modalMedia = document.getElementById('modal_edit_media');
 const modalPinned = document.getElementById('modal_edit_pinned');
 const modalPreviewContainer = document.getElementById('modal-preview-container');
 const modalPreviewTarget = document.getElementById('modal-preview-target');
 const modalRemoveMediaBtn = document.getElementById('btn-modal-remove-media');
 const modalTriggerUploadBtn = document.getElementById('btn-modal-trigger-upload');
 const modalFileInput = document.getElementById('modal-file-input');

 modalTriggerUploadBtn.addEventListener('click', () => modalFileInput.click());
 modalFileInput.addEventListener('change', () => {
 if (modalFileInput.files && modalFileInput.files[0]) {
 uploadFile(modalFileInput.files[0], (url, fullUrl, type) => {
 setModalMedia(url, fullUrl, type);
 });
 }
 });

 modalRemoveMediaBtn.addEventListener('click', () => {
 modalMedia.value = '';
 modalPreviewContainer.classList.add('d-none');
 modalPreviewTarget.innerHTML = '';
 modalType.value = 'text';
 });

 // --- Unified Clipboard Paste Listener (Ctrl+V) ---
 window.addEventListener('paste', function(e) {
 const isModalOpen = editModalEl.classList.contains('show');
 const items = (e.clipboardData || e.originalEvent.clipboardData).items;
 for (let item of items) {
 if (item.type.indexOf('image') !== -1) {
 const file = item.getAsFile();
 const reader = new FileReader();
 reader.onload = function(event) {
 uploadBase64(event.target.result, (url, fullUrl, type) => {
 if (isModalOpen) {
 setModalMedia(url, fullUrl, type);
 } else {
 setMainMedia(url, fullUrl, type);
 }
 });
 };
 reader.readAsDataURL(file);
 break;
 }
 }
 });

 function setMainMedia(relativeUrl, fullUrl, type) {
 mediaInput.value = relativeUrl;
 typeSelect.value = type === 'video' ? 'video' : 'image';
 previewContainer.classList.remove('d-none');

 if (type === 'video') {
 previewTarget.innerHTML = `<video src="${fullUrl}" controls style="max-width:320px;max-height:180px;border-radius:6px"></video>`;
 } else {
 previewTarget.innerHTML = `<img src="${fullUrl}" style="max-width:320px;max-height:180px;border-radius:6px;object-fit:cover">`;
 }
 }

 function setModalMedia(relativeUrl, fullUrl, type) {
 modalMedia.value = relativeUrl;
 modalType.value = type === 'video' ? 'video' : 'image';
 modalPreviewContainer.classList.remove('d-none');

 if (type === 'video') {
 modalPreviewTarget.innerHTML = `<video src="${fullUrl}" controls style="max-width:100%;max-height:220px;border-radius:6px"></video>`;
 } else {
 modalPreviewTarget.innerHTML = `<img src="${fullUrl}" style="max-width:100%;max-height:220px;border-radius:6px;object-fit:cover">`;
 }
 }

 removePreviewBtn.addEventListener('click', () => {
 mediaInput.value = '';
 previewContainer.classList.add('d-none');
 previewTarget.innerHTML = '';
 typeSelect.value = 'text';
 });

 function uploadBase64(base64Str, callback) {
 showMainProgress(true);
 const formData = new FormData();
 formData.append('_csrf', csrfToken);
 formData.append('base64_data', base64Str);

 fetch('<?= app_url("admin/live-blog/upload-media") ?>', {
 method: 'POST',
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 showMainProgress(false);
 if (data.success && callback) {
 callback(data.url, data.full_url, 'image');
 } else {
 alert(data.message || 'فشل رفع الصورة الملصقة.');
 }
 })
 .catch(() => {
 showMainProgress(false);
 alert('حدث خطأ أثناء رفع الصورة.');
 });
 }

 function uploadFile(file, callback) {
 showMainProgress(true);
 const formData = new FormData();
 formData.append('_csrf', csrfToken);
 formData.append('file', file);

 fetch('<?= app_url("admin/live-blog/upload-media") ?>', {
 method: 'POST',
 body: formData
 })
 .then(res => res.json())
 .then(data => {
 showMainProgress(false);
 if (data.success && callback) {
 callback(data.url, data.full_url, data.type);
 } else {
 alert(data.message || 'فشل رفع الملف.');
 }
 })
 .catch(() => {
 showMainProgress(false);
 alert('حدث خطأ أثناء رفع الملف.');
 });
 }

 function showMainProgress(show) {
 if (show) progressBar.classList.remove('d-none');
 else progressBar.classList.add('d-none');
 }

 // --- Edit Modal Population & Image/Video Display ---
 document.querySelectorAll('.btn-edit-entry').forEach(btn => {
 btn.addEventListener('click', function() {
 const id = this.getAttribute('data-id');
 const content = this.getAttribute('data-content');
 const type = this.getAttribute('data-type');
 const media = this.getAttribute('data-media');
 const pinned = this.getAttribute('data-pinned') === '1';

 editForm.action = '<?= app_url("admin/live-blog/entry/") ?>' + id + '/update';
 modalContent.value = content;
 modalType.value = type;
 modalMedia.value = media;
 modalPinned.checked = pinned;

 if (media) {
 const fullUrl = media.startsWith('http') ? media : '<?= app_url("") ?>' + media;
 setModalMedia(media, fullUrl, type);
 } else {
 modalPreviewContainer.classList.add('d-none');
 modalPreviewTarget.innerHTML = '';
 }

 editModal.show();
 });
 });

 // --- Instant AJAX Pin/Unpin Toggle (No Page Refresh) ---
 document.querySelectorAll('.btn-ajax-pin').forEach(btn => {
 btn.addEventListener('click', function(e) {
 e.preventDefault();
 const id = this.getAttribute('data-id');
 const card = document.getElementById('entry-card-' + id);
 const badge = card ? card.querySelector('.pin-status-badge') : null;
 const label = this.querySelector('.pin-btn-label');

 const formData = new FormData();
 formData.append('_csrf', csrfToken);
 formData.append('ajax', '1');

 this.disabled = true;

 fetch('<?= app_url("admin/live-blog/entry/") ?>' + id + '/toggle-pin', {
 method: 'POST',
 body: formData,
 headers: { 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(res => res.json())
 .then(data => {
 this.disabled = false;
 if (data.success) {
 if (data.is_pinned) {
 this.classList.remove('btn-outline-secondary');
 this.classList.add('btn-primary');
 if (label) label.textContent = 'مثبت';
 if (badge) badge.classList.remove('d-none');
 if (card) card.classList.add('border-primary', 'border-2', 'is-pinned-card');
 } else {
 this.classList.remove('btn-primary');
 this.classList.add('btn-outline-secondary');
 if (label) label.textContent = 'تثبيت';
 if (badge) badge.classList.add('d-none');
 if (card) card.classList.remove('border-primary', 'border-2', 'is-pinned-card');
 }
 } else {
 alert(data.message || 'حدث خطأ أثناء تغيير التثبيت.');
 }
 })
 .catch(() => {
 this.disabled = false;
 alert('فشل الاتصال بالخادم.');
 });
 });
 });

 // --- Instant AJAX Delete (No Page Refresh) ---
 document.querySelectorAll('.btn-ajax-delete').forEach(btn => {
 btn.addEventListener('click', function(e) {
 e.preventDefault();
 if (!confirm('هل تريد حذف هذه التدوينة نهائياً من البث؟')) return;

 const id = this.getAttribute('data-id');
 const card = document.getElementById('entry-card-' + id);

 const formData = new FormData();
 formData.append('_csrf', csrfToken);
 formData.append('ajax', '1');

 this.disabled = true;

 fetch('<?= app_url("admin/live-blog/entry/") ?>' + id + '/delete', {
 method: 'POST',
 body: formData,
 headers: { 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(res => res.json())
 .then(data => {
 if (data.success) {
 if (card) {
 card.style.transition = 'all 0.3s ease';
 card.style.opacity = '0';
 card.style.transform = 'scale(0.95)';
 setTimeout(() => {
 card.remove();
 const remaining = document.querySelectorAll('.entry-card-item').length;
 const countBadge = document.getElementById('entries-count-badge');
 if (countBadge) countBadge.textContent = remaining;

 if (remaining === 0) {
 const container = document.getElementById('admin-entries-container');
 if (container) {
 container.innerHTML = `
 <div id="no-entries-admin-msg" class="text-center py-5 text-muted">
 <div style="margin-bottom:10px"><?= ui_icon('live', 'text-muted', 32) ?></div>
 <h6>لا توجد تدوينات بعد في هذا البث.</h6>
 <p class="small">ابدأ بكتابة أول خبر أو إعلان من النموذج أعلاه.</p>
 </div>
 `;
 }
 }
 }, 300);
 }
 } else {
 this.disabled = false;
 alert(data.message || 'حدث خطأ أثناء الحذف.');
 }
 })
 .catch(() => {
 this.disabled = false;
 alert('فشل الاتصال بالخادم.');
 });
 });
 });

 // --- Instant AJAX Admin Live Chat Delete ---
 document.querySelectorAll('.btn-admin-delete-chat').forEach(btn => {
 btn.addEventListener('click', function(e) {
 e.preventDefault();
 if (!confirm('هل تريد بالتأكيد حذف هذه الرسالة من الدردشة الحية؟')) return;

 const id = this.getAttribute('data-id');
 const item = document.getElementById('admin-chat-msg-' + id);

 const formData = new FormData();
 formData.append('_csrf', csrfToken);
 formData.append('ajax', '1');

 this.disabled = true;

 fetch('<?= app_url("admin/live-blog/chat/") ?>' + id + '/delete', {
 method: 'POST',
 body: formData,
 headers: { 'X-Requested-With': 'XMLHttpRequest' }
 })
 .then(res => res.json())
 .then(data => {
 if (data.success) {
 if (item) {
 item.style.transition = 'all 0.3s ease';
 item.style.opacity = '0';
 item.style.transform = 'scale(0.95)';
 setTimeout(() => item.remove(), 300);
 }
 } else {
 this.disabled = false;
 alert(data.message || 'حدث خطأ أثناء حذف الرسالة.');
 }
 })
 .catch(() => {
 this.disabled = false;
 alert('فشل الاتصال بالخادم.');
 });
 });
 });
});
</script>
