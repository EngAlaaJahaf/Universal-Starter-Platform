<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1">بدء تغطية حية جديدة</h2>
 </div>
 <a href="<?= app_url('admin/live-blog') ?>" class="btn btn-outline-secondary">← العودة للتغطيات</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
 <div class="card-body p-4">
 <form action="<?= app_url('admin/live-blog/store') ?>" method="post">
 <?= CSRF::field() ?>

 <div class="row g-3">
 <div class="col-md-8">
 <label class="form-label fw-bold">عنوان التغطية (بالعربية)</label>
 <input type="text" name="title_ar" class="form-control" placeholder="مثال: تغطية حية لمؤتمر Apple وإطلاق هواتف آيفون الجديدة" required>
 </div>
 <div class="col-md-4">
 <label class="form-label fw-bold">الحالة</label>
 <select name="status" class="form-select">
 <option value="active">مباشر الآن (Active Live)</option>
 <option value="ended">منتهي (Ended)</option>
 </select>
 </div>
 </div>

 <hr class="my-4">
 <button type="submit" class="btn btn-danger px-4 py-2 fw-bold">بدء التغطية الحية</button>
 </form>
 </div>
</div>
