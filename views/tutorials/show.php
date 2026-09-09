<?php
$shareUrl = app_url('tutorial/' . $tutorial['slug']);
$shareTitle = $tutorial['title'];
$pageTitle = $tutorial['title'] . ' | ' . Settings::get('site_name_ar', 'منصة الأخبار التقنية');
$pageDesc  = $tutorial['summary'] ?: $tutorial['title'];

require_once APP_ROOT . '/views/partials/header.php';
?>

<!-- Dynamic Reading Progress Bar -->
<div class="reading-progress-bar" id="reading-progress"></div>

<main class="article-page-wrap">
    
    <!-- Steps Stream Column -->
    <div>
        
        <!-- Hero Overview Card -->
        <div class="side-widget-card" style="margin-bottom:28px;padding:32px;border-radius:var(--radius-card)">
            
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap">
                <a href="<?= view_e(app_url('tutorials')) ?>" style="color:var(--accent-primary);font-size:0.85rem;font-weight:700">🎓 أكاديمية الشروحات</a>
                <span style="color:var(--text-dim)">›</span>
                <span style="font-size:0.85rem;color:var(--text-muted)"><?= view_e($tutorial['category_name'] ?: 'تقنية عامة') ?></span>
            </div>

            <h1 style="font-size:clamp(1.75rem, 3.2vw, 2.45rem);font-weight:800;line-height:1.35;margin-bottom:18px;color:var(--text-main)">
                <?= view_e($tutorial['title']) ?>
            </h1>

            <?php if (!empty($tutorial['summary'])): ?>
                <div style="background:var(--bg-surface-elevated);border-right:4px solid var(--accent-primary);padding:14px 18px;border-radius:8px;font-size:1.05rem;line-height:1.8;color:var(--text-muted);margin-bottom:22px">
                    <?= view_e($tutorial['summary']) ?>
                </div>
            <?php endif; ?>

            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;padding-top:16px;border-top:1px solid var(--border-subtle);font-size:0.88rem;color:var(--text-muted)">
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--grad-primary);display:grid;place-items:center;color:#fff;font-weight:bold">
                        <?= mb_substr($tutorial['author_name'] ?: 'م', 0, 1) ?>
                    </div>
                    <strong>إعداد: <?= view_e($tutorial['author_name'] ?: 'فريق الشروحات والتعليم') ?></strong>
                </div>

                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                    <?php if ($tutorial['difficulty'] === 'beginner'): ?>
                        <span style="color:#10b981;font-weight:700">🟢 مستوى: مبتدئ</span>
                    <?php elseif ($tutorial['difficulty'] === 'intermediate'): ?>
                        <span style="color:#f59e0b;font-weight:700">🟠 مستوى: متوسط</span>
                    <?php else: ?>
                        <span style="color:#f43f5e;font-weight:700">🔴 مستوى: متقدم وخبير</span>
                    <?php endif; ?>
                    <span>⏱️ وقت التطبيق: <?= (int)$tutorial['estimated_minutes'] ?> دقيقة</span>
                    <span>📸 <?= count($steps) ?> خطوات مصورة</span>
                </div>
            </div>

        </div>

        <!-- Step-by-Step Cards -->
        <?php foreach ($steps as $idx => $step): ?>
            <?php 
                $stepNum = $step['step_number'] ?: ($idx + 1);
                $stepId = "step-" . $stepNum;
            ?>
            <section class="side-widget-card" id="<?= $stepId ?>" style="margin-bottom:28px;padding:32px;border-radius:var(--radius-card);scroll-margin-top:80px">
                
                <div style="display:inline-flex;align-items:center;gap:8px;background:var(--bg-surface-elevated);color:var(--accent-primary);border:1px solid var(--border-subtle);padding:6px 16px;border-radius:9999px;font-weight:800;font-size:0.88rem;margin-bottom:14px">
                    <span>الخطوة رقم <?= $stepNum ?></span>
                    <span>/ <?= count($steps) ?></span>
                </div>

                <h2 style="font-size:clamp(1.35rem, 2.5vw, 1.75rem);font-weight:800;color:var(--text-main);margin-bottom:20px;line-height:1.4">
                    <?= view_e($step['title']) ?>
                </h2>

                <!-- Step Image (If exists) -->
                <?php if (!empty($step['image_url'])): ?>
                    <div style="border-radius:16px;overflow:hidden;border:1px solid var(--border-subtle);margin:20px 0;background:#000;box-shadow:var(--shadow-sm)">
                        <img src="<?= view_e(app_url($step['image_url'])) ?>" alt="<?= view_e($step['title']) ?>" style="width:100%;height:auto;display:block;object-fit:contain" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=1000&q=80';">
                        <?php if (!empty($step['image_caption'])): ?>
                            <div style="padding:10px 16px;background:rgba(0,0,0,0.7);border-top:1px solid var(--border-subtle);font-size:0.82rem;color:#d1d5db;text-align:center">
                                📷 <?= view_e($step['image_caption']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Step Content Text -->
                <div style="font-size:1.12rem;line-height:2;color:var(--text-main);margin:20px 0">
                    <?= nl2br(view_e($step['content'])) ?>
                </div>

                <!-- Code / Terminal Block (If exists) -->
                <?php if (!empty($step['code_snippet'])): ?>
                    <div style="background:#090d16;border:1px solid var(--border-subtle);border-radius:14px;overflow:hidden;margin:20px 0">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;background:rgba(255,255,255,0.04);border-bottom:1px solid var(--border-subtle);font-size:0.78rem;color:var(--accent-primary);font-weight:700">
                            <span>💻 <?= strtoupper(view_e($step['code_language'] ?: 'BASH')) ?> COMMAND</span>
                            <button type="button" class="btn-copy-code" onclick="copyStepCode(this)" style="background:transparent;color:var(--text-muted);font-size:0.78rem;cursor:pointer;display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;border:1px solid var(--border-subtle)">
                                <span>نسخ الكود</span>
                                <span>📋</span>
                            </button>
                        </div>
                        <pre style="padding:16px 20px;overflow-x:auto;font-family:monospace;font-size:0.95rem;color:#38bdf8;line-height:1.6;margin:0;white-space:pre-wrap"><?= view_e($step['code_snippet']) ?></pre>
                    </div>
                <?php endif; ?>

                <!-- Callout Note / Tip / Warning (If exists) -->
                <?php if (!empty($step['callout_type']) && $step['callout_type'] !== 'none' && !empty($step['callout_text'])): ?>
                    <div style="border-radius:14px;padding:16px 20px;margin:20px 0;display:flex;align-items:flex-start;gap:12px;font-size:0.95rem;line-height:1.7;background:var(--bg-surface-elevated);border:1px solid var(--border-subtle)">
                        <?php if ($step['callout_type'] === 'tip'): ?>
                            <span style="font-size:1.3rem">💡</span>
                        <?php elseif ($step['callout_type'] === 'warning'): ?>
                            <span style="font-size:1.3rem">⚠️</span>
                        <?php elseif ($step['callout_type'] === 'important'): ?>
                            <span style="font-size:1.3rem">📌</span>
                        <?php else: ?>
                            <span style="font-size:1.3rem">ℹ️</span>
                        <?php endif; ?>
                        <div>
                            <?= view_e($step['callout_text']) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Step Footer / Next Action -->
                <div style="display:flex;justify-content:flex-end;margin-top:20px;padding-top:14px;border-top:1px solid var(--border-subtle)">
                    <?php if (isset($steps[$idx + 1])): ?>
                        <a href="#step-<?= $steps[$idx + 1]['step_number'] ?: ($idx + 2) ?>" class="btn-primary-glow" style="padding:8px 20px;font-size:0.85rem">
                            <span>الخطوة التالية (<?= $idx + 2 ?>)</span>
                            <span>←</span>
                        </a>
                    <?php else: ?>
                        <span class="badge-tag" style="background:rgba(16,185,129,0.15);color:#10b981;border-color:#10b981">
                            🎉 اكتملت كافة خطوات هذا الشرح بنجاح!
                        </span>
                    <?php endif; ?>
                </div>

            </section>
        <?php endforeach; ?>

        <!-- Share & Completed Banner -->
        <div class="side-widget-card" style="text-align:center;padding:32px;border-radius:var(--radius-card)">
            <h3 style="font-weight:800;margin-bottom:10px;color:var(--text-main)">🎉 تهانينا! أتممت تطبيق الشرح بالكامل</h3>
            <p style="color:var(--text-muted);margin-bottom:20px">هل أعجبك هذا الشرح؟ شاركه مع زملائك ومجتمعك التقني:</p>
            <div style="display:flex;justify-content:center;gap:10px">
                <a class="action-btn" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url=<?= rawurlencode($shareUrl) ?>&text=<?= rawurlencode($shareTitle) ?>" title="مشاركة على X">𝕏</a>
                <a class="action-btn" target="_blank" rel="noopener" href="https://wa.me/?text=<?= rawurlencode($shareTitle . ' ' . $shareUrl) ?>" title="مشاركة على واتساب">💬</a>
                <a class="action-btn" target="_blank" rel="noopener" href="https://t.me/share/url?url=<?= rawurlencode($shareUrl) ?>&text=<?= rawurlencode($shareTitle) ?>" title="مشاركة على تليجرام">✈️</a>
                <button class="action-btn" onclick="navigator.clipboard.writeText('<?= view_e($shareUrl) ?>');showToast('تم نسخ رابط الشرح بنجاح!','📋')" title="نسخ الرابط">🔗</button>
            </div>
        </div>

    </div>

    <!-- Sticky Table of Contents Sidebar -->
    <aside class="sidebar-sticky-wrap">
        <div class="side-widget-card" style="border-radius:var(--radius-card)">
            <h4 style="font-size:1rem;font-weight:800;margin-bottom:14px;color:var(--text-main);display:flex;align-items:center;gap:8px">
                <span>📑</span> فهرس خطوات الشرح
            </h4>
            <div style="display:flex;flex-direction:column;gap:6px">
                <?php foreach ($steps as $idx => $step): ?>
                    <?php $sNum = $step['step_number'] ?: ($idx + 1); ?>
                    <a href="#step-<?= $sNum ?>" style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:var(--radius-btn);font-size:0.88rem;color:var(--text-muted);text-decoration:none;transition:all var(--tr-fast)" onmouseover="this.style.background='var(--bg-surface-elevated)';this.style.color='var(--accent-primary)'" onmouseout="this.style.background='transparent';this.style.color='var(--text-muted)'">
                        <span style="font-weight:800;color:var(--accent-primary);min-width:20px"><?= $sNum ?>.</span>
                        <span style="line-height:1.4"><?= view_e($step['title']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

</main>

<script>
function copyStepCode(btn) {
    const codeBlock = btn.closest('.side-widget-card, div').querySelector('pre');
    if (codeBlock) {
        navigator.clipboard.writeText(codeBlock.innerText).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '<span>تم النسخ!</span> <span>✅</span>';
            setTimeout(() => { btn.innerHTML = original; }, 2000);
        });
    }
}
</script>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
