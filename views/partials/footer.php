<?php
if (!function_exists('view_e')) {
    function view_e($val) {
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
}

if (!isset($categories)) {
    try {
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
    } catch (Exception $e) {
        $categories = [];
    }
}

$siteName = Settings::get('site_name_ar', 'منصتي الذكية');
$siteDesc = Settings::get('meta_description', 'منصة ويب متقدمة ومبنية بأحدث المعايير البرمجية والأمنية.');
?>

<!-- Command Palette Modal -->
<div class="cmd-modal-backdrop" id="cmd-modal-backdrop">
    <div class="cmd-modal">
        <div class="cmd-input-wrap">
            <span style="color:var(--accent-primary);font-size:1.3rem">⌕</span>
            <input type="text" id="cmd-search-input" placeholder="اكتب للبحث الفوري في المقالات والصفحات والتصنيفات..." autocomplete="off">
            <kbd style="background:var(--bg-surface);padding:4px 8px;border-radius:6px;border:1px solid var(--border-subtle);font-size:0.75rem">ESC</kbd>
        </div>
        <div class="cmd-results" id="cmd-results-list">
            <!-- Dynamically populated -->
        </div>
        <div class="cmd-footer">
            <span>استخدم <b>↑</b> <b>↓</b> للتنقل و <b>Enter</b> للاختيار</span>
            <span><?= htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>
</div>

<!-- Bookmarks / Reading List Drawer -->
<div class="drawer-backdrop" id="bookmarks-drawer-backdrop">
    <div class="bookmarks-drawer">
        <div class="drawer-header">
            <h3 style="display:flex;align-items:center;gap:8px">
                <?= ui_icon('bookmark', 'text-primary', 18) ?>
                <span>قائمة القراءة والمفضلة</span>
            </h3>
            <button type="button" class="btn-close-drawer" id="close-bookmarks-drawer" aria-label="إغلاق">✕</button>
        </div>
        <div class="drawer-content" id="drawer-bookmarks-list">
            <!-- Dynamically populated via JS -->
        </div>
    </div>
</div>

<!-- Unified Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col footer-brand-col">
                <div class="footer-brand-header" style="display:flex;align-items:center;gap:8px">
                    <span class="footer-brand-icon" style="display:grid;place-items:center"><?= ui_icon('ai', 'text-primary', 20) ?></span>
                    <strong class="footer-brand-name"><?= view_e($siteName) ?></strong>
                </div>
                <p class="footer-brand-desc">
                    <?= view_e($siteDesc) ?>
                </p>
            </div>
            <div class="footer-col">
                <h4>أقسام المنصة</h4>
                <ul>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="<?= view_e(app_url('category/' . $category['slug'])) ?>" style="display:inline-flex;align-items:center;gap:6px">
                                <?= category_icon_html($category['slug'], 13) ?>
                                <span><?= view_e($category['name']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4>استكشف وتعلّم</h4>
                <ul>
                    <li><a href="<?= view_e(app_url('tutorials')) ?>" style="display:inline-flex;align-items:center;gap:6px"><?= ui_icon('tutorials', '', 13) ?> <span>الشروحات المصورة</span></a></li>
                    <li><a href="<?= view_e(app_url('live-blog')) ?>" style="display:inline-flex;align-items:center;gap:6px"><?= ui_icon('live') ?> <span>التغطية المباشرة</span></a></li>
                    <li><a href="<?= view_e(app_url('series')) ?>" style="display:inline-flex;align-items:center;gap:6px"><?= ui_icon('series', '', 13) ?> <span>السلاسل والملفات</span></a></li>
                    <li><a href="<?= view_e(app_url('stories')) ?>" style="display:inline-flex;align-items:center;gap:6px"><?= ui_icon('flame', '', 13) ?> <span>قصص سريعة</span></a></li>
                    <li><a href="<?= view_e(app_url('archive')) ?>" style="display:inline-flex;align-items:center;gap:6px"><?= ui_icon('general', '', 13) ?> <span>أرشيف المنشورات</span></a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>روابط المنصة</h4>
                <ul>
                    <li><a href="<?= view_e(app_url('contact')) ?>">اتصل بنا والتواصل</a></li>
                    <li><a href="<?= view_e(app_url('privacy')) ?>">سياسة الخصوصية</a></li>
                    <li><a href="<?= view_e(app_url('terms')) ?>">شروط الاستخدام</a></li>
                    <li><a href="<?= view_e(app_url('sitemap.xml')) ?>">خريطة الموقع XML</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© <?= date('Y') ?> جميع الحقوق محفوظة · <?= view_e($siteName) ?></p>
            <div class="footer-bottom-actions">
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= view_e(app_url('admin')) ?>" class="footer-admin-btn" style="display:inline-flex;align-items:center;gap:6px"><?= ui_icon('admin', '', 14) ?> <span>لوحة الإدارة</span></a>
                <?php endif; ?>
                <a href="<?= view_e(app_url('feed/rss')) ?>" class="footer-rss-btn" style="display:inline-flex;align-items:center;gap:6px"><?= ui_icon('rss', '', 14) ?> <span>RSS Feed</span></a>
            </div>
        </div>
    </div>
</footer>

<!-- Floating Smart Scroll Navigation (Top / Bottom) -->
<div class="floating-scroll-nav" id="floatingScrollNav">
    <button type="button" class="floating-scroll-btn" id="btnScrollTop" title="الرجوع إلى أعلى الصفحة" aria-label="أعلى الصفحة">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 15l-6-6-6 6"/></svg>
    </button>
    <button type="button" class="floating-scroll-btn" id="btnScrollBottom" title="التمرير إلى أسفل الصفحة" aria-label="أسفل الصفحة">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
    </button>
</div>

<?php require_once APP_ROOT . '/views/partials/ai-assistant.php'; ?>

<script>
window.APP_BASE_URL = <?= json_encode(app_url()) ?>;
window.TNP_READER = <?= json_encode([
    'unreadEnabled' => (bool) Settings::get('reader_unread_enabled', true),
    'autoMark'      => (bool) Settings::get('reader_unread_auto_mark', true),
    'timeAgoEnabled'=> (bool) Settings::get('reader_time_ago_enabled', true),
    'timeAgoMode'   => (string) Settings::get('reader_time_ago_mode', 'relative'),
    'newHours'      => max(1, (int) Settings::get('reader_new_badge_hours', 24)),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= view_e(app_url('assets/js/tech-platform.js') . '?v=' . @filemtime(__DIR__ . '/../../assets/js/tech-platform.js')) ?>"></script>
<?= site_footer_injections() ?>
</body>
</html>
