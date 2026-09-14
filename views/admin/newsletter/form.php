<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">إنشاء حملة نشرة بريدية</h2>
 <p class="text-muted mb-0">كتابة وتجهيز محتوى الرسالة البريدية لإرسالها للمشتركين.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/newsletter')) ?>" class="btn btn-outline-secondary">
 ← العودة لقائمة الحملات
 </a>
</div>

<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="card-header bg-transparent py-3 border-bottom">
 <h6 class="mb-0 fw-bold">رسالة النشرة البريدية</h6>
 </div>
 <div class="card-body p-4">
 <form method="post" action="<?= admin_e(app_url('admin/newsletter/store')) ?>">
 <?= CSRF::field() ?>

 <div class="mb-3">
 <label class="form-label fw-bold" for="subject">عنوان موضوع الرسالة (Subject) <span class="text-danger">*</span></label>
 <input type="text" class="form-control form-control-lg" id="subject" name="subject" required placeholder="مثال: أهم 5 منشورات هذا الأسبوع ">
 </div>

 <div class="mb-4">
 <label class="form-label fw-bold" for="body_html">محتوى البريد الكامل (HTML/Text) <span class="text-danger">*</span></label>
 <textarea class="form-control font-monospace" id="body_html" name="body_html" rows="12" required placeholder="اكتب نص أو أكواد HTML الخاصة بالنشرة البريدية..."></textarea>
 </div>

 <div class="d-flex justify-content-end gap-2">
 <a href="<?= admin_e(app_url('admin/newsletter')) ?>" class="btn btn-light px-4">إلغاء</a>
 <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
 حفظ كمسودة
 </button>
 </div>
 </form>
 </div>
</div>
