<?php
// Relative "ago" formatter for the per-feed fresh-news signal.
if (!function_exists('rss_new_ago')) {
function rss_new_ago($dt) {
$ts = is_numeric($dt) ? (int) $dt : strtotime((string) $dt);
if (!$ts) return '';
$d = max(0, time() - $ts);
if ($d < 60) return 'الآن';
$m = (int) floor($d / 60);
if ($m < 60) {
if ($m === 1) return 'قبل دقيقة';
if ($m === 2) return 'قبل دقيقتين';
if ($m <= 10) return 'قبل ' . $m . ' دقائق';
return 'قبل ' . $m . ' دقيقة';
}
$h = (int) floor($m / 60);
if ($h < 24) {
if ($h === 1) return 'قبل ساعة';
if ($h === 2) return 'قبل ساعتين';
if ($h <= 10) return 'قبل ' . $h . ' ساعات';
return 'قبل ' . $h . ' ساعة';
}
$days = (int) floor($h / 24);
if ($days === 1) return 'قبل يوم';
if ($days === 2) return 'قبل يومين';
if ($days <= 10) return 'قبل ' . $days . ' أيام';
return date('Y-m-d', $ts);
}
}
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-rss-fill text-warning me-2"></i>إدارة مصادر خلاصات الأخبار (RSS Sources)</h2>
 <p class="text-muted mb-0">إضافة وتعديل وحذف وتصنيف مصادر الأخبار التقنية المعتمدة في المنصة.</p>
 </div>
 <div class="d-flex gap-2 flex-wrap">
 <button type="button" id="btnCheckAllFeeds" class="btn btn-outline-primary fw-bold">
 <i class="bi bi-activity me-1"></i> فحص صحة جميع الخلاصات
 </button>
 <a href="<?= admin_e(app_url('admin/news-feeds')) ?>" class="btn btn-outline-secondary">
 <i class="bi bi-lightning-charge me-1"></i> استوديو استيراد الأخبار
 </a>
 <a href="<?= admin_e(app_url('admin/rss-sources/create')) ?>" class="btn btn-primary fw-bold shadow-sm">
 <i class="bi bi-plus-lg me-1"></i> إضافة مصدر جديد
 </a>
 </div>
</div>

<?php if ($msg = Session::getFlash('success')): ?>
 <div class="alert alert-success alert-dismissible fade show shadow-sm">
 <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
 <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
 </div>
<?php endif; ?>

<!-- ================= SMART INSTANT FETCHER & WEB SCRAPER HERO BOX ================= -->
<div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4" style="background:linear-gradient(135deg, rgba(0, 242, 254, 0.05), rgba(157, 78, 221, 0.05)); border: 1px solid rgba(0, 242, 254, 0.25)!important">
 <div class="row align-items-center g-3">
 <div class="col-lg-5">
 <div class="d-flex align-items-center gap-2 mb-1">
 <span class="badge bg-primary px-2 py-1"><i class="bi bi-magic me-1"></i>المستخرج التلقائي</span>
 <h5 class="fw-bold mb-0 text-dark">سحب الأخبار من أي رابط أو موقع ويب</h5>
 </div>
 <p class="text-muted small mb-0">
 الصق رابط <strong>أي موقع أو صفحة أخبار عادية (حتى لو لم تكن تملك خلاصة RSS)</strong> وسيقوم المحرك بتحليلها واستخراج بطاقات الأخبار والصور فوراً!
 </p>
 </div>
 <div class="col-lg-7">
 <form method="get" action="<?= admin_e(app_url('admin/news-feeds')) ?>" class="d-flex gap-2">
 <div class="input-group input-group-lg shadow-sm rounded-3 overflow-hidden w-100">
 <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-link-45deg fs-4 text-primary"></i></span>
 <input type="url" name="custom_url" class="form-control border-start-0 font-monospace fs-6" placeholder="https://example.com/news أو رابط أي موقع تقني..." required>
 <button type="submit" class="btn btn-primary px-4 fw-bold text-nowrap" style="background:linear-gradient(135deg,#00f2fe,#9d4edd);border:none">
 <i class="bi bi-lightning-charge-fill me-1"></i> سحب الأخبار
 </button>
 </div>
 </form>
 </div>
 </div>
</div>

