<?php
function article_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$shareUrl = app_url('article/' . $article['slug']);
$shareTitle = $article['title'];
$lead = $article['excerpt'] ?: 'نشرح لك خلفية الخبر، أهم تفاصيله، وما الذي يعنيه للمستخدم وصناعة التقنية.';

// وقت النشر المستخدم في الشارت والترميز المقروء (published_at أو created_at)
$articlePublishTs = strtotime($article['published_at'] ?: $article['created_at']);
if ($articlePublishTs === false || $articlePublishTs <= 0) $articlePublishTs = time();
$articlePublishIso = date('c', $articlePublishTs);
$readerTimeAgoOn = (bool) Settings::get('reader_time_ago_enabled', true);

$allowedArticleTags = '<p><br><strong><b><em><i><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><code><pre><img><hr><div><span>';
$articleContent = (string) ($article['content_ar'] ?? $article['content'] ?? '');

// Smart Link & Paragraph Formatter: If plain text, convert links and paragraphs safely
if (strpos($articleContent, '<p') === false && strpos($articleContent, '<h') === false && strpos($articleContent, '<div') === false) {
    $escaped = htmlspecialchars($articleContent, ENT_QUOTES, 'UTF-8');
    // Convert plain URLs into clickable links
    $escaped = preg_replace('/(https?:\/\/[^\s<]+)/i', '<a href="$1" target="_blank" rel="noopener noreferrer" class="source-link fw-bold" style="color:var(--accent-primary);text-decoration:underline">$1 ↗</a>', $escaped);
    $articleContent = nl2br($escaped);
} else {
    $articleContent = strip_tags($articleContent, $allowedArticleTags);
}

// Strip any legacy embedded source alert boxes so only the modern designated source card renders
$articleContent = preg_replace('/<hr\s*\/?>\s*<div class=[\'"]alert alert-light border my-3[\'"]>.*?<\/div>/si', '', $articleContent);
$articleContent = preg_replace('/<div class=[\'"]alert alert-light border my-3[\'"]>.*?<\/div>/si', '', $articleContent);

// Fetch comments for this article
$db = Database::getInstance();
$commStmt = $db->prepare("
    SELECT c.*, u.username, u.avatar
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.article_id = ? AND c.status = 'approved'
    ORDER BY c.created_at DESC
");
$commStmt->execute([(int) $article['id']]);
$commentsList = $commStmt->fetchAll(PDO::FETCH_ASSOC);

$currentUser = Auth::user();
$readingTime = (int) ($article['reading_time_minutes'] ?? 0);
if ($readingTime < 1) {
    $wordCount = count(preg_split('/\s+/u', trim(strip_tags($articleContent))));
    $readingTime = max(1, (int) ceil($wordCount / 180));
}

$pageTitle    = ($article['title_ar'] ?? $article['title']) . ' | ' . Settings::get('site_name_ar', 'منصة الأخبار التقنية');
$pageDesc     = $lead;
$ogType       = 'article';
$ogImage      = !empty($article['featured_image']) ? app_url($article['featured_image']) : null;
$canonicalUrl = app_url('article/' . $article['slug']);
$authorName   = $article['author_name'] ?? Settings::get('site_name_ar', 'هيئة التحرير');

require_once APP_ROOT . '/views/partials/header.php';
?>

<!-- Schema.org NewsArticle & Breadcrumbs Rich Snippet JSON-LD -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@graph": [
        {
            "@type": "NewsArticle",
            "mainEntityOfPage": {
                "@type": "WebPage",
                "@id": "<?= article_e($canonicalUrl) ?>"
            },
            "headline": <?= json_encode($article['title_ar'] ?? $article['title'], JSON_UNESCAPED_UNICODE) ?>,
            "description": <?= json_encode($lead, JSON_UNESCAPED_UNICODE) ?>,
            "image": [
                <?= json_encode($ogImage ?: app_url('uploads/brand/og_share.png')) ?>
            ],
            "datePublished": "<?= date('c', strtotime($article['created_at'])) ?>",
            "dateModified": "<?= date('c', strtotime($article['updated_at'] ?? $article['created_at'])) ?>",
            "author": {
                "@type": "Person",
                "name": <?= json_encode($authorName, JSON_UNESCAPED_UNICODE) ?>
            },
            "publisher": {
                "@type": "Organization",
                "name": <?= json_encode(Settings::get('site_name_ar', 'منصة الأخبار التقنية'), JSON_UNESCAPED_UNICODE) ?>,
                "logo": {
                    "@type": "ImageObject",
                    "url": "<?= article_e(app_url('uploads/brand/logo.png')) ?>"
                }
            },
            "articleSection": <?= json_encode($article['category_name'] ?? 'أخبار التكنولوجيا', JSON_UNESCAPED_UNICODE) ?>
        },
        {
            "@type": "BreadcrumbList",
            "itemListElement": [
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "الرئيسية",
                    "item": "<?= article_e(app_url()) ?>"
                },
                <?php if (!empty($article['category_name'])): ?>
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": <?= json_encode($article['category_name'], JSON_UNESCAPED_UNICODE) ?>,
                    "item": "<?= article_e(app_url('category/' . $article['category_slug'])) ?>"
                },
                {
                    "@type": "ListItem",
                    "position": 3,
                    "name": <?= json_encode($article['title_ar'] ?? $article['title'], JSON_UNESCAPED_UNICODE) ?>,
                    "item": "<?= article_e($canonicalUrl) ?>"
                }
                <?php else: ?>
                {
                    "@type": "ListItem",
                    "position": 2,
                    "name": <?= json_encode($article['title_ar'] ?? $article['title'], JSON_UNESCAPED_UNICODE) ?>,
                    "item": "<?= article_e($canonicalUrl) ?>"
                }
                <?php endif; ?>
            ]
        }
    ]
}
</script>

