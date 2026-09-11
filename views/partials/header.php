<?php
if (!function_exists('view_e')) {
    function view_e($val) {
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($categories)) {
    try {
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
    } catch (Exception $e) {
        $categories = [];
    }
}

$siteName       = Settings::get('site_name_ar', 'عصب التقنية');
$siteTagline    = Settings::get('site_tagline', 'نبض التكنولوجيا والذكاء الاصطناعي');
$cookieTheme    = $_COOKIE['site_theme'] ?? null;
$themeDefault   = in_array($cookieTheme, ['dark', 'light'], true) ? $cookieTheme : Settings::get('theme_default', 'dark');
$activeTemplate = Settings::get('site_theme_template', 'editorial_verge');
$pageTitle      = $pageTitle ?? ($siteName . ' | ' . $siteTagline);
$pageDesc       = $pageDesc ?? Settings::get('meta_description_default', Settings::get('meta_description', 'منصة عربية رائدة متخصصة في تغطية أحدث الأخبار والتحليلات التقنية والذكاء الاصطناعي.'));
$metaKeywords   = $metaKeywords ?? Settings::get('meta_keywords', 'أخبار تقنية, ذكاء اصطناعي, أمن سيبراني, برمجة, تقارير تكنولوجية');
$currentUri     = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$canonicalUrl   = $canonicalUrl ?? (rtrim(app_url(), '/') . ($currentUri ? '/' . $currentUri : ''));
$ogType         = $ogType ?? 'website';
$ogImage        = $ogImage ?? Settings::get('og_default_image');
if (empty($ogImage)) {
    $ogImage = app_url('uploads/brand/og_share.png');
}
$twitterHandle      = Settings::get('twitter_site_handle', '@TechNewsAr');
// Verification codes are forgiving: accept the bare token OR a full pasted
// <meta ... content="TOKEN" ...> tag (Search Console's copy button copies the
// whole tag). Normalize to the token so output is always a clean single tag.
$normalizeVerification = function ($v) {
    $v = trim((string) $v);
    if ($v === '') return '';
    if (stripos($v, '<meta') !== false && preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $v, $m)) {
        $v = trim($m[1]);
    }
    $v = trim(strip_tags(html_entity_decode($v, ENT_QUOTES, 'UTF-8')));
    return $v;
};
$googleVerification = $normalizeVerification(Settings::get('google_site_verification'));
$bingVerification   = $normalizeVerification(Settings::get('bing_site_verification'));
$ga4Id              = Settings::get('google_analytics_id');
?>
<!doctype html>
<html lang="ar" dir="rtl" data-theme="<?= view_e($themeDefault) ?>" data-template="<?= view_e($activeTemplate) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Instant Anti-Flicker / Zero FOUT Theme Initializer (Executes before any CSS paint) -->
    <script>
        (function() {
            try {
                var stored = localStorage.getItem('tech-platform-theme') || localStorage.getItem('site_theme') || localStorage.getItem('theme');
                if (stored === 'dark' || stored === 'light') {
                    document.documentElement.setAttribute('data-theme', stored);
                } else if ('<?= view_e($themeDefault) ?>' === 'auto') {
                    var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
                }
            } catch (e) {}
        })();
    </script>
    
    <!-- Primary SEO Metadata -->
    <title><?= view_e($pageTitle) ?></title>
    <meta name="description" content="<?= view_e($pageDesc) ?>">
    <meta name="keywords" content="<?= view_e($metaKeywords) ?>">
    <link rel="canonical" href="<?= view_e($canonicalUrl) ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Search Engine Verification Tags -->
    <?php if (!empty($googleVerification)): ?>
        <meta name="google-site-verification" content="<?= view_e($googleVerification) ?>">
    <?php endif; ?>
    <?php if (!empty($bingVerification)): ?>
        <meta name="msvalidate.01" content="<?= view_e($bingVerification) ?>">
    <?php endif; ?>

    <!-- Open Graph / Facebook / WhatsApp (Rich Social Cards) -->
    <meta property="og:site_name" content="<?= view_e($siteName) ?>">
    <meta property="og:type" content="<?= view_e($ogType) ?>">
    <meta property="og:title" content="<?= view_e($pageTitle) ?>">
    <meta property="og:description" content="<?= view_e($pageDesc) ?>">
    <meta property="og:url" content="<?= view_e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= view_e($ogImage) ?>">
    <meta property="og:locale" content="ar_AR">
    <meta property="og:locale:alternate" content="en_US">

    <!-- Twitter / X Cards (Large Image Summary) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="<?= view_e($twitterHandle) ?>">
    <meta name="twitter:creator" content="<?= view_e($twitterHandle) ?>">
    <meta name="twitter:title" content="<?= view_e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= view_e($pageDesc) ?>">
    <meta name="twitter:image" content="<?= view_e($ogImage) ?>">

    <!-- Global JSON-LD Structured Data: WebSite & SearchAction -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "WebSite",
                "@id": "<?= view_e(app_url('#website')) ?>",
                "url": "<?= view_e(app_url()) ?>",
                "name": "<?= view_e($siteName) ?>",
                "alternateName": "AsabTech",
                "description": "<?= view_e($siteTagline) ?>",
                "inLanguage": "ar",
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "<?= view_e(app_url('search?q={search_term_string}')) ?>",
                    "query-input": "required name=search_term_string"
                }
            },
            {
                "@type": "Organization",
                "@id": "<?= view_e(app_url('#organization')) ?>",
                "name": "<?= view_e($siteName) ?>",
                "alternateName": "AsabTech",
                "url": "<?= view_e(app_url()) ?>",
                "logo": {
                    "@type": "ImageObject",
                    "url": "<?= view_e(app_url('uploads/brand/logo.png')) ?>"
                }
            }
        ]
    }
    </script>

    <!-- Google Analytics 4 (GA4) -->
    <?php if (!empty($ga4Id)): ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?= view_e($ga4Id) ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?= view_e($ga4Id) ?>');
        </script>
    <?php endif; ?>

    <!-- Progressive Web App (PWA) & Mobile Metadata -->
    <link rel="manifest" href="<?= view_e(app_url('manifest.json')) ?>">
    <meta name="theme-color" content="#2563eb" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0b1120" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="<?= view_e($siteName) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= view_e(app_url('assets/images/icons/apple-touch-icon.png')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= view_e(app_url('assets/images/icons/favicon-32x32.png')) ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= view_e(app_url('assets/images/icons/favicon-16x16.png')) ?>">

    <?= site_favicon_tag() ?>
    <?= site_head_injections() ?>
    <link rel="stylesheet" href="<?= view_e(app_url('assets/css/site.css') . '?v=' . @filemtime(__DIR__ . '/../../assets/css/site.css')) ?>">
</head>
<body>

<!-- Breaking Headlines + Social Topbar (AITnews style) -->
<?php
$breakingEnabled = Settings::get('breaking_ticker_enabled', '1');
$breakingNews = [];
// NOTE: boolean settings arrive as real bool (Settings casts them), so use
// empty() which correctly treats false / '0' / 0 / '' as disabled.
if (!empty($breakingEnabled) && class_exists('Database')) {
    try {
        $breakingDb = new Database();
        $breakingNews = $breakingDb->fetchAll("SELECT title, title_ar, slug FROM articles WHERE status = 'published' ORDER BY published_at DESC, id DESC LIMIT 8");
    } catch (Throwable $e) { $breakingNews = []; }
}
$breakingSocials = [
    ['key' => 'social_facebook',  'icon' => 'facebook',  'label' => 'فيسبوك'],
    ['key' => 'social_x',         'icon' => 'twitter-x', 'label' => 'منصة X'],
    ['key' => 'social_linkedin',  'icon' => 'linkedin',  'label' => 'لينكدإن'],
    ['key' => 'social_youtube',   'icon' => 'youtube',   'label' => 'يوتيوب'],
    ['key' => 'social_instagram', 'icon' => 'instagram', 'label' => 'انستقرام'],
    ['key' => 'social_telegram',  'icon' => 'telegram',  'label' => 'تيلجرام'],
    ['key' => 'social_tiktok',    'icon' => 'tiktok',    'label' => 'تيك توك'],
    ['key' => 'social_whatsapp',  'icon' => 'whatsapp',  'label' => 'واتساب'],
];
$breakingSocialLinks = [];
foreach ($breakingSocials as $net) {
    $u = trim((string) Settings::get($net['key'], ''));
    if ($u !== '') { $net['url'] = $u; $breakingSocialLinks[] = $net; }
}
?>
<?php if (!empty($breakingNews) || !empty($breakingSocialLinks)): ?>
<div class="breaking-topbar">
    <div class="container breaking-topbar-inner">
        <?php if (!empty($breakingNews)): ?>
        <div class="breaking-news" role="marquee" aria-label="أحدث المستجدات التقنية">
            <span class="breaking-label">
                <?= ui_icon('bolt', '', 14) ?>
                <span class="breaking-label-text">أحدث المستجدات التقنية:</span>
            </span>
            <div class="breaking-viewport" id="breaking-viewport">
                <?php foreach ($breakingNews as $bi => $bart): ?>
                <?php $bTitle = trim((string) ($bart['title'] ?? '')) !== '' ? $bart['title'] : ($bart['title_ar'] ?? ''); ?>
                <p class="breaking-item<?= $bi === 0 ? ' active' : '' ?>">
                    <a href="<?= view_e(app_url('article/' . $bart['slug'])) ?>"><?= view_e(mb_strimwidth($bTitle, 0, 140, '…', 'UTF-8')) ?></a>
                </p>
                <?php endforeach; ?>
            </div>
            <div class="breaking-controls" role="group" aria-label="التنقل بين المستجدات">
                <button type="button" class="breaking-btn" id="breaking-prev" aria-label="الخبر السابق">‹</button>
                <button type="button" class="breaking-btn" id="breaking-next" aria-label="الخبر التالي">›</button>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($breakingSocialLinks)): ?>
        <ul class="breaking-social">
            <?php foreach ($breakingSocialLinks as $net): ?>
            <li>
                <a class="breaking-social-link" href="<?= view_e($net['url']) ?>" target="_blank" rel="external noopener nofollow" title="<?= view_e($net['label']) ?>" aria-label="<?= view_e($net['label']) ?>">
                    <?= ui_icon($net['icon'], '', 15) ?>
                    <span class="screen-reader-text"><?= view_e($net['label']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Live Tech Pulse & Financials Ticker -->
<?php 
$showTicker = Settings::get('enable_ticker');
if ($showTicker === null) {
    $showTicker = Settings::get('show_ticker', true);
}
if (!empty($showTicker) && $showTicker !== '0' && $showTicker !== false): 
?>
<div class="tech-pulse-bar">
    <div class="container ticker-wrap">
        <div class="ticker-label">
            <span class="pulse-dot"></span>
            <span>النبض التقني الحي</span>
        </div>
        <div class="ticker-track" id="pulse-ticker-track"></div>
    </div>
</div>
<?php endif; ?>

<!-- Header -->
<header class="site-header">
    <div class="container header-main">
        <?= site_brand_logo_html(36, true) ?>

        <!-- Interactive Header Live Search -->
        <form class="header-search-form" action="<?= view_e(app_url('search')) ?>" method="get" role="search">
            <div class="search-input-wrapper">
                <button type="submit" class="search-icon-btn" title="بحث" aria-label="بحث"><?= ui_icon('search', '', 16) ?></button>
                <input type="text" 
                       name="q" 
                       class="header-search-input" 
                       placeholder="ابحث في الأخبار والتقارير والشروحات..." 
                       value="<?= view_e($_GET['q'] ?? '') ?>" 
                       autocomplete="off" 
                       aria-label="ابحث في الموقع">
                <button type="button" class="search-kbd-trigger open-cmd-palette" title="البحث السريع المتقدم (Ctrl+K)">
                    <kbd>Ctrl+K</kbd>
                </button>
            </div>
        </form>

        <div class="header-actions">
            <!-- Bookmarks Drawer Trigger -->
            <button class="action-btn open-bookmarks-drawer" type="button" title="قائمة القراءة والمفضلة" aria-label="قائمة القراءة">
                <?= ui_icon('bookmark', 'action-icon-bookmark', 18) ?>
                <span class="badge-count bookmark-badge-count" style="display:none">0</span>
            </button>

            <!-- Dark / Light Mode Toggle -->
            <?php if (Settings::get('enable_theme_toggle', '1') == '1'): ?>
                <button class="action-btn theme-toggle" id="theme-toggle" type="button" title="تبديل المظهر" aria-label="تبديل المظهر">
                    <span class="theme-icon-light"><?= ui_icon('sun', '', 18) ?></span>
                    <span class="theme-icon-dark"><?= ui_icon('moon', '', 18) ?></span>
                </button>
            <?php endif; ?>

            <!-- User Menu / Login -->
            <?= site_user_menu_html() ?>
        </div>
    </div>

    <!-- Category Topics Navigation Bar (Unified Across Whole Site) -->
    <div class="header-topics">
        <div class="container">
            <nav class="topic-nav" aria-label="التصنيفات">
                <a class="topic-pill <?= empty($currentUri) ? 'active' : '' ?>" href="<?= view_e(app_url()) ?>">
                    <?= ui_icon('home', 'topic-icon', 15) ?>
                    <span>الرئيسية</span>
                </a>
                <a class="topic-pill <?= str_starts_with($currentUri, 'tutorials') || str_starts_with($currentUri, 'tutorial/') ? 'active' : '' ?>" href="<?= view_e(app_url('tutorials')) ?>">
                    <?= ui_icon('tutorials', 'topic-icon', 15) ?>
                    <span>الشروحات والدروس</span>
                </a>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php 
                            $isCatActive = (isset($category) && $category['id'] == $cat['id']) 
                                        || (isset($article) && ($article['category_id'] ?? 0) == $cat['id'])
                                        || str_starts_with($currentUri, 'category/' . $cat['slug']);
                        ?>
                        <a class="topic-pill <?= $isCatActive ? 'active' : '' ?>" href="<?= view_e(app_url('category/' . $cat['slug'])) ?>">
                            <?= category_icon_html($cat['slug'], 15) ?>
                            <span><?= view_e($cat['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a class="topic-pill <?= str_starts_with($currentUri, 'live-blog') ? 'active' : '' ?>" href="<?= view_e(app_url('live-blog')) ?>">
                    <?= ui_icon('live', 'topic-icon') ?>
                    <span>تغطية حية</span>
                </a>
                <a class="topic-pill <?= str_starts_with($currentUri, 'series') ? 'active' : '' ?>" href="<?= view_e(app_url('series')) ?>">
                    <?= ui_icon('series', 'topic-icon', 15) ?>
                    <span>سلاسل وملفات</span>
                </a>
            </nav>
        </div>
    </div>
</header>

<!-- Header Leaderboard Ad Slot (غير ظاهر في صفحات المقال لضبط كثافة الإعلانات) -->
<?php
$headerAdPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$isArticlePage = (bool) preg_match('#^/article/[^/]+$#i', $headerAdPath);
if (!$isArticlePage) {
    echo site_ad_slot('ad_header_slot', 'container text-center header-leaderboard-ad my-3');
}
?>

<!-- Flash Alerts -->
<div class="container" style="margin-top:20px">
    <?php if ($msg = Session::getFlash('success')): ?>
        <div class="toast" style="position:static;animation:none;margin-bottom:15px;background:rgba(16,185,129,0.12);border-color:#10b981;color:#10b981;display:flex;align-items:center;gap:8px">
            <?= ui_icon('check', 'text-success flex-shrink-0', 18) ?> <span><?= view_e($msg) ?></span>
        </div>
    <?php endif; ?>
    <?php if ($err = Session::getFlash('error')): ?>
        <div class="toast" style="position:static;animation:none;margin-bottom:15px;background:rgba(244,63,94,0.12);border-color:#f43f5e;color:#f43f5e;display:flex;align-items:center;gap:8px">
            <?= ui_icon('alert', 'text-danger flex-shrink-0', 18) ?> <span><?= view_e($err) ?></span>
        </div>
    <?php endif; ?>
</div>
