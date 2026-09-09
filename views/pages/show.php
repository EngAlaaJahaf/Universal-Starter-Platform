<?php
$siteName = Settings::get('site_name_ar', 'منصة الأخبار التقنية');
$pageTitle = ($page['title_ar'] ?? 'صفحة') . ' | ' . $siteName;
$pageDesc  = mb_substr(strip_tags($page['content_ar'] ?? ''), 0, 160);

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="max-width:880px;padding:36px 16px 80px">
    
    <!-- Page Breadcrumbs -->
    <nav style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:var(--text-muted);margin-bottom:20px">
        <a href="<?= view_e(app_url()) ?>" style="color:var(--text-muted)">الرئيسية</a>
        <span>/</span>
        <span style="color:var(--accent-primary)"><?= view_e($page['title_ar']) ?></span>
    </nav>

    <!-- Page Header -->
    <header style="margin-bottom:30px;padding-bottom:20px;border-bottom:1px solid var(--border-subtle)">
        <span class="badge-tag" style="margin-bottom:10px;display:inline-block">📄 وثيقة رسمية</span>
        <h1 style="font-size:2.2rem;font-weight:800;color:var(--text-main);line-height:1.3;margin-bottom:10px">
            <?= view_e($page['title_ar']) ?>
        </h1>
        <?php if (!empty($page['title_en'])): ?>
            <p style="color:var(--text-muted);font-size:1rem;direction:ltr;text-align:right">
                <?= view_e($page['title_en']) ?>
            </p>
        <?php endif; ?>
    </header>

    <!-- Page Content Container -->
    <article class="side-widget-card" style="padding:clamp(20px, 4vw, 36px);border-radius:var(--radius-card);line-height:1.85;font-size:1.02rem;color:var(--text-main)">
        <div class="article-content-body" style="font-size:1.02rem;line-height:1.85">
            <?= $page['content_ar'] ?? '' ?>
        </div>
    </article>

    <!-- Quick Navigation Bar -->
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:30px;justify-content:center">
        <a href="<?= view_e(app_url('privacy')) ?>" class="btn-secondary-custom" style="padding:8px 18px;border-radius:10px;font-size:0.88rem;background:var(--bg-surface);border:1px solid var(--border-subtle);color:var(--text-main)">
            🔒 سياسة الخصوصية
        </a>
        <a href="<?= view_e(app_url('terms')) ?>" class="btn-secondary-custom" style="padding:8px 18px;border-radius:10px;font-size:0.88rem;background:var(--bg-surface);border:1px solid var(--border-subtle);color:var(--text-main)">
            📜 شروط الاستخدام
        </a>
        <a href="<?= view_e(app_url('contact')) ?>" class="btn-secondary-custom" style="padding:8px 18px;border-radius:10px;font-size:0.88rem;background:var(--bg-surface);border:1px solid var(--border-subtle);color:var(--text-main)">
            📬 تواصل مع الإدارة
        </a>
        <a href="<?= view_e(app_url('rss.xml')) ?>" class="btn-secondary-custom" style="padding:8px 18px;border-radius:10px;font-size:0.88rem;background:var(--bg-surface);border:1px solid var(--border-subtle);color:var(--text-main)">
            📡 خلاصة RSS
        </a>
    </div>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
