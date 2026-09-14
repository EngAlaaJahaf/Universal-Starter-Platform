<?php
$pageTitle = $category['name'] . ' | ' . Settings::get('site_name_ar', 'منصتي الذكية');
$pageDesc  = !empty($category['description']) ? $category['description'] : 'تغطية شاملة وحصرية لآخر مستجدات وتحليلات هذا المجال التقني الحيوي.';

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding-top:20px;padding-bottom:80px">
    
    <!-- Compact & Sleek Category Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0 18px;border-bottom:1px solid var(--border-subtle);margin-bottom:28px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:12px">
            <span class="badge-tag" style="padding:4px 10px;font-size:0.78rem;display:inline-flex;align-items:center;gap:5px">
                <?= category_icon_html($category['slug'], 14) ?> <?= view_e($category['name']) ?>
            </span>
            <h1 style="font-size:1.35rem;font-weight:800;margin:0;color:var(--text-main);display:inline"><?= view_e($category['name']) ?></h1>
            <?php if (!empty($articles)): ?>
                <span style="font-size:0.85rem;color:var(--text-muted)">(<?= count($articles) ?> خبر ومقال)</span>
            <?php endif; ?>
        </div>
        <div style="font-size:0.85rem;color:var(--text-dim);display:inline-flex;align-items:center;gap:6px">
            <a href="<?= view_e(app_url()) ?>" style="color:var(--text-muted);display:inline-flex;align-items:center;gap:4px"><?= ui_icon('home', '', 13) ?> الرئيسية</a> / 
            <span style="color:var(--accent-primary);font-weight:700"><?= view_e($category['name']) ?></span>
        </div>
    </div>

    <!-- Articles Grid -->
    <div class="news-grid">
        <?php if (empty($articles)): ?>
            <div style="grid-column: 1 / -1; padding: 60px; text-align: center; background: var(--bg-surface); border-radius: var(--radius-card); border: 1px solid var(--border-subtle); color: var(--text-muted)">
                <div style="display:grid;place-items:center;margin-bottom:16px;color:var(--accent-primary)">
                    <?= ui_icon('folder', '', 48) ?>
                </div>
                <h3>لا توجد مقالات منشورة في هذا التصنيف حالياً.</h3>
                <p>تابعنا قريباً للمزيد من التغطيات والتحليلات المتخصصة.</p>
            </div>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <article class="news-card" <?= news_card_attrs($article) ?>>
                    <div class="card-img-wrap">
                        <span class="card-tag"><?= view_e($category['name']) ?></span>
                        <button class="card-bookmark-btn" 
                                data-article-id="<?= view_e($article['id']) ?>"
                                data-title="<?= view_e($article['title']) ?>"
                                data-url="<?= view_e(app_url('article/' . $article['slug'])) ?>"
                                data-category="<?= view_e($category['name']) ?>"
                                title="حفظ للقراءة"><?= ui_icon('bookmark-star', '', 14) ?></button>
                        <?php if (!empty($article['featured_image'])): ?>
                            <img src="<?= view_e(app_url($article['featured_image'])) ?>" alt="<?= view_e($article['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= view_e(\FallbackImage::general()) ?>';">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2rem"><?= ui_icon('general', '', 32) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><a href="<?= view_e(app_url('article/' . $article['slug'])) ?>"><?= view_e($article['title']) ?></a></h3>
                        <p><?= view_e($article['excerpt'] ?: 'تفاصيل شاملة وتحليلات معمقة تواكب أحدث المعايير في هذا القطاع.') ?></p>
                        <div class="card-footer">
                            <span style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('author', '', 13) ?> بقلم <?= view_e($article['author_name'] ?: 'فريق التحرير') ?></span>
                            <a class="read-more-link" href="<?= view_e(app_url('article/' . $article['slug'])) ?>">قراءة التفاصيل ←</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
