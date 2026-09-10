<?php
$pageTitle = $pageTitle ?? 'المقالات المحفوظة والمفضلة | ' . Settings::get('site_name_ar', 'عصب التقنية');
$pageDesc  = 'قائمة مقالاتك وأخبارك التقنية المحفوظة للرجوع إليها في أي وقت.';

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding-top:20px;padding-bottom:80px">
    
    <!-- Page Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0 18px;border-bottom:1px solid var(--border-subtle);margin-bottom:28px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:12px">
            <span class="badge-tag" style="padding:6px 12px;font-size:0.85rem;display:inline-flex;align-items:center;gap:6px;background:rgba(234,179,8,0.15);color:#eab308;border:1px solid rgba(234,179,8,0.3);border-radius:20px">
                ⭐ المحفوظات
            </span>
            <h1 style="font-size:1.45rem;font-weight:800;margin:0;color:var(--text-main);display:inline">المقالات المحفوظة</h1>
            <?php if (!empty($articles)): ?>
                <span style="font-size:0.85rem;color:var(--text-muted)">(<?= count($articles) ?> مقال محفوظ)</span>
            <?php endif; ?>
        </div>
        <div style="font-size:0.85rem;color:var(--text-dim);display:inline-flex;align-items:center;gap:6px">
            <a href="<?= view_e(app_url()) ?>" style="color:var(--text-muted);display:inline-flex;align-items:center;gap:4px"><?= ui_icon('home', '', 13) ?> الرئيسية</a> / 
            <span style="color:var(--accent-primary);font-weight:700">المفضلة</span>
        </div>
    </div>

    <!-- Bookmarks Grid -->
    <?php if (empty($articles)): ?>
        <div style="padding:60px;text-align:center;background:var(--bg-surface);border-radius:var(--radius-card);border:1px solid var(--border-subtle);color:var(--text-muted)">
            <div style="display:grid;place-items:center;margin-bottom:16px;color:#eab308">
                <?= ui_icon('bookmark-star', '', 48) ?>
            </div>
            <h3 style="color:var(--text-main);font-weight:700;margin-bottom:8px">لم تقم بحفظ أي مقالات بعد</h3>
            <p style="color:var(--text-muted);margin-bottom:20px">يمكنك النقر على أيقونة الإشارة المرجعية داخل أي مقال لإضافته إلى قائمة قراءتك هنا.</p>
            <a href="<?= view_e(app_url()) ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:700">
                تصفح أحدث الأخبار ←
            </a>
        </div>
    <?php else: ?>
        <div class="news-grid">
            <?php foreach ($articles as $article): ?>
                <article class="news-card" <?= news_card_attrs($article) ?>>
                    <div class="card-img-wrap">
                        <span class="card-tag"><?= view_e($article['category_name'] ?: 'عام') ?></span>
                        <button class="card-bookmark-btn active" 
                                data-article-id="<?= (int)$article['id'] ?>"
                                data-title="<?= view_e($article['title']) ?>"
                                data-url="<?= view_e(app_url('article/' . $article['slug'])) ?>"
                                title="إلغاء الحفظ"><?= ui_icon('bookmark-star', '', 14) ?></button>
                        <?php if (!empty($article['featured_image'])): ?>
                            <img src="<?= view_e(app_url($article['featured_image'])) ?>" alt="<?= view_e($article['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2rem"><?= ui_icon('general', '', 32) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><a href="<?= view_e(app_url('article/' . $article['slug'])) ?>"><?= view_e($article['title']) ?></a></h3>
                        <p><?= view_e($article['excerpt'] ?: 'تفاصيل وتحليلات شاملة للمقال المحفوظ.') ?></p>
                        <div class="card-footer">
                            <span style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('author', '', 13) ?> <?= view_e($article['author_name'] ?: 'المحرر التقني') ?></span>
                            <a class="read-more-link" href="<?= view_e(app_url('article/' . $article['slug'])) ?>">قراءة المقال ←</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