<!-- Dynamic Reading Progress Bar -->
<div class="reading-progress-bar" id="reading-progress"></div>

<!-- ================= 📖 ARTICLE READER CONTAINER ================= -->
<main class="article-page-wrap">

    <!-- Main Content Column -->
    <article class="article-card-main" <?= news_card_attrs($article) ?> data-role="article-page">
        
        <div class="<?= $activeTemplate === 'bento_grid' ? 'bento-article-header-box' : '' ?>">
            <!-- Top Navigation & Actions Bar -->
            <nav class="article-top-nav" aria-label="مسار المقال">
                <div class="article-breadcrumbs">
                    <a href="<?= article_e(app_url()) ?>" style="display:inline-flex;align-items:center;gap:5px">
                        <?= ui_icon('home', '', 14) ?> <span>الرئيسية</span>
                    </a>
                    <span class="bc-sep">/</span>
                    <?php if (!empty($article['category_name'])): ?>
                        <a href="<?= article_e(app_url('category/' . $article['category_slug'])) ?>" style="display:inline-flex;align-items:center;gap:5px">
                            <?= category_icon_html($article['category_slug'] ?? '', 14) ?> <span><?= article_e($article['category_name']) ?></span>
                        </a>
                        <span class="bc-sep">/</span>
                    <?php endif; ?>
                    <span style="color:var(--text-muted)">تفاصيل الخبر</span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button class="action-btn card-bookmark-btn" 
                            data-article-id="<?= article_e($article['id']) ?>"
                            data-title="<?= article_e($article['title']) ?>"
                            data-url="<?= article_e($shareUrl) ?>"
                            data-category="<?= article_e($article['category_name'] ?: 'تقنية') ?>"
                            title="حفظ للقراءة لاحقاً" style="position:static;width:36px;height:36px"><?= ui_icon('bookmark-star', '', 15) ?></button>
                </div>
            </nav>

            <!-- Refined Category Badge -->
            <?php if (!empty($article['category_name'])): ?>
                <div style="margin-bottom:14px">
                    <a href="<?= article_e(app_url('category/' . $article['category_slug'])) ?>" class="article-cat-badge">
                        <?= category_icon_html($article['category_slug'] ?? '', 14) ?>
                        <span><?= article_e($article['category_name']) ?></span>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Article Headline -->
            <h1 class="article-headline">
                <?= article_e($article['title']) ?>
            </h1>

            <!-- Executive Lead Highlight -->
            <div class="article-lead-box">
                <?= article_e($lead) ?>
            </div>

            <!-- Meta Information Ribbon -->
            <div class="article-meta-ribbon">
                <div class="author-meta-info">
                    <div class="author-avatar-img">
                        <?= mb_substr($article['author_name'] ?: 'م', 0, 1) ?>
                    </div>
                    <div>
                        <strong style="color:var(--text-main);display:block"><?= article_e($article['author_name'] ?: 'فريق التحرير') ?></strong>
                        <small style="color:var(--text-dim)">محرر ومحلل تقني</small>
                    </div>
                </div>

                <div class="meta-chips-wrap">
                    <span class="meta-chip" style="display:inline-flex;align-items:center;gap:5px">
                        <?= ui_icon('calendar', '', 14) ?>
                        <?php if ($readerTimeAgoOn): ?>
                            <time class="news-time-ago-static" datetime="<?= $articlePublishIso ?>"><?= article_e(fmt_relative_time($article['published_at'] ?: $article['created_at'])) ?></time>
                        <?php else: ?>
                            <?= article_e(fmt_date($article['published_at'] ?: $article['created_at'])) ?>
                        <?php endif; ?>
                    </span>
                    <?php if (Settings::get('enable_reading_time', '1') == '1'): ?>
                        <span class="meta-chip" style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('read-time', '', 14) ?> <?= $readingTime ?> دقائق قراءة</span>
                    <?php endif; ?>
                    <?php if (Settings::get('enable_views_counter', '1') == '1'): ?>
                        <span class="meta-chip" style="display:inline-flex;align-items:center;gap:5px"><?= ui_icon('eye', '', 14) ?> <?= number_format((int)($article['views_count'] ?? 0)) ?> قراءة</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Reader Comfort & Accessibility Toolbar -->
        <div class="reader-toolbar-ribbon">
            <div class="reader-tool-group">
                <span style="font-size:0.85rem;color:var(--text-muted);display:flex;align-items:center;gap:4px">
                    <?= ui_icon('general', '', 14) ?> <span>حجم الخط:</span>
                </span>
                <button type="button" class="reader-tool-btn" id="btn-font-dec" title="تصغير حجم الخط">A-</button>
                <button type="button" class="reader-tool-btn" id="btn-font-reset" title="الخط القياسي المريح">A</button>
                <button type="button" class="reader-tool-btn" id="btn-font-inc" title="تكبير حجم الخط">A+</button>
            </div>

            <div class="reader-tool-group">
                <button type="button" class="reader-tool-btn" id="btn-zen-mode" title="التركيز على القراءة وإخفاء العناصر المشتتة">
                    <span>📖 وضع القراءة الهادئ</span>
                </button>
            </div>
        </div>

        <!-- AI Executive Summary Card (Only if enabled in settings) -->
        <?php if (Settings::get('enable_ai_summary', '1') == '1' && $activeTemplate !== 'minimal_techcrunch'): ?>
            <div class="ai-summary-lux">
                <div class="ai-summary-head">
                    <span class="ai-glow-tag" style="display:inline-flex;align-items:center;gap:5px">
                        <?= ui_icon('ai', '', 15) ?> ملخص الذكاء الاصطناعي السريع
                    </span>
                    <span style="font-size:0.75rem;color:var(--text-muted)">تحليل فوري للمضمون</span>
                </div>
                <ul class="ai-bullets-list">
                    <li><?= article_e($lead) ?></li>
                    <li>تأثير مباشر على تجربة الاستخدام ومستقبل التقنية والأنظمة الذكية.</li>
                    <li>الخطوة تعكس وتيرة التسارع الحالية في أسواق التكنولوجيا والمنافسة العالمية.</li>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Smart Audio Player (Only if enabled in settings) -->
        <?php if (Settings::get('enable_audio_player', '1') == '1' && $activeTemplate !== 'minimal_techcrunch'): ?>
            <div class="audio-player-lux" id="smart-audio-player">
                <div class="audio-controls" style="display:flex;align-items:center;gap:12px">
                    <button class="audio-play-btn" id="audio-play-btn" type="button" title="استمع إلى المقال" style="width:36px;height:36px;border-radius:50%;background:var(--grad-primary);color:#ffffff;display:grid;place-items:center;font-weight:bold;cursor:pointer">
                        <?= ui_icon('play', '', 14) ?>
                    </button>
                    <div class="audio-info">
                        <strong style="display:flex;align-items:center;gap:6px;font-size:0.88rem">
                            <?= ui_icon('audio', 'text-primary', 15) ?>
                            <span>القارئ الصوتي الذكي</span>
                        </strong>
                        <span style="font-size:0.75rem;color:var(--text-muted)">استمع إلى قراءة هذا المقال بالذكاء الاصطناعي</span>
                    </div>
                </div>
                <div class="waveform-bars" style="display:flex;align-items:center;gap:3px">
                    <div class="wave-bar" style="width:3px;height:12px;background:var(--accent-primary);border-radius:2px"></div>
                    <div class="wave-bar" style="width:3px;height:20px;background:var(--accent-primary);border-radius:2px"></div>
                    <div class="wave-bar" style="width:3px;height:16px;background:var(--accent-primary);border-radius:2px"></div>
                    <div class="wave-bar" style="width:3px;height:24px;background:var(--accent-primary);border-radius:2px"></div>
                    <div class="wave-bar" style="width:3px;height:14px;background:var(--accent-primary);border-radius:2px"></div>
                </div>
                <button class="audio-speed-btn" id="audio-speed-btn" type="button" title="تغيير السرعة" style="padding:4px 10px;border-radius:6px;background:var(--bg-surface);border:1px solid var(--border-subtle);font-size:0.78rem;font-weight:700">1x</button>
            </div>
        <?php endif; ?>

        <!-- Featured Image Hero -->
        <?php if (!empty($article['featured_image'])): ?>
            <div class="article-hero-banner">
                <img src="<?= article_e(app_url($article['featured_image'])) ?>" alt="<?= article_e($article['title']) ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80';">
            </div>
        <?php endif; ?>

        <!-- Reader Main Content -->
        <div class="reader-article-content">
            <?= inject_in_article_ad($articleContent) ?>
        </div>

        <!-- Post Bottom Meta: Tags Cloud (AITnews style) -->
        <?php if (!empty($article['tags']) && is_array($article['tags'])): ?>
            <div class="post-bottom-meta post-bottom-tags">
                <div class="post-bottom-meta-title">
                    <span class="post-tags-icon" aria-hidden="true"><?= ui_icon('tags', '', 15) ?></span>
                    <span>الوسوم</span>
                </div>
                <span class="tagcloud">
                    <?php foreach ($article['tags'] as $postTag): ?>
                        <a href="<?= article_e(app_url('search?q=' . rawurlencode($postTag['name']))) ?>" rel="tag"><?= article_e($postTag['name']) ?></a>
                    <?php endforeach; ?>
                </span>
            </div>
        <?php endif; ?>

        <!-- Shortlink Copy Box (AITnews style) -->
        <div class="post-shortlink">
            <input type="text" id="short-post-url" value="<?= article_e(app_url('p/' . (int) $article['id'])) ?>" data-url="<?= article_e(app_url('p/' . (int) $article['id'])) ?>" readonly>
            <button type="button" id="copy-post-url" class="button">نسخ الرابط</button>
            <span id="copy-post-url-msg" style="display:none;">تم نسخ الرابط</span>
        </div>
        <script>
        (function() {
            var copyBtn = document.getElementById('copy-post-url');
            if (!copyBtn) return;
            copyBtn.addEventListener('click', function() {
                var copyText = document.getElementById('short-post-url');
                var msg = document.getElementById('copy-post-url-msg');
                if (!navigator.clipboard || !navigator.clipboard.writeText) {
                    copyText.select();
                    try { document.execCommand('copy'); } catch (e) {}
                    if (msg) { msg.style.display = 'block'; }
                    return;
                }
                navigator.clipboard.writeText(copyText.getAttribute('data-url') || copyText.value).then(function() {
                    if (msg) { msg.style.display = 'block'; }
                });
            });
        })();
        </script>

        <!-- Source Attribution Button (If exists) -->
        <?php if (!empty($article['source_url'])): ?>
            <div style="margin:28px 0;padding-top:20px;border-top:1px solid var(--border-subtle)">
                <a href="<?= article_e($article['source_url']) ?>" target="_blank" rel="noopener noreferrer" class="source-callout-pill" style="display:inline-flex;align-items:center;gap:10px;padding:12px 20px;border-radius:12px;background:var(--bg-surface-elevated);border:1px solid var(--border-medium);color:var(--text-main);text-decoration:none;font-size:0.94rem;box-shadow:var(--shadow-sm);transition:all 0.2s ease;">
                    <?= ui_icon('link', 'text-primary', 18) ?>
                    <span>المصدر الأصلي والتقرير الكامل:</span>
                    <strong style="color:var(--accent-primary)"><?= article_e($article['source_name'] ?: 'رابط المصدر المعتمد') ?></strong>
                    <span style="color:var(--accent-primary);font-size:1.1rem">↗</span>
                </a>
            </div>
        <?php endif; ?>

        <!-- Micro-Reactions Bar (If enabled in settings) -->
        <?php if (Settings::get('enable_reactions', '1') == '1'): ?>
            <div class="reactions-bar-lux">
                <h4 style="font-size:1.05rem;font-weight:800;color:var(--text-main);margin-bottom:6px">ما هو تقييمك وانطباعك عن هذا الخبر؟</h4>
                <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:0">شاركنا رأيك بنقرة واحدة:</p>
                <div class="reactions-grid-lux">
                    <button class="reaction-pill-btn <?= in_array('fire', $userReacted ?? []) ? 'active' : '' ?>" data-article-id="<?= article_e($article['id']) ?>" data-reaction="fire">
                        <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('flame', '', 14) ?> ممتاز</span>
                        <span class="rx-count count"><?= (int) ($reactions['fire'] ?? 0) ?></span>
                    </button>
                    <button class="reaction-pill-btn <?= in_array('rocket', $userReacted ?? []) ? 'active' : '' ?>" data-article-id="<?= article_e($article['id']) ?>" data-reaction="rocket">
                        <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('ai', '', 14) ?> ثوري</span>
                        <span class="rx-count count"><?= (int) ($reactions['rocket'] ?? 0) ?></span>
                    </button>
                    <button class="reaction-pill-btn <?= in_array('bulb', $userReacted ?? []) ? 'active' : '' ?>" data-article-id="<?= article_e($article['id']) ?>" data-reaction="bulb">
                        <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('general', '', 14) ?> مفيد</span>
                        <span class="rx-count count"><?= (int) ($reactions['bulb'] ?? 0) ?></span>
                    </button>
                    <button class="reaction-pill-btn <?= in_array('thinking', $userReacted ?? []) ? 'active' : '' ?>" data-article-id="<?= article_e($article['id']) ?>" data-reaction="thinking">
                        <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('tutorials', '', 14) ?> تحليلي</span>
                        <span class="rx-count count"><?= (int) ($reactions['thinking'] ?? 0) ?></span>
                    </button>
                    <button class="reaction-pill-btn <?= in_array('bolt', $userReacted ?? []) ? 'active' : '' ?>" data-article-id="<?= article_e($article['id']) ?>" data-reaction="bolt">
                        <span style="display:inline-flex;align-items:center;gap:4px"><?= ui_icon('security', '', 14) ?> هام</span>
                        <span class="rx-count count"><?= (int) ($reactions['bolt'] ?? 0) ?></span>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Social Share Bar -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-top:1px solid var(--border-subtle);border-bottom:1px solid var(--border-subtle);margin-top:28px;flex-wrap:wrap;gap:12px">
            <span style="font-weight:700;font-size:0.92rem;color:var(--text-main);display:flex;align-items:center;gap:6px">
                <?= ui_icon('share', 'text-primary', 16) ?>
                <span>نشر ومشاركة المقال:</span>
            </span>
            <div style="display:flex;gap:8px">
                <?php if (Settings::get('enable_share_twitter', '1') == '1'): ?>
                    <a class="action-btn" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= rawurlencode($shareUrl) ?>&text=<?= rawurlencode($shareTitle) ?>" title="مشاركة على X">𝕏</a>
                <?php endif; ?>
                <?php if (Settings::get('enable_share_whatsapp', '1') == '1'): ?>
                    <a class="action-btn" target="_blank" rel="noopener" href="https://wa.me/?text=<?= rawurlencode($shareTitle . ' ' . $shareUrl) ?>" title="مشاركة على واتساب"><?= ui_icon('comments', '', 16) ?></a>
                <?php endif; ?>
                <a class="action-btn" target="_blank" rel="noopener" href="https://t.me/share/url?url=<?= rawurlencode($shareUrl) ?>&text=<?= rawurlencode($shareTitle) ?>" title="مشاركة على تليجرام"><?= ui_icon('link', '', 16) ?></a>
                <a class="action-btn" target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" title="مشاركة على لينكد إن" style="font-weight:800;font-size:0.85rem">in</a>
                <button class="action-btn" type="button" onclick="navigator.clipboard.writeText('<?= article_e($shareUrl) ?>').then(()=>{ if(window.showToast) showToast('تم نسخ رابط المقال بنجاح!','✔️'); else alert('تم نسخ رابط المقال بنجاح!'); })" title="نسخ رابط المقال"><?= ui_icon('link', '', 16) ?></button>
            </div>
        </div>

        <!-- Bottom Article Ad Slot -->
        <?= site_ad_slot('ad_bottom_article_slot', 'bottom-article-ad-slot my-4 text-center') ?>

        <!-- Comments & Discussion Section -->
        <?php if (Settings::get('enable_comments', '1') == '1'): ?>
            <section id="comments-section" style="margin-top:36px;border-top:1px solid var(--border-subtle);padding-top:28px">
                <h3 style="font-size:1.3rem;font-weight:800;margin-bottom:20px;display:flex;align-items:center;gap:8px">
                    <?= ui_icon('comments', 'text-primary', 20) ?>
                    <span>النقاش والتعليقات (<?= count($commentsList) ?>)</span>
                </h3>

                <!-- Comment Submission Form -->
                <div class="side-widget-card" style="margin-bottom:28px;padding:24px;border-radius:var(--radius-card)">
                    <h4 style="font-size:1.05rem;font-weight:700;margin-bottom:14px;color:var(--accent-primary)">أضف تعليقك ورأيك حول الخبر:</h4>
                    <?php if (!$currentUser && Settings::get('allow_guest_comments', '1') != '1'): ?>
                        <div style="padding:16px;background:var(--bg-surface-elevated);border-radius:var(--radius-btn);border:1px solid var(--border-subtle);font-size:0.9rem;color:var(--text-muted);display:flex;align-items:center;gap:8px">
                            <?= ui_icon('security', 'text-muted', 16) ?>
                            <span>يرجى <a href="<?= article_e(app_url('login')) ?>" style="color:var(--accent-primary);font-weight:700">تسجيل الدخول</a> للمشاركة بالتعليق على هذا المقال.</span>
                        </div>
                    <?php else: ?>
                        <form action="<?= article_e(app_url('comment/store')) ?>" method="post">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="article_id" value="<?= article_e($article['id']) ?>">

                            <?php if ($currentUser): ?>
                                <div style="margin-bottom:12px;font-size:0.9rem;color:var(--text-muted)">
                                    التعليق بحساب: <strong style="color:var(--accent-primary)"><?= article_e($currentUser['username']) ?></strong>
                                </div>
                            <?php else: ?>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                                    <input type="text" name="guest_name" placeholder="اسمك أو لقبك" required style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:10px 14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.9rem">
                                    <input type="email" name="guest_email" placeholder="بريدك الإلكتروني (اختياري)" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:10px 14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.9rem">
                                </div>
                            <?php endif; ?>

                            <div style="margin-bottom:14px">
                                <textarea name="content" rows="3" required placeholder="اكتب تعليقك أو وجهة نظرك هنا..." style="width:100%;background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:12px 14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.95rem;line-height:1.6"></textarea>
                            </div>

                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn-primary-glow" style="padding:10px 24px;font-size:0.9rem;display:inline-flex;align-items:center;gap:6px">
                                    <span>نشر التعليق</span>
                                    <?= ui_icon('comments', '', 14) ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Comments Stream -->
                <?php if (empty($commentsList)): ?>
                    <div style="padding:28px;text-align:center;background:var(--bg-surface-elevated);border-radius:var(--radius-card);color:var(--text-muted);display:flex;align-items:center;justify-content:center;gap:8px">
                        <?= ui_icon('comments', 'text-muted', 16) ?>
                        <span>لا توجد تعليقات حتى الآن. كن أول من يشارك برأيه!</span>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:14px">
                        <?php foreach ($commentsList as $comm): ?>
                            <div class="side-widget-card" style="padding:18px 20px;border-radius:var(--radius-card)">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <div style="width:34px;height:34px;border-radius:50%;background:var(--grad-primary);display:grid;place-items:center;font-weight:700;color:#fff;font-size:0.85rem">
                                            <?= mb_substr($comm['username'] ?: $comm['guest_name'] ?: 'ق', 0, 1) ?>
                                        </div>
                                        <strong style="color:var(--accent-primary);font-size:0.95rem"><?= article_e($comm['username'] ?: $comm['guest_name'] ?: 'قارئ تقني') ?></strong>
                                    </div>
                                    <small style="color:var(--text-dim);font-size:0.8rem"><?= article_e(date('Y-m-d H:i', strtotime($comm['created_at']))) ?></small>
                                </div>
                                <p style="color:var(--text-main);font-size:0.95rem;line-height:1.7;margin:0"><?= nl2br(article_e($comm['content'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

    </article>

    <!-- ================= 📊 SIDEBAR (Visible on Non-Minimalist Templates) ================= -->
    <?php if ($activeTemplate !== 'minimal_techcrunch'): ?>
        <aside class="sidebar-sticky-wrap">
            
            <!-- Sidebar Ad Slot -->
            <?= site_ad_slot('ad_sidebar_slot', 'side-widget-card sidebar-ad-slot text-center') ?>

            <!-- Editorial Desk Card -->
            <div class="side-widget-card">
                <h4>هيئة التحرير والتحليل</h4>
                <p style="font-size:0.88rem;color:var(--text-muted);line-height:1.7;margin-bottom:12px">
                    تغطية متخصصة ومستقلة لأحدث تحولات الذكاء الاصطناعي، الأمن السيبراني، ومنظومات السحابة والعتاد.
                </p>
                <div style="display:flex;align-items:center;gap:8px;font-size:0.8rem;color:var(--accent-primary);font-weight:700">
                    <span class="pulse-dot"></span>
                    <span>فريق تحرير نشط على مدار الساعة</span>
                </div>
            </div>

            <!-- Related Articles -->
            <?php if (!empty($related)): ?>
                <div class="side-widget-card">
                    <h4>مقالات وأخبار ذات صلة</h4>
                    <div style="display:flex;flex-direction:column;gap:4px">
                        <?php foreach ($related as $rel): ?>
                            <a class="related-story-item" href="<?= article_e(app_url('article/' . $rel['slug'])) ?>" <?= news_card_attrs($rel) ?>>
                                <span class="related-story-thumb-wrap">
                                    <?php if (!empty($rel['featured_image'])): ?>
                                        <img class="related-story-thumb" src="<?= article_e(app_url($rel['featured_image'])) ?>" alt="<?= article_e($rel['title']) ?>" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=200&q=70';">
                                    <?php else: ?>
                                        <div class="related-story-thumb" style="background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:1.2rem">📰</div>
                                    <?php endif; ?>
                                </span>
                                <div class="related-story-info">
                                    <h5><?= article_e($rel['title']) ?></h5>
                                    <span style="font-size:0.75rem;color:var(--accent-primary);font-weight:600">قراءة سريعة ↗</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Newsletter Widget -->
            <?php if (Settings::get('enable_newsletter', '1') == '1'): ?>
                <div class="side-widget-card" style="background:var(--bg-surface-elevated);border-color:var(--border-subtle)">
                    <h4 style="color:var(--text-main)">ابقَ في الصدارة دائماً</h4>
                    <p style="font-size:0.84rem;color:var(--text-muted);margin-bottom:14px;line-height:1.6">
                        انضم لأكثر من 50,000 متخصص يتلقون ملخصنا الاستراتيجي لأهم ابتكارات الأسبوع.
                    </p>
                    <form action="<?= article_e(app_url('newsletter/subscribe')) ?>" method="post" style="display:flex;flex-direction:column;gap:10px">
                        <?= CSRF::field() ?>
                        <input type="email" name="email" required placeholder="أدخل بريدك الإلكتروني..." style="background:var(--bg-surface);border:1px solid var(--border-subtle);padding:10px 14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.88rem">
                        <button type="submit" class="btn-primary-glow" style="justify-content:center;padding:10px">
                            <span>اشتراك مجاني فوري</span>
                            <span>⚡</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

        </aside>
    <?php endif; ?>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
