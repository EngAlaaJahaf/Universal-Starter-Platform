<?php
/**
 * FetchOg — خدمة جلب الصورة البارزة الحقيقية لأي خبر.
 *
 * - يقرأ og:image / twitter:image / link image_src من صفحة المقال الأصلية
 *   عبر اعتراضات curlArmored (يتابع التحويلات، ومنها صفحات توجيه Google News).
 * - يعيد تكبير الصور المخزّنة في كاش Google (lh3.googleusercontent.com)
 *   من العرض الافتراضي 300px إلى 1200px حتى تكون واضحة في البطاقات والهيرو.
 * - يحفظ النتائج في كاش ملفي (storage/cache/og_images_cache.json) لمدة
 *   14 يوماً حتى لا نكرر الطلبات الشبكية عند كل معاينة أو نشر.
 */
class FetchOg
{
    /** @var array|null */
    private static $cache = null;
    /** @var bool */
    private static $cacheLoaded = false;
    /** @var string|null */
    private static $cacheFile = null;

    public static function cacheFile(): string
    {
        if (self::$cacheFile === null) {
            $dir = defined('APP_ROOT') ? APP_ROOT : __DIR__ . '/..';
            self::$cacheFile = rtrim($dir, '/\\') . '/storage/cache/og_images_cache.json';
        }
        return self::$cacheFile;
    }

    private static function loadCache(): array
    {
        if (!self::$cacheLoaded) {
            self::$cacheLoaded = true;
            self::$cache = [];
            $f = self::cacheFile();
            if (is_file($f)) {
                $j = @json_decode((string) file_get_contents($f), true);
                if (is_array($j)) {
                    self::$cache = $j;
                }
            }
        }
        return self::$cache;
    }

    private static function saveCache(): void
    {
        $now = time();
        $clean = [];
        foreach (self::$cache as $u => $v) {
            if (is_array($v) && ($now - (int) ($v['ts'] ?? 0)) < 14 * 86400) {
                $clean[$u] = $v;
            }
        }
        if (count($clean) > 4000) {
            $clean = array_slice($clean, -4000, null, true);
        }
        self::$cache = $clean;
        @file_put_contents(self::cacheFile(), json_encode($clean, JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    /** قراءة الصورة المخزنة للرابط (فوري، بلا شبكة) */
    public static function cacheGet(string $url): string
    {
        if ($url === '') return '';
        $v = self::loadCache()[$url] ?? null;
        if (!is_array($v)) return '';
        return (string) ($v['img'] ?? '');
    }

    public static function cacheSet(string $url, string $img): void
    {
        if ($url === '' || $img === '') return;
        self::loadCache();
        self::$cache[$url] = ['img' => $img, 'ts' => time()];
        self::saveCache();
    }

    /** هل الصورة احتياطية عامة (placeholder) أم فارغة؟ */
    public static function isPlaceholder(?string $url): bool
    {
        $u = trim((string) $url);
        if ($u === '') return true;
        return str_contains($u, 'photo-1518770660439-4636190af475');
    }

    /** تكبير صور كاش Google (lh3/ggpht) من العرض الصغير إلى 1200px */
    public static function upscaleGoogleImage(string $img): string
    {
        if (preg_match('#^https?://(lh3|lh4|lh5|ggpht)\.googleusercontent\.com/#i', $img)) {
            if (preg_match('#=s\d+-w\d+#', $img)) {
                $img = preg_replace('#(=s\d+-w)\d+($|-)#', '${1}1200${2}', $img, 1);
            } elseif (preg_match('#=s0$#', $img)) {
                $img = preg_replace('#=s0$#', '=s0-w1200', $img);
            }
        }
        return $img;
    }

    /** تحويل المسارات النسبية إلى رابط كامل */
    public static function resolveOgUrl(string $imgUrl, string $baseUrl): string
    {
        $imgUrl = trim($imgUrl);
        if ($imgUrl === '') return '';
        if (preg_match('#^https?://#i', $imgUrl)) return $imgUrl;
        if (str_starts_with($imgUrl, '//')) return 'https:' . $imgUrl;

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host'] ?? '';
        if (empty($host)) return $imgUrl;

        if (str_starts_with($imgUrl, '/')) {
            return "{$scheme}://{$host}{$imgUrl}";
        }
        $path = dirname($parsed['path'] ?? '/');
        return "{$scheme}://{$host}/" . ltrim($path, '/') . '/' . ltrim($imgUrl, '/');
    }

    /** جلب الصورة البارزة من صفحة المقال (طلب شبكة مباشر بلا كاش) */
    public static function fetchOgImage(string $url): string
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) return '';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER     => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Range: bytes=0-262144',
            ],
        ]);
        $html = curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url;
        curl_close($ch);

        if (empty($html)) return '';

        // 1. og:image
        if (preg_match('/<meta\s+[^>]*property=[\'"]og:image[\'"]\s+[^>]*content=[\'"]([^\'"]+)[\'"]/i', $html, $m)
            || preg_match('/<meta\s+[^>]*content=[\'"]([^\'"]+)[\'"]\s+[^>]*property=[\'"]og:image[\'"]/i', $html, $m)) {
            $img = trim($m[1]);
            if ($img !== '' && (filter_var($img, FILTER_VALIDATE_URL) || str_starts_with($img, '/'))) {
                return self::normalize(self::resolveOgUrl($img, $finalUrl));
            }
        }

        // 2. twitter:image
        if (preg_match('/<meta\s+[^>]*name=[\'"]twitter:image(:src)?[\'"]\s+[^>]*content=[\'"]([^\'"]+)[\'"]/i', $html, $m)
            || preg_match('/<meta\s+[^>]*content=[\'"]([^\'"]+)[\'"]\s+[^>]*name=[\'"]twitter:image(:src)?[\'"]/i', $html, $m)) {
            $img = trim($m[2] ?? $m[1]);
            if ($img !== '' && (filter_var($img, FILTER_VALIDATE_URL) || str_starts_with($img, '/'))) {
                return self::normalize(self::resolveOgUrl($img, $finalUrl));
            }
        }

        // 3. link rel=image_src
        if (preg_match('/<link\s+[^>]*rel=[\'"]image_src[\'"]\s+[^>]*href=[\'"]([^\'"]+)[\'"]/i', $html, $m)) {
            $img = trim($m[1]);
            if ($img !== '') {
                return self::normalize(self::resolveOgUrl($img, $finalUrl));
            }
        }

        return '';
    }

    private static function normalize(string $img): string
    {
        $img = trim($img);
        if ($img === '' || strlen($img) > 1000) return '';
        return self::upscaleGoogleImage($img);
    }

    /** حل الصورة من الكاش أو الشبكة (كاش أولاً) */
    public static function resolve(string $url): string
    {
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) return '';

        $cached = self::cacheGet($url);
        if ($cached !== '') return $cached;

        $img = self::fetchOgImage($url);
        $img = self::normalize($img);
        if ($img === '' || self::isPlaceholder($img)) return '';

        self::cacheSet($url, $img);
        return $img;
    }

    /**
     * للأغراض التي جلبنا لها صورة سابقة: نعيد الصورة الحقيقية فقط إذا كانت
     * الحالية فارغة أو placeholder؛ وإلا نبقيها كما هي (بلا أي طلب شبكة).
     */
    public static function resolveFor(string $itemUrl, string $currentImage): string
    {
        $cur = trim($currentImage);
        if ($cur !== '' && !self::isPlaceholder($cur)) {
            return $cur;
        }
        return self::resolve($itemUrl);
    }
}