<?php
$error = Session::getFlash('error');
$success = Session::getFlash('success');
$resetLink = Session::getFlash('reset_link');
$siteName = Settings::get('site_name_ar', 'منصة الأخبار التقنية');
?>
<!doctype html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>استعادة كلمة المرور | <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></title>
    <?= site_favicon_tag() ?>
    <?= site_head_injections() ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/site.css'), ENT_QUOTES, 'UTF-8') ?>">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-surface);
            padding: 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-surface-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 36px 32px;
            box-shadow: var(--shadow-lg, 0 20px 40px rgba(0,0,0,0.3));
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.88rem;
            color: var(--text-main);
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text-main);
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        [data-theme="light"] .form-group input {
            background: #ffffff;
            color: #0f172a;
        }
        .form-group input:focus {
            border-color: var(--cyan);
            box-shadow: 0 0 10px rgba(0,242,254,0.15);
        }
        .submit-btn {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
            cursor: pointer;
            border: none;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div style="text-align:center;margin-bottom:28px">
        <div style="display:flex;justify-content:center;margin-bottom:14px">
            <?= site_brand_logo_html(40, false) ?>
        </div>
        <h2 style="font-size:1.35rem;font-weight:800;margin:8px 0 6px">استعادة كلمة المرور</h2>
        <p style="color:var(--text-muted);font-size:0.88rem">أدخل بريدك الإلكتروني لإرسال رابط إعادة تعيين كلمة المرور</p>
    </div>

    <?php if ($error): ?>
        <div style="padding:12px 16px;border-radius:10px;background:rgba(244,63,94,0.15);border:1px solid var(--rose);color:var(--rose);font-size:0.88rem;margin-bottom:18px">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="padding:12px 16px;border-radius:10px;background:rgba(16,185,129,0.15);border:1px solid var(--emerald);color:var(--emerald);font-size:0.88rem;margin-bottom:18px">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($resetLink): ?>
        <div style="padding:12px 16px;border-radius:10px;background:rgba(0,210,255,0.1);border:1px solid var(--border-glow);font-size:0.85rem;margin-bottom:18px;word-break:break-all">
            <strong style="color:var(--cyan);display:block;margin-bottom:4px">رابط إعادة التعيين المباشر:</strong>
            <a href="<?= htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') ?>" style="color:var(--text-main);text-decoration:underline"><?= htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(app_url('forgot-password'), ENT_QUOTES, 'UTF-8') ?>">
        <?= CSRF::field() ?>

        <div class="form-group">
            <label for="email">البريد الإلكتروني المسجل</label>
            <input type="email" id="email" name="email" required autocomplete="email" placeholder="name@example.com">
        </div>

        <button type="submit" class="btn-primary-glow submit-btn">
            <span>إرسال رابط الاستعادة</span>
            <span>←</span>
        </button>
    </form>

    <div style="text-align:center;margin-top:22px;font-size:0.88rem;color:var(--text-muted)">
        تذكرت كلمة المرور؟ <a href="<?= htmlspecialchars(app_url('login'), ENT_QUOTES, 'UTF-8') ?>" style="color:var(--cyan);font-weight:700;text-decoration:none">العودة لتسجيل الدخول</a>
    </div>

    <div style="text-align:center;margin-top:18px;font-size:0.84rem">
        <a href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>" style="color:var(--text-muted);text-decoration:none">← العودة إلى الصفحة الرئيسية</a>
    </div>
</div>

</body>
</html>
