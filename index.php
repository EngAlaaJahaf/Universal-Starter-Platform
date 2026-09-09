<?php

ini_set('display_errors', '0');
error_reporting(E_ALL);

// Datetimes are stored/compared in UTC (see Database.php); display helpers
// convert to the site timezone setting. Pin PHP's default so strtotime/date
// parse stored UTC strings consistently on any host.
date_default_timezone_set('UTC');

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/database.php';

// Spl Autoloader for Core, Models, and Controllers
spl_autoload_register(function ($class) {
    if ($class === 'AdminLiveBlogController') {
        require_once APP_ROOT . '/controllers/admin/LiveBlogController.php';
        return;
    }

    $paths = [
        APP_ROOT . '/core/' . $class . '.php',
        APP_ROOT . '/models/' . $class . '.php',
        APP_ROOT . '/controllers/' . $class . '.php',
        APP_ROOT . '/controllers/admin/' . $class . '.php',
        APP_ROOT . '/controllers/api/' . $class . '.php',
        APP_ROOT . '/controllers/api/v1/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Start Session safely
Session::start();

// Helper Functions
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

if (!function_exists('app_url')) {
    function app_url($path = '')
    {
        $base = defined('APP_URL') ? rtrim(APP_URL, '/') : '';
        if (empty($base)) {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000';
            $base = $scheme . '://' . $host;
        }
        $path = ltrim((string) $path, '/');
        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('site_timezone')) {
    function site_timezone()
    {
        static $tz = null;
        if ($tz === null) {
            $tz = (string) Settings::get('timezone', 'Asia/Riyadh');
            if ($tz === '' || !in_array($tz, timezone_identifiers_list(DateTimeZone::ALL_WITH_BC), true)) {
                $tz = 'Asia/Riyadh';
            }
        }
        return $tz;
    }
}

/**
 * تحويل طابع زمني مخزَّن (UTC) إلى وقت الموقع المحلي (حسب إعداد المنطقة الزمنية).
 */
if (!function_exists('site_dt')) {
    function site_dt($dateStr)
    {
        if (empty($dateStr)) return null;
        $dt = new DateTime((string) $dateStr, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone(site_timezone()));
        return $dt;
    }
}

if (!function_exists('fmt_date')) {
    function fmt_date($dateStr, $fallback = '-')
    {
        if (empty($dateStr)) return $fallback;
        try {
            $dt = site_dt($dateStr);
            if (!$dt) return $fallback;
            $fmt = Settings::get('date_format', 'Y-m-d H:i');
            if (!is_string($fmt) || $fmt === '') $fmt = 'Y-m-d H:i';
            return $dt->format($fmt);
        } catch (Throwable $e) {
            return $fallback;
        }
    }
}

/**
 * وقت نسبي بالصيغة العادية (H:i / H:i:s) بمنطقة الموقع المحلية،
 * للاستخدام في الساعات المباشرة والتعليقات وغيرها.
 */
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

/**
 * قيمة حقل <input type="datetime-local"> في لوحة التحكم: من UTC المخزَّن إلى وقت الموقع.
 */
if (!function_exists('form_datetime_local')) {
    function form_datetime_local($dateStr)
    {
        $dt = site_dt($dateStr);
        return $dt ? $dt->format('Y-m-d\TH:i') : '';
    }
}

/**
 * تحويل قيمة حقل الوقت من نموذج اللوحة (بوقت الموقع) إلى UTC قبل الحفظ في قاعدة البيانات.
 */
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

/**
 * تاريخ اليوم الحالي بمنطقة الموقع المحلية (بدون التوقيت).
 */
if (!function_exists('site_today')) {
    function site_today($format = 'l, F j, Y')
    {
        return (new DateTime('now', new DateTimeZone(site_timezone())))->format($format);
    }
}

/**
 * صياغة زمن نسبي بالعربية (قبل 5 دقائق، قبل ساعة، قبل يومين...).
 */
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

/**
 * سمات بيانات (data-*) تُضاف لبطاقات المقالات لتمكين محرك
 * تتبع المقروء/غير المقروء وعرض الوقت النسبي من جهة المتصفح.
 */
if (!function_exists('news_card_attrs')) {
    function news_card_attrs($article)
    {
        if (!is_array($article)) return '';
        $id = (int) ($article['id'] ?? 0);
        if ($id <= 0) return '';

        $ts = strtotime((string) ($article['published_at'] ?? ($article['created_at'] ?? '')));
        $ts = ($ts === false || $ts <= 0) ? time() : $ts;
        $hours = max(1, (int) Settings::get('reader_new_badge_hours', 24));

        return ' data-article-id="' . $id . '"'
             . ' data-published-at="' . $ts . '"'
             . ' data-new-hours="' . $hours . '"';
    }
}

if (!function_exists('site_favicon_tag')) {
    function site_favicon_tag()
    {
        $favicon = Settings::get('site_favicon', '');
        if (empty($favicon)) {
            $favicon = 'assets/images/favicon.ico';
        }
        // Search engines & browsers ignore oversized icons — fall back to the
        // bundled small icon so Google/Chrome still display one.
        if (strpos($favicon, '://') === false && !empty($favicon)) {
            $local = APP_ROOT . '/' . ltrim($favicon, '/');
            if (is_file($local) && filesize($local) > 200 * 1024) {
                $favicon = 'assets/images/favicon.ico';
            }
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
        $siteName = Settings::get('site_name_ar', 'منصة الأخبار التقنية');
        $siteTagline = Settings::get('site_tagline', 'نبض التكنولوجيا والذكاء الاصطناعي');
        $siteLogo = Settings::get('site_logo', '');

        $out = '<a class="brand" href="' . htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') . '">';
        if (!empty($siteLogo)) {
            $logoUrl = str_starts_with($siteLogo, 'http') ? $siteLogo : app_url($siteLogo);
            $out .= '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') . '" style="height:' . (int)$imgHeight . 'px;max-width:160px;object-fit:contain;border-radius:6px">';
        } else {
            $out .= '<div class="brand-icon">T</div>';
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
            case 'close':
            case 'x':
                return '<svg ' . $baseAttr . '><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
            case 'plus':
            case 'add':
                return '<svg ' . $baseAttr . '><path d="M5 12h14"/><path d="M12 5v14"/></svg>';
            case 'copy':
            case 'clipboard':
                return '<svg ' . $baseAttr . '><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
            case 'star':
                return '<svg ' . $baseAttr . '><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
            case 'tags':
                return '<svg ' . $baseAttr . '><path d="M2 2h8l10 10-8 8L2 10z"/><circle cx="6.5" cy="6.5" r="1.5"/></svg>';
            case 'poll':
            case 'bar-chart':
                return '<svg ' . $baseAttr . '><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>';
            case 'refresh':
            case 'arrow-repeat':
                return '<svg ' . $baseAttr . '><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>';
            case 'external-link':
                return '<svg ' . $baseAttr . '><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" x2="21" y1="14" y2="3"/></svg>';
            case 'home':
            case 'flame':
                return '<svg ' . $baseAttr . '><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>';
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
            case 'bookmark-star':
                return '<svg ' . $baseAttr . '><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg>';
            case 'sun':
                return '<svg ' . $baseAttr . '><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
            case 'moon':
                return '<svg ' . $baseAttr . '><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>';
            case 'calendar':
                return '<svg ' . $baseAttr . '><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>';
            case 'read-time':
            case 'clock':
                return '<svg ' . $baseAttr . '><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
            case 'eye':
                return '<svg ' . $baseAttr . '><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
            case 'author':
            case 'user':
                return '<svg ' . $baseAttr . '><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
            case 'link':
                return '<svg ' . $baseAttr . '><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
            case 'share':
                return '<svg ' . $baseAttr . '><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>';
            case 'comments':
                return '<svg ' . $baseAttr . '><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
            case 'check':
                return '<svg ' . $baseAttr . '><polyline points="20 6 9 17 4 12"/></svg>';
            case 'alert':
                return '<svg ' . $baseAttr . '><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>';
            case 'play':
                return '<svg ' . $baseAttr . '><polygon points="5 3 19 12 5 21 5 3"/></svg>';
            case 'audio':
                return '<svg ' . $baseAttr . '><path d="M11 5 6 9H2v6h4l5 4V5z"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>';
            case 'series':
                return '<svg ' . $baseAttr . '><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>';
            case 'bolt':
            case 'zap':
                return '<svg ' . $baseAttr . '><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>';
            case 'facebook':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
            case 'twitter-x':
            case 'x-logo':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>';
            case 'youtube':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>';
            case 'instagram':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>';
            case 'linkedin':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
            case 'telegram':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>';
            case 'tiktok':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>';
            case 'whatsapp':
                return '<svg class="' . $cls . '" width="' . $sz . '" height="' . $sz . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>';
            default:
                return '<svg ' . $baseAttr . '><path d="M12 2v20M2 12h20"/></svg>';
        }
    }
}

if (!function_exists('category_icon_html')) {
    function category_icon_html($slug, $size = 14)
    {
        return ui_icon('sparkle', 'cat-icon', $size);
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
        $primaryColor = Settings::get('primary_color', Settings::get('theme_primary_color', '#00f2fe'));
        $fontFamily   = Settings::get('font_family', 'Tajawal');
        $customCss    = Settings::get('custom_css', '');
        $customJsHead = Settings::get('custom_js_header', '');
        $gaId         = Settings::get('google_analytics_id', '');

        $html = '';
        if ($fontFamily && $fontFamily !== 'Tajawal') {
            $fontSlug = str_replace(' ', '+', $fontFamily);
            $html .= '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
            $html .= '    <link href="https://fonts.googleapis.com/css2?family=' . htmlspecialchars($fontSlug, ENT_QUOTES, 'UTF-8') . ':wght@400;500;600;700;800&display=swap" rel="stylesheet">' . "\n";
            $html .= '    <style>:root { --site-font: "' . htmlspecialchars($fontFamily, ENT_QUOTES, 'UTF-8') . '", "IBM Plex Sans Arabic", "Tajawal", sans-serif; --font-body: var(--site-font); --font-heading: var(--site-font); } body { font-family: var(--site-font); }</style>' . "\n";
        }
        if (!empty($primaryColor) && $primaryColor !== '#00f2fe') {
            $html .= '    <style>:root { --cyan: ' . htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8') . ' !important; --primary: ' . htmlspecialchars($primaryColor, ENT_QUOTES, 'UTF-8') . ' !important; }</style>' . "\n";
        }
        if (!empty($customCss)) {
            $html .= "    <style>/* Custom CSS from Settings */\n" . $customCss . "\n</style>\n";
        }
        if (!empty($gaId)) {
            $html .= '    <script async src="https://www.googletagmanager.com/gtag/js?id=' . htmlspecialchars($gaId, ENT_QUOTES, 'UTF-8') . '"></script>' . "\n";
            $html .= '    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . htmlspecialchars($gaId, ENT_QUOTES, 'UTF-8') . '");</script>' . "\n";
        }
        if (!empty($customJsHead)) {
            $html .= "    <!-- Custom Header Scripts from Settings -->\n" . $customJsHead . "\n";
        }
        $adsEnabled = Settings::get('ads_enabled', '1');
        $adsHead = Settings::get('ads_header_code', '');
        if ($adsEnabled == '1' && !empty($adsHead)) {
            $html .= "    <!-- Ads Global Head Script -->\n" . $adsHead . "\n";
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
        // لا تُعرض الإعلانات لمشرفي المنصة (يمنع النقرات/المشاهدات الذاتية)
        if (Auth::isLoggedIn() && Auth::isAdmin()) {
            return '';
        }
        $adsEnabled = Settings::get('ads_enabled', '1');
        if ($adsEnabled != '1') {
            return '';
        }
        $code = trim((string) Settings::get($slotKey, ''));
        if (empty($code)) {
            return '';
        }

        return '<div class="site-ad-wrapper ' . htmlspecialchars($containerClass, ENT_QUOTES, 'UTF-8') . '" data-ad-slot="' . htmlspecialchars($slotKey, ENT_QUOTES, 'UTF-8') . '">'
             . '  <div class="ad-badge-header"><span>إعلان</span></div>'
             . '  <div class="ad-content-box">' . $code . '</div>'
             . '</div>';
    }
}

if (!function_exists('inject_in_article_ad')) {
    function inject_in_article_ad($articleHtml)
    {
        $adHtml = site_ad_slot('ad_in_article_slot', 'in-article-ad-slot my-4');
        if (empty($adHtml)) {
            return $articleHtml;
        }

        $closingTag = '</p>';
        $paragraphs = explode($closingTag, $articleHtml);
        $totalP = count($paragraphs) - 1;

        if ($totalP >= 3) {
            $result = '';
            foreach ($paragraphs as $idx => $p) {
                if ($idx < $totalP) {
                    $result .= $p . $closingTag;
                    if ($idx === 1) {
                        $result .= "\n" . $adHtml . "\n";
                    }
                } else {
                    $result .= $p;
                }
            }
            return $result;
        } elseif ($totalP >= 1) {
            $paragraphs[0] .= $closingTag . "\n" . $adHtml . "\n";
            return implode($closingTag, array_slice($paragraphs, 0, 1)) . implode($closingTag, array_slice($paragraphs, 1));
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
        $general = $db->fetch("SELECT id FROM categories WHERE slug IN ('general-tech', 'general') OR name IN ('تقنية عامة', 'أخبار عامة', 'عام') ORDER BY id ASC LIMIT 1");
        if ($general) return (int)$general['id'];
        $first = $db->fetch("SELECT id FROM categories ORDER BY id ASC LIMIT 1");
        return $first ? (int)$first['id'] : 1;
    }
}

// Router Setup
$router = new Router();

// ==========================================
// 🌐 PUBLIC / FRONTEND ROUTES
// ==========================================
$router->get('/', 'HomeController@index');
$router->get('/articles', 'ArticleController@index');
$router->get('/article/{slug}', 'ArticleController@show');
$router->get('/p/{id}', 'ArticleController@shortlink');
$router->get('/category/{slug}', 'ArticleController@category');
$router->get('/search', 'SearchController@index');

// Tutorials & How-To Guides
$router->get('/tutorials', 'TutorialController@index');
$router->get('/tutorial/{slug}', 'TutorialController@show');
$router->get('/tutorials/{slug}', 'TutorialController@show');

// Live Blog Coverage
$router->get('/live-blog', 'LiveBlogController@index');
$router->get('/live-blog/{id}', 'LiveBlogController@show');
$router->get('/live-blog/{id}/updates', 'LiveBlogController@fetchUpdates');
$router->get('/live-blog/{id}/chat-messages', 'LiveBlogController@fetchChat');
$router->post('/live-blog/{id}/chat/send', 'LiveBlogController@sendChatMessage');

// Series & Topic Hubs
$router->get('/series', 'SeriesController@index');
$router->get('/series/{slug}', 'SeriesController@show');

// Stories
$router->get('/stories', 'StoryController@index');
$router->get('/story/{id}', 'StoryController@show');

// Archive (الأخبار المتقادمة/المؤرشفة)
$router->get('/archive', 'ArchiveController@index');

// Interactions, Comments & Newsletter
$router->post('/comment/store', 'CommentController@store');
$router->post('/reaction/toggle', 'ReactionController@toggle');
$router->post('/newsletter/subscribe', 'NewsletterController@subscribe');
$router->get('/newsletter/unsubscribe/{token}', 'NewsletterController@unsubscribe');

// Interactive Polls
$router->post('/poll/vote', 'PollController@vote');
$router->get('/poll/active', 'PollController@active');

// Bookmarks & Push Notifications
$router->get('/bookmarks', 'BookmarkController@index');
$router->post('/push/subscribe', 'PushController@subscribe');

// Feeds & RSS
$router->get('/rss.xml', 'FeedController@rss');
$router->get('/rss', 'FeedController@rss');
$router->get('/feed', 'FeedController@rss');
$router->get('/feed/rss', 'FeedController@rss');
$router->get('/feed.xml', 'FeedController@rss');
$router->get('/feed/{slug}.xml', 'FeedController@rssByCategory');
$router->get('/feed/category/{slug}', 'FeedController@rssByCategory');
$router->get('/rss/category/{slug}', 'FeedController@rssByCategory');
$router->get('/feed/{slug}', 'FeedController@rssByCategory');

// Static Pages
$router->get('/privacy', 'PageController@privacy');
$router->get('/privacy-policy', 'PageController@privacy');
$router->get('/terms', 'PageController@terms');
$router->get('/terms-of-service', 'PageController@terms');
$router->get('/about', 'PageController@about');
$router->get('/about-us', 'PageController@about');
$router->get('/offline', 'PageController@offline');
$router->get('/page/{slug}', 'PageController@show');

// Sitemap, SEO & Monetization
$router->get('/sitemap.xml', 'SitemapController@index');
$router->get('/news-sitemap.xml', 'SitemapController@index');
$router->get('/robots.txt', 'SitemapController@robotsTxt');
$router->get('/ads.txt', 'SitemapController@adsTxt');

// Contact
$router->get('/contact', 'ContactController@show');
$router->get('/contact-us', 'ContactController@show');
$router->post('/contact', 'ContactController@send');
$router->post('/contact/send', 'ContactController@send');

// Auth, Password Recovery & Profile
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->get('/reset-password', 'AuthController@showForgotPassword');
$router->post('/reset-password', 'AuthController@resetPassword');
$router->get('/profile', 'ProfileController@show');
$router->get('/profile/edit', 'ProfileController@edit');
$router->post('/profile/edit', 'ProfileController@update');
$router->post('/profile/update', 'ProfileController@update');
$router->post('/profile/change-password', 'ProfileController@changePassword');
$router->post('/profile/password', 'ProfileController@changePassword');

// ==========================================
// 🛡️ ADMIN PANEL ROUTES
// ==========================================
$router->get('/admin', 'DashboardController@index');
$router->get('/admin/dashboard', 'DashboardController@index');
$router->get('/admin/analytics', 'AnalyticsController@index');

// Articles Management
$router->get('/admin/articles', 'ArticlesController@index');
$router->get('/admin/articles/create', 'ArticlesController@create');
$router->post('/admin/articles/store', 'ArticlesController@store');
$router->get('/admin/articles/{id}/edit', 'ArticlesController@edit');
$router->post('/admin/articles/{id}/update', 'ArticlesController@update');
$router->post('/admin/articles/{id}/delete', 'ArticlesController@delete');
$router->post('/admin/articles/bulk-expire', 'ArticlesController@bulkExpire');
$router->post('/admin/articles/translate-preview', 'ArticlesController@translatePreview');
$router->post('/admin/articles/ai-generate-full', 'ArticlesController@aiGenerateFull');

// Categories Management
$router->get('/admin/categories', 'CategoriesController@index');
$router->get('/admin/categories/create', 'CategoriesController@create');
$router->post('/admin/categories/store', 'CategoriesController@store');
$router->get('/admin/categories/{id}/edit', 'CategoriesController@edit');
$router->post('/admin/categories/{id}/update', 'CategoriesController@update');
$router->post('/admin/categories/{id}/delete', 'CategoriesController@delete');

// Live Blog Moderation
$router->get('/admin/live-blog', 'AdminLiveBlogController@index');
$router->get('/admin/live-blog/create', 'AdminLiveBlogController@create');
$router->post('/admin/live-blog/store', 'AdminLiveBlogController@store');
$router->get('/admin/live-blog/{id}/entries', 'AdminLiveBlogController@entries');
$router->post('/admin/live-blog/entries/store', 'AdminLiveBlogController@storeEntry');
$router->post('/admin/live-blog/entries/{id}/update', 'AdminLiveBlogController@updateEntry');
$router->post('/admin/live-blog/entries/{id}/delete', 'AdminLiveBlogController@deleteEntry');
$router->post('/admin/live-blog/entries/{id}/toggle-pin', 'AdminLiveBlogController@togglePin');
$router->post('/admin/live-blog/chat/store', 'AdminLiveBlogController@storeChatMessage');
$router->post('/admin/live-blog/chat/delete', 'AdminLiveBlogController@deleteChatMessage');

// Interactive Polls Management (CRUD)
$router->get('/admin/polls', 'PollsController@index');
$router->get('/admin/polls/create', 'PollsController@create');
$router->post('/admin/polls/store', 'PollsController@store');
$router->get('/admin/polls/{id}/edit', 'PollsController@edit');
$router->post('/admin/polls/{id}/update', 'PollsController@update');
$router->post('/admin/polls/{id}/delete', 'PollsController@delete');
$router->post('/admin/polls/{id}/set-featured', 'PollsController@setFeatured');
$router->post('/admin/polls/{id}/reset-votes', 'PollsController@resetVotes');

// Tutorials Management
$router->get('/admin/tutorials', 'TutorialsController@index');
$router->get('/admin/tutorials/create', 'TutorialsController@create');
$router->post('/admin/tutorials/store', 'TutorialsController@store');
$router->get('/admin/tutorials/{id}/edit', 'TutorialsController@edit');
$router->post('/admin/tutorials/{id}/update', 'TutorialsController@update');
$router->post('/admin/tutorials/{id}/delete', 'TutorialsController@delete');
$router->post('/admin/tutorials/upload-step-image', 'TutorialsController@ajaxUploadStepImage');

// RSS Aggregator & News Feeds
$router->get('/admin/news-feeds', 'AggregatorController@index');
$router->get('/admin/aggregator', 'AggregatorController@index');
$router->post('/admin/news-feeds/fetch-feed', 'AggregatorController@fetchFeed');
$router->post('/admin/aggregator/fetch-feed', 'AggregatorController@fetchFeed');
$router->post('/admin/news-feeds/bulk-action', 'AggregatorController@bulkAction');
$router->post('/admin/aggregator/bulk-action', 'AggregatorController@bulkAction');
$router->post('/admin/news-feeds/translate-publish', 'AggregatorController@translatePublish');
$router->post('/admin/aggregator/translate-publish', 'AggregatorController@translatePublish');
$router->post('/admin/news-feeds/fast-publish', 'AggregatorController@quickPublish');
$router->post('/admin/news-feeds/quick-publish', 'AggregatorController@quickPublish');
$router->post('/admin/aggregator/quick-publish', 'AggregatorController@quickPublish');
$router->post('/admin/news-feeds/draft-article', 'AggregatorController@draftArticle');
$router->post('/admin/news-feeds/draft', 'AggregatorController@draftArticle');
$router->post('/admin/aggregator/draft', 'AggregatorController@draftArticle');
$router->get('/admin/news-feeds/export/opml', 'AggregatorController@exportOpml');
$router->get('/admin/news-feeds/export/json', 'AggregatorController@exportJson');
$router->post('/admin/news-feeds/export-opml', 'AggregatorController@exportOpml');
$router->post('/admin/news-feeds/export-json', 'AggregatorController@exportJson');
$router->post('/admin/aggregator/export-opml', 'AggregatorController@exportOpml');
$router->post('/admin/aggregator/export-json', 'AggregatorController@exportJson');
$router->post('/admin/news-feeds/import', 'AggregatorController@importFeeds');
$router->post('/admin/aggregator/import', 'AggregatorController@importFeeds');
$router->post('/admin/news-feeds/auto-sync-all', 'AggregatorController@autoSyncAll');
$router->post('/admin/aggregator/auto-sync-all', 'AggregatorController@autoSyncAll');
// فحص صحة خلاصات RSS (يُرجع JSON)
$router->get('/admin/news-feeds/health-check', 'AggregatorController@healthCheck');
$router->post('/admin/news-feeds/health-check', 'AggregatorController@healthCheck');
$router->get('/admin/aggregator/health-check', 'AggregatorController@healthCheck');
$router->post('/admin/aggregator/health-check', 'AggregatorController@healthCheck');
$router->get('/admin/rss-sources/health-check', 'AggregatorController@healthCheck');

// RSS Sources (CRUD)
$router->get('/admin/rss-sources', 'RssSourcesController@index');
$router->get('/admin/rss-sources/create', 'RssSourcesController@create');
$router->post('/admin/rss-sources/store', 'RssSourcesController@store');
$router->get('/admin/rss-sources/{id}/edit', 'RssSourcesController@edit');
$router->post('/admin/rss-sources/{id}/update', 'RssSourcesController@update');
$router->post('/admin/rss-sources/{id}/delete', 'RssSourcesController@delete');

// Classifier Rules (AI & Source Mapping)
$router->get('/admin/classifier-rules', 'ClassifierRulesController@index');
$router->post('/admin/classifier-rules/source-rule', 'ClassifierRulesController@updateSourceRule');
$router->post('/admin/classifier-rules/add', 'ClassifierRulesController@add');
$router->post('/admin/classifier-rules/check-conflict', 'ClassifierRulesController@checkConflict');
$router->post('/admin/classifier-rules/delete', 'ClassifierRulesController@delete');
$router->post('/admin/classifier-rules/reclassify-all', 'ClassifierRulesController@reclassifyAll');

// Comments Moderation
$router->get('/admin/comments', 'CommentsController@index');
$router->post('/admin/comments/approve/{id}', 'CommentsController@approve');
$router->get('/admin/comments/approve/{id}', 'CommentsController@approve');
$router->post('/admin/comments/reject/{id}', 'CommentsController@reject');
$router->get('/admin/comments/reject/{id}', 'CommentsController@reject');
$router->post('/admin/comments/spam/{id}', 'CommentsController@spam');
$router->get('/admin/comments/spam/{id}', 'CommentsController@spam');
$router->post('/admin/comments/delete/{id}', 'CommentsController@delete');
$router->get('/admin/comments/delete/{id}', 'CommentsController@delete');

// Contact Messages Inbox
$router->get('/admin/messages', 'ContactMessagesController@index');
$router->get('/admin/messages/{id}', 'ContactMessagesController@show');
$router->post('/admin/messages/{id}/toggle-read', 'ContactMessagesController@toggleRead');
$router->post('/admin/messages/{id}/mark-replied', 'ContactMessagesController@markReplied');
$router->post('/admin/messages/{id}/delete', 'ContactMessagesController@delete');

// Ads & Monetization Management
$router->get('/admin/ads', 'AdsController@index');
$router->get('/admin/ads/create', 'AdsController@create');
$router->post('/admin/ads/store', 'AdsController@store');
$router->get('/admin/ads/{id}/edit', 'AdsController@edit');
$router->post('/admin/ads/{id}/update', 'AdsController@update');
$router->post('/admin/ads/{id}/delete', 'AdsController@delete');

// Newsletter Campaigns & Subscribers
$router->get('/admin/newsletter', 'NewsletterCampaignController@index');
$router->get('/admin/newsletter/create', 'NewsletterCampaignController@create');
$router->post('/admin/newsletter/store', 'NewsletterCampaignController@store');
$router->post('/admin/newsletter/{id}/send', 'NewsletterCampaignController@send');
$router->post('/admin/newsletter/send/{id}', 'NewsletterCampaignController@send');
$router->post('/admin/newsletter/subscribers/store', 'NewsletterCampaignController@addSubscriber');
$router->post('/admin/newsletter/subscribers/add', 'NewsletterCampaignController@addSubscriber');
$router->post('/admin/newsletter/subscribers/{id}/toggle', 'NewsletterCampaignController@toggleSubscriber');
$router->post('/admin/newsletter/subscribers/{id}/delete', 'NewsletterCampaignController@deleteSubscriber');
$router->post('/admin/newsletter/smtp', 'NewsletterCampaignController@saveSmtpSettings');
$router->post('/admin/newsletter/smtp-settings', 'NewsletterCampaignController@saveSmtpSettings');
$router->post('/admin/newsletter/smtp/test', 'NewsletterCampaignController@testSmtp');
$router->post('/admin/newsletter/test-smtp', 'NewsletterCampaignController@testSmtp');

// Static Pages Management
$router->get('/admin/pages', 'PagesController@index');
$router->get('/admin/pages/create', 'PagesController@create');
$router->post('/admin/pages/store', 'PagesController@store');
$router->get('/admin/pages/{id}/edit', 'PagesController@edit');
$router->post('/admin/pages/{id}/update', 'PagesController@update');
$router->post('/admin/pages/{id}/delete', 'PagesController@delete');

// Menus Management
$router->get('/admin/menus', 'MenusController@index');
$router->get('/admin/menus/create', 'MenusController@create');
$router->post('/admin/menus/store', 'MenusController@store');
$router->get('/admin/menus/{id}/edit', 'MenusController@edit');
$router->post('/admin/menus/{id}/update', 'MenusController@update');
$router->post('/admin/menus/{id}/delete', 'MenusController@delete');
$router->get('/admin/menus/items/{id}', 'MenusController@items');
$router->get('/admin/menus/{id}/items', 'MenusController@items');
$router->post('/admin/menus/items/{id}/store', 'MenusController@itemStore');
$router->post('/admin/menus/items/{id}/delete', 'MenusController@itemDelete');

// Media Library
$router->get('/admin/media', 'MediaController@index');
$router->post('/admin/media/upload', 'MediaController@upload');
$router->post('/admin/media/{id}/delete', 'MediaController@delete');

// Settings & Brand Assets
$router->get('/admin/settings', 'SettingsController@index');
$router->post('/admin/settings/update', 'SettingsController@update');
$router->post('/admin/settings/upload-asset', 'SettingsController@uploadAsset');
$router->post('/admin/settings/quick-switch-provider', 'SettingsController@quickSwitchProvider');

// API Keys & Providers
$router->get('/admin/api-keys', 'ApiKeysController@index');
$router->post('/admin/api-keys/store', 'ApiKeysController@store');
$router->post('/admin/api-keys/{id}/update', 'ApiKeysController@update');
$router->post('/admin/api-keys/{id}/regenerate', 'ApiKeysController@regenerate');
$router->post('/admin/api-keys/{id}/toggle', 'ApiKeysController@toggle');
$router->post('/admin/api-keys/{id}/delete', 'ApiKeysController@delete');

// Translation Logs & AI Testing
$router->get('/admin/translation-logs', 'TranslationLogsController@index');
$router->get('/admin/translation-logs/{id}', 'TranslationLogsController@show');
$router->post('/admin/translation-logs/{id}/delete', 'TranslationLogsController@delete');
$router->post('/admin/translation-logs/clear-all', 'TranslationLogsController@clearAll');
$router->post('/admin/translation-logs/clear', 'TranslationLogsController@clearAll');
$router->post('/admin/translation-logs/test', 'TranslationLogsController@testProvider');
$router->post('/admin/ai/test-provider', 'TranslationLogsController@testProvider');
$router->get('/admin/ai/test-provider', 'TranslationLogsController@testProvider');
$router->get('/admin/ai/models', 'TranslationLogsController@getModels');
$router->post('/admin/ai/models', 'TranslationLogsController@getModels');

// Activity Log & Traffic Radar
$router->get('/admin/activity-log', 'ActivityLogController@index');
$router->get('/admin/activity-log/export-csv', 'ActivityLogController@exportCsv');
$router->get('/admin/activity-log/export-json', 'ActivityLogController@exportJson');
$router->post('/admin/activity-log/cleanup', 'ActivityLogController@cleanup');

$router->get('/admin/traffic-radar', 'TrafficRadarController@index');
$router->get('/admin/traffic-radar/export-csv', 'TrafficRadarController@exportCsv');
$router->post('/admin/traffic-radar/purge', 'TrafficRadarController@purge');

// Security Alerts
$router->get('/admin/security-alerts', 'SecurityAlertsController@index');
$router->post('/admin/security-alerts/{id}/resolve', 'SecurityAlertsController@resolve');
$router->post('/admin/security-alerts/{id}/delete', 'SecurityAlertsController@delete');
$router->post('/admin/security-alerts/clear-all', 'SecurityAlertsController@clearAll');

// Admin Profile
$router->get('/admin/profile', 'AdminProfileController@show');
$router->post('/admin/profile/update', 'AdminProfileController@update');
$router->post('/admin/profile/change-password', 'AdminProfileController@changePassword');

// Comments, Users & Backup
$router->get('/admin/comments', 'CommentsController@index');
$router->post('/admin/comments/{id}/status', 'CommentsController@updateStatus');
$router->post('/admin/comments/{id}/delete', 'CommentsController@delete');

$router->get('/admin/users', 'UsersController@index');
$router->get('/admin/users/create', 'UsersController@create');
$router->post('/admin/users/store', 'UsersController@store');
$router->get('/admin/users/{id}/edit', 'UsersController@edit');
$router->post('/admin/users/{id}/update', 'UsersController@update');
$router->post('/admin/users/{id}/ban', 'UsersController@ban');
$router->post('/admin/users/{id}/activate', 'UsersController@activate');
$router->post('/admin/users/{id}/delete', 'UsersController@delete');

// Backup & Data Export / Import Center
$router->get('/admin/backup', 'BackupController@index');
$router->get('/admin/backup/export-db', 'BackupController@exportDatabase');
$router->post('/admin/backup/import-db', 'BackupController@importDatabase');
$router->get('/admin/backup/export-articles-json', 'BackupController@exportArticlesJson');
$router->get('/admin/backup/export-articles-csv', 'BackupController@exportArticlesCsv');
$router->post('/admin/backup/import-articles-json', 'BackupController@importArticlesJson');
$router->get('/admin/backup/export-settings-json', 'BackupController@exportSettingsJson');
$router->post('/admin/backup/import-settings-json', 'BackupController@importSettingsJson');
$router->get('/admin/backup/export-subscribers-csv', 'BackupController@exportSubscribersCsv');
$router->post('/admin/backup/import-subscribers-csv', 'BackupController@importSubscribersCsv');
$router->get('/admin/backup/export-polls-json', 'BackupController@exportPollsJson');
$router->get('/admin/backup/export-polls-csv', 'BackupController@exportPollsCsv');
$router->get('/admin/backup/export-tutorials-json', 'BackupController@exportTutorialsJson');
$router->get('/admin/backup/export-rss-opml', 'BackupController@exportRssOpml');
$router->get('/admin/backup/export-classifier-rules-json', 'BackupController@exportClassifierRulesJson');
$router->get('/admin/backup/export-contact-messages-csv', 'BackupController@exportContactMessagesCsv');
$router->get('/admin/backup/export-live-blogs-json', 'BackupController@exportLiveBlogJson');
$router->get('/admin/backup/export-activity-logs-csv', 'BackupController@exportActivityLogsCsv');

// Diagnostics Center & Tools
$router->get('/admin/diagnostics', 'DiagnosticsController@index');
$router->get('/admin/diagnostics/seo', 'DiagnosticsController@seo');
$router->get('/admin/diagnostics_seo.php', 'DiagnosticsController@seo');
$router->get('/admin/diagnostics/security', 'DiagnosticsController@security');
$router->get('/admin/diagnostics_security.php', 'DiagnosticsController@security');
$router->get('/admin/diagnostics/database', 'DiagnosticsController@database');
$router->get('/admin/diagnostics_database.php', 'DiagnosticsController@database');
$router->get('/admin/diagnostics/media', 'DiagnosticsController@media');
$router->get('/admin/diagnostics_media.php', 'DiagnosticsController@media');
$router->get('/admin/diagnostics/health', 'DiagnosticsController@health');
$router->get('/admin/health_check.php', 'DiagnosticsController@health');
$router->get('/admin/diagnostics_hub.php', 'DiagnosticsController@index');

// Cron Jobs & Live Auto-Publish Engine
$router->get('/admin/cron', 'CronController@index');
$router->post('/admin/cron', 'CronController@index');
$router->post('/admin/cron/run-now', 'CronController@index');
$router->post('/admin/cron/pause', 'CronController@pause');
$router->post('/admin/cron/resume', 'CronController@resume');
$router->get('/admin/cron/status-json', 'CronController@statusJson');
$router->post('/admin/cron/stop', 'CronController@stop');

// Security Guard (IDS): scan every request for intrusions (path traversal,
// SQLi, XSS, vulnerability scanners) — blocks with 403 + logs a security
// alert + notifies admins. Must run before dispatch and after helpers.
SecurityGuard::scanRequest();

// Traffic & Bot Radar: start measuring response time, then record the hit
// (visitor / spider / AI crawler) once the response completes — even on
// redirects/exit() because it runs as a shutdown function.
TrafficRadar::startTimer();
register_shutdown_function(function () {
    TrafficRadar::recordHit();
});

// Dispatch incoming HTTP Request
$router->dispatch();
