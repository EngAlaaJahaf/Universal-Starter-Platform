<?php

/**
 * FeedFetcher — محرك جلب خلاصات RSS/Atom مقاوم للحجب
 *
 * يعالج المشاكل الواقعية التي تمنع جلب الخلاصات:
 *  - 403 Forbidden من Cloudflare / WAF  -> تدوير User-Agent (زواحف معروفة مسموح لها)
 *  - 429 Too Many Requests               -> إعادة محاولة مع تأخر تصاعدي + احترام Retry-After
 *  - 301/302 إلى نطاقات ميتة (FeedBurner) -> كشف الوجهة الميتة والرجوع للرابط الأصلي
 *  - ترميز brotli غير مدعوم في libcurl    -> التفاوض على gzip/deflate فقط
 *  - أخطاء DNS مؤقتة                      -> إعادة محاولة
 *
 * تُستخدم من: cron/rss_auto_publish.php و controllers/admin/AggregatorController.php
 */
class FeedFetcher
{
    /**
     * قائمة وكلاء المستخدم مرتبة حسب الأفضلية.
     * المتصفح أولاً (يعطي محتوى كاملاً)، ثم زواحف الخلاصات المعروفة
     * التي تُدرجها معظم جدران الحماية في القوائم البيضاء.
     */
    private const USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Feedly/1.0 (+https://feedly.com/fetcher.html; like FeedFetcher-Google)',
        'SimplePie/1.5.6 (Feed Parser; http://simplepie.org)',
        'Mozilla/5.0 (compatible; TechNewsPlatform/2.0; +https://tech-news.42web.io)',
    ];

    /** نطاقات معروفة بأنها توقفت عن العمل ولا يجب اتباع التحويل إليها */
    private const DEAD_HOSTS = [
        'feeds.feedburner.com',
        'feedproxy.google.com',
        'feeds2.feedburner.com',
    ];

    private const MAX_RETRIES_PER_UA = 2;

    /** @var string|null آخر رسالة خطأ مفصّلة */
    private static $lastError = null;

    /** @var int|null آخر رمز حالة HTTP */
    private static $lastHttpCode = null;

    /** @var string|null اسم وكيل المستخدم الذي نجح */
    private static $lastSuccessUa = null;

    /**
     * جلب محتوى الخلاصة الخام مع تدوير وكيل المستخدم وإعادة المحاولة.
     *
     * @param bool $fast وضع الفحص السريع: عدد أقل من الوكلاء ومحاولة واحدة لكل وكيل
     *                   (مثالي لفحص سلامة كل المصادر دفعة واحدة).
     * @param int  $maxMs مهلة إجمالية اختيارية بالميلي ثانية لمصدر واحد (0 = بدون مهلة).
     *
     * @return array{success:bool, body:string, http_code:int, error:string, ua:string}
     */
    public static function fetchRaw(string $url, bool $fast = false, int $maxMs = 0): array
    {
        self::$lastError     = null;
        self::$lastHttpCode  = null;
        self::$lastSuccessUa = null;

        $url = trim($url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            self::$lastError = 'رابط الخلاصة غير صالح.';
            return ['success' => false, 'body' => '', 'http_code' => 0, 'error' => self::$lastError, 'ua' => ''];
        }

        $uas = $fast
            ? [self::USER_AGENTS[0], self::USER_AGENTS[1]] // Chrome + Googlebot فقط
            : self::USER_AGENTS;

        $attempts = $fast ? 1 : self::MAX_RETRIES_PER_UA;
        $deadline = $maxMs > 0 ? (microtime(true) * 1000 + $maxMs) : 0;

        $attemptLog = [];

        foreach ($uas as $ua) {
            for ($attempt = 1; $attempt <= $attempts; $attempt++) {
                // احترام المهلة الإجمالية للمصدر الواحد
                if ($deadline > 0 && (microtime(true) * 1000) > $deadline) {
                    $attemptLog[] = 'تجاوز المهلة الزمنية';
                    break 2;
                }

                $res = self::singleRequest($url, $ua);

                $code = $res['http_code'];
                $body = $res['body'];

                // نجاح: رمز حالة سليم + محتوى يبدو كخلاصة
                if ($code >= 200 && $code < 300 && $body !== '' && self::looksLikeFeed($body)) {
                    self::$lastSuccessUa = $ua;
                    self::$lastHttpCode  = $code;
                    return [
                        'success'   => true,
                        'body'      => $body,
                        'http_code' => $code,
                        'error'     => '',
                        'ua'        => $ua,
                    ];
                }

                $attemptLog[] = sprintf('%s → HTTP %d%s', self::shortUa($ua), $code, $res['error'] ? ' (' . $res['error'] . ')' : '');

                // 429: احترم Retry-After ثم أعد المحاولة بنفس الوكيل
                if ($code === 429) {
                    if ($fast) {
                        break; // في الفحص السريع: لا ننتظر Retry-After لمصدر مقيّد
                    }
                    $wait = min($res['retry_after'] ?: (2 * $attempt), 6);
                    if ($attempt < $attempts) {
                        sleep($wait);
                        continue;
                    }
                    break; // انتقل للوكيل التالي
                }

                // 403/401: الوكيل محجوب — لا فائدة من إعادة المحاولة، انتقل للوكيل التالي
                if ($code === 403 || $code === 401 || $code === 406) {
                    break;
                }

                // 404/410: الرابط ميت فعلاً — لا فائدة من تدوير الوكلاء
                if ($code === 404 || $code === 410) {
                    self::$lastHttpCode = $code;
                    self::$lastError    = "الرابط غير موجود (HTTP {$code}). يحتاج تحديث عنوان الخلاصة.";
                    return ['success' => false, 'body' => '', 'http_code' => $code, 'error' => self::$lastError, 'ua' => ''];
                }

                // خطأ شبكة/DNS أو 5xx: تأخر قصير وأعد المحاولة
                if ($attempt < $attempts) {
                    usleep(600000);
                }
            }
        }

        self::$lastHttpCode = self::$lastHttpCode ?: 0;
        self::$lastError    = 'تعذر جلب الخلاصة بعد تجربة ' . count(self::USER_AGENTS) . ' وكلاء. المحاولات: ' . implode(' | ', array_slice($attemptLog, 0, 5));

        return [
            'success'   => false,
            'body'      => '',
            'http_code' => self::$lastHttpCode,
            'error'     => self::$lastError,
            'ua'        => '',
        ];
    }

