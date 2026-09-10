<?php
$pageTitle = 'محرك البحث الذكي | ' . Settings::get('site_name_ar', 'عصب التقنية');
$pageDesc  = 'ابحث في كافة أخبار وتحليلات وشروحات المنصة التقنية.';

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding:24px 0 80px">
    
    <div class="search-filter-card">
        <span class="badge-tag">محرك البحث المتقدم</span>
        <h1 class="search-page-title">استكشف أرشيف الأخبار والتحليلات التقنية</h1>
        <p class="search-page-subtitle">ابحث في آلاف المقالات، التحليلات، الشروحات، والمصادر التقنية بدقة وسرعة</p>
        
        <form method="get" action="<?= view_e(app_url('search')) ?>" class="search-filter-form">
            <input type="text" name="q" value="<?= view_e($q) ?>" placeholder="ما الذي تبحث عنه؟ اكتب عبارة البحث هنا..." class="search-main-input" autocomplete="off">
            
            <div class="search-options-grid">
                <select name="category_id" class="search-select">
                    <option value="">جميع التصنيفات</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= view_e($cat['id']) ?>" <?= (int) $category === (int) $cat['id'] ? 'selected' : '' ?>>
                            <?= view_e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="date" name="from" value="<?= view_e($from) ?>" class="search-date-input" title="من تاريخ">

                <select name="sort" class="search-select">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>الأحدث أولاً</option>
                    <option value="views" <?= $sort === 'views' ? 'selected' : '' ?>>الأكثر قراءة</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>الأقدم</option>
                </select>

                <button type="submit" class="btn-primary-glow search-submit-btn">
                    <span>بحث ⌕</span>
                </button>
            </div>
        </form>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
        <span style="font-size:0.95rem;color:var(--text-muted)">عدد النتائج المطابقة: <b style="color:var(--accent-primary)"><?= view_e($total) ?></b></span>
        <?php if (!empty($q)): ?>
            <span style="font-size:0.85rem;color:var(--text-dim)">عبارة البحث: "<?= view_e($q) ?>"</span>
        <?php endif; ?>
    </div>

    <div class="news-grid">
        <?php if (empty($articles)): ?>
            <div style="grid-column: 1 / -1; padding: 60px; text-align: center; background: var(--bg-surface); border-radius: var(--radius-card); border: 1px solid var(--border-subtle); color: var(--text-muted)">
                <div style="font-size: 3rem; margin-bottom: 12px">🔍</div>
                <h3>لم نعثر على مقالات تطابق معايير البحث.</h3>
                <p>جرب استخدام كلمات بحث مختلفة أو إزالة الفلاتر المحددة.</p>
            </div>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <article class="news-card" <?= news_card_attrs($article) ?>>
                    <div class="card-img-wrap">
                        <span class="card-tag"><?= view_e($article['category_name'] ?? 'تقنية') ?></span>
                        <button class="card-bookmark-btn" 
                                data-article-id="<?= view_e($article['id']) ?>"
                                data-title="<?= view_e($article['title']) ?>"
                                data-url="<?= view_e(app_url('article/' . $article['slug'])) ?>"
                                data-category="<?= view_e($article['category_name'] ?? 'تقنية') ?>"
                                title="حفظ للقراءة">☆</button>
                        <?php if (!empty($article['featured_image'])): ?>
                            <img src="<?= view_e(app_url($article['featured_image'])) ?>" alt="<?= view_e($article['title']) ?>" loading="lazy">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2rem">⚡</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><a href="<?= view_e(app_url('article/' . $article['slug'])) ?>"><?= view_e($article['title']) ?></a></h3>
                        <p><?= view_e($article['excerpt'] ?? '') ?></p>
                        <div class="card-footer">
                            <span>بقلم <?= view_e($article['author_name'] ?? 'فريق التحرير') ?></span>
                            <a class="read-more-link" href="<?= view_e(app_url('article/' . $article['slug'])) ?>">قراءة التفاصيل ←</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total > $perPage): ?>
        <nav style="display:flex;justify-content:center;gap:8px;margin-top:40px">
            <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
                <a href="<?= view_e(app_url('search?q=' . rawurlencode($q) . '&page=' . $i)) ?>" 
                   class="filter-btn <?= (int) ($page ?? 1) === $i ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
