<?php
$pageTitle = $series['title_ar'] . ' | سلسلة مقالات';
$pageDesc  = $series['description_ar'] ?? '';

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding:32px 0 80px">
    
    <div style="background:var(--bg-surface);border:1px solid var(--border-subtle);padding:32px;border-radius:var(--radius-card);margin-bottom:32px;box-shadow:var(--shadow-sm)">
        <span class="badge-tag">سلسلة مقالات خاصة</span>
        <h1 style="font-size:2.2rem;font-weight:800;margin:10px 0;color:var(--text-main)"><?= view_e($series['title_ar']) ?></h1>
        <p style="color:var(--text-muted);font-size:1.05rem;margin-bottom:0"><?= view_e($series['description_ar'] ?? '') ?></p>
    </div>

    <h3 style="font-size:1.3rem;font-weight:800;margin-bottom:20px;color:var(--text-main)">مقالات السلسلة (مرتبة بالتسلسل):</h3>

    <div style="display:flex;flex-direction:column;gap:14px">
        <?php foreach ($articles as $idx => $art): ?>
            <div class="side-widget-card" style="display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 24px;border-radius:var(--radius-card);flex-wrap:wrap">
                <div style="display:flex;align-items:center;gap:18px">
                    <span class="trend-number" style="color:var(--accent-primary);font-weight:900"><?= str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <div>
                        <h4 style="margin-bottom:4px;font-size:1.1rem">
                            <a href="<?= view_e(app_url('article/' . $art['slug'])) ?>" style="color:var(--text-main);font-weight:700"><?= view_e($art['title']) ?></a>
                        </h4>
                        <small style="color:var(--text-muted)">بقلم <?= view_e($art['author_name'] ?: 'فريق التحرير') ?> · <?= view_e(fmt_date($art['created_at'])) ?></small>
                    </div>
                </div>
                <a href="<?= view_e(app_url('article/' . $art['slug'])) ?>" class="btn-primary-glow" style="padding:6px 16px;font-size:0.85rem">قراءة المقال ←</a>
            </div>
        <?php endforeach; ?>
    </div>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
