<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
 <div>
 <h2 class="h3 fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>التحليلات والإحصاءات المباشرة</h2>
 <p class="text-muted mb-0">بيانات رقمية حقيقية ومباشرة من قاعدة البيانات للمشاهدات والتفاعل والأعضاء.</p>
 </div>
 <a href="<?= admin_e(app_url('admin/articles')) ?>" class="btn btn-outline-primary fw-bold shadow-sm">
 <i class="bi bi-pencil-square me-1"></i> تعديل إحصائيات مقال
 </a>
</div>

<!-- Primary Real KPIs -->
<div class="row g-3 mb-4">
 <div class="col-md-3">
 <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-primary border-4">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <span class="text-muted small d-block mb-1">إجمالي المشاهدات</span>
 <h3 class="fw-bold mb-0 text-primary"><?= number_format((int) $totalViews) ?></h3>
 </div>
 <div class="bg-primary-subtle text-primary p-3 rounded-circle" style="width:52px;height:52px;display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-eye"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3">
 <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-success border-4">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <span class="text-muted small d-block mb-1">المقالات المنشورة</span>
 <h3 class="fw-bold mb-0 text-success"><?= number_format((int) $totalArticles) ?></h3>
 </div>
 <div class="bg-success-subtle text-success p-3 rounded-circle" style="width:52px;height:52px;display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-journal-check"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3">
 <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-info border-4">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <span class="text-muted small d-block mb-1">المستخدمون المسجلون</span>
 <h3 class="fw-bold mb-0 text-info"><?= number_format((int) $totalUsers) ?></h3>
 </div>
 <div class="bg-info-subtle text-info p-3 rounded-circle" style="width:52px;height:52px;display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-people"></i>
 </div>
 </div>
 </div>
 </div>

 <div class="col-md-3">
 <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-warning border-4">
 <div class="d-flex justify-content-between align-items-center">
 <div>
 <span class="text-muted small d-block mb-1">مشتركو النشرة البريدية</span>
 <h3 class="fw-bold mb-0 text-warning"><?= number_format((int) $totalSubscribers) ?></h3>
 </div>
 <div class="bg-warning-subtle text-warning p-3 rounded-circle" style="width:52px;height:52px;display:grid;place-items:center;font-size:1.3rem">
 <i class="bi bi-envelope-check"></i>
 </div>
 </div>
 </div>
 </div>
</div>

<!-- Secondary Metrics -->
<div class="row g-3 mb-4">
 <div class="col-md-4">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
 <div class="bg-purple text-white p-3 rounded-3" style="background:#9d4edd;font-size:1.2rem"><i class="bi bi-chat-quote"></i></div>
 <div>
 <small class="text-muted d-block">التعليقات المعتمدة</small>
 <strong class="h5 mb-0"><?= number_format((int) $totalComments) ?> تعليق</strong>
 </div>
 </div>
 </div>
 <div class="col-md-4">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
 <div class="bg-danger text-white p-3 rounded-3" style="font-size:1.2rem"><i class="bi bi-broadcast"></i></div>
 <div>
 <small class="text-muted d-block">تحديثات البث المباشر</small>
 <strong class="h5 mb-0"><?= number_format((int) $totalLiveUpdates) ?> تحديث حي</strong>
 </div>
 </div>
 </div>
 <div class="col-md-4">
 <div class="card border-0 shadow-sm rounded-4 p-3 bg-white d-flex flex-row align-items-center gap-3">
 <div class="bg-primary text-white p-3 rounded-3" style="font-size:1.2rem"><i class="bi bi-share"></i></div>
 <div>
 <small class="text-muted d-block">إجمالي المشاركات</small>
 <strong class="h5 mb-0"><?= number_format((int) $totalShares) ?> مشاركة</strong>
 </div>
 </div>
 </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
 <!-- Category Views Doughnut -->
 <div class="col-lg-5">
 <div class="card border-0 shadow-sm rounded-4 bg-white h-100 p-4">
 <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>توزيع المشاهدات حسب الأقسام</h6>
 <div style="max-height:280px;position:relative">
 <canvas id="categoryChart"></canvas>
 </div>
 </div>
 </div>

 <!-- Top Articles Bar Chart -->
 <div class="col-lg-7">
 <div class="card border-0 shadow-sm rounded-4 bg-white h-100 p-4">
 <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line text-success me-2"></i>أعلى المقالات قراءة ومتابعة</h6>
 <div style="max-height:280px;position:relative">
 <canvas id="articlesChart"></canvas>
 </div>
 </div>
 </div>
