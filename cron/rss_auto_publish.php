<?php
/**
 * ─────────────────────────────────────────────────────────────────
 *  rss_auto_publish.php — نظام النشر التلقائي عبر RSS + الذكاء الاصطناعي
 * ─────────────────────────────────────────────────────────────────
 *  يُستدعى من Cron Job كل ساعة:
 *    0 * * * *  php /path/to/cron/rss_auto_publish.php
 *  أو عبر HTTP (من لوحة التحكم فقط بمفتاح سري):
 *    https://domain.com/cron/rss_auto_publish.php?secret=CRON_SECRET
 * ─────────────────────────────────────────────────────────────────
 */

define('CRON_START', microtime(true));
define('MAX_PER_SOURCE', 5);
define('MIN_CONTENT_LEN', 50);

// رفع حد وقت التنفيذ لاستيعاب ترجمة AI
@ini_set('max_execution_time', 0);  // 0 = بلا حد عند التشغيل من CLI
set_time_limit(0);

// ─── Bootstrap ─────────────────────────────────────────────────
// NOTE: database.php loads FIRST so config/hosting.php (live override)
// and env vars win; the guarded CRON_SECRET below only fills the default.
$root = dirname(__DIR__);
require_once $root . '/config/database.php';
require_once $root . '/core/Database.php';
require_once $root . '/core/AiTranslator.php';
require_once $root . '/core/CategoryClassifier.php';
require_once $root . '/core/FeedFetcher.php';

if (!defined('CRON_SECRET')) {
    $cronSecretEnv = getenv('CRON_SECRET');
    define('CRON_SECRET', ($cronSecretEnv !== false && $cronSecretEnv !== '') ? $cronSecretEnv : 'cron_tnp_2026_secure_key');
}

// ─── Security: التحقق من الصلاحية ─────────────────────────────
$isCli  = (php_sapi_name() === 'cli');
$isHttp = !$isCli;

if ($isHttp) {
    $secret = $_GET['secret'] ?? '';
    if ($secret !== CRON_SECRET) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit(1);
    }
    header('Content-Type: application/json; charset=utf-8');
}

// ─── Logger Helper ────────────────────────────────────────────
$log = [];
function logMsg(string $msg): void {
    global $log;
    $log[] = $msg;
    if (php_sapi_name() === 'cli') {
        echo $msg . "\n";
    }
}

// ─── Stats ────────────────────────────────────────────────────
$stats = [
    'articles_published'  => 0,
    'articles_skipped'    => 0,
    'articles_failed'     => 0,
    'sources_processed'   => 0,
    'status'              => 'success',
];

$triggeredBy = $isCli ? 'cron' : 'manual';

logMsg("═══════════════════════════════════════════");
logMsg("  🚀 RSS Auto-Publisher — " . date('Y-m-d H:i:s'));
logMsg("  مُشغَّل بواسطة: " . ($isCli ? 'Cron Job' : 'لوحة التحكم'));
logMsg("═══════════════════════════════════════════");

