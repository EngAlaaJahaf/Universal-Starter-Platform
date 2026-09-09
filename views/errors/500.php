<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>خطأ في الخادم (500)</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; background: #0f172a; color: #f1f5f9; text-align: center; padding: 8vh 20px; line-height: 1.6; }
        .box { background: #1e293b; border: 1px solid #334155; border-radius: 16px; padding: 35px; max-width: 680px; margin: auto; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        h1 { color: #f43f5e; margin-top: 0; }
        a { color: #00f2fe; text-decoration: none; font-weight: bold; }
        .debug-box { margin-top: 20px; text-align: left; direction: ltr; background: #0d1117; padding: 15px; border-radius: 8px; border: 1px solid #ef4444; font-family: monospace; font-size: 0.85rem; color: #fca5a5; overflow-x: auto; white-space: pre-wrap; }
        .hint-box { background: rgba(0, 242, 254, 0.08); border: 1px solid rgba(0, 242, 254, 0.3); border-radius: 8px; padding: 15px; margin-top: 20px; text-align: right; font-size: 0.9rem; color: #e2e8f0; }
    </style>
</head>
<body>
    <main class="box">
        <h1>⚠️ حدث خطأ غير متوقع (500)</h1>
        <p>تعذر إكمال الطلب، والسبب الأكثر شيوعاً في الاستضافات الجديدة هو عدم تطابق بيانات الاتصال بقاعدة البيانات.</p>
        
        <?php if (!empty($exception)): ?>
            <div class="debug-box">
<strong>Error:</strong> <?= htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') ?>

<strong>File:</strong> <?= htmlspecialchars($exception->getFile() . ':' . $exception->getLine(), ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="hint-box">
            <strong>💡 خطوات سريعة للحل:</strong>
            <ul style="margin:8px 0;padding-right:20px">
                <li>تأكد من تعديل ملف <code>config/database.php</code> بالبيانات التي زودتك بها شركة الاستضافة (MySQL Host, DB Name, User, Password).</li>
                <li>تأكد من استيراد ملف <code>schema.sql</code> في قاعدة البيانات عبر phpMyAdmin في لوحة الاستضافة.</li>
            </ul>
        </div>

        <div style="margin-top:25px">
            <a href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">العودة إلى الرئيسية</a>
        </div>
    </main>
</body>
</html>
