<?php
/*
 * AI Assistant floating chat widget («مرشد عصب التقنية»)
 * Rendered on every public page when enabled in admin settings (المظهر والتصميم).
 */
if (!class_exists('AiChatAssistant') || !AiChatAssistant::enabled()) {
    return;
}
$aiLimit = AiChatAssistant::freeLimit();
$aiUsed = (int) Session::get('ai_assistant_used', 0);
$aiRemaining = $aiLimit > 0 ? max(0, $aiLimit - $aiUsed) : null;
if (Auth::isAdmin()) {
    $aiRemaining = null; // admins bypass the free budget
}
?>
<!-- AI Assistant Chat Widget -->
<div class="ai-assistant" id="aiAssistant"
     data-limit="<?= (int) $aiLimit ?>"
     data-remaining="<?= $aiRemaining === null ? '' : (int) $aiRemaining ?>"
     data-bypass="<?= Auth::isAdmin() ? '1' : '0' ?>">

    <!-- Launcher -->
    <button type="button" class="ai-launcher" id="aiLauncher" aria-label="فتح محادث مرشد عصب التقنية" aria-expanded="false">
        <svg class="ai-launcher-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        <svg class="ai-launcher-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        <span class="ai-launcher-badge">1</span>
    </button>

    <!-- Panel -->
    <section class="ai-panel" id="aiPanel" aria-label="مرشد عصب التقنية" hidden>
        <header class="ai-panel-header">
            <div class="ai-panel-avatar" aria-hidden="true">🤖</div>
            <div class="ai-panel-title-wrap">
                <h2 class="ai-panel-title">مرشد عصب التقنية</h2>
                <p class="ai-panel-subtitle">مساعدك التقني من محتوى عصب التقنية</p>
            </div>
            <button type="button" class="ai-panel-close" id="aiPanelClose" aria-label="إغلاق المحادثة">✕</button>
        </header>

        <div class="ai-messages" id="aiMessages" role="log" aria-live="polite">
            <div class="ai-msg ai-msg-ai">
                <div class="ai-msg-bubble ai-msg-bubble-ai">
                    <div class="ai-msg-text">مرحباً 👋 أنا مرشد عصب التقنية. اسألني عن آخر أخبار التقنية والمقالات المنشورة في المنصة.</div>
                </div>
            </div>
            <div class="ai-suggestions" id="aiSuggestions">
                <button type="button" class="ai-chip" data-q="ما آخر أخبار الذكاء الاصطناعي؟">أخبار الذكاء الاصطناعي</button>
                <button type="button" class="ai-chip" data-q="ما أحدث الهواتف الذكية؟">أحدث الهواتف</button>
                <button type="button" class="ai-chip" data-q="ما جديد الأمن السيبراني؟">الأمن السيبراني</button>
            </div>
        </div>

        <footer class="ai-panel-footer">
            <div class="ai-input-wrap">
                <textarea id="aiInput" rows="1" maxlength="500" placeholder="اسأل مرشد عصب التقنية..." aria-label="رسالتك إلى مرشد عصب التقنية"></textarea>
                <button type="button" id="aiSend" aria-label="إرسال السؤال">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
            <div class="ai-panel-meta">
                <span>يعتمد مرشد عصب التقنية على المقالات المنشورة محلياً.</span>
                <?php if ($aiRemaining !== null): ?>
                    <span class="ai-quota" data-counter><?= (int) $aiRemaining ?>/<?= (int) $aiLimit ?> مجاناً</span>
                <?php endif; ?>
            </div>
        </footer>
    </section>
</div>

<script>
window.APP_CSRF = <?= json_encode(class_exists('CSRF') ? CSRF::getToken() : '', JSON_UNESCAPED_UNICODE) ?>;
window.AI_ASSISTANT = {
    limit: <?= json_encode((int) $aiLimit) ?>,
    bypass: <?= Auth::isAdmin() ? '1' : '0' ?>
};
</script>