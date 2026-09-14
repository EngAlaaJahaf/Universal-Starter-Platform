<?php
/**
 * Global Helper Functions for Universal Starter Platform
 */

if (!function_exists('admin_e')) {
    function admin_e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('view_e')) {
    function view_e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('site_timezone')) {
    function site_timezone()
    {
        static $tz = null;
        if ($tz === null) {
            $tz = class_exists('Settings') ? (string) Settings::get('timezone', 'Asia/Riyadh') : 'Asia/Riyadh';
            if ($tz === '' || !in_array($tz, timezone_identifiers_list(DateTimeZone::ALL_WITH_BC), true)) {
                $tz = 'Asia/Riyadh';
            }
        }
        return $tz;
    }
}

if (!function_exists('site_dt')) {
    function site_dt($dateStr)
    {
        if (empty($dateStr)) return null;
        try {
            $dt = new DateTime((string) $dateStr, new DateTimeZone('UTC'));
            $dt->setTimezone(new DateTimeZone(site_timezone()));
            return $dt;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('fmt_date')) {
    function fmt_date($dateStr, $format = null, $fallback = '-')
    {
        if (empty($dateStr)) return $fallback;
        try {
            $dt = site_dt($dateStr);
            if (!$dt) return $fallback;
            if ($format === null) {
                $format = class_exists('Settings') ? Settings::get('date_format', 'Y-m-d H:i') : 'Y-m-d H:i';
                if (!is_string($format) || $format === '') $format = 'Y-m-d H:i';
            }
            return $dt->format($format);
        } catch (Throwable $e) {
            return $fallback;
        }
    }
}

if (!function_exists('fmt_time_site')) {
    function fmt_time_site($dateStr, $format = 'H:i:s')
    {
        if (empty($dateStr)) return '';
        try {
            $dt = site_dt($dateStr);
            return $dt ? $dt->format($format) : '';
        } catch (Throwable $e) {
            return '';
        }
    }
}

if (!function_exists('form_datetime_local')) {
    function form_datetime_local($dateStr)
    {
        $dt = site_dt($dateStr);
        return $dt ? $dt->format('Y-m-d\TH:i') : '';
    }
}

if (!function_exists('storage_datetime')) {
    function storage_datetime($value)
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        try {
            $dt = new DateTime($value, new DateTimeZone(site_timezone()));
            $dt->setTimezone(new DateTimeZone('UTC'));
            return $dt->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?$/', $value)) {
                return preg_replace('/T/', ' ', $value);
            }
            return null;
        }
    }
}

if (!function_exists('site_today')) {
    function site_today($format = 'l, F j, Y')
    {
        return (new DateTime('now', new DateTimeZone(site_timezone())))->format($format);
    }
}

if (!function_exists('fmt_relative_time')) {
    function fmt_relative_time($dateStr)
    {
        if (empty($dateStr)) return '';
        $ts = strtotime((string) $dateStr);
        if ($ts === false || $ts <= 0) return '';
        $diff = time() - $ts;
        if ($diff < 0) $diff = 0;

        $units = [
            [31536000, 'سنة', 'سنتين', 'سنوات'],
            [2592000,  'شهر', 'شهرين', 'أشهر'],
            [604800,   'أسبوع', 'أسبوعين', 'أسابيع'],
            [86400,    'يوم', 'يومين', 'أيام'],
            [3600,     'ساعة', 'ساعتين', 'ساعات'],
            [60,       'دقيقة', 'دقيقتين', 'دقائق'],
        ];

        foreach ($units as [$secs, $one, $two, $many]) {
            if ($diff >= $secs) {
                $n = (int) floor($diff / $secs);
                if ($n === 1) return 'قبل ' . $one;
                if ($n === 2) return 'قبل ' . $two;
                return 'قبل ' . $n . ' ' . $many;
            }
        }
        return 'الآن';
    }
}

if (!function_exists('news_card_attrs')) {
    function news_card_attrs($article)
    {
        if (!is_array($article)) return '';
        $id = (int) ($article['id'] ?? 0);
        if ($id <= 0) return '';

        $ts = strtotime((string) ($article['published_at'] ?? ($article['created_at'] ?? '')));
        $ts = ($ts === false || $ts <= 0) ? time() : $ts;
        $hours = max(1, (int) (class_exists('Settings') ? Settings::get('reader_new_badge_hours', 24) : 24));

        return ' data-article-id="' . $id . '"'
             . ' data-published-at="' . $ts . '"'
             . ' data-new-hours="' . $hours . '"';
    }
}