<!-- ================= FEED HEALTH REPORT PANEL ================= -->
<div class="card border-0 shadow-sm rounded-4 mb-4 d-none" id="feedHealthPanel">
 <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
 <h5 class="fw-bold mb-0"><i class="bi bi-heart-pulse text-danger me-2"></i>تقرير صحة خلاصات RSS</h5>
 <div class="d-flex align-items-center gap-2">
 <span id="feedHealthSummary" class="small text-muted"></span>
 <button type="button" class="btn-close" onclick="document.getElementById('feedHealthPanel').classList.add('d-none')"></button>
 </div>
 </div>
 <div class="card-body pt-3">
 <div class="progress mb-3 d-none" id="feedHealthProgressWrap" style="height:6px">
 <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="feedHealthProgress" style="width:0%"></div>
 </div>
 <div id="feedHealthResults" class="d-flex flex-column gap-2"></div>
 </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
 <div class="card-body p-3">
 <form method="get" action="<?= admin_e(app_url('admin/rss-sources')) ?>" class="row g-2 align-items-center">
 <div class="col-md-6">
 <input type="text" name="q" value="<?= admin_e($q ?? '') ?>" class="form-control" placeholder="ابحث باسم المصدر أو الرابط...">
 </div>
 <div class="col-md-4">
 <select name="category_id" class="form-select">
 <option value="">جميع التصنيفات</option>
 <?php foreach ($categories as $cat): ?>
 <option value="<?= (int) $cat['id'] ?>" <?= ((int) ($catId ?? 0) === (int) $cat['id']) ? 'selected' : '' ?>>
 <?= admin_e($cat['name']) ?>
 </option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-md-2">
 <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> تصفية</button>
 </div>
 </form>
 </div>
</div>

<!-- Sources Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white">
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:60px">#</th>
 <th>اسم المصدر</th>
 <th>رابط خلاصة الـ RSS</th>
 <th>القسم المسند</th>
  <th>الحالة</th>
  <th style="width:150px">صحة الخلاصة</th>
  <th style="width:140px" title="عدد الأخبار الجديدة التي وصلت عبر الخلاصة ولم تُنشر بعد (تُحدَّث عند كل جلب فعلي) — اضغط الشارة لعرضها">
  <i class="bi bi-bell-fill text-success me-1"></i>جديد لم يُنشر
  </th>
  <th class="text-end" style="width:180px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($sources)): ?>
 <tr>
  <td colspan="8" class="text-center py-5 text-muted">
 <i class="bi bi-rss d-block mb-2" style="font-size:2rem"></i>
 لا توجد مصادر مطابقة لبحثك.
 </td>
 </tr>
 <?php else: ?>
 <?php foreach ($sources as $s): ?>
 <tr>
 <td><span class="text-muted small">#<?= (int) $s['id'] ?></span></td>
 <td>
 <strong class="text-dark d-block"><?= admin_e($s['name']) ?></strong>
 </td>
 <td>
 <code class="small text-primary font-monospace text-truncate d-inline-block" style="max-width:300px" title="<?= admin_e($s['url']) ?>">
 <?= admin_e($s['url']) ?>
 </code>
 </td>
 <td>
 <span class="badge bg-light text-dark border">
 <?= admin_e($s['category_name'] ?: 'عام') ?>
 </span>
 </td>
 <td>
 <span class="badge bg-<?= !empty($s['auto_fetch']) ? 'success' : 'secondary' ?>-subtle text-<?= !empty($s['auto_fetch']) ? 'success' : 'secondary' ?>">
 <?= !empty($s['auto_fetch']) ? 'نشط' : 'معطل' ?>
 </span>
 </td>
 <td>
 <?php
 $hStatus = $s['last_status'] ?? null;
 $hItems = (int) ($s['last_item_count'] ?? 0);
 $hCode = (int) ($s['last_http_code'] ?? 0);
 $hError = $s['last_error'] ?? '';
 $hFails = (int) ($s['fail_count'] ?? 0);
 $hWhen = $s['last_checked_at'] ?? null;
 ?>
 <?php if ($hStatus === 'ok'): ?>
 <span class="badge bg-success-subtle text-success border border-success-subtle"
 title="آخر فحص: <?= admin_e((string) $hWhen) ?> — HTTP <?= $hCode ?>">
 <i class="bi bi-check-circle-fill me-1"></i><?= $hItems ?> عنصر
 </span>
 <?php elseif ($hStatus === 'empty'): ?>
 <span class="badge bg-warning-subtle text-warning border border-warning-subtle"
 title="<?= admin_e($hError) ?>">
 <i class="bi bi-exclamation-triangle-fill me-1"></i>خلاصة فارغة
 </span>
 <?php elseif ($hStatus === 'failed'): ?>
 <span class="badge bg-danger-subtle text-danger border border-danger-subtle"
 title="<?= admin_e($hError) ?>">
 <i class="bi bi-x-circle-fill me-1"></i>فشل (<?= $hCode ?: 'شبكة' ?>)
 </span>
 <?php if ($hFails > 1): ?>
 <div class="small text-muted mt-1"><?= $hFails ?> محاولات فاشلة</div>
 <?php endif; ?>
 <?php else: ?>
 <span class="badge bg-light text-muted border">
 <i class="bi bi-dash-circle me-1"></i>لم يُفحص
 </span>
  <?php endif; ?>
  </td>
  <td>
  <?php
  // عدد الأخبار الجديدة غير المنشورة الواصلة من الخلاصة نفسها
  $feedStat = ($newMap ?? [])[$s['name']] ?? null;
  $feedNew = (int) ($s['new_items_count'] ?? ($feedStat['new_count'] ?? 0));
  $feedLatest = $s['new_items_last_at'] ?? ($feedStat['latest_at'] ?? null);
  ?>
  <?php if ($feedNew > 0): ?>
  <a href="<?= admin_e(app_url('admin/news-feeds?source_id=' . $s['id'])) ?>" class="text-decoration-none"
  title="عرض أحدث أخبار هذا المصدر في الاستوديو (الأخبار الجديدة غير المنشورة)">
  <span class="badge bg-success text-white border shadow-sm">
  <i class="bi bi-bell-fill me-1"></i>+<?= $feedNew ?> <?= $feedNew === 1 ? 'خبر جديد لم يُنشر' : ($feedNew === 2 ? 'خبران جديدان لم يُنشرا' : 'أخبار جديدة لم تُنشر') ?>
  </span>
  </a>
  <?php if (!empty($feedLatest)): ?>
  <div class="small text-muted mt-1">أحدثها <?= admin_e(rss_new_ago($feedLatest)) ?></div>
  <?php endif; ?>
  <?php else: ?>
  <span class="text-muted small">—</span>
  <?php endif; ?>
  </td>
  <td class="text-end">
 <div class="d-inline-flex gap-1">
 <button type="button" class="btn-action-icon btn-action-view btn-check-one-feed"
 data-id="<?= (int) $s['id'] ?>" title="فحص صحة هذه الخلاصة الآن">
 <i class="bi bi-activity"></i>
 </button>
 <a href="<?= admin_e(app_url('admin/news-feeds?source_id=' . $s['id'])) ?>" class="btn-action-icon btn-action-view" title="استعراض أحدث أخبار هذا المصدر">
 <i class="bi bi-eye"></i>
 </a>
 <a href="<?= admin_e(app_url('admin/rss-sources/' . $s['id'] . '/edit')) ?>" class="btn-action-icon btn-action-edit" title="تعديل بيانات المصدر">
 <i class="bi bi-pencil"></i>
 </a>
 <form method="post" action="<?= admin_e(app_url('admin/rss-sources/' . $s['id'] . '/delete')) ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المصدر من القائمة؟')">
 <?= CSRF::field() ?>
 <button type="submit" class="btn-action-icon btn-action-delete" title="حذف المصدر">
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

