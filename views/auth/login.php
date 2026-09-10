<?php
$error = Session::getFlash('error');
$success = Session::getFlash('success');
$siteName = Settings::get('site_name_ar', 'عصب التقنية');
?>
<!doctype html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>تسجيل الدخول | <?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></title>
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
        .login-card {
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
        }
        [data-theme="light"] .form-group input {
            background: #ffffff;
            color: #0f172a;
        }
        .form-group input:focus {
            border-color: var(--cyan);
            box-shadow: 0 0 10px rgba(0,242,254,0.15);
        }
        .login-btn {
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

<div class="login-card">
    <div style="text-align:center;margin-bottom:28px">
        <div style="display:flex;justify-content:center;margin-bottom:14px">
            <?= site_brand_logo_html(40, false) ?>
        </div>
        <h2 style="font-size:1.35rem;font-weight:800;margin:8px 0 6px">تسجيل الدخول</h2>
        <p style="color:var(--text-muted);font-size:0.88rem">أدخل بيانات حسابك للمتابعة والوصول للمنصة</p>
    </div>

    <?php if ($error): ?>
        <div style="padding:12px;border-radius:10px;background:rgba(244,63,94,0.15);border:1px solid var(--rose);color:var(--rose);font-size:0.88rem;margin-bottom:18px">
            ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="padding:12px;border-radius:10px;background:rgba(16,185,129,0.15);border:1px solid var(--emerald);color:var(--emerald);font-size:0.88rem;margin-bottom:18px">
            ✅ <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(app_url('login'), ENT_QUOTES, 'UTF-8') ?>">
        <?= CSRF::field() ?>

        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email" placeholder="name@example.com">
        </div>

        <div class="form-group">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                <label for="password" style="margin-bottom:0">كلمة المرور</label>
                <a href="<?= htmlspecialchars(app_url('forgot-password'), ENT_QUOTES, 'UTF-8') ?>" style="font-size:0.8rem;color:var(--cyan);text-decoration:none">نسيت كلمة المرور؟</a>
            </div>
            <div class="password-input-wrap">
                <input type="password" id="password" name="password" value="" required autocomplete="current-password" placeholder="••••••••">
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', this)" title="إظهار / إخفاء كلمة المرور" aria-label="إظهار / إخفاء كلمة المرور">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary-glow login-btn">
            <span>تسجيل الدخول</span>
            <span>←</span>
        </button>
    </form>

    <div style="text-align:center;margin-top:22px;font-size:0.88rem;color:var(--text-muted)">
        ليس لديك حساب؟ <a href="<?= htmlspecialchars(app_url('register'), ENT_QUOTES, 'UTF-8') ?>" style="color:var(--cyan);font-weight:700;text-decoration:none">إنشاء حساب جديد</a>
    </div>

    <div style="text-align:center;margin-top:18px;font-size:0.84rem">
        <a href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>" style="color:var(--text-muted);text-decoration:none">← العودة إلى الصفحة الرئيسية</a>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId) || (btn ? btn.closest('.password-input-wrap')?.querySelector('input') : null);
    if (!input) return;
    const isPassword = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPassword ? 'text' : 'password');
    const svg = btn.querySelector('svg');
    if (svg) {
        svg.innerHTML = isPassword 
            ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
            : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>
</body>
</html>
