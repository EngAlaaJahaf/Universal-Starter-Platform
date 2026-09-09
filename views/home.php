<?php
if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

// Read live settings from DB
$siteName       = Settings::get('site_name_ar', 'منصة الأخبار التقنية');
$siteTagline    = Settings::get('site_tagline', 'نبض التكنولوجيا والذكاء الاصطناعي');
$siteDesc       = Settings::get('meta_description', 'منصة عربية رائدة في تغطية الأخبار التقنية، أحدث تطورات الذكاء الاصطناعي، الأجهزة الذكية، والأمن السيبراني.');
$siteLogo       = Settings::get('site_logo', '');
$themeDefault   = Settings::get('theme_default', 'dark');
$fontFamily     = Settings::get('font_family', 'Tajawal');
$dateFormat     = Settings::get('date_format', 'Y-m-d H:i');
$siteTitle      = $siteName . ' | ' . $siteTagline;
$activeTemplate = Settings::get('site_theme_template', 'editorial_verge');

$featured = null;
foreach ($articles as $candidate) {
    if (!empty($candidate['is_featured'])) {
        $featured = $candidate;
        break;
    }
}
if (!$featured && !empty($articles)) {
    $featured = $articles[0];
}

$latest = array();
foreach ($articles as $candidate) {
    if (!$featured || (int) $candidate['id'] !== (int) $featured['id']) {
        $latest[] = $candidate;
    }
}

$subFeatured = array_slice($latest, 0, 2);
$trending = !empty($popular) ? $popular : array_slice($articles, 0, 5);
$currentUser = Auth::user();

$pageTitle = $siteTitle;
$pageDesc = $siteDesc;
require_once APP_ROOT . '/views/partials/header.php';
?>

<!-- Flash Alerts -->
<div class="container" style="margin-top:20px">
    <?php if ($msg = Session::getFlash('success')): ?>
        <div class="toast" style="position:static;animation:none;margin-bottom:15px;background:rgba(16,185,129,0.15);border-color:#10b981;color:#10b981">
            <span>✅</span> <span><?= e($msg) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($err = Session::getFlash('error')): ?>
        <div class="toast" style="position:static;animation:none;margin-bottom:15px;background:rgba(244,63,94,0.15);border-color:#f43f5e;color:#f43f5e">
            <span>⚠️</span> <span><?= e($err) ?></span>
        </div>
    <?php endif; ?>
</div>

