<?php
$pageTitle = 'أرشيف الأخبار | ' . Settings::get('site_name_ar', 'منصة الأخبار التقنية');
$pageDesc  = 'استعرض أخبارنا ومقالاتنا السابقة المؤرشفة مرتبة زمنياً، مع إمكانية التصفية حسب الشهر والتصنيف والبحث.';

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding-top:20px;padding-bottom:80px">

    <!-- Archive Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0 18px;border-bottom:1px solid var(--border-subtle);margin-bottom:24px;flex-wrap:wrap;gap:12px">
        <div style="display:flex;align-items:center;gap:12px">
            <span class="badge-tag" style="padding:4px 10px;font-size:0.78rem;display:inline-flex;align-items:center;gap:5px">
                <?= ui_icon('general', '', 14) ?> الأرشيف
            </span>
            <h1 style="font-size:1.35rem;font-weight:800;margin:0;color:var(--text-main);display:inline">أرشيف الأخبار</h1>
            <?php if ($total > 0): ?>
                <span style="font-size:0.85rem;color:var(--text-muted)">(<?= number_format($total) ?> خبر ومقال)</span>
            <?php endif; ?>
        </div>
        <div style="font-size:0.85rem;color:var(--text-dim);display:inline-flex;align-items:center;gap:6px">
            <a href="<?= view_e(app_url()) ?>" style="color:var(--text-muted);display:inline-flex;align-items:center;gap:4px"><?= ui_icon('home', '', 13) ?> الرئيسية</a> /
            <span style="color:var(--accent-primary);font-weight:700">الأرشيف</span>
        </div>
    </div>

    <!-- Filters -->
    <div style="background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:var(--radius-card);padding:14px 16px;margin-bottom:28px">
        <form method="get" action="<?= view_e(app_url('archive')) ?>" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center">
            <input type="text" name="q" value="<?= view_e($q) ?>" placeholder="ابحث في الأرشيف..." style="flex:1;min-width:160px;padding:8px 12px;border:1px solid var(--border-medium);border-radius:8px;background:var(--bg-surface-elevated);color:var(--text-main)">
            <select name="category" style="padding:8px 12px;border:1px solid var(--border-medium);border-radius:8px;background:var(--bg-surface-elevated);color:var(--text-main)">
                <option value="0">كل التصنيفات</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= ($cat === (int) $c['id']) ? 'selected' : '' ?>><?= view_e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="month" style="padding:8px 12px;border:1px solid var(--border-medium);border-radius:8px;background:var(--bg-surface-elevated);color:var(--text-main)">
                <option value="">كل الأشهر</option>
                <?php foreach ($months as $m): ?>
                    <?php
                        $label = '';
                        $ts = strtotime($m['ym'] . '-01');
                        if ($ts !== false) {
                            $label = date('F Y', $ts);
                        }
                    ?>
                    <option value="<?= view_e($m['ym']) ?>" <?= ($month === $m['ym']) ? 'selected' : '' ?>><?= view_e($label ?: $m['ym']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" style="padding:8px 18px;border:0;border-radius:8px;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));color:#fff;font-weight:700;cursor:pointer">تصفية</button>
            <?php if (!empty($q) || !empty($cat) || !empty($month)): ?>
                <a href="<?= view_e(app_url('archive')) ?>" style="padding:8px 14px;border:1px solid var(--border-medium);border-radius:8px;color:var(--text-muted);text-decoration:none">إلغاء الفلتر</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Archive Grid -->
    <div class="news-grid">
        <?php if (empty($articles)): ?>
            <div style="grid-column:1 / -1;padding:60px;text-align:center;background:var(--bg-surface);border-radius:var(--radius-card);border:1px solid var(--border-subtle);color:var(--text-muted)">
                <div style="display:grid;place-items:center;margin-bottom:16px;color:var(--accent-primary)"><?= ui_icon('general', '', 48) ?></div>
                <h3>لا توجد أخبار مؤرشفة مطابقة.</h3>
                <p>جرّب تغيير مرشحات البحث أو العودة لاحقاً.</p>
            </div>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <article class="news-card" <?= news_card_attrs($article) ?>>
                    <div class="card-img-wrap">
                        <span class="card-tag"><?= view_e($article['category_name'] ?: 'أرشيف') ?></span>
                        <button class="card-bookmark-btn"
                                data-article-id="<?= view_e($article['id']) ?>"
                                data-title="<?= view_e($article['title']) ?>"
                                data-url="<?= view_e(app_url('article/' . $article['slug'])) ?>"
                                data-category="<?= view_e($article['category_name'] ?: 'أرشيف') ?>"
                                title="حفظ للقراءة"><?= ui_icon('bookmark-star', '', 14) ?></button>
                        <?php if (!empty($article['featured_image'])): ?>
                            <img src="<?= view_e(app_url($article['featured_image'])) ?>" alt="<?= view_e($article['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2rem"><?= ui_icon('general', '', 32) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><a href="<?= view_e(app_url('article/' . $article['slug'])) ?>"><?= view_e($article['title']) ?></a></h3>
                        <p><?= view_e($article['excerpt'] ?: 'تفاصيل شاملة وتحليلات معمقة تواكب أحدث المستجدات التقنية.') ?></p>
                        <div class="card-footer">
                            <span style="display:inline-flex;align-items:center;gap:5px">
                                <?= ui_icon('calendar', '', 13) ?> <?= view_e(fmt_date($article['published_at'] ?: $article['created_at'])) ?>
                            </span>
                            <a class="read-more-link" href="<?= view_e(app_url('article/' . $article['slug'])) ?>">قراءة التفاصيل ←</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:32px;flex-wrap:wrap">
            <?php if ($page > 1): ?>
                <a href="<?= view_e(app_url('archive?page=' . ($page - 1) . '&month=' . urlencode($month) . '&category=' . (int) $cat . '&q=' . urlencode($q))) ?>" style="padding:6px 14px;border:1px solid var(--border-medium);border-radius:8px;color:var(--text-muted);text-decoration:none">← السابق</a>
            <?php endif; ?>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <?php if ($p === $page): ?>
                    <span style="padding:6px 14px;border-radius:8px;background:linear-gradient(135deg,var(--accent-primary),var(--accent-secondary));color:#fff;font-weight:700"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= view_e(app_url('archive?page=' . $p . '&month=' . urlencode($month) . '&category=' . (int) $cat . '&q=' . urlencode($q))) ?>" style="padding:6px 14px;border:1px solid var(--border-medium);border-radius:8px;color:var(--text-muted);text-decoration:none"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="<?= view_e(app_url('archive?page=' . ($page + 1) . '&month=' . urlencode($month) . '&category=' . (int) $cat . '&q=' . urlencode($q))) ?>" style="padding:6px 14px;border:1px solid var(--border-medium);border-radius:8px;color:var(--text-muted);text-decoration:none">التالي ←</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
