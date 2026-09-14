<?php
$pageTitle = 'تعديل الملف الشخصي | ' . Settings::get('site_name_ar', 'منصتي الذكية');
$pageDesc = 'تعديل بيانات الحساب وتغيير كلمة المرور';

require_once APP_ROOT . '/views/partials/header.php';
?>

<div class="container page-shell" style="max-width: 780px; margin: 40px auto; min-height: 60vh;">

    <!-- Breadcrumb -->
    <nav class="article-breadcrumbs" style="margin-bottom: 24px;">
        <a href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>" style="display:inline-flex;align-items:center;gap:4px">
            <?= ui_icon('home', '', 13) ?> الرئيسية
        </a>
        <span>/</span>
        <a href="<?= htmlspecialchars(app_url('profile'), ENT_QUOTES, 'UTF-8') ?>">الملف الشخصي</a>
        <span>/</span>
        <span style="color:var(--text-dim)">تعديل البيانات</span>
    </nav>

    <!-- Header Section -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">
                إعدادات الحساب والملف الشخصي
            </h1>
            <p style="font-size: 0.88rem; color: var(--text-muted); margin: 0;">
                قم بتحديث معلوماتك الشخصية وتفضيلات المظهر أو تغيير كلمة المرور.
            </p>
        </div>
        <a href="<?= htmlspecialchars(app_url('profile'), ENT_QUOTES, 'UTF-8') ?>" class="action-btn" style="width: auto; padding: 0 16px; font-size: 0.85rem; font-weight: 700; text-decoration: none;">
            ← الرجوع للملف
        </a>
    </div>

    <!-- 1. Edit Profile Form -->
    <div class="glass-panel" style="padding: 28px; border-radius: var(--radius-card, 16px); background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); box-shadow: var(--shadow-sm); margin-bottom: 28px;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--border-subtle);">
            <?= ui_icon('author', 'text-primary', 18) ?>
            <span>المعلومات الأساسية</span>
        </h3>

        <form method="post" action="<?= htmlspecialchars(app_url('profile/update'), ENT_QUOTES, 'UTF-8') ?>">
            <?= CSRF::getField() ?>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px;">
                    اسم المستخدم
                </label>
                <input type="text" 
                       name="username" 
                       value="<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>" 
                       required 
                       style="width: 100%; padding: 12px 16px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); font-size: 0.92rem; outline: none; box-sizing: border-box; transition: all var(--tr-fast);">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px;">
                    البريد الإلكتروني (للعرض فقط)
                </label>
                <input type="email" 
                       value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>" 
                       disabled 
                       style="width: 100%; padding: 12px 16px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-muted); font-size: 0.92rem; outline: none; box-sizing: border-box; opacity: 0.7; cursor: not-allowed;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px;">
                    نبذة تعريفية (Bio)
                </label>
                <textarea name="bio" 
                          rows="4" 
                          placeholder="اكتب نبذة قصيرة عن اهتماماتك التقنية..." 
                          style="width: 100%; padding: 12px 16px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); font-size: 0.92rem; outline: none; box-sizing: border-box; resize: vertical; line-height: 1.6;"><?= htmlspecialchars(($user['preferred_language'] ?? 'ar') === 'en' ? ($user['bio_en'] ?? '') : ($user['bio_ar'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px;">
                        اللغة المفضلة
                    </label>
                    <select name="preferred_language" style="width: 100%; padding: 12px 16px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); font-size: 0.92rem; outline: none; box-sizing: border-box; cursor: pointer;">
                        <option value="ar" <?= ($user['preferred_language'] ?? 'ar') === 'ar' ? 'selected' : '' ?>>العربية</option>
                        <option value="en" <?= ($user['preferred_language'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px;">
                        المظهر المفضل
                    </label>
                    <select name="theme_preference" style="width: 100%; padding: 12px 16px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); font-size: 0.92rem; outline: none; box-sizing: border-box; cursor: pointer;">
                        <option value="auto" <?= ($user['theme_preference'] ?? 'auto') === 'auto' ? 'selected' : '' ?>>تلقائي (حسب نظام جهازك)</option>
                        <option value="dark" <?= ($user['theme_preference'] ?? '') === 'dark' ? 'selected' : '' ?>>النمط الليلي (Dark)</option>
                        <option value="light" <?= ($user['theme_preference'] ?? '') === 'light' ? 'selected' : '' ?>>النمط الفاتح (Light)</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-primary-glow" style="padding: 12px 28px; font-size: 0.92rem; border-radius: var(--radius-btn, 8px); cursor: pointer; border: none; font-weight: 800;">
                حفظ التعديلات
            </button>
        </form>
    </div>

    <!-- 2. Change Password Form -->
    <div class="glass-panel" style="padding: 28px; border-radius: var(--radius-card, 16px); background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); box-shadow: var(--shadow-sm);">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--border-subtle);">
            <?= ui_icon('security', 'text-warning', 18) ?>
            <span>أمان الحساب وكلمة المرور</span>
        </h3>

        <form method="post" action="<?= htmlspecialchars(app_url('profile/change-password'), ENT_QUOTES, 'UTF-8') ?>">
            <?= CSRF::getField() ?>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px;">
                    كلمة المرور الحالية
                </label>
                <div class="password-input-wrap">
                    <input type="password" 
                           id="profile_current_pass"
                           name="current_password" 
                           required 
                           placeholder="••••••••"
                           style="width: 100%; padding: 12px 16px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); font-size: 0.92rem; outline: none; box-sizing: border-box;">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('profile_current_pass', this)" title="إظهار / إخفاء كلمة المرور">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px;">
                    كلمة المرور الجديدة (6 أحرف على الأقل)
                </label>
                <div class="password-input-wrap">
                    <input type="password" 
                           id="profile_new_pass"
                           name="password" 
                           minlength="6" 
                           required 
                           placeholder="••••••••"
                           style="width: 100%; padding: 12px 16px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); font-size: 0.92rem; outline: none; box-sizing: border-box;">
                    <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('profile_new_pass', this)" title="إظهار / إخفاء كلمة المرور">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="action-btn" style="width: auto; padding: 12px 28px; font-size: 0.92rem; border-radius: var(--radius-btn, 8px); cursor: pointer; font-weight: 700; border-color: var(--accent-primary); color: var(--accent-primary);">
                تحديث كلمة المرور
            </button>
        </form>
    </div>

</div>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