<!-- Main Page Body -->
<main class="container page-shell">
    <h1 class="visually-hidden"><?= e(Settings::get('site_name_ar', 'منصة الأخبار التقنية')) ?> - أحدث مستجدات التكنولوجيا والذكاء الاصطناعي</h1>

    <?php if ($activeTemplate === 'classic_techwd'): ?>
        <!-- ================= 🗞️ TEMPLATE 2: AUTHENTIC TECH-WD MAGAZINE LAYOUT ================= -->
        
        <!-- 3-Box Top Featured Grid -->
        <section style="margin-top:24px;margin-bottom:36px">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px">
                <?php if ($featured): ?>
                    <div style="grid-column: span 2; position:relative; min-height:380px; border-radius:12px; overflow:hidden; background:#000; box-shadow:var(--shadow-sm)" <?= news_card_attrs($featured) ?>>
                        <img src="<?= e(app_url($featured['featured_image'])) ?>" alt="<?= e($featured['title']) ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.85" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=1000&q=80';">
                        <div style="position:absolute;inset:0;background:linear-gradient(180deg, transparent 30%, rgba(0,0,0,0.9) 100%);display:flex;flex-direction:column;justify-content:flex-end;padding:28px">
                            <span class="badge-tag" style="background:#c5162a;align-self:flex-start;margin-bottom:10px;display:inline-flex;align-items:center;gap:5px">
                                <?= ui_icon('flame', '', 14) ?> <?= e($featured['category_name'] ?: 'أخبار مميزة') ?>
                            </span>
                            <h2 style="font-size:1.85rem;font-weight:800;color:#fff;line-height:1.4;margin-bottom:10px">
                                <a href="<?= e(app_url('article/' . $featured['slug'])) ?>"><?= e($featured['title']) ?></a>
                            </h2>
                            <div style="display:flex;align-items:center;gap:16px;color:#e5e7eb;font-size:0.85rem">
                                <span style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('calendar', '', 14) ?> <?= e(fmt_date($featured['published_at'] ?: $featured['created_at'])) ?></span>
                                <span style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('author', '', 14) ?> <?= e($featured['author_name'] ?: 'فريق التحرير') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Sub-featured side cards -->
                <div style="display:flex;flex-direction:column;gap:20px">
                    <?php foreach ($subFeatured as $sf): ?>
                        <div style="position:relative; min-height:180px; border-radius:12px; overflow:hidden; background:#000; flex:1; box-shadow:var(--shadow-sm)" <?= news_card_attrs($sf) ?>>
                            <img src="<?= e(app_url($sf['featured_image'])) ?>" alt="<?= e($sf['title']) ?>" style="width:100%;height:100%;object-fit:cover;opacity:0.85" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&q=80';">
                            <div style="position:absolute;inset:0;background:linear-gradient(180deg, transparent 20%, rgba(0,0,0,0.9) 100%);display:flex;flex-direction:column;justify-content:flex-end;padding:16px">
                                <span class="badge-tag" style="background:#c5162a;font-size:0.7rem;padding:3px 8px;align-self:flex-start;margin-bottom:6px;display:inline-flex;align-items:center;gap:4px">
                                    <?= ui_icon('general', '', 12) ?> <?= e($sf['category_name'] ?: 'تقارير') ?>
                                </span>
                                <h3 style="font-size:1.1rem;font-weight:700;color:#fff;line-height:1.35;margin-bottom:6px">
                                    <a href="<?= e(app_url('article/' . $sf['slug'])) ?>"><?= e($sf['title']) ?></a>
                                </h3>
                                <span style="font-size:0.78rem;color:#d1d5db;display:inline-flex;align-items:center;gap:4px">
                                    <?= ui_icon('calendar', '', 13) ?> <?= e(fmt_date($sf['published_at'] ?: $sf['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Main 2-Column Section (Feed 8 Cols + Classic Sidebar 4 Cols) -->
        <div style="display:grid;grid-template-columns:1fr;gap:32px;align-items:start;" class="article-page-wrap">
            
            <!-- Main Horizontal News Stream -->
            <section>
                <div class="section-head" style="border-bottom:2px solid #c5162a">
                    <h2 class="section-title" style="font-size:1.45rem;display:flex;align-items:center;gap:8px">
                        <?= ui_icon('flame', 'text-primary', 22) ?>
                        <span>أحدث التدوينات والأخبار</span>
                    </h2>
                    <span style="font-size:0.85rem;color:var(--text-muted);font-weight:600">تغطية شاملة وموثوقة</span>
                </div>

                <div class="news-grid">
                    <?php foreach ($latest as $article): ?>
                        <article class="news-card" <?= news_card_attrs($article) ?>>
                            <div class="card-img-wrap">
                                <span class="card-tag"><?= e($article['category_name'] ?: 'بيانات صحفية') ?></span>
                                <?php if (!empty($article['featured_image'])): ?>
                                    <img src="<?= e(app_url($article['featured_image'])) ?>" alt="<?= e($article['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
                                <?php else: ?>
                                    <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2rem"><?= ui_icon('general', '', 32) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <h3><a href="<?= e(app_url('article/' . $article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
                                <div style="display:flex;align-items:center;gap:14px;font-size:0.82rem;color:var(--text-muted);margin-bottom:10px;flex-wrap:wrap">
                                    <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('calendar', '', 14) ?> <?= e(fmt_date($article['published_at'] ?: $article['created_at'])) ?></span>
                                    <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('author', '', 14) ?> <?= e($article['author_name'] ?: 'فريق التحرير') ?></span>
                                    <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('eye', '', 14) ?> <?= number_format((int)($article['views_count'] ?? 0)) ?> قراءة</span>
                                </div>
                                <p><?= e($article['excerpt'] ?: 'تفاصيل شاملة وتحليلات معمقة تواكب أحدث المستجدات التقنية.') ?></p>
                                <div class="card-footer">
                                    <a class="read-more-link" href="<?= e(app_url('article/' . $article['slug'])) ?>" style="font-weight:800">إقرأ المزيد ←</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Classic Sidebar -->
            <aside class="sidebar-sticky-wrap">
                <!-- Top 5 Ranked Stories -->
                <div class="side-widget-card" style="border-top:3px solid #c5162a">
                    <h4 style="font-size:1.15rem;font-weight:800;color:var(--text-main);margin-bottom:16px;display:flex;align-items:center;gap:8px">
                        <?= ui_icon('flame', 'text-danger', 18) ?>
                        <span>الأكثر قراءة ومتابعة</span>
                    </h4>
                    <div style="display:flex;flex-direction:column;gap:12px">
                        <?php foreach ($trending as $index => $item): ?>
                            <a href="<?= e(app_url('article/' . $item['slug'])) ?>" style="display:flex;align-items:flex-start;gap:12px;padding:8px 0;border-bottom:1px solid var(--border-subtle)" <?= news_card_attrs($item) ?>>
                                <span style="font-size:1.2rem;font-weight:900;color:#c5162a;width:24px;text-align:center"><?= $index + 1 ?></span>
                                <div>
                                    <h5 style="font-size:0.92rem;font-weight:700;line-height:1.45;color:var(--text-main);margin-bottom:4px"><?= e($item['title']) ?></h5>
                                    <small style="color:var(--text-muted);font-size:0.75rem;display:inline-flex;align-items:center;gap:4px">
                                        <?= ui_icon('calendar', '', 12) ?> <?= e(fmt_date($item['published_at'] ?: $item['created_at'])) ?>
                                    </small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Newsletter Subscription -->
                <?php if (Settings::get('enable_newsletter', '1') == '1'): ?>
                    <div class="side-widget-card" style="border-top:3px solid #c5162a">
                        <h4 style="display:flex;align-items:center;gap:8px">
                            <?= ui_icon('comments', 'text-primary', 18) ?>
                            <span>النشرة البريدية</span>
                        </h4>
                        <p style="font-size:0.86rem;color:var(--text-muted);margin-bottom:14px">احصل على أهم الأخبار والتحليلات التقنية مباشرة إلى بريدك الإلكتروني.</p>
                        <form action="<?= e(app_url('newsletter/subscribe')) ?>" method="post" style="display:flex;flex-direction:column;gap:10px">
                            <?= CSRF::field() ?>
                            <input type="email" name="email" required placeholder="بريدك الإلكتروني..." style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:10px 14px;border-radius:6px;font-size:0.88rem;color:var(--text-main)">
                            <button type="submit" class="btn-primary-glow" style="background:#c5162a;justify-content:center;padding:10px;border-radius:6px">اشتراك مجاني</button>
                        </form>
                    </div>
                <?php endif; ?>
            </aside>
        </div>

    <?php elseif ($activeTemplate === 'bento_grid'): ?>
        <!-- ================= 🍱 TEMPLATE 5: MODERN BENTO BOX TECH GRID ================= -->
        <section class="bento-hero-section" style="margin-top:24px;margin-bottom:36px">
            <div class="bento-matrix-grid">
                <?php if ($featured): ?>
                    <!-- Main Master Bento Cell (Span 8 Cols / 2 Rows) -->
                    <div class="bento-cell bento-cell-master" style="background-image:linear-gradient(180deg, rgba(8,12,20,0.2) 0%, rgba(8,12,20,0.92) 100%), url('<?= e(app_url($featured['featured_image'])) ?>')" <?= news_card_attrs($featured) ?>>
                        <div class="bento-badge-row">
                            <span class="badge-tag" style="background:linear-gradient(135deg,#00f2fe,#38bdf8);color:#080c14;font-weight:800;display:inline-flex;align-items:center;gap:6px">
                                <?= ui_icon('ai', '', 14) ?> <?= e($featured['category_name'] ?: 'قصة الغلاف') ?>
                            </span>
                            <span class="bento-live-pill">⚡ قصة رئيسية</span>
                        </div>
                        <div class="bento-master-content">
                            <h2 class="bento-title-lg">
                                <a href="<?= e(app_url('article/' . $featured['slug'])) ?>"><?= e($featured['title']) ?></a>
                            </h2>
                            <p class="bento-excerpt"><?= e($featured['excerpt'] ?: 'تحليل شامل ومفصل لأهم التطورات التقنية وتأثيرها المباشر على الصناعة الرقمية.') ?></p>
                            <div class="bento-meta-row">
                                <span><?= ui_icon('author', '', 14) ?> <?= e($featured['author_name'] ?: 'فريق التحرير') ?></span>
                                <span>•</span>
                                <span><?= ui_icon('calendar', '', 14) ?> <?= e(fmt_date($featured['published_at'] ?: $featured['created_at'])) ?></span>
                                <span>•</span>
                                <span><?= ui_icon('read-time', '', 14) ?> 4 دقائق قراءة</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 2 Side Spotlight Bento Cells -->
                <?php foreach (array_slice($latest, 0, 2) as $sArt): ?>
                    <div class="bento-cell bento-cell-spotlight" <?= news_card_attrs($sArt) ?>>
                        <?php if (!empty($sArt['featured_image'])): ?>
                            <div class="bento-spotlight-img">
                                <img src="<?= e(app_url($sArt['featured_image'])) ?>" alt="<?= e($sArt['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&q=80';">
                            </div>
                        <?php endif; ?>
                        <div class="bento-spotlight-body">
                            <span class="bento-mini-tag"><?= e($sArt['category_name'] ?: 'أبرز الأخبار') ?></span>
                            <h3 class="bento-title-sm">
                                <a href="<?= e(app_url('article/' . $sArt['slug'])) ?>"><?= e($sArt['title']) ?></a>
                            </h3>
                            <small class="text-muted"><?= e(fmt_date($sArt['published_at'] ?: $sArt['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 3 Quick Pulse Cards Row -->
            <div class="bento-pulse-row" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-top:20px">
                <!-- Pulse 1: AI Radar -->
                <div class="bento-cell bento-cell-compact">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong class="text-cyan small d-flex align-items-center gap-2"><?= ui_icon('ai', 'text-primary', 16) ?> رادار الذكاء الاصطناعي</strong>
                        <span class="badge bg-primary-subtle text-primary small">مباشر</span>
                    </div>
                    <?php if (!empty($radar[0])): ?>
                        <h4 class="small fw-bold mb-1 text-dark"><?= e($radar[0]['name']) ?> - <?= e($radar[0]['label'] ?? 'تريند تقني') ?></h4>
                        <small class="text-muted"><?= e($radar[0]['desc']) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Pulse 2: Breaking Flash -->
                <div class="bento-cell bento-cell-compact">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong class="text-danger small d-flex align-items-center gap-2"><?= ui_icon('flame', 'text-danger', 16) ?> عاجل ومهم</strong>
                        <span class="badge bg-danger-subtle text-danger small">الآن</span>
                    </div>
                    <?php $breakingItem = $latest[2] ?? $featured; ?>
                    <?php if ($breakingItem): ?>
                        <h4 class="small fw-bold mb-1 text-dark"><a href="<?= e(app_url('article/' . $breakingItem['slug'])) ?>" class="text-dark text-decoration-none"><?= e(mb_substr($breakingItem['title'], 0, 75)) ?>...</a></h4>
                        <small class="text-muted"><?= e(fmt_date($breakingItem['published_at'] ?: $breakingItem['created_at'])) ?></small>
                    <?php endif; ?>
                </div>

                <!-- Pulse 3: Academy Tutorials -->
                <div class="bento-cell bento-cell-compact">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <strong class="text-success small d-flex align-items-center gap-2"><?= ui_icon('tutorials', 'text-success', 16) ?> جديد الشروحات</strong>
                        <a href="<?= e(app_url('tutorials')) ?>" class="text-success small text-decoration-none fw-bold">الأكاديمية ↗</a>
                    </div>
                    <?php if (!empty($tutorials[0])): ?>
                        <h4 class="small fw-bold mb-1 text-dark"><a href="<?= e(app_url('tutorials/' . ($tutorials[0]['slug'] ?? $tutorials[0]['id']))) ?>" class="text-dark text-decoration-none"><?= e(mb_substr($tutorials[0]['title'], 0, 75)) ?>...</a></h4>
                        <small class="text-muted"><?= (int)$tutorials[0]['steps_count'] ?> خطوات مصورة</small>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Bento Multi-Stream Feed Section -->
        <section style="margin-top:40px">
            <div class="section-head">
                <h2 class="section-title">شبكة الأخبار والتحليلات المتخصصة</h2>
            </div>
            <div class="news-grid" style="grid-template-columns:repeat(auto-fill, minmax(320px, 1fr))">
                <?php foreach (array_slice($latest, 2) as $article): ?>
                    <article class="news-card bento-card-item" <?= news_card_attrs($article) ?>>
                        <div class="card-img-wrap">
                            <span class="card-tag"><?= e($article['category_name'] ?: 'تقنية') ?></span>
                            <?php if (!empty($article['featured_image'])): ?>
                                <img src="<?= e(app_url($article['featured_image'])) ?>" alt="<?= e($article['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
                            <?php else: ?>
                                <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2rem"><?= ui_icon('general', '', 32) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3><a href="<?= e(app_url('article/' . $article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
                            <p><?= e($article['excerpt'] ?: 'تفاصيل شاملة حول أحدث الأخبار والتحليلات المتخصصة.') ?></p>
                            <div class="card-footer">
                                <small class="text-muted"><?= e(fmt_date($article['published_at'] ?: $article['created_at'])) ?></small>
                                <a class="read-more-link" href="<?= e(app_url('article/' . $article['slug'])) ?>">قراءة ←</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

    <?php elseif ($activeTemplate === 'digital_broadsheet'): ?>
        <!-- ================= 📰 TEMPLATE 6: DIGITAL BROADSHEET NEWSPAPER GRID ================= -->
        <section class="broadsheet-header-banner" style="margin-top:20px;margin-bottom:30px;padding-bottom:16px;border-bottom:3px double var(--border-medium)">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span style="font-size:0.85rem;color:var(--text-muted);font-weight:700">🗞️ النشرة الرقمية الموثوقة</span>
                <span class="text-muted small"><?= date('l, F j, Y') ?> · <?= e(Settings::get('site_name_ar', 'منصة الأخبار التقنية')) ?></span>
                <span style="font-size:0.85rem;color:var(--accent-primary);font-weight:700">تغطية مباشرة 24/7</span>
            </div>
        </section>

        <!-- 3-Column Broadsheet Grid -->
        <div class="broadsheet-3col-grid">
            <!-- Col 1: Left Wire & Quick Briefs (3.2 Cols) -->
            <aside class="broadsheet-col broadsheet-wire-col">
                <div class="broadsheet-col-head">
                    <h3><?= ui_icon('flame', 'text-danger', 16) ?> موجز الأخبار العاجلة</h3>
                </div>
                <div class="broadsheet-wire-list">
                    <?php foreach (array_slice($trending, 0, 6) as $idx => $tItem): ?>
                        <div class="broadsheet-wire-item" <?= news_card_attrs($tItem) ?>>
                            <span class="broadsheet-num"><?= $idx + 1 ?></span>
                            <div>
                                <h4><a href="<?= e(app_url('article/' . $tItem['slug'])) ?>"><?= e($tItem['title']) ?></a></h4>
                                <small class="text-muted"><?= e($tItem['category_name'] ?: 'أخبار') ?> · <?= e(fmt_date($tItem['published_at'] ?: $tItem['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>

            <!-- Col 2: Center Main Manchette & Lead (5.6 Cols) -->
            <section class="broadsheet-col broadsheet-main-col">
                <?php if ($featured): ?>
                    <article class="broadsheet-manchette" <?= news_card_attrs($featured) ?>>
                        <span class="badge-tag" style="background:#0f172a;border:1px solid var(--border-medium);color:var(--accent-primary);margin-bottom:12px;display:inline-block">
                            <?= e($featured['category_name'] ?: 'المانشيت التحريري') ?>
                        </span>
                        <h2 class="broadsheet-manchette-title">
                            <a href="<?= e(app_url('article/' . $featured['slug'])) ?>"><?= e($featured['title']) ?></a>
                        </h2>
                        <?php if (!empty($featured['featured_image'])): ?>
                            <div class="broadsheet-manchette-img">
                                <img src="<?= e(app_url($featured['featured_image'])) ?>" alt="<?= e($featured['title']) ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80';">
                            </div>
                        <?php endif; ?>
                        <p class="broadsheet-manchette-lead">
                            <?= e($featured['excerpt'] ?: 'تقرير تحريري معمق يسلط الضوء على خلفيات الحدث وأبعاده الاستراتيجية في قطاع التكنولوجيا والصناعة الذكية.') ?>
                        </p>
                        <div class="broadsheet-manchette-meta">
                            <span>بقلم <?= e($featured['author_name'] ?: 'هيئة التحرير') ?></span>
                            <span>·</span>
                            <span><?= e(fmt_date($featured['published_at'] ?: $featured['created_at'])) ?></span>
                            <a href="<?= e(app_url('article/' . $featured['slug'])) ?>" class="fw-bold" style="color:var(--accent-primary);margin-inline-start:auto">متابعة التقرير الكامل ←</a>
                        </div>
                    </article>
                <?php endif; ?>

                <!-- Secondary 2-Card Row inside Broadsheet -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:30px;padding-top:24px;border-top:1px solid var(--border-subtle)">
                    <?php foreach (array_slice($latest, 0, 2) as $sItem): ?>
                        <article class="broadsheet-sub-card" <?= news_card_attrs($sItem) ?>>
                            <span class="badge bg-light text-dark border small mb-2"><?= e($sItem['category_name'] ?: 'تقارير') ?></span>
                            <h4 style="font-size:1.05rem;font-weight:700;line-height:1.45;margin-bottom:8px">
                                <a href="<?= e(app_url('article/' . $sItem['slug'])) ?>" class="text-dark text-decoration-none"><?= e($sItem['title']) ?></a>
                            </h4>
                            <small class="text-muted d-block"><?= e(fmt_date($sItem['published_at'] ?: $sItem['created_at'])) ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Col 3: Right Analysis & Community (3.2 Cols) -->
            <aside class="broadsheet-col broadsheet-analysis-col">
                <div class="broadsheet-col-head">
                    <h3><?= ui_icon('ai', 'text-primary', 16) ?> تحليلات وملفات خاصة</h3>
                </div>
                <div style="display:flex;flex-direction:column;gap:18px">
                    <?php foreach (array_slice($latest, 2, 5) as $aItem): ?>
                        <article <?= news_card_attrs($aItem) ?> style="padding-bottom:14px;border-bottom:1px solid var(--border-subtle)">
                            <span class="small text-muted d-block mb-1"><?= e($aItem['category_name'] ?: 'تحليل تقني') ?></span>
                            <h4 style="font-size:0.95rem;font-weight:700;line-height:1.4;margin-bottom:6px">
                                <a href="<?= e(app_url('article/' . $aItem['slug'])) ?>" class="text-dark text-decoration-none"><?= e($aItem['title']) ?></a>
                            </h4>
                            <small class="text-muted"><?= e(fmt_date($aItem['published_at'] ?: $aItem['created_at'])) ?></small>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>

    <?php else: ?>
        <!-- ================= 📰 EDITORIAL VERGE / MINIMALIST / CYBER LAYOUTS ================= -->

        <!-- Hero / Featured Section -->
        <section class="hero-section" aria-label="أبرز الأخبار">
            <div class="hero-grid">
                <?php if ($featured): ?>
                    <div class="featured-card" <?= news_card_attrs($featured) ?> style="<?= !empty($featured['featured_image']) ? '--bg-img: url(' . e(app_url($featured['featured_image'])) . ')' : '' ?>">
                        <button class="card-bookmark-btn" 
                                data-article-id="<?= e($featured['id']) ?>"
                                data-title="<?= e($featured['title']) ?>"
                                data-url="<?= e(app_url('article/' . $featured['slug'])) ?>"
                                data-category="<?= e($featured['category_name'] ?: 'تقنية') ?>"
                                title="حفظ للقراءة لاحقاً"><?= ui_icon('bookmark', '', 15) ?></button>
                        <div class="featured-content">
                            <span class="badge-tag" style="display:inline-flex;align-items:center;gap:5px">
                                <?= ui_icon('ai', '', 14) ?> اختيار المحرر
                            </span>
                            <h2 class="featured-title">
                                <a href="<?= e(app_url('article/' . $featured['slug'])) ?>"><?= e($featured['title']) ?></a>
                            </h2>
                            <p class="featured-excerpt"><?= e($featured['excerpt'] ?: 'تحليل شامل ومفصل لأهم التطورات التقنية وتأثيرها المباشر على المستخدمين والشركات.') ?></p>
                            <div class="meta-row">
                                <span style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('author', '', 13) ?> بقلم <?= e($featured['author_name'] ?: 'فريق التحرير') ?></span>
                                <span class="dot"></span>
                                <span><?= e($featured['category_name'] ?: 'تقنية') ?></span>
                                <span class="dot"></span>
                                <span style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('read-time', '', 13) ?> 4 دقائق قراءة</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Sidebar Widgets -->
                <aside class="hero-sidebar">
                    <!-- Real Dynamic AI & Tech Radar -->
                    <div class="radar-widget">
                        <div class="radar-header">
                            <h3 style="display:flex;align-items:center;gap:8px">
                                <?= ui_icon('ai', 'text-primary', 18) ?>
                                <span>رادار الذكاء الاصطناعي والترندات</span>
                            </h3>
                            <span style="font-size:0.75rem;color:var(--accent-primary);font-weight:700">بيانات حية ومحدثة</span>
                        </div>
                        <div class="radar-grid">
                            <?php foreach ($radar as $rad): ?>
                                <div class="radar-item">
                                    <span class="tool-name"><?= e($rad['name']) ?></span>
                                    <span class="tool-desc"><?= e($rad['desc']) ?></span>
                                    <span class="tool-stat" style="display:inline-flex;align-items:center;gap:5px">
                                        <?= ui_icon($rad['icon'] ?? 'flame', 'text-primary', 13) ?>
                                        <span><?= e($rad['label'] ?? '') ?> (<?= e($rad['views'] ?? '') ?>)</span>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Trending Stories List -->
                    <div class="glass-panel">
                        <div class="panel-header">
                            <h3 style="display:flex;align-items:center;gap:8px">
                                <?= ui_icon('flame', 'text-warning', 18) ?>
                                <span>الأكثر قراءة ومتابعة</span>
                            </h3>
                            <a class="view-all" href="<?= e(app_url('search?sort=popular')) ?>">عرض الكل ↗</a>
                        </div>
                        <div class="trending-list">
                            <?php foreach ($trending as $index => $item): ?>
                                <a class="trending-card" href="<?= e(app_url('article/' . $item['slug'])) ?>" <?= news_card_attrs($item) ?>>
                                    <span class="trend-number"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                    <div class="trending-info">
                                        <h4><?= e($item['title']) ?></h4>
                                        <span><?= e($item['category_name'] ?: 'أخبار تقنية') ?> · قبل ساعات</span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        </section>

        <!-- Latest Articles Grid -->
        <section style="margin-top:20px" aria-label="أحدث المقالات">
            <div class="section-head">
                <h2 class="section-title">أحدث التحليلات والأخبار</h2>
                <div class="filter-tabs">
                    <button class="filter-btn active" type="button">الكل</button>
                    <a href="<?= e(app_url('tutorials')) ?>" class="filter-btn text-decoration-none" style="color:var(--accent-primary);border-color:var(--border-subtle);display:inline-flex;align-items:center;gap:5px">
                        <?= ui_icon('tutorials', '', 14) ?> <span>شروحات مصورة</span>
                    </a>
                    <button class="filter-btn" type="button">ذكاء اصطناعي</button>
                    <button class="filter-btn" type="button">أمن سيبراني</button>
                    <button class="filter-btn" type="button">أجهزة ذكية</button>
                </div>
            </div>

            <div class="news-grid">
                <?php foreach ($latest as $article): ?>
                    <article class="news-card" <?= news_card_attrs($article) ?>>
                        <div class="card-img-wrap">
                            <span class="card-tag"><?= e($article['category_name'] ?: 'تقنية') ?></span>
                            <button class="card-bookmark-btn" 
                                    data-article-id="<?= e($article['id']) ?>"
                                    data-title="<?= e($article['title']) ?>"
                                    data-url="<?= e(app_url('article/' . $article['slug'])) ?>"
                                    data-category="<?= e($article['category_name'] ?: 'تقنية') ?>"
                                    title="حفظ للقراءة"><?= ui_icon('bookmark-star', '', 14) ?></button>
                            <?php if (!empty($article['featured_image'])): ?>
                                <img src="<?= e(app_url($article['featured_image'])) ?>" alt="<?= e($article['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
                            <?php else: ?>
                                <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2rem"><?= ui_icon('general', '', 32) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3><a href="<?= e(app_url('article/' . $article['slug'])) ?>"><?= e($article['title']) ?></a></h3>
                            <p><?= e($article['excerpt'] ?: 'تفاصيل شاملة حول أحدث الأخبار والتحليلات المتخصصة مع شرح مبسط وموثوق.') ?></p>
                            <div class="card-footer">
                                <span style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('author', '', 13) ?> بقلم <?= e($article['author_name'] ?: 'فريق التحرير') ?></span>
                                <a class="read-more-link" href="<?= e(app_url('article/' . $article['slug'])) ?>">قراءة التفاصيل ←</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Homepage Ad Slot -->
        <?= site_ad_slot('ad_home_slot', 'container my-4 text-center homepage-ad-banner') ?>

        <!-- Step-by-Step Tutorials Section -->
        <?php if (!empty($tutorials)): ?>
            <section style="margin-top:40px" aria-label="الشروحات والدروس المصورة">
                <div class="section-head">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-tag" style="display:inline-flex;align-items:center;gap:5px">
                            <?= ui_icon('tutorials', '', 13) ?> جديد الأكاديمية
                        </span>
                        <h2 class="section-title mb-0">الشروحات والدروس المصورة خطوة بخطوة</h2>
                    </div>
                    <a class="view-all" href="<?= e(app_url('tutorials')) ?>" style="color:var(--accent-primary);font-weight:700">تصفح كافة الشروحات (<?= count($tutorials) ?>) ↗</a>
                </div>

                <div class="news-grid" style="grid-template-columns:repeat(auto-fill, minmax(320px, 1fr))">
                    <?php foreach ($tutorials as $tut): ?>
                        <article class="news-card">
                            <div class="card-img-wrap" style="height:190px">
                                <span class="card-tag" style="display:inline-flex;align-items:center;gap:5px">
                                    <?= ui_icon('tutorials', '', 12) ?> <?= (int)$tut['steps_count'] ?> خطوات مصورة
                                </span>
                                <?php if (!empty($tut['featured_image'])): ?>
                                    <img src="<?= e(app_url($tut['featured_image'])) ?>" alt="<?= e($tut['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&q=80';">
                                <?php else: ?>
                                    <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2.5rem"><?= ui_icon('tutorials', '', 36) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                    <?php if ($tut['difficulty'] === 'beginner'): ?>
                                        <span style="font-size:0.75rem;font-weight:700;color:#10b981;display:inline-flex;align-items:center;gap:4px">
                                            <span style="width:7px;height:7px;border-radius:50%;background:#10b981"></span> مبتدئ
                                        </span>
                                    <?php elseif ($tut['difficulty'] === 'intermediate'): ?>
                                        <span style="font-size:0.75rem;font-weight:700;color:#f59e0b;display:inline-flex;align-items:center;gap:4px">
                                            <span style="width:7px;height:7px;border-radius:50%;background:#f59e0b"></span> متوسط
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size:0.75rem;font-weight:700;color:#f43f5e;display:inline-flex;align-items:center;gap:4px">
                                            <span style="width:7px;height:7px;border-radius:50%;background:#f43f5e"></span> خبير ومتقدم
                                        </span>
                                    <?php endif; ?>
                                    <span style="font-size:0.75rem;color:var(--text-dim);display:inline-flex;align-items:center;gap:4px">
                                        <?= ui_icon('read-time', '', 12) ?> <?= (int)$tut['estimated_minutes'] ?> دقيقة
                                    </span>
                                </div>
                                <h3><a href="<?= e(app_url("tutorial/{$tut['slug']}")) ?>"><?= e($tut['title']) ?></a></h3>
                                <p><?= e($tut['summary'] ?: 'شرح تطبيقي مصور خطوة بخطوة مدعم بالأكواد والتوجيهات.') ?></p>
                                <div class="card-footer">
                                    <span>إعداد <?= e($tut['author_name'] ?: 'فريق الشروحات') ?></span>
                                    <a class="read-more-link" href="<?= e(app_url("tutorial/{$tut['slug']}")) ?>" style="color:var(--accent-primary);font-weight:700">ابدأ التطبيق ←</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Interactive Live Tech Poll Section (Database-Driven) -->
        <?php if (!empty($activePoll) && !empty($activePoll['options'])): ?>
            <section class="poll-box" id="interactive-tech-poll" data-poll-id="<?= (int)$activePoll['id'] ?>" data-has-voted="<?= !empty($activePoll['has_voted']) ? '1' : '0' ?>" data-voted-id="<?= (int)($activePoll['user_voted_option_id'] ?? 0) ?>">
                <div class="poll-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="poll-icon-badge"><?= ui_icon('flame', '', 20) ?></div>
                        <div>
                            <span class="poll-tag">استطلاع الأسبوع التفاعلي</span>
                            <h3 class="poll-question"><?= e($activePoll['question']) ?></h3>
                            <?php if (!empty($activePoll['description'])): ?>
                                <p style="font-size:0.82rem;color:var(--text-muted);margin:3px 0 0 0"><?= e($activePoll['description']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="poll-status-pill" id="poll-status-msg">
                        <?= !empty($activePoll['has_voted']) ? '<span style="color:var(--accent-primary)">✔️ تم تسجيل صوتك بنجاح</span>' : 'انقر على أي خيار للتصويت' ?>
                    </span>
                </div>

                <div class="poll-options">
                    <?php foreach ($activePoll['options'] as $opt): ?>
                        <div class="poll-opt <?= ((int)($activePoll['user_voted_option_id'] ?? 0) === (int)$opt['id']) ? 'voted' : '' ?>" 
                             data-opt-id="<?= (int)$opt['id'] ?>" 
                             data-percent="<?= (int)$opt['percent'] ?>" 
                             data-votes="<?= (int)$opt['votes_count'] ?>">
                            <div class="poll-bar" style="width: <?= (int)$opt['percent'] ?>%;"></div>
                            <div class="poll-opt-content">
                                <span class="poll-opt-title">
                                    <?= ui_icon($opt['icon'] ?? 'sparkle', 'text-primary', 18) ?>
                                    <span><?= e($opt['title']) ?></span>
                                </span>
                                <span class="poll-percent"><?= (int)$opt['percent'] ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="poll-footer">
                    <span class="poll-total-votes" id="poll-total-count">إجمالي الأصوات: <?= number_format((int)$activePoll['total_votes']) ?> مشاركاً</span>
                    <span style="font-size:0.78rem;color:var(--text-dim)">يتم حفظ الأصوات وتحديث النتائج فورياً</span>
                </div>
            </section>
        <?php endif; ?>

    <?php endif; ?>

</main>

<!-- Command Palette Modal -->
<div class="cmd-modal-backdrop" id="cmd-modal-backdrop">
    <div class="cmd-modal">
        <div class="cmd-input-wrap">
            <span style="color:var(--accent-primary);display:grid;place-items:center"><?= ui_icon('search', '', 18) ?></span>
            <input type="text" id="cmd-search-input" placeholder="اكتب للبحث الفوري في المقالات والتصنيفات..." autocomplete="off">
            <kbd style="background:var(--bg-surface);padding:4px 8px;border-radius:6px;border:1px solid var(--border-subtle);font-size:0.75rem">ESC</kbd>
        </div>
        <div class="cmd-results" id="cmd-results-list">
            <!-- Dynamically populated -->
        </div>
        <div class="cmd-footer">
            <span>استخدم <b>↑</b> <b>↓</b> للتنقل و <b>Enter</b> للاختيار</span>
            <span>منصة الأخبار التقنية الذكية</span>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
