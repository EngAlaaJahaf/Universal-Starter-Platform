<?php
$pageTitle = 'الملف الشخصي | ' . Settings::get('site_name_ar', 'منصة الأخبار التقنية');
$pageDesc = 'إدارة حسابك وتفضيلات القراءة والمظهر';

require_once APP_ROOT . '/views/partials/header.php';
?>

<div class="container page-shell" style="max-width: 860px; margin: 40px auto; min-height: 60vh;">

    <!-- Breadcrumb -->
    <nav class="article-breadcrumbs" style="margin-bottom: 24px;">
        <a href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>" style="display:inline-flex;align-items:center;gap:4px">
            <?= ui_icon('home', '', 13) ?> الرئيسية
        </a>
        <span>/</span>
        <span style="color:var(--text-dim)">الملف الشخصي</span>
    </nav>

    <!-- Profile Header Card -->
    <div class="glass-panel" style="padding: 32px; border-radius: var(--radius-card, 16px); margin-bottom: 28px; background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); box-shadow: var(--shadow-sm); position: relative; overflow: hidden;">
        <div style="position: absolute; top: -50px; left: -50px; width: 160px; height: 160px; background: var(--accent-glow); filter: blur(60px); border-radius: 50%; pointer-events: none;"></div>
        
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--grad-primary); color: #ffffff; display: grid; place-items: center; font-size: 2rem; font-weight: 900; box-shadow: 0 4px 20px var(--accent-glow); text-transform: uppercase;">
                    <?= htmlspecialchars(mb_substr($user['username'], 0, 1), ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div>
                    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--text-main); margin-bottom: 4px;">
                        <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <span style="font-size: 0.88rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 6px;">
                        <?= ui_icon('author', '', 14) ?> <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <div style="margin-top: 6px;">
                        <span style="background: rgba(0, 242, 254, 0.12); color: var(--accent-primary); font-size: 0.75rem; font-weight: 800; padding: 2px 10px; border-radius: 99px; border: 1px solid var(--border-subtle);">
                            <?= ($user['role'] ?? 'user') === 'admin' ? 'مدير النظام ⚡' : 'عضو نشط' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <a href="<?= htmlspecialchars(app_url('admin'), ENT_QUOTES, 'UTF-8') ?>" class="action-btn" style="width: auto; padding: 0 18px; font-size: 0.88rem; font-weight: 700; gap: 6px; text-decoration: none; color: var(--accent-primary); border-color: var(--accent-primary);">
                        <span>⚡</span> <span>لوحة الإدارة</span>
                    </a>
                <?php endif; ?>
                <a href="<?= htmlspecialchars(app_url('profile/edit'), ENT_QUOTES, 'UTF-8') ?>" class="btn-primary-glow" style="padding: 10px 22px; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border-radius: var(--radius-btn, 8px);">
                    <span>تعديل الحساب</span>
                    <span>✏️</span>
                </a>
            </div>
        </div>

        <?php if (!empty($user['bio'])): ?>
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-subtle); color: var(--text-body); font-size: 0.95rem; line-height: 1.7;">
                <?= nl2br(htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8')) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Preferences & Activity Quick Overview -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
        <div class="glass-panel" style="padding: 24px; border-radius: var(--radius-card, 16px); background: var(--bg-surface); border: 1px solid var(--border-subtle);">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <?= ui_icon('general', 'text-primary', 18) ?>
                <span>تفضيلات الحساب</span>
            </h3>
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; font-size: 0.9rem;">
                <li style="display: flex; justify-content: space-between; color: var(--text-muted);">
                    <span>لغة العرض:</span>
                    <strong style="color: var(--text-main);"><?= ($user['preferred_language'] ?? 'ar') === 'en' ? 'English' : 'العربية' ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; color: var(--text-muted);">
                    <span>المظهر المفضل:</span>
                    <strong style="color: var(--text-main);"><?= ($user['theme_preference'] ?? 'auto') === 'light' ? 'فاتح' : (($user['theme_preference'] ?? '') === 'dark' ? 'داكن' : 'تلقائي (حسب النظام)') ?></strong>
                </li>
                <li style="display: flex; justify-content: space-between; color: var(--text-muted);">
                    <span>تاريخ الانضمام:</span>
                    <strong style="color: var(--text-main);"><?= htmlspecialchars(fmt_date($user['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                </li>
            </ul>
        </div>

        <div class="glass-panel" style="padding: 24px; border-radius: var(--radius-card, 16px); background: var(--bg-surface); border: 1px solid var(--border-subtle); display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--text-main); margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <?= ui_icon('bookmark', 'text-primary', 18) ?>
                    <span>قائمة القراءة والمفضلة</span>
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 16px;">
                    يمكنك الوصول السريع للمقالات المحفوظة في أي وقت عبر زر المفضلة في أعلى الموقع.
                </p>
            </div>
            <button class="open-bookmarks-drawer" type="button" style="padding: 10px 18px; border-radius: var(--radius-btn, 8px); background: var(--bg-surface-elevated); border: 1px solid var(--border-medium); color: var(--text-main); font-size: 0.88rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                <?= ui_icon('bookmark', '', 15) ?>
                <span>فتح قائمة القراءة</span>
            </button>
        </div>
    </div>

</div>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