if (!function_exists('site_favicon_tag')) {
    function site_favicon_tag()
    {
        $favicon = class_exists('Settings') ? Settings::get('site_favicon', '') : '';
        if (empty($favicon)) {
            $favicon = 'assets/images/favicon.ico';
        }
        $url = str_starts_with($favicon, 'http') ? $favicon : app_url($favicon);
        $type = 'image/x-icon';
        if (str_ends_with(strtolower($favicon), '.png')) $type = 'image/png';
        if (str_ends_with(strtolower($favicon), '.svg')) $type = 'image/svg+xml';
        if (str_ends_with(strtolower($favicon), '.webp')) $type = 'image/webp';
        
        $escaped = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return '<link rel="icon" type="' . $type . '" href="' . $escaped . '">' . "\n"
             . '    <link rel="shortcut icon" type="' . $type . '" href="' . $escaped . '">' . "\n"
             . '    <link rel="apple-touch-icon" href="' . $escaped . '">';
    }
}

if (!function_exists('site_brand_logo_html')) {
    function site_brand_logo_html($imgHeight = 38, $showTagline = true)
    {
        $siteName = class_exists('Settings') ? Settings::get('site_name_ar', 'منصتي الذكية') : 'منصتي الذكية';
        $siteTagline = class_exists('Settings') ? Settings::get('site_tagline', 'القالب الأساسي لتطوير تطبيقات ومواقع الويب') : '';
        $siteLogo = class_exists('Settings') ? Settings::get('site_logo', '') : '';

        $out = '<a class="brand" href="' . htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') . '">';
        if (!empty($siteLogo)) {
            $logoUrl = str_starts_with($siteLogo, 'http') ? $siteLogo : app_url($siteLogo);
            $out .= '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '" style="height:' . (int)$imgHeight . 'px;max-width:160px;object-fit:contain;border-radius:6px">';
        } else {
            $out .= '<div class="brand-icon">⚡</div>';
        }
        $out .= '<div class="brand-text">';
        $out .= '<span class="brand-title">' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '</span>';
        if ($showTagline && !empty($siteTagline)) {
            $out .= '<small>' . htmlspecialchars($siteTagline, ENT_QUOTES, 'UTF-8') . '</small>';
        }
        $out .= '</div>';
        $out .= '</a>';
        return $out;
    }
}

if (!function_exists('ui_icon')) {
    function ui_icon($name, $extraClass = '', $size = 16)
    {
        $sz = (int) $size;
        $cls = 'ui-icon ui-icon-' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . (!empty($extraClass) ? ' ' . htmlspecialchars($extraClass, ENT_QUOTES, 'UTF-8') : '');
        $baseAttr = 'class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

        switch ($name) {
            case 'admin':
            case 'dashboard':
            case 'layout-dashboard':
                return '<svg ' . $baseAttr . '><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M9 21V9"/></svg>';
            case 'rss':
            case 'general':
            case 'feed':
                return '<svg ' . $baseAttr . '><path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg>';
            case 'edit':
            case 'pencil':
                return '<svg ' . $baseAttr . '><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>';
            case 'trash':
            case 'delete':
                return '<svg ' . $baseAttr . '><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>';
            case 'star':
                return '<svg ' . $baseAttr . '><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
            case 'tags':
                return '<svg ' . $baseAttr . '><path d="M2 2h8l10 10-8 8L2 10z"/><circle cx="6.5" cy="6.5" r="1.5"/></svg>';
            case 'tutorials':
            case 'graduation':
                return '<svg ' . $baseAttr . '><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>';
            case 'ai':
            case 'sparkle':
                return '<svg ' . $baseAttr . '><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>';
            case 'security':
            case 'shield':
                return '<svg ' . $baseAttr . '><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>';
            case 'live':
            case 'radio':
                return '<svg ' . $baseAttr . '><circle cx="12" cy="12" r="2"/><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"/></svg>';
            case 'search':
                return '<svg ' . $baseAttr . '><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';
            case 'bookmark':
                return '<svg ' . $baseAttr . '><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>';
            case 'calendar':
                return '<svg ' . $baseAttr . '><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>';
            case 'clock':
                return '<svg ' . $baseAttr . '><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
            case 'author':
            case 'user':
                return '<svg ' . $baseAttr . '><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
            default:
                return '<svg ' . $baseAttr . '><path d="M12 2v20M2 12h20"/></svg>';
        }
    }
}