try {
    $db = new Database();

    // ─── فحص ما إذا تم تحديد مصادر معينة للتشغيل ───────────────────
    $filterSourceIds = [];
    if (!empty($_GET['sources'])) {
        $raw = trim((string)$_GET['sources']);
        if ($raw !== 'all' && !empty($raw)) {
            $filterSourceIds = array_filter(array_map('intval', explode(',', $raw)));
        }
    }
    if (empty($filterSourceIds) && !empty($argv) && is_array($argv)) {
        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--sources=')) {
                $raw = substr($arg, 10);
                if ($raw !== 'all' && !empty($raw)) {
                    $filterSourceIds = array_filter(array_map('intval', explode(',', $raw)));
                }
            }
        }
    }

    $statusFile = $root . '/storage/cron_status.json';
    if (empty($filterSourceIds) && file_exists($statusFile)) {
        $st = @json_decode(file_get_contents($statusFile), true);
        if (!empty($st['selected_source_ids']) && is_array($st['selected_source_ids'])) {
            $filterSourceIds = array_filter(array_map('intval', $st['selected_source_ids']));
        }
    }

    // ─── 1. جلب مصادر RSS (الكل أو المحددة فقط) ─────────────────────
    if (!empty($filterSourceIds)) {
        $cleanIds = implode(',', array_map('intval', $filterSourceIds));
        $sources = $db->fetchAll("SELECT * FROM rss_sources WHERE id IN ($cleanIds) ORDER BY ISNULL(last_fetched_at) DESC, last_fetched_at ASC");
        logMsg("\n🎯 تم تحديد مصادر مخصصة للدورة: " . count($sources) . " مصدر");
    } else {
        $sources = $db->fetchAll("SELECT * FROM rss_sources ORDER BY ISNULL(last_fetched_at) DESC, last_fetched_at ASC");
        logMsg("\n📡 مصادر RSS المتاحة: " . count($sources));
    }

    // ─── تحميل التصنيفات الصحيحة مسبقاً ──────────────────────────
    $validCategories = [];
    $catSlugMap = [];
    $catRows = $db->fetchAll("SELECT id, slug FROM categories ORDER BY id ASC");
    foreach ($catRows as $cr) {
        $cid = (int) $cr['id'];
        $validCategories[$cid] = true;
        $catSlugMap[$cr['slug']] = $cid;
    }
    $firstValidCatId = !empty($catRows) ? (int)$catRows[0]['id'] : null;

    // دالة التصنيف الذكي التلقائي للمقالات مع مراعاة المصدر ورابط المقال
    $detectCategory = function($titleEn, $contentEn, $arabicTitle, $sourceCatId, $sourceName = '', $itemUrl = '') use ($catSlugMap) {
        return CategoryClassifier::classify($titleEn, $contentEn, $arabicTitle, $sourceCatId, $catSlugMap, $sourceName, $itemUrl);
    };

    // ─── 2. جلب جميع source_url الموجودة لتجنب التكرار ─────────
    $existingUrls = [];
    $existingRows = $db->fetchAll("SELECT source_url FROM articles WHERE source_url IS NOT NULL AND source_url != ''");
    foreach ($existingRows as $row) {
        $existingUrls[trim($row['source_url'])] = true;
    }
    // ─── ملف تتبع حالة التقدم الحي ────────────────────────────
    $statusFile = $root . '/storage/cron_status.json';

    // استرجاع الإحصائيات التراكمية في حال الاستئناف
    if (file_exists($statusFile)) {
        $existingStatus = @json_decode(file_get_contents($statusFile), true);
        if (is_array($existingStatus) && in_array($existingStatus['state'] ?? '', ['running', 'paused'])) {
            $stats['articles_published'] = (int) ($existingStatus['articles_published'] ?? 0);
            $stats['articles_skipped']   = (int) ($existingStatus['articles_skipped'] ?? 0);
            $stats['articles_failed']    = (int) ($existingStatus['articles_failed'] ?? 0);
            $stats['sources_processed']  = (int) ($existingStatus['sources_processed'] ?? 0);
        }
    }

    $updateCronStatus = function(string $currentStatusText, bool $isRunning = true, string $state = 'running', int $progressPercent = -1) use ($statusFile, $sources, &$stats) {
        $total = count($sources);
        $done = $stats['sources_processed'];
        $percent = ($progressPercent >= 0) ? $progressPercent : ($total > 0 ? (int) round(($done / $total) * 100) : 100);
        $data = [
            'pid'                => $isRunning ? getmypid() : null,
            'is_running'         => $isRunning,
            'state'              => $state,
            'current_source'     => $currentStatusText,
            'sources_processed'  => $done,
            'total_sources'      => $total,
            'progress_percent'   => min(100, max(0, $percent)),
            'articles_published' => $stats['articles_published'],
            'articles_skipped'   => $stats['articles_skipped'],
            'articles_failed'    => $stats['articles_failed'],
            'updated_at'         => date('Y-m-d H:i:s'),
            'timestamp'          => time()
        ];
        @file_put_contents($statusFile, json_encode($data, JSON_UNESCAPED_UNICODE));
    };

    // فحص فوري لحالة الإيقاف أو الإيقاف المؤقت
    $checkAbortSignal = function(string $contextName) use ($statusFile) {
        if (!file_exists($statusFile)) return;
        $st = @json_decode(file_get_contents($statusFile), true);
        if (!is_array($st)) return;

        if (($st['state'] ?? '') === 'paused') {
            logMsg("⏸️ تم الإيقاف المؤقت فوراً عند: {$contextName}");
            $st['is_running'] = false;
            $st['state'] = 'paused';
            $st['pid'] = null;
            $st['current_source'] = 'متوقف مؤقتاً عند: ' . $contextName;
            $st['timestamp'] = time();
            @file_put_contents($statusFile, json_encode($st, JSON_UNESCAPED_UNICODE));
            exit(0);
        }
        if (($st['state'] ?? '') === 'stopped') {
            logMsg("⏹️ تم استلام إشارة الإيقاف الفوري (Stop) — إنهاء العملية.");
            $st['is_running'] = false;
            $st['state'] = 'stopped';
            $st['pid'] = null;
            $st['current_source'] = 'تم الإيقاف بنجاح';
            $st['timestamp'] = time();
            @file_put_contents($statusFile, json_encode($st, JSON_UNESCAPED_UNICODE));
            exit(0);
        }
    };

    // إرسال إشعار البدء الفوري مع رقم الـ PID الحقيقي
    $updateCronStatus('بدء تشغيل الدورة وفحص المصادر...', true, 'running', 2);

    // ─── 3. معالجة كل مصدر ─────────────────────────────────────
    $totalSourcesCount = count($sources);
    foreach ($sources as $sourceIndex => $source) {
        $sourceId   = (int) $source['id'];
        $sourceName = $source['name'];
        $feedUrl    = trim($source['url']);
        $sourceCatId = (int) ($source['category_id'] ?? 0);
        $categoryId  = $sourceCatId;

        $checkAbortSignal($sourceName);

        $currentPercent = $totalSourcesCount > 0 ? (int) round((($sourceIndex) / $totalSourcesCount) * 100) : 0;
        $updateCronStatus("المصدر [{$sourceName}]...", true, 'running', max(3, $currentPercent));

        logMsg("\n─── المصدر [{$sourceId}]: {$sourceName} ───");

        if (empty($feedUrl)) {
            logMsg("  ⚠️  لا يوجد URL، تخطي...");
            continue;
        }

        // ─── جلب RSS ─────────────────────────────────────────
        $feedItems = fetchRssFeed($feedUrl);

        if (empty($feedItems)) {
            logMsg("  ❌ فشل جلب RSS أو لا توجد عناصر");
            $stats['sources_processed']++;
            $db->query("UPDATE rss_sources SET last_fetched_at = CURRENT_TIMESTAMP WHERE id = :id", [':id' => $sourceId]);
            continue;
        }

        logMsg("  ✅ تم جلب " . count($feedItems) . " عنصر");
        $stats['sources_processed']++;

        // ─── معالجة المقالات ──────────────────────────────────
        $publishedInSource = 0;
        foreach ($feedItems as $itemIndex => $item) {
            $checkAbortSignal($sourceName);

            if ($publishedInSource >= MAX_PER_SOURCE) {
                logMsg("  ⏸️  وصلنا للحد الأقصى (" . MAX_PER_SOURCE . ") من هذا المصدر");
                break;
            }

            $itemUrl   = trim($item['link'] ?? '');
            $titleEn   = trim($item['title'] ?? '');
            $contentEn = trim(strip_tags($item['content'] ?? ($item['description'] ?? '')));

            if (empty($titleEn) || empty($itemUrl)) {
                $stats['articles_skipped']++;
                continue;
            }

            if (mb_strlen($contentEn) < MIN_CONTENT_LEN) {
                $contentEn = $titleEn . '. ' . $contentEn;
            }

            // ─── تحقق من التكرار ──────────────────────────────
            if (isset($existingUrls[$itemUrl])) {
                logMsg("  ⏭️  مكرر (موجود مسبقاً): " . mb_substr($titleEn, 0, 60));
                $stats['articles_skipped']++;
                continue;
            }

            $checkAbortSignal($titleEn);

            // ─── ترجمة بالذكاء الاصطناعي ──────────────────────
            logMsg("  🤖 ترجمة: " . mb_substr($titleEn, 0, 60) . "...");
            $updateCronStatus("جاري ترجمة: " . mb_substr($titleEn, 0, 35) . "...", true, 'running');

            try {
                $aiResult = AiTranslator::translateArticle($titleEn, $contentEn);
            } catch (Throwable $e) {
                logMsg("     ❌ فشل الترجمة: " . $e->getMessage());
                $stats['articles_failed']++;
                continue;
            }

            $checkAbortSignal($titleEn);

            $arabicTitle   = $aiResult['title_ar']  ?? $titleEn;
            $arabicContent = $aiResult['content_ar'] ?? "<p>{$contentEn}</p>";
            $arabicExcerpt = $aiResult['excerpt']    ?? '';
            $readingTime   = $aiResult['reading_time_minutes'] ?? 2;

            // ─── التصنيف الذكي المتقدم بمراعاة المصدر والمحتوى ────────
            $finalCategoryId = $detectCategory($titleEn, $contentEn, $arabicTitle, $sourceCatId, $sourceName, $itemUrl);

            // ─── إنشاء Slug ───────────────────────────────────
            $slug = strtolower(trim(preg_replace('/[^\p{L}\p{N}]+/u', '-', (string) $arabicTitle), '-'));
            if (empty($slug)) $slug = 'news-' . time();

            $slugCheck = $db->fetch("SELECT id FROM articles WHERE slug = :s", [':s' => $slug]);
            if ($slugCheck) $slug .= '-' . time();

            // ─── تحديد الصورة البارزة الذكية ───────────────────
            $featuredImage = trim($item['featured_image'] ?? '');
            if (empty($featuredImage) || !filter_var($featuredImage, FILTER_VALIDATE_URL)) {
                $featuredImage = fetchOgImage($itemUrl);
            }
            if (empty($featuredImage)) {
                $featuredImage = getCategoryFallbackImage($finalCategoryId, $catSlugMap);
            }
            // Drop absurdly long image URLs (feed junk) so the INSERT never
            // overflows the column; fallback/placeholder logic covers empty values.
            if (strlen($featuredImage) > 1000) {
                $featuredImage = '';
            }

            // ─── الإدراج في قاعدة البيانات ────────────────────
            try {
                $db->query("
                    INSERT INTO articles
                    (title, title_ar, title_en, slug, excerpt, content, content_ar, content_en,
                     featured_image, category_id, author_id, status, source_name, source_url,
                     allow_comments, reading_time_minutes, published_at, created_at)
                    VALUES
                    (:title, :title_ar, :title_en, :slug, :excerpt, :content, :content_ar, :content_en,
                     :featured_image, :category_id, 1, 'published', :source_name, :source_url,
                     1, :reading_time, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ", [
                    ':title'          => $arabicTitle,
                    ':title_ar'       => $arabicTitle,
                    ':title_en'       => $titleEn,
                    ':slug'           => $slug,
                    ':excerpt'        => $arabicExcerpt,
                    ':content'        => $arabicContent,
                    ':content_ar'     => $arabicContent,
                    ':content_en'     => $contentEn,
                    ':featured_image' => $featuredImage,
                    ':category_id'    => $finalCategoryId,
                    ':source_name'    => $sourceName,
                    ':source_url'     => $itemUrl,
                    ':reading_time'   => $readingTime,
                ]);

                $existingUrls[$itemUrl] = true; // تسجيل الرابط لمنع التكرار في نفس الدورة
                $publishedInSource++;
                $stats['articles_published']++;
                logMsg("     ✅ نُشر مع الصورة [{$featuredImage}]: {$arabicTitle}");

            } catch (Throwable $e) {
                logMsg("     ❌ خطأ في الحفظ: " . $e->getMessage());
                $stats['articles_failed']++;
            }
        }

        // ─── تحديث وقت آخر جلب ────────────────────────────────
        $db->query("UPDATE rss_sources SET last_fetched_at = CURRENT_TIMESTAMP WHERE id = :id", [':id' => $sourceId]);
    }

} catch (Throwable $e) {
    $stats['status'] = 'error';
    logMsg("\n❌ خطأ عام: " . $e->getMessage());
}