</div>

<!-- Top Articles Detailed Table with Direct Stat Editing -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
 <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center">
 <h6 class="mb-0 fw-bold"><i class="bi bi-trophy text-warning me-2"></i>المقالات الأكثر زيارة (مع إمكانية التحكم في العدادات)</h6>
 <small class="text-muted">انقر على زر التعديل للتحكم في عدد المشاهدات والمشاركات يدوياً</small>
 </div>
 <div class="table-responsive">
 <table class="table table-hover align-middle mb-0">
 <thead class="table-light">
 <tr>
 <th style="width:50px">#</th>
 <th>عنوان المقال</th>
 <th>القسم</th>
 <th>المشاهدات الفعلية</th>
 <th>المشاركات</th>
 <th>الكاتب</th>
 <th class="text-end" style="width:120px">الإجراءات</th>
 </tr>
 </thead>
 <tbody>
 <?php foreach ($topArticles as $idx => $art): ?>
 <tr>
 <td><strong>#<?= $idx + 1 ?></strong></td>
 <td>
 <a href="<?= admin_e(app_url('article/' . $art['slug'])) ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
 <?= admin_e($art['title']) ?> ↗
 </a>
 </td>
 <td><span class="badge bg-light text-dark border"><?= admin_e($art['category_name'] ?: 'عام') ?></span></td>
 <td><strong class="text-primary font-monospace"><?= number_format((int) $art['views_count']) ?></strong></td>
 <td><span class="text-muted font-monospace"><?= number_format((int) $art['shares_count']) ?></span></td>
 <td><small class="text-muted"><?= admin_e($art['author_name'] ?: 'المدير') ?></small></td>
 <td class="text-end">
 <a href="<?= admin_e(app_url('admin/articles/' . $art['id'] . '/edit')) ?>" class="btn-action-icon btn-action-edit" title="تعديل المقال والتحكم في إحصائياته">
 <i class="bi bi-pencil"></i>
 </a>
 </td>
 </tr>
 <?php endforeach; ?>
 </tbody>
 </table>
 </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
 // 1. Category Views Doughnut Chart
 const catLabels = <?= json_encode(array_column($categoryStats, 'name')) ?>;
 const catData = <?= json_encode(array_column($categoryStats, 'total_views')) ?>;

 const catCtx = document.getElementById('categoryChart').getContext('2d');
 new Chart(catCtx, {
 type: 'doughnut',
 data: {
 labels: catLabels.length ? catLabels : ['عام'],
 datasets: [{
 data: catData.length ? catData : [1],
 backgroundColor: ['#00f2fe', '#9d4edd', '#10b981', '#f59e0b', '#3b82f6', '#ec4899', '#64748b'],
 borderWidth: 0
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: { position: 'bottom' }
 }
 }
 });

 // 2. Top Articles Bar Chart
 const artLabels = <?= json_encode(array_map(function($a) { return mb_strimwidth($a['title'], 0, 20, '…', 'UTF-8'); }, $topArticles)) ?>;
 const artViews = <?= json_encode(array_column($topArticles, 'views_count')) ?>;

 const artCtx = document.getElementById('articlesChart').getContext('2d');
 new Chart(artCtx, {
 type: 'bar',
 data: {
 labels: artLabels,
 datasets: [{
 label: 'عدد المشاهدات',
 data: artViews,
 backgroundColor: 'rgba(0, 242, 254, 0.7)',
 borderColor: '#00f2fe',
 borderWidth: 1,
 borderRadius: 8
 }]
 },
 options: {
 responsive: true,
 maintainAspectRatio: false,
 plugins: {
 legend: { display: false }
 },
 scales: {
 y: { beginAtZero: true }
 }
 }
 });
});
</script>
