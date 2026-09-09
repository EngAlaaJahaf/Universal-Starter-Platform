<?php
/**
 * ─────────────────────────────────────────────────────────────────
 *  archive_old_news.php — نظام أرشفة الأخبار المتقادمة تلقائياً
 * ─────────────────────────────────────────────────────────────────
 *  مهمة مجدولة تحوّل الأخبار "العاجلة" (التي تفقد قيمتها الزمنية
 *  بسرعة) إلى حالة "مؤرشف (archived)" تلقائياً بعد مرور مدة قابلة
 *  للضبط من تاريخ النشر.
 *
 *  الفئة المؤهلة للأرشفة التلقائية: breaking, standard, video
 *  المحتوى الدائم (analysis, review, tutorial) لا يُؤرشف تلقائياً.
 *
 *  يُستدعى من Cron Job (يومياً أو كل ساعة):
 *    0 * * * *  php /path/to/cron/archive_old_news.php
 *  أو عبر HTTP (من لوحة التحكم فقط بمفتاح سري):
 *    https://domain.com/cron/archive_old_news.php?secret=CRON_SECRET
 * ─────────────────────────────────────────────────────────────────
 */

@ini_set('max_execution_time', 0);
set_time_limit(0);

// NOTE: database.php loads FIRST so config/hosting.php (live override)
// and env vars win; the guarded CRON_SECRET below only fills the default.
$root = dirname(__DIR__);
require_once $root . '/config/database.php';
require_once $root . '/core/Database.php';
require_once $root . '/core/Settings.php';

if (!defined('CRON_SECRET')) {
    $cronSecretEnv = getenv('CRON_SECRET');
    define('CRON_SECRET', ($cronSecretEnv !== false && $cronSecretEnv !== '') ? $cronSecretEnv : 'cron_tnp_2026_secure_key');
}

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

$db = new Database();

// ─── عتبة التقادم القابلة للضبط (أيام) ─────────────────────────
$expiryDays = (int) Settings::get('articles_expiry_days', 30);

// القيمة 0 تعني تعطيل الأرشفة التلقائية
if ($expiryDays <= 0) {
    $result = [
        'status'         => 'disabled',
        'archived'       => 0,
        'candidate_count'=> 0,
        'expiry_days'    => 0,
        'message'        => 'أرشفة الأخبار القديمة معطلة (العتبة = 0).',
        'ran_at'         => date('Y-m-d H:i:s'),
    ];
    if ($isCli) {
        echo "أرشفة الأخبار القديمة معطلة.\n";
    } else {
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
    exit(0);
}

// أنواع المقالات التي تتقادم بسرعة
$expirableTypes = ['breaking', 'standard', 'video'];

$placeholders = implode(',', array_fill(0, count($expirableTypes), '?'));

$sql = "SELECT id, title, published_at FROM articles
        WHERE status = 'published'
          AND article_type IN ({$placeholders})
          AND COALESCE(published_at, created_at) < (NOW() - INTERVAL ? DAY)";

$stmt = $db->prepare($sql);
$stmt->execute(array_merge($expirableTypes, [$expiryDays]));
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

$archived = 0;
foreach ($candidates as $article) {
    $db->query(
        "UPDATE articles SET status = 'archived', updated_at = CURRENT_TIMESTAMP WHERE id = :id AND status = 'published'",
        [':id' => (int) $article['id']]
    );
    $archived++;
}

$result = [
    'status'           => 'success',
    'archived'         => $archived,
    'candidate_count'  => count($candidates),
    'expiry_days'      => $expiryDays,
    'expirable_types'  => $expirableTypes,
    'ran_at'           => date('Y-m-d H:i:s'),
];

if ($isCli) {
    echo "تم أرشفة {$archived} خبراً متقادماً (عتبة: {$expiryDays} يوم).\n";
} else {
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
exit(0);
