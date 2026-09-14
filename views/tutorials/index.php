<?php
$pageTitle = 'أكاديمية الشروحات والدروس المصورة | ' . Settings::get('site_name_ar', 'منصتي الذكية');
$pageDesc  = 'دروس وشروحات عملية متسلسلة خطوة بخطوة بأسلوب واضح ومصور وداعم.';
require_once APP_ROOT . '/views/partials/header.php';
?>

<main class="container page-shell" style="padding-top:24px;padding-bottom:80px">
    
    <!-- Hero Header -->
    <div style="text-align:center;padding:24px 16px 32px;max-width:860px;margin:0 auto">
        <span class="badge-tag" style="margin-bottom:14px;display:inline-block">🎓 الأكاديمية والشروحات العملية</span>
        <h1 style="font-size:clamp(1.85rem, 3.8vw, 2.6rem);font-weight:800;color:var(--text-main);line-height:1.35;margin-bottom:14px">
            دروس وشروحات مصورة خطوة بخطوة
        </h1>
        <p style="font-size:1.05rem;color:var(--text-muted);line-height:1.8;margin-bottom:0">
            أدلة تطبيقية متسلسلة تشرح لك بالصور التوضيحية والأمثلة العملية كيفية إنجاز المهام والوصول لنتائج واضحة.
        </p>
    </div>

    <!-- Tutorials Grid -->
    <?php if (empty($tutorials)): ?>
        <div style="text-align:center;padding:60px 20px;background:var(--bg-surface);border-radius:var(--radius-card);border:1px solid var(--border-subtle);margin-bottom:60px">
            <div style="font-size:3rem;margin-bottom:12px">🎓</div>
            <h4 style="font-weight:700;margin-bottom:8px">لا توجد دروس أو شروحات منشورة حالياً</h4>
            <p style="color:var(--text-muted)">انتظروا شروحاتنا التطبيقية القادمة قريباً جداً!</p>
        </div>
    <?php else: ?>
        <div class="tutorials-cards-grid">
            <?php foreach ($tutorials as $tut): ?>
                <a class="tutorial-card-item" href="<?= view_e(app_url("tutorial/{$tut['slug']}")) ?>">
                    <div class="tutorial-thumb-wrap">
                        <span class="tutorial-badge-pill">
                            📸 <?= (int)$tut['steps_count'] ?> خطوات مصورة
                        </span>
                        <?php if (!empty($tut['featured_image'])): ?>
                            <img src="<?= view_e(app_url($tut['featured_image'])) ?>" alt="<?= view_e($tut['title']) ?>" loading="lazy" onerror="this.onerror=null;this.src='<?= view_e(\FallbackImage::general()) ?>';">
                        <?php else: ?>
                            <div style="width:100%;height:100%;background:var(--bg-surface-elevated);display:grid;place-items:center;color:var(--accent-primary);font-size:2.5rem">🎓</div>
                        <?php endif; ?>
                    </div>
                    <div class="tutorial-body-content">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                            <?php if ($tut['difficulty'] === 'beginner'): ?>
                                <span style="font-size:0.75rem;font-weight:700;color:#10b981">🟢 مبتدئ</span>
                            <?php elseif ($tut['difficulty'] === 'intermediate'): ?>
                                <span style="font-size:0.75rem;font-weight:700;color:#f59e0b">🟠 متوسط</span>
                            <?php else: ?>
                                <span style="font-size:0.75rem;font-weight:700;color:#f43f5e">🔴 خبير ومتقدم</span>
                            <?php endif; ?>
                            <span style="font-size:0.75rem;color:var(--text-dim)">⏱️ <?= (int)$tut['estimated_minutes'] ?> دقيقة</span>
                        </div>
                        <h3><?= view_e($tut['title']) ?></h3>
                        <p><?= view_e($tut['summary'] ?: 'شرح تطبيقي مصور خطوة بخطوة مدعم بالأكواد والتوجيهات.') ?></p>
                        <div class="card-footer" style="padding-top:14px;border-top:1px solid var(--border-subtle);display:flex;justify-content:space-between;align-items:center;font-size:0.82rem;color:var(--text-dim)">
                            <span>إعداد <?= view_e($tut['author_name'] ?: 'فريق الشروحات') ?></span>
                            <span class="read-more-link" style="color:var(--accent-primary);font-weight:700">ابدأ التطبيق ←</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