if (!function_exists('site_user_menu_html')) {
    function site_user_menu_html()
    {
        $user = Auth::user();
        if ($user) {
            $isAdmin = Auth::isAdmin();
            $adminLink = $isAdmin ? '<a class="menu-item" href="' . htmlspecialchars(app_url('admin'), ENT_QUOTES, 'UTF-8') . '"><span style="color:var(--accent-primary)">⚡</span> <span>لوحة الإدارة</span></a>' : '';
            return '<div class="user-dropdown-wrap">
                <button class="action-btn user-avatar-btn" type="button" aria-haspopup="true" aria-expanded="false" title="حساب: ' . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars(mb_substr($user['username'], 0, 1), ENT_QUOTES, 'UTF-8') . '</button>
                <div class="user-dropdown-menu">
                    <div class="menu-header">مرحباً بك، <strong>' . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . '</strong></div>
                    ' . $adminLink . '
                    <a class="menu-item" href="' . htmlspecialchars(app_url('profile'), ENT_QUOTES, 'UTF-8') . '"><span>👤</span> <span>إعدادات الحساب</span></a>
                    <div style="border-top:1px solid var(--border-subtle);margin:4px 0"></div>
                    <a class="menu-item text-danger" href="' . htmlspecialchars(app_url('logout'), ENT_QUOTES, 'UTF-8') . '"><span>🚪</span> <span>تسجيل الخروج</span></a>
                </div>
            </div>';
        }
        return '<a class="login-nav-btn" href="' . htmlspecialchars(app_url('login'), ENT_QUOTES, 'UTF-8') . '">تسجيل الدخول</a>';
    }
}

if (!function_exists('site_head_injections')) {
    function site_head_injections()
    {
        $primaryColor = class_exists('Settings') ? Settings::get('primary_color', '#00f2fe') : '#00f2fe';
        $fontFamily   = class_exists('Settings') ? Settings::get('font_family', 'Tajawal') : 'Tajawal';
        $customCss    = class_exists('Settings') ? Settings::get('custom_css', '') : '';
        $html = '';
        if (!empty($primaryColor) && $primaryColor !== '#00f2fe') {
            $html .= '    <style>:root { --cyan: ' . htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8') . ' !important; --primary: ' . htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8') . ' !important; }</style>' . "\n";
        }
        if (!empty($customCss)) {
            $html .= "    <style>/* Custom CSS */\n" . $customCss . "\n</style>\n";
        }
        return $html;
    }
}

if (!function_exists('site_footer_injections')) {
    function site_footer_injections()
    {
        $customJsFoot = Settings::get('custom_js_footer', '');
        return !empty($customJsFoot) ? "\n<!-- Custom Footer Scripts from Settings -->\n" . $customJsFoot . "\n" : '';
    }
}

