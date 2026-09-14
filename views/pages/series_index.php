<?php
$pageTitle = 'سلاسل المقالات والملفات الشاملة | ' . Settings::get('site_name_ar', 'منصتي الذكية');
$pageDesc  = 'مجموعات مقالات متسلسلة ومرتبطة تقدم تغطيات ودراسات شاملة لموضوعات تقنية متقدمة.';

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding:32px 0 80px">
    <div style="margin-bottom:32px;background:var(--bg-surface);border:1px solid var(--border-subtle);padding:32px;border-radius:var(--radius-card);box-shadow:var(--shadow-sm)">
        <span class="badge-tag">سلاسل وملفات خاصة</span>
        <h1 style="font-size:2.2rem;font-weight:800;margin:10px 0;color:var(--text-main)">سلاسل المقالات والدراسات المعمقة</h1>
        <p style="color:var(--text-muted);font-size:1.05rem;max-width:700px;margin-bottom:0">مجموعات مقالات متسلسلة ومرتبطة تقدم تغطيات ودراسات شاملة لموضوعات تقنية متقدمة.</p>
    </div>

    <div class="news-grid">
        <?php if (empty($seriesList)): ?>
            <div style="grid-column:1/-1;padding:60px;text-align:center;background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:var(--radius-card);color:var(--text-muted)">
                لا توجد سلاسل مقالات منشورة حالياً.
            </div>
        <?php else: ?>
            <?php foreach ($seriesList as $s): ?>
                <article class="news-card">
                    <div class="card-body">
                        <span class="badge-tag">سلسلة مقالات</span>
                        <h3 style="margin-top:10px"><a href="<?= view_e(app_url('series/' . $s['slug'])) ?>"><?= view_e($s['title_ar']) ?></a></h3>
                        <p><?= view_e($s['description_ar'] ?: 'سلسلة مقالات تخصصية تقدم نظرة شاملة وتحليلات دقيقة.') ?></p>
                        <div class="card-footer" style="margin-top:20px">
                            <span>📚 <?= (int) $s['articles_count'] ?> مقالات في السلسلة</span>
                            <a class="read-more-link" href="<?= view_e(app_url('series/' . $s['slug'])) ?>">تصفح السلسلة ←</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
