<?php
/**
 * Universal Starter Platform - Quick Web Setup Wizard
 *
 * Install and configure your new project in under 30 seconds.
 */

define('APP_ROOT', __DIR__);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// SECURITY (SH-03): installer lock. After a successful install a lock file
// is created at storage/data/installed.lock (git-ignored, never deployed).
// While the lock exists the wizard refuses to re-run. To reinstall
// legitimately, delete the lock file manually via FTP/file manager.
define('INSTALLER_LOCK_FILE', APP_ROOT . '/storage/data/installed.lock');
$installerLocked = is_file(INSTALLER_LOCK_FILE);
if ($installerLocked && ($_SERVER['REQUEST_METHOD'] === 'POST')) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(403);
    echo json_encode([
        'ok'      => false,
        'success' => false,
        'message' => 'التثبيت مكتمل مسبقاً — المثبت مقفل. احذف storage/data/installed.lock يدوياً لإعادة التثبيت.',
        'error'   => 'Installer locked: platform already installed.'
    ]);
    exit;
}

// Handle AJAX Database Connection Test
if (isset($_POST['action']) && $_POST['action'] === 'test_db') {
    header('Content-Type: application/json; charset=utf-8');
    $host = trim($_POST['db_host'] ?? '127.0.0.1');
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? 'root');
    $pass = $_POST['db_pass'] ?? '';

    try {
        $dsn = "mysql:host={$host};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 4
        ]);
        
        // Check if database exists or can be created
        if (!empty($name)) {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        echo json_encode([
            'ok'      => true,
            'message' => '✔ الاتصال بخادم قاعدة البيانات ناجح وجاهز!'
        ]);
    } catch (Throwable $e) {
        echo json_encode([
            'ok'      => false,
            'message' => '❌ فشل الاتصال: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Handle Full Installation Process
$installResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_install'])) {
    $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
    $dbName = trim($_POST['db_name'] ?? 'starter_platform_db');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = $_POST['db_pass'] ?? '';

    $siteNameAr = trim($_POST['site_name_ar'] ?? 'منصتي الذكية');
    $siteNameEn = trim($_POST['site_name_en'] ?? 'SmartPlatform');
    $adminUser  = trim($_POST['admin_user'] ?? 'admin');
    $adminEmail = trim($_POST['admin_email'] ?? 'admin@platform.local');
    // SECURITY (SH-03): no default password — the admin MUST choose one.
    $adminPass  = (string) ($_POST['admin_pass'] ?? '');

    // Server-side credential policy (never trust client-side only).
    $weakPasswords = ['admin', 'password', '123456', 'admin123', 'Admin@123456', 'qwerty'];
    if (mb_strlen($adminUser) < 3 || !preg_match('/^[A-Za-z0-9_.-]+$/', $adminUser)) {
        throw new Exception('اسم مستخدم المدير غير صالح (3 أحرف على الأقل: أحرف/أرقام/._-).');
    }
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('البريد الإلكتروني للمدير غير صالح.');
    }
    if (mb_strlen($adminPass) < 10
        || !preg_match('/[A-Za-z]/', $adminPass)
        || !preg_match('/[0-9]/', $adminPass)
        || in_array(strtolower($adminPass), array_map('strtolower', $weakPasswords), true)
    ) {
        throw new Exception('كلمة مرور المدير ضعيفة: 10 أحرف على الأقل مع حرف ورقم، وتجنب الكلمات الشائعة.');
    }

    try {
        // 1. Connect and create DB
        $pdo = new PDO("mysql:host={$dbHost};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");

        // 2. Import Schema
        $schemaFile = APP_ROOT . '/database/schema.sql';
        if (!is_file($schemaFile)) {
            throw new Exception("ملف المخطط database/schema.sql غير موجود.");
        }
        $schemaSql = file_get_contents($schemaFile);
        $pdo->exec($schemaSql);

        // 3. Import Seed
        $seedFile = APP_ROOT . '/database/seed.sql';
        if (is_file($seedFile)) {
            $seedSql = file_get_contents($seedFile);
            $pdo->exec($seedSql);
        }

        // 4. Update Admin User and Branding Settings
        $hashedPass = password_hash($adminPass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = 1");
        $stmt->execute([$adminUser, $adminEmail, $hashedPass]);

        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'site_name_ar'");
        $stmt->execute([$siteNameAr]);

        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = 'site_name_en'");
        $stmt->execute([$siteNameEn]);

        // 5. Write config/hosting.php (incl. a fresh CRON_SECRET — SH-02)
        $freshCronSecret = bin2hex(random_bytes(32));
        $configContent = "<?php\n/**\n * Auto-generated hosting configuration\n */\n"
            . "define('DB_HOST', " . var_export($dbHost, true) . ");\n"
            . "define('DB_NAME', " . var_export($dbName, true) . ");\n"
            . "define('DB_USER', " . var_export($dbUser, true) . ");\n"
            . "define('DB_PASS', " . var_export($dbPass, true) . ");\n"
            . "define('DB_CHARSET', 'utf8mb4');\n"
            . "define('CRON_SECRET', " . var_export($freshCronSecret, true) . ");\n";

        file_put_contents(APP_ROOT . '/config/hosting.php', $configContent);

        // 6. Create the installer lock (SH-03) — never expose credentials.
        @mkdir(dirname(INSTALLER_LOCK_FILE), 0777, true);
        @file_put_contents(INSTALLER_LOCK_FILE, json_encode([
            'installed_at' => gmdate('c'),
            'site'         => $siteNameEn,
        ], JSON_UNESCAPED_UNICODE));

        $installResult = ['success' => true];
    } catch (Throwable $e) {
        $installResult = ['success' => false, 'error' => $e->getMessage()];
    }
}

// Server Checks
$checks = [
    'PHP 8.0+'     => version_compare(phpversion(), '8.0.0', '>='),
    'PDO & MySQL'  => extension_loaded('pdo') && extension_loaded('pdo_mysql'),
    'cURL'         => extension_loaded('curl'),
    'Mbstring'     => extension_loaded('mbstring'),
    'OpenSSL'      => extension_loaded('openssl'),
    'Uploads Path' => is_dir(APP_ROOT . '/uploads') && is_writable(APP_ROOT . '/uploads'),
    'Config Path'  => is_dir(APP_ROOT . '/config') && is_writable(APP_ROOT . '/config'),
];
$allPassed = !in_array(false, $checks, true);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>معالج تثبيت المنصة الذكية | Setup Wizard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Readex+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Readex Pro', sans-serif;
            background: linear-gradient(135deg, #090d16 0%, #111827 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .setup-card {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            max-width: 780px;
            width: 100%;
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, rgba(0,242,254,0.1), rgba(157,78,221,0.15));
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding: 28px;
            text-align: center;
        }
        .form-control, .form-select {
            background-color: #0f172a !important;
            border-color: rgba(255,255,255,0.15) !important;
            color: #f8fafc !important;
            border-radius: 10px;
            padding: 10px 14px;
        }
        .form-control:focus {
            border-color: #00f2fe !important;
            box-shadow: 0 0 0 3px rgba(0,242,254,0.15) !important;
        }
        .btn-brand {
            background: linear-gradient(135deg, #00f2fe, #0ea5e9);
            color: #090d16;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 12px 28px;
            transition: all 0.2s ease;
        }
        .btn-brand:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,242,254,0.3);
            color: #090d16;
        }
        .check-pill {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
            padding: 8px 14px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>

<div class="setup-card">
    <div class="setup-header">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-25 rounded-circle p-3 mb-3" style="width:64px;height:64px">
            <i class="bi bi-rocket-takeoff-fill text-info fs-2"></i>
        </div>
        <h3 class="fw-bold mb-1 text-white">معالج تثبيت وإعداد المنصة</h3>
        <p class="text-muted mb-0 small">Universal Starter Platform • Setup Wizard</p>
    </div>

    <div class="p-4 p-md-5">
        <?php if ($installerLocked): ?>
            <div class="text-center py-4">
                <div class="text-warning fs-1 mb-3"><i class="bi bi-lock-fill"></i></div>
                <h4 class="fw-bold text-white mb-2">المثبت مقفل — المنصة مثبتة مسبقاً 🔒</h4>
                <p class="text-muted mb-4">تم إكمال التثبيت سابقاً. لإعادة التثبيت احذف الملف <code dir="ltr">storage/data/installed.lock</code> يدوياً من الاستضافة ثم أعد تحميل الصفحة.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="admin" class="btn btn-brand px-4 py-2"><i class="bi bi-speedometer2 me-1"></i> الدخول للوحة التحكم</a>
                    <a href="./" class="btn btn-outline-light px-4 py-2"><i class="bi bi-house me-1"></i> زيارة الموقع</a>
                </div>
            </div>
        <?php elseif ($installResult && $installResult['success']): ?>
            <div class="text-center py-4">
                <div class="text-success fs-1 mb-3"><i class="bi bi-check-circle-fill"></i></div>
                <h4 class="fw-bold text-white mb-2">تم تثبيت المنصة بنجاح تام! 🎉</h4>
                <p class="text-muted mb-4">تم إنشاء قاعدة البيانات، واستيراد المخطط، وإعداد حساب المدير الفائق بنجاح. سجّل الدخول بالبيانات التي اخترتها — وتم قفل المثبت تلقائياً.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="admin" class="btn btn-brand px-4 py-2"><i class="bi bi-speedometer2 me-1"></i> الدخول للوحة التحكم</a>
                    <a href="./" class="btn btn-outline-light px-4 py-2"><i class="bi bi-house me-1"></i> زيارة الموقع</a>
                </div>
            </div>
        <?php else: ?>

            <?php if ($installResult && !$installResult['success']): ?>
                <div class="alert alert-danger mb-4 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>خطأ أثناء التثبيت:</strong> <?= htmlspecialchars($installResult['error']) ?>
                </div>
            <?php endif; ?>

            <!-- System Checks -->
            <div class="mb-4">
                <h6 class="fw-bold text-info mb-3"><i class="bi bi-cpu me-1"></i> فحص متطلبات الخادم والبيئة</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($checks as $name => $ok): ?>
                        <div class="check-pill">
                            <i class="bi <?= $ok ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?>"></i>
                            <span><?= htmlspecialchars($name) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <form method="post" id="installForm">
                <input type="hidden" name="do_install" value="1">

                <!-- DB Configuration -->
                <h6 class="fw-bold text-info mt-4 mb-3"><i class="bi bi-database me-1"></i> إعدادات قاعدة البيانات (MySQL / MariaDB)</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">مضيف قاعدة البيانات (Host)</label>
                        <input type="text" name="db_host" id="db_host" value="127.0.0.1" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">اسم قاعدة البيانات (Database Name)</label>
                        <input type="text" name="db_name" id="db_name" value="starter_platform_db" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">اسم المستخدم (User)</label>
                        <input type="text" name="db_user" id="db_user" value="root" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">كلمة المرور (Password)</label>
                        <input type="password" name="db_pass" id="db_pass" value="" class="form-control" placeholder="اتركه فارغاً إن لم توجد كلمة مرور">
                    </div>
                    <div class="col-12">
                        <button type="button" class="btn btn-sm btn-outline-info" id="btnTestDb">
                            <i class="bi bi-plug me-1"></i> اختبار الاتصال بقاعدة البيانات
                        </button>
                        <span id="dbTestMsg" class="ms-2 small"></span>
                    </div>
                </div>

                <!-- Site Branding -->
                <h6 class="fw-bold text-info mt-4 mb-3"><i class="bi bi-brush me-1"></i> هوية وبيانات المنصة</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">اسم المنصة (بالعربية)</label>
                        <input type="text" name="site_name_ar" value="منصتي الذكية" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted">اسم المنصة (بالإنجليزية)</label>
                        <input type="text" name="site_name_en" value="SmartPlatform" class="form-control" required>
                    </div>
                </div>

                <!-- Super Admin Account -->
                <h6 class="fw-bold text-info mt-4 mb-3"><i class="bi bi-person-badge me-1"></i> حساب المدير الفائق (Super Admin)</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">اسم المستخدم</label>
                        <input type="text" name="admin_user" value="admin" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">البريد الإلكتروني</label>
                        <input type="email" name="admin_email" value="admin@platform.local" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">كلمة المرور (10 أحرف على الأقل: حرف + رقم)</label>
                        <input type="password" name="admin_pass" value="" minlength="10" autocomplete="new-password" class="form-control" placeholder="اختر كلمة مرور قوية" required>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-brand py-3 fs-5" <?= !$allPassed ? 'disabled' : '' ?>>
                        <i class="bi bi-lightning-charge-fill me-1"></i> بدء التثبيت والتشغيل الفوري
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('btnTestDb')?.addEventListener('click', function() {
    const btn = this;
    const msg = document.getElementById('dbTestMsg');
    btn.disabled = true;
    msg.textContent = 'جاري الاختبار...';
    msg.className = 'ms-2 small text-muted';

    const fd = new FormData();
    fd.append('action', 'test_db');
    fd.append('db_host', document.getElementById('db_host').value);
    fd.append('db_name', document.getElementById('db_name').value);
    fd.append('db_user', document.getElementById('db_user').value);
    fd.append('db_pass', document.getElementById('db_pass').value);

    fetch('install.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            msg.textContent = data.message;
            msg.className = data.ok ? 'ms-2 small text-success fw-bold' : 'ms-2 small text-danger fw-bold';
        })
        .catch(err => {
            btn.disabled = false;
            msg.textContent = '❌ تعذر اختبار الاتصال.';
            msg.className = 'ms-2 small text-danger fw-bold';
        });
});
</script>

</body>
</html>
