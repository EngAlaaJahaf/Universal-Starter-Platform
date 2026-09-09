<?php
$error = Session::getFlash('error');
$old = $old ?? [];
?>
<!doctype html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إنشاء حساب جديد | <?= htmlspecialchars(Settings::get('site_name_ar', 'منصة الأخبار التقنية'), ENT_QUOTES, 'UTF-8') ?></title>
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
        .register-card {
            width: 100%;
            max-width: 480px;
            background: var(--bg-surface-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            padding: 36px 32px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: var(--text-main);
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 11px 14px;
            color: var(--text-main);
            font-size: 0.92rem;
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
        .submit-btn {
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div style="text-align:center;margin-bottom:24px">
        <div style="display:flex;justify-content:center;margin-bottom:12px">
            <?= site_brand_logo_html(40, false) ?>
        </div>
        <h2 style="font-size:1.25rem;font-weight:800;margin:6px 0 2px">انضم إلى مجتمع التقنية</h2>
        <p style="color:var(--text-muted);font-size:0.82rem">أنشئ حسابك لحفظ المقالات، التعليق، وتخصيص تجربتك</p>
    </div>

    <?php if ($error): ?>
        <div style="padding:12px;border-radius:10px;background:rgba(244,63,94,0.15);border:1px solid var(--rose);color:var(--rose);font-size:0.85rem;margin-bottom:16px">
            ⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars(app_url('register'), ENT_QUOTES, 'UTF-8') ?>">
        <?= CSRF::field() ?>

        <div class="form-group">
            <label for="username">اسم المستخدم</label>
            <input type="text" id="username" name="username" required minlength="3" value="<?= htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="مثال: tech_enthusiast">
        </div>

        <div class="form-group">
            <label for="email">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="name@example.com">
        </div>

        <div class="form-group">
            <label for="password">كلمة المرور</label>
            <div class="password-input-wrap">
                <input type="password" id="password" name="password" required minlength="6" placeholder="6 أحرف على الأقل">
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password', this)" title="إظهار / إخفاء كلمة المرور" aria-label="إظهار / إخفاء كلمة المرور">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">تأكيد كلمة المرور</label>
            <div class="password-input-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6" placeholder="أعد إدخال كلمة المرور">
                <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('password_confirmation', this)" title="إظهار / إخفاء كلمة المرور" aria-label="إظهار / إخفاء كلمة المرور">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-primary-glow submit-btn">
            <span>إنشاء الحساب الآن</span>
            <span>✨</span>
        </button>
    </form>

    <div style="text-align:center;margin-top:20px;font-size:0.85rem;color:var(--text-muted)">
        لديك حساب بالفعل؟ <a href="<?= htmlspecialchars(app_url('login'), ENT_QUOTES, 'UTF-8') ?>" style="color:var(--cyan);font-weight:600">تسجيل الدخول</a>
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
