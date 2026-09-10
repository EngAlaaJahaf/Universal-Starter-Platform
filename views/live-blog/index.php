<?php
$pageTitle = 'التغطيات الحية والبث المباشر | ' . Settings::get('site_name_ar', 'عصب التقنية');
$pageDesc  = 'متابعة مستمرة وبث نصي حي لأهم المؤتمرات التقنية وإطلاقات الأجهزة والذكاء الاصطناعي.';

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding:32px 0 80px">
    <div style="margin-bottom:32px;background:var(--bg-surface);border:1px solid var(--border-subtle);padding:32px;border-radius:var(--radius-card);box-shadow:var(--shadow-sm)">
        <span class="badge-tag" style="background:#f43f5e;color:#fff">🔴 تغطية حية</span>
        <h1 style="font-size:2.2rem;font-weight:800;margin:10px 0;color:var(--text-main)">مؤتمرات وأحداث التقنية لحظة بلحظة</h1>
        <p style="color:var(--text-muted);font-size:1.05rem;max-width:700px;margin-bottom:0">متابعة مستمرة وبث نصي حي لأهم المؤتمرات التقنية وإطلاقات الأجهزة والذكاء الاصطناعي.</p>
    </div>

    <div class="news-grid">
        <?php if (empty($blogs)): ?>
            <div style="grid-column:1/-1;padding:60px;text-align:center;background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:var(--radius-card);color:var(--text-muted)">
                لا توجد تغطيات حية جارية حالياً.
            </div>
        <?php else: ?>
            <?php foreach ($blogs as $b): ?>
                <article class="news-card">
                    <div class="card-body">
                        <span class="badge-tag" style="<?= $b['status'] === 'active' ? 'background:#f43f5e;color:#fff' : '' ?>">
                            <?= $b['status'] === 'active' ? '🔴 مباشر الآن' : 'انتهت التغطية' ?>
                        </span>
                        <h3 style="margin-top:10px"><a href="<?= view_e(app_url('live-blog/' . $b['id'])) ?>"><?= view_e($b['title_ar']) ?></a></h3>
                        <div class="card-footer" style="margin-top:20px">
                            <span>📝 <?= (int) $b['entries_count'] ?> تحديثات</span>
                            <a class="read-more-link" href="<?= view_e(app_url('live-blog/' . $b['id'])) ?>">متابعة البث ←</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