// ─── 4. تسجيل النتيجة في cron_logs ────────────────────────────
$elapsedMs = (int) round((microtime(true) - CRON_START) * 1000);

$summary = implode("\n", array_slice($log, -20)); // آخر 20 سطر فقط

try {
    $db->query("
        INSERT INTO cron_logs
        (job_name, status, message, articles_published, articles_skipped, articles_failed, sources_processed, execution_time_ms, triggered_by)
        VALUES ('rss_auto_publish', :status, :msg, :pub, :skip, :fail, :src, :ms, :by)
    ", [
        ':status' => $stats['status'],
        ':msg'    => $summary,
        ':pub'    => $stats['articles_published'],
        ':skip'   => $stats['articles_skipped'],
        ':fail'   => $stats['articles_failed'],
        ':src'    => $stats['sources_processed'],
        ':ms'     => $elapsedMs,
        ':by'     => $triggeredBy,
    ]);
} catch (Throwable $e) {
    logMsg("⚠️ لم يمكن تسجيل السجل: " . $e->getMessage());
}

// ─── 5. أرشفة الأخبار المتقادمة تلقائياً ──────────────────────
try {
    require_once $root . '/core/Settings.php';
    $expiryDays = (int) Settings::get('articles_expiry_days', 30);
    if ($expiryDays > 0) {
        $expirableTypes = ['breaking', 'standard', 'video'];
        $ph = implode(',', array_fill(0, count($expirableTypes), '?'));
        $stmt = $db->prepare("
            SELECT id FROM articles
            WHERE status = 'published'
              AND article_type IN ({$ph})
              AND COALESCE(published_at, created_at) < (NOW() - INTERVAL ? DAY)
        ");
        $stmt->execute(array_merge($expirableTypes, [$expiryDays]));
        $expiredIds = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
        if (!empty($expiredIds)) {
            $marks = implode(',', array_fill(0, count($expiredIds), '?'));
            $db->query("UPDATE articles SET status = 'archived', updated_at = CURRENT_TIMESTAMP WHERE id IN ($marks)", $expiredIds);
            logMsg("🗂️  أُرشف تلقائياً (عتبة {$expiryDays} يوم): " . count($expiredIds) . " خبر متقادم");
        }
    }
} catch (Throwable $e) {
    logMsg("⚠️ أرشفة تلقائية: " . $e->getMessage());
}

// ─── 6. النتيجة النهائية ───────────────────────────────────────
$updateCronStatus('مكتمل بنجاح', false, 'idle', 100);

logMsg("\n═══════════════════════════════════════════");
logMsg("  📊 ملخص التشغيل:");
logMsg("  ✅ نُشر       : {$stats['articles_published']} مقال");
logMsg("  ⏭️  تخطى       : {$stats['articles_skipped']} مقال (مكرر)");
logMsg("  ❌ فشل        : {$stats['articles_failed']} مقال");
logMsg("  📡 مصادر      : {$stats['sources_processed']} مصدر");
logMsg("  ⏱️  زمن التنفيذ: {$elapsedMs}ms");
logMsg("═══════════════════════════════════════════");

if ($isHttp) {
    echo json_encode([
        'success' => $stats['status'] === 'success',
        'stats'   => $stats,
        'execution_time_ms' => $elapsedMs,
        'log'     => $log,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

exit($stats['status'] === 'error' ? 1 : 0);

// ════════════════════════════════════════════════════════════════
//  Helper Functions for RSS, Images and Fallbacks
// ════════════════════════════════════════════════════════════════

/**
 * جلب وتحليل محتوى خلاصة RSS أو Atom مع استخراج الصور المضمنة
 */
function fetchRssFeed(string $url): array
{
    // محرك مقاوم للحجب (تدوير وكيل المستخدم + إعادة المحاولة + كشف FeedBurner الميت)
    $fetch = FeedFetcher::fetchRaw($url);

    if (!$fetch['success']) {
        logMsg('    ! فشل جلب الخلاصة: ' . $fetch['error']);
        return [];
    }

    $xml = $fetch['body'];

    libxml_use_internal_errors(true);
    $feed = simplexml_load_string($xml);
    if (!$feed) return [];

    $items = [];

    // RSS 2.0
    if (isset($feed->channel->item)) {
        foreach ($feed->channel->item as $item) {
            $rawContent = (string) ($item->children('content', true)->encoded ?? $item->description ?? '');
            
            // Extract image from enclosure / media tags / content
            $image = '';
            if (isset($item->enclosure) && str_starts_with((string)$item->enclosure['type'], 'image')) {
                $image = (string) $item->enclosure['url'];
            }
            if (!$image && isset($item->children('media', true)->content)) {
                $image = (string) $item->children('media', true)->content->attributes()->url;
            }
            if (!$image && isset($item->children('media', true)->thumbnail)) {
                $image = (string) $item->children('media', true)->thumbnail->attributes()->url;
            }
            if (!$image && preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $rawContent, $m)) {
                $image = $m[1];
            }

            $items[] = [
                'title'          => html_entity_decode((string) $item->title, ENT_QUOTES, 'UTF-8'),
                'link'           => (string) $item->link,
                'description'    => html_entity_decode(strip_tags((string) $item->description), ENT_QUOTES, 'UTF-8'),
                'content'        => $rawContent,
                'featured_image' => trim($image),
                'published_at'   => (string) $item->pubDate,
            ];
        }
    }
    // Atom
    elseif (isset($feed->entry)) {
        foreach ($feed->entry as $entry) {
            $link = '';
            foreach ($entry->link as $l) {
                if ((string) $l['rel'] === 'alternate' || empty($link)) {
                    $link = (string) $l['href'];
                }
            }
            $rawContent = (string) ($entry->content ?? $entry->summary ?? '');
            
            $image = '';
            if (isset($entry->children('media', true)->content)) {
                $image = (string) $entry->children('media', true)->content->attributes()->url;
            }
            if (!$image && isset($entry->children('media', true)->thumbnail)) {
                $image = (string) $entry->children('media', true)->thumbnail->attributes()->url;
            }
            if (!$image && preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $rawContent, $m)) {
                $image = $m[1];
            }

            $items[] = [
                'title'          => html_entity_decode((string) $entry->title, ENT_QUOTES, 'UTF-8'),
                'link'           => $link,
                'description'    => html_entity_decode(strip_tags((string) ($entry->summary ?? '')), ENT_QUOTES, 'UTF-8'),
                'content'        => $rawContent,
                'featured_image' => trim($image),
                'published_at'   => (string) ($entry->published ?? $entry->updated ?? ''),
            ];
        }
    }

    return $items;
}

/**
 * جلب الصورة البارزة الأصلية مباشرة من وسوم OpenGraph و Twitter بالموقع الأصلي
 */
function fetchOgImage(string $url): string
{
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) return '';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 4,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER     => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Range: bytes=0-65536' // جلب أول 64KB فقط التي تحتوي على وسوم الميتا
        ]
    ]);
    $html = curl_exec($ch);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url;
    curl_close($ch);

    if (empty($html)) return '';

    // 1. og:image
    if (preg_match('/<meta\s+[^>]*property=[\'"]og:image[\'"]\s+[^>]*content=[\'"]([^\'"]+)[\'"]/i', $html, $m) ||
        preg_match('/<meta\s+[^>]*content=[\'"]([^\'"]+)[\'"]\s+[^>]*property=[\'"]og:image[\'"]/i', $html, $m)) {
        $img = trim($m[1]);
        if (filter_var($img, FILTER_VALIDATE_URL) || str_starts_with($img, '/')) {
            return resolveOgUrl($img, $finalUrl);
        }
    }

    // 2. twitter:image
    if (preg_match('/<meta\s+[^>]*name=[\'"]twitter:image(:src)?[\'"]\s+[^>]*content=[\'"]([^\'"]+)[\'"]/i', $html, $m) ||
        preg_match('/<meta\s+[^>]*content=[\'"]([^\'"]+)[\'"]\s+[^>]*name=[\'"]twitter:image(:src)?[\'"]/i', $html, $m)) {
        $img = trim($m[2] ?? $m[1]);
        if (filter_var($img, FILTER_VALIDATE_URL) || str_starts_with($img, '/')) {
            return resolveOgUrl($img, $finalUrl);
        }
    }

    // 3. link image_src
    if (preg_match('/<link\s+[^>]*rel=[\'"]image_src[\'"]\s+[^>]*href=[\'"]([^\'"]+)[\'"]/i', $html, $m)) {
        $img = trim($m[1]);
        return resolveOgUrl($img, $finalUrl);
    }

    return '';
}

/**
 * تحويل المسارات النسبية إلى روابط كاملة
 */
function resolveOgUrl(string $imgUrl, string $baseUrl): string
{
    if (empty($imgUrl)) return '';
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

/**
 * صور افتراضية متناسقة فائقة الجودة بحسب نوع وتصنيف الخبر
 */
function getCategoryFallbackImage(int $categoryId, array $catSlugMap): string
{
    $reverseMap = array_flip($catSlugMap);
    $slug = $reverseMap[$categoryId] ?? 'general-tech';

    switch ($slug) {
        case 'artificial-intelligence':
            return 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=1200&q=80'; // AI Neural
        case 'cybersecurity':
            return 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=1200&q=80'; // Cyber Matrix
        case 'hardware-devices':
            return 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80'; // Hardware Microchip
        case 'software-development':
            return 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=1200&q=80'; // Code / IDE
        default:
            return 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80'; // Global Tech
    }
}
