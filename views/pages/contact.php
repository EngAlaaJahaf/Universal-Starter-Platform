<?php
$pageTitle = 'تواصل مع فريق التحرير | ' . Settings::get('site_name_ar', 'منصة الأخبار التقنية');
$pageDesc  = 'تواصل مع هيئة التحرير والخبراء لمقترحات التغطيات والشراكات الإعلامية.';
$currentUser = Auth::user();
$prefillName = $old['name'] ?? ($currentUser['username'] ?? $currentUser['name'] ?? '');
$prefillEmail = $old['email'] ?? ($currentUser['email'] ?? '');

require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="max-width:760px;padding:32px 16px 80px">
    
    <div style="text-align:center;margin-bottom:32px">
        <span class="badge-tag" style="margin-bottom:12px;display:inline-block">📬 قنوات التواصل المباشر</span>
        <h1 style="font-size:2.2rem;font-weight:800;margin-bottom:12px;color:var(--text-main)">تواصل مع هيئة التحرير والخبراء</h1>
        <p style="color:var(--text-muted);font-size:0.95rem;max-width:560px;margin:0 auto">
            نرحب باقتراحاتكم، المقالات المقترحة، التسريبات التقنية، والشراكات الإعلامية والإعلانية.
        </p>
    </div>

    <!-- Contact Form Card -->
    <div class="side-widget-card" style="padding:clamp(20px, 4vw, 36px);border-radius:var(--radius-card)">
        
        <form action="<?= view_e(app_url('contact')) ?>" method="post" id="contact-form">
            <?= CSRF::field() ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px" class="contact-grid">
                <div>
                    <label style="display:block;font-size:0.88rem;font-weight:700;margin-bottom:8px;color:var(--text-main)">الاسم الكريم: <span style="color:#f43f5e">*</span></label>
                    <input type="text" name="name" required value="<?= view_e($prefillName) ?>" placeholder="أدخل اسمك..." style="width:100%;background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:12px 14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.92rem">
                </div>
                <div>
                    <label style="display:block;font-size:0.88rem;font-weight:700;margin-bottom:8px;color:var(--text-main)">البريد الإلكتروني: <span style="color:#f43f5e">*</span></label>
                    <input type="email" name="email" required value="<?= view_e($prefillEmail) ?>" placeholder="name@domain.com" style="width:100%;background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:12px 14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.92rem">
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label style="display:block;font-size:0.88rem;font-weight:700;margin-bottom:8px;color:var(--text-main)">موضوع الرسالة: <span style="color:#f43f5e">*</span></label>
                <input type="text" name="subject" required value="<?= view_e($old['subject'] ?? '') ?>" placeholder="عنوان مختصر لموضوع تواصلك..." style="width:100%;background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:12px 14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.92rem">
            </div>

            <div style="margin-bottom:24px">
                <label style="display:block;font-size:0.88rem;font-weight:700;margin-bottom:8px;color:var(--text-main)">نص الرسالة أو المقترح: <span style="color:#f43f5e">*</span></label>
                <textarea name="message" rows="5" required placeholder="اكتب تفاصيل استفسارك أو اقتراحك أو تقريرك هنا..." style="width:100%;background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:14px;border-radius:var(--radius-btn);color:var(--text-main);font-size:0.95rem;line-height:1.6"><?= view_e($old['message'] ?? '') ?></textarea>
            </div>

            <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="btn-primary-glow" style="padding:12px 32px;font-size:0.95rem">
                    <span>إرسال الرسالة الآن</span>
                    <span>✉️</span>
                </button>
            </div>
        </form>

    </div>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