<script>
(function () {
 var HEALTH_URL = <?= json_encode(app_url('admin/news-feeds/health-check'), JSON_UNESCAPED_SLASHES) ?>;

 var panel = document.getElementById('feedHealthPanel');
 var resultsBox = document.getElementById('feedHealthResults');
 var summaryEl = document.getElementById('feedHealthSummary');
 var progressWrap = document.getElementById('feedHealthProgressWrap');
 var progressBar = document.getElementById('feedHealthProgress');
 var btnAll = document.getElementById('btnCheckAllFeeds');

 function esc(s) {
  return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
   return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
  });
 }

 function rowHtml(r) {
  var isOk = r.status === 'ok';
  var isEmpty = r.status === 'empty';
  var cls = isOk ? 'success' : (isEmpty ? 'warning' : 'danger');
  var icon = isOk ? 'check-circle-fill' : (isEmpty ? 'exclamation-triangle-fill' : 'x-circle-fill');

  var html = '<div class="d-flex align-items-start gap-2 p-2 rounded-3 border border-' + cls + '-subtle bg-' + cls + '-subtle">';
  html += '<i class="bi bi-' + icon + ' text-' + cls + ' mt-1"></i>';
  html += '<div class="flex-grow-1 small">';
  html += '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">';
  html += '<strong class="text-dark">#' + r.id + ' — ' + esc(r.name) + '</strong>';
  html += '<span class="d-inline-flex align-items-center gap-2">';
  if (isOk) {
   html += '<span class="badge bg-success">' + r.items + ' عنصر</span>';
   if (r.ua) html += '<span class="badge bg-secondary-subtle text-secondary" title="وكيل المستخدم الذي نجح">' + esc(r.ua) + '</span>';
  } else {
   html += '<span class="badge bg-' + cls + '">HTTP ' + (r.http_code || '—') + '</span>';
  }
  html += '</span></div>';

  if (isOk && r.sample) {
   html += '<div class="text-muted mt-1"><i class="bi bi-quote me-1"></i>' + esc(r.sample) + '</div>';
  }
  if (!isOk && r.error) {
   html += '<div class="text-' + cls + ' mt-1">' + esc(r.error) + '</div>';
  }
  if (!isOk && r.suggestions && r.suggestions.length) {
   html += '<div class="mt-2"><span class="text-muted">روابط مقترحة:</span><div class="d-flex flex-wrap gap-1 mt-1">';
   r.suggestions.forEach(function (u) {
    html += '<code class="small bg-white border rounded px-2 py-1 font-monospace">' + esc(u) + '</code>';
   });
   html += '</div></div>';
  }
  html += '</div></div>';
  return html;
 }

 function render(data) {
  resultsBox.innerHTML = data.results.map(rowHtml).join('');
  var rate = data.total ? Math.round((data.ok / data.total) * 100) : 0;
  summaryEl.innerHTML = '<span class="badge bg-success me-1">' + data.ok + ' سليمة</span>' +
   '<span class="badge bg-danger me-1">' + data.failed + ' معطوبة</span>' +
   '<span class="text-muted">' + rate + '% نسبة النجاح</span>';
 }

 function fetchJsonSafe(url) {
  return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
   .then(function (res) {
    return res.text().then(function (t) {
     var trimmed = (t || '').trim();
     if (!trimmed) {
      throw new Error('استجابة فارغة من الخادم — غالباً انتهت مهلة التنفيذ في الاستضافة قبل إتمام الفحص. حاول مرة أخرى أو افحص المصادر واحداً تلو الآخر.');
     }
     if (trimmed.charAt(0) !== '{' && trimmed.charAt(0) !== '[') {
      throw new Error('استجابة غير متوقعة (HTML/نص) برمز HTTP ' + res.status + ' — غالباً انتهت مهلة التنفيذ في الاستضافة. جرّب الفحص للمصادر واحداً تلو الآخر.');
     }
     var data;
     try { data = JSON.parse(trimmed); }
     catch (e) { throw new Error('تعذّرت قراءة استجابة الخادم (JSON غير صالح) برمز HTTP ' + res.status + '.'); }
     return data;
    });
   });
 }

 function run(url, btn, originalHtml) {
  var isSingle = url.indexOf('id=') !== -1;
  var BATCH = 3;
  var allResults = [];
  var allOk = 0;
  var allFailed = 0;
  var totalSources = 0;

  panel.classList.remove('d-none');
  progressWrap.classList.remove('d-none');
  progressBar.style.width = '5%';
  resultsBox.innerHTML = '<div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span>جارٍ فحص الخلاصات مباشرة...</div>';
  summaryEl.textContent = '';
  panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

  var tick = setInterval(function () {
   var w = parseInt(progressBar.style.width) || 5;
   if (w < 95) progressBar.style.width = (w + 1) + '%';
  }, 500);

  function renderAccumulated() {
   render({ results: allResults, total: allResults.length, ok: allOk, failed: allFailed });
  }

  function step(batchUrl) {
   return fetchJsonSafe(batchUrl).then(function (data) {
    if (!data || !data.results) throw new Error('استجابة غير صالحة من الخادم.');
    allResults = allResults.concat(data.results);
    allOk += data.ok || 0;
    allFailed += data.failed || 0;
    totalSources = data.sourceCount || ((data.offset || 0) + data.results.length);
    if (!data.done) {
     resultsBox.innerHTML = '<div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span>جارٍ الفحص... تم فحص ' + allResults.length + ' من ' + (totalSources || '?') + ' مصدر</div>';
     return step(url.split('?')[0] + '?offset=' + (data.offset || 0) + '&limit=' + BATCH);
    }
    progressBar.style.width = '100%';
    clearInterval(tick);
    setTimeout(function () { progressWrap.classList.add('d-none'); }, 500);
    renderAccumulated();
   });
  }

  function fail(err) {
   clearInterval(tick);
   progressWrap.classList.add('d-none');
   resultsBox.innerHTML = '<div class="alert alert-danger mb-0 small">تعذر إجراء الفحص: ' + esc(err.message) + '</div>';
  }

  var firstUrl;
  if (isSingle) {
   firstUrl = url;
  } else {
   firstUrl = url + (url.indexOf('?') === -1 ? '?' : '&') + 'offset=0&limit=' + BATCH;
  }

  step(firstUrl)
   .catch(fail)
   .finally(function () {
    if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
   });
 }

 if (btnAll) {
  btnAll.addEventListener('click', function () {
   var original = btnAll.innerHTML;
   btnAll.disabled = true;
   btnAll.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> جارٍ الفحص...';
   run(HEALTH_URL, btnAll, original);
  });
 }

 document.querySelectorAll('.btn-check-one-feed').forEach(function (b) {
  b.addEventListener('click', function () {
   var original = b.innerHTML;
   b.disabled = true;
   b.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
   run(HEALTH_URL + '?id=' + b.dataset.id, b, original);
  });
 });
})();
</script>