    /**
     * طلب واحد بوكيل مستخدم محدد.
     *
     * @return array{body:string, http_code:int, error:string, retry_after:int, effective_url:string}
     */
    private static function singleRequest(string $url, string $ua): array
    {
        $ch = curl_init($url);

        $responseHeaders = [];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false, // نتعامل مع التحويل يدوياً لكشف النطاقات الميتة
            CURLOPT_TIMEOUT        => 18,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            // gzip/deflate فقط — brotli يسبب "Unrecognized content encoding type" في libcurl القديم
            CURLOPT_ENCODING       => 'gzip, deflate',
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/rss+xml, application/atom+xml, application/xml;q=0.9, text/xml;q=0.9, text/html;q=0.8, */*;q=0.7',
                'Accept-Language: en-US,en;q=0.9,ar;q=0.8',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
            ],
            CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
                $parts = explode(':', $header, 2);
                if (count($parts) === 2) {
                    $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return strlen($header);
            },
        ]);

        $body = (string) curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        // تتبع التحويلات يدوياً مع تخطي النطاقات الميتة
        $redirects = 0;
        $current   = $url;

        while (in_array($code, [301, 302, 303, 307, 308], true) && $redirects < 5) {
            $location = $responseHeaders['location'] ?? '';
            if ($location === '') {
                break;
            }

            $target = self::resolveUrl($current, $location);
            $host   = strtolower((string) parse_url($target, PHP_URL_HOST));

            // التحويل يذهب إلى نطاق ميت (FeedBurner أُغلق) — أعِد الرابط الأصلي بدلاً من متابعة التحويل
            if (in_array($host, self::DEAD_HOSTS, true)) {
                return [
                    'body'          => '',
                    'http_code'     => 410,
                    'error'         => "التحويل يشير إلى خدمة متوقفة ({$host}) — يجب تحديث الرابط إلى خلاصة الموقع الأصلية.",
                    'retry_after'   => 0,
                    'effective_url' => $target,
                ];
            }

            $responseHeaders = [];
            $ch = curl_init($target);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_TIMEOUT        => 18,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_ENCODING       => 'gzip, deflate',
                CURLOPT_USERAGENT      => $ua,
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/rss+xml, application/atom+xml, application/xml;q=0.9, text/xml;q=0.9, */*;q=0.7',
                    'Accept-Language: en-US,en;q=0.9,ar;q=0.8',
                ],
                CURLOPT_HEADERFUNCTION => function ($curl, $header) use (&$responseHeaders) {
                    $parts = explode(':', $header, 2);
                    if (count($parts) === 2) {
                        $responseHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                    }
                    return strlen($header);
                },
            ]);
            $body = (string) curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);

            $current = $target;
            $redirects++;
        }

        $retryAfter = 0;
        if (isset($responseHeaders['retry-after'])) {
            $retryAfter = (int) $responseHeaders['retry-after'];
        }

        return [
            'body'          => $body,
            'http_code'     => $code,
            'error'         => $err,
            'retry_after'   => $retryAfter,
            'effective_url' => $current,
        ];
    }

    /** تحويل رابط نسبي في رأس Location إلى رابط مطلق */
    private static function resolveUrl(string $base, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }

        $p      = parse_url($base);
        $scheme = $p['scheme'] ?? 'https';
        $host   = $p['host'] ?? '';

        if (str_starts_with($location, '//')) {
            return $scheme . ':' . $location;
        }
        if (str_starts_with($location, '/')) {
            return $scheme . '://' . $host . $location;
        }

        $path = $p['path'] ?? '/';
        $dir  = rtrim(dirname($path), '/');

        return $scheme . '://' . $host . $dir . '/' . $location;
    }

    /** فحص سريع: هل المحتوى يبدو خلاصة XML أو صفحة HTML قابلة للكشط؟ */
    private static function looksLikeFeed(string $body): bool
    {
        $head = strtolower(substr(ltrim($body), 0, 1500));

        // خلاصة XML صريحة
        if (str_contains($head, '<rss') || str_contains($head, '<feed') || str_contains($head, '<rdf:rdf')) {
            return true;
        }
        // إعلان XML عام (بعض الخلاصات تبدأ بتعليقات طويلة)
        if (str_contains($head, '<?xml') && (str_contains(strtolower($body), '<item') || str_contains(strtolower($body), '<entry'))) {
            return true;
        }
        // صفحة HTML — يمكن للكاشط التعامل معها كخيار احتياطي
        if (str_contains($head, '<!doctype html') || str_contains($head, '<html')) {
            return true;
        }

        return false;
    }

    /** اسم مختصر لوكيل المستخدم لأجل رسائل الخطأ */
    private static function shortUa(string $ua): string
    {
        if (str_contains($ua, 'Googlebot'))         return 'Googlebot';
        if (str_contains($ua, 'Feedly'))            return 'Feedly';
        if (str_contains($ua, 'SimplePie'))         return 'SimplePie';
        if (str_contains($ua, 'TechNewsPlatform'))  return 'TechNews';
        return 'Chrome';
    }

    public static function lastError(): ?string
    {
        return self::$lastError;
    }

    public static function lastHttpCode(): ?int
    {
        return self::$lastHttpCode;
    }

    public static function lastSuccessUa(): ?string
    {
        return self::$lastSuccessUa;
    }

    /**
     * اقتراح روابط بديلة لخلاصة معطوبة (يُستخدم في واجهة الفحص).
     *
     * @return string[]
     */
    public static function suggestAlternatives(string $url): array
    {
        $p    = parse_url($url);
        $host = $p['host'] ?? '';
        if ($host === '') {
            return [];
        }

        $bare   = preg_replace('/^www\./', '', $host);
        $scheme = 'https';

        // خلاصات FeedBurner الميتة: خمّن خلاصة الموقع الأصلية من اسم الخلاصة
        if (in_array(strtolower($host), self::DEAD_HOSTS, true)) {
            $slug = trim((string) ($p['path'] ?? ''), '/');
            $guess = strtolower(preg_replace('/[^a-z0-9]/i', '', $slug));
            return array_values(array_unique([
                "{$scheme}://www.{$guess}.com/feed/",
                "{$scheme}://{$guess}.com/feed/",
            ]));
        }

        return array_values(array_unique([
            "{$scheme}://{$bare}/feed/",
            "{$scheme}://www.{$bare}/feed/",
            "{$scheme}://{$bare}/rss/",
            "{$scheme}://{$bare}/rss.xml",
            "{$scheme}://{$bare}/feed/atom/",
            "{$scheme}://{$bare}/atom.xml",
            "{$scheme}://{$bare}/index.xml",
            "{$scheme}://{$bare}/feeds/posts/default?alt=rss&redirect=false",
        ]));
    }
}