if (!function_exists('site_ad_slot')) {
    function site_ad_slot($slotKey, $containerClass = '')
    {
        $adsEnabled = Settings::get('enable_ads', Settings::get('ads_enabled', '1'));
        if ($adsEnabled != '1' && $adsEnabled !== 1 && $adsEnabled !== true) {
            return '';
        }

        static $placementMap = [
            'ad_header_slot'         => ['header_top', 'header', 'ad_header_slot'],
            'header_top'             => ['header_top', 'header', 'ad_header_slot'],
            'header'                 => ['header_top', 'header', 'ad_header_slot'],
            'ad_sidebar_slot'        => ['sidebar', 'sidebar_sticky', 'ad_sidebar_slot'],
            'sidebar'                => ['sidebar', 'sidebar_sticky', 'ad_sidebar_slot'],
            'ad_in_article_slot'     => ['article_inside', 'in_article', 'ad_in_article_slot'],
            'article_inside'         => ['article_inside', 'in_article', 'ad_in_article_slot'],
            'in_article'             => ['article_inside', 'in_article', 'ad_in_article_slot'],
            'ad_home_slot'           => ['between_articles', 'between_content', 'home', 'ad_home_slot'],
            'between_articles'       => ['between_articles', 'between_content', 'home', 'ad_home_slot'],
            'ad_bottom_article_slot' => ['footer', 'footer_banner', 'bottom_article', 'ad_bottom_article_slot'],
            'footer'                 => ['footer', 'footer_banner', 'bottom_article', 'ad_bottom_article_slot'],
        ];

        $searchPlacements = $placementMap[$slotKey] ?? [$slotKey];

        static $cachedAds = null;
        if ($cachedAds === null) {
            $cachedAds = [];
            try {
                $db = new Database();
                $now = date('Y-m-d H:i:s');
                $rows = $db->fetchAll(
                    "SELECT * FROM ads 
                     WHERE status = 'active' 
                       AND (start_at IS NULL OR start_at = '0000-00-00 00:00:00' OR start_at = '' OR start_at <= :now1) 
                       AND (end_at IS NULL OR end_at = '0000-00-00 00:00:00' OR end_at = '' OR end_at >= :now2)
                       AND (start_date IS NULL OR start_date = '0000-00-00' OR start_date <= CURDATE())
                       AND (end_date IS NULL OR end_date = '0000-00-00' OR end_date >= CURDATE())
                     ORDER BY priority DESC, id DESC",
                    [':now1' => $now, ':now2' => $now]
                );
                foreach ($rows as $row) {
                    $p = strtolower(trim((string)$row['placement']));
                    if (!isset($cachedAds[$p])) {
                        $cachedAds[$p] = $row;
                    }
                }
            } catch (Throwable $e) {
                $cachedAds = [];
            }
        }

        $ad = null;
        foreach ($searchPlacements as $p) {
            $p = strtolower($p);
            if (isset($cachedAds[$p])) {
                $ad = $cachedAds[$p];
                break;
            }
        }

        $contentHtml = '';
        if ($ad) {
            try {
                $db = new Database();
                $db->query("UPDATE ads SET impressions = impressions + 1, impressions_count = impressions_count + 1 WHERE id = :id", [':id' => (int)$ad['id']]);
            } catch (Throwable $e) {}

            $adType = $ad['ad_type'] ?? 'html';
            $image = !empty($ad['image_path']) ? $ad['image_path'] : ($ad['image_url'] ?? '');
            $url = !empty($ad['target_url']) ? $ad['target_url'] : '#';
            $code = !empty($ad['html_code']) ? $ad['html_code'] : ($ad['code'] ?? '');

            if ($adType === 'image' && !empty($image)) {
                $imgSrc = (strpos($image, 'http') === 0 || strpos($image, '//') === 0) ? $image : app_url($image);
                $contentHtml = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="nofollow noopener" class="site-ad-link d-inline-block">'
                             . '<img src="' . htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($ad['name'] ?? 'إعلان', ENT_QUOTES, 'UTF-8') . '" class="img-fluid rounded site-ad-banner" />'
                             . '</a>';
            } elseif ($adType === 'text') {
                $contentHtml = '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="nofollow noopener" class="site-ad-text-link">'
                             . htmlspecialchars($ad['name'] ?? 'إعلان مميز', ENT_QUOTES, 'UTF-8')
                             . '</a>';
            } else {
                $contentHtml = $code;
            }
        }

        if (empty($contentHtml)) {
            $code = trim((string) Settings::get($slotKey, ''));
            if (!empty($code)) {
                $contentHtml = $code;
            }
        }

        if (empty($contentHtml)) {
            return '';
        }

        return '<div class="site-ad-wrapper ' . htmlspecialchars($containerClass, ENT_QUOTES, 'UTF-8') . '" data-ad-slot="' . htmlspecialchars($slotKey, ENT_QUOTES, 'UTF-8') . '">'
             . '  <div class="ad-badge-header"><span>إعلان</span></div>'
             . '  <div class="ad-content-box">' . $contentHtml . '</div>'
             . '</div>';
    }
}

if (!function_exists('inject_in_article_ad')) {
    function inject_in_article_ad($articleHtml)
    {
        $adHtml = site_ad_slot('ad_in_article_slot', 'in-article-ad-slot my-4 text-center');
        if (empty($adHtml)) {
            return $articleHtml;
        }

        $closingTag = '</p>';
        $paragraphs = explode($closingTag, $articleHtml);
        $totalP = count($paragraphs) - 1;

        if ($totalP >= 2) {
            $result = '';
            foreach ($paragraphs as $idx => $p) {
                if ($idx < $totalP) {
                    $result .= $p . $closingTag;
                    if ($idx === 1 || ($totalP == 2 && $idx === 0)) {
                        $result .= "\n" . $adHtml . "\n";
                    }
                } else {
                    $result .= $p;
                }
            }
            return $result;
        }

        return $articleHtml . "\n" . $adHtml;
    }
}

if (!function_exists('get_default_category_id')) {
    function get_default_category_id($db = null)
    {
        if (!$db) {
            $db = new Database();
        }
        $general = $db->fetch("SELECT id FROM categories WHERE slug IN ('general', 'general-tech', 'main') ORDER BY id ASC LIMIT 1");
        if ($general) return (int)$general['id'];
        $first = $db->fetch("SELECT id FROM categories ORDER BY id ASC LIMIT 1");
        return $first ? (int)$first['id'] : 1;
    }
}
