<?php
$pageTitle = $blog['title_ar'] . ' | تغطية حية وتفاعل مباشر';
$pageDesc  = 'متابعة حية ومباشرة لحظة بلحظة مع دردشة وتفاعلات فورية: ' . $blog['title_ar'];

require_once APP_ROOT . '/views/partials/header.php';
$currentUser = Auth::user();
?>

<!-- Floating Particle Canvas Container -->
<div id="floating-particles-container" style="position:fixed;bottom:20px;right:20px;width:120px;height:400px;pointer-events:none;z-index:9999;overflow:hidden"></div>

<main class="container page-shell" style="padding:24px 0 80px;max-width:1280px">
    
    <!-- Hero Live Header -->
    <div style="background:var(--bg-surface);border:1px solid var(--border-subtle);padding:28px 32px;border-radius:var(--radius-card);margin-bottom:24px;box-shadow:var(--shadow-md);position:relative;overflow:hidden">
        <div style="position:absolute;top:0;right:0;width:100%;height:4px;background:var(--grad-primary)"></div>
        
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;flex-wrap:wrap">
            <div style="display:flex;align-items:center;gap:10px">
                <span class="badge-tag" id="live-status-badge" style="<?= $blog['status'] === 'active' ? 'background:#f43f5e;color:#fff' : 'background:var(--bg-surface-elevated);color:var(--text-muted)' ?>">
                    <?= ui_icon('live', 'me-1') ?>
                    <span id="live-status-text"><?= $blog['status'] === 'active' ? 'بث حي ومباشر الآن' : 'تغطية مؤرشفة ومنتهية' ?></span>
                </span>
                <span style="font-size:0.8rem;color:var(--text-dim)">بدأت التغطية: <?= view_e(date('Y-m-d H:i', strtotime($blog['created_at']))) ?></span>
            </div>

            <!-- Auto-Update & Sort Controls -->
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                <span id="live-sync-indicator" style="font-size:0.8rem;color:#10b981;font-weight:700;display:inline-flex;align-items:center;gap:5px">
                    <span class="pulse-dot" style="background:#10b981;box-shadow:0 0 8px #10b981"></span>
                    <span>متصل بالتحديث اللحظي</span>
                </span>

                <div class="btn-group" style="background:var(--bg-surface-elevated);border-radius:8px;padding:2px;border:1px solid var(--border-subtle);display:flex">
                    <button type="button" id="btn-sort-newest" class="btn-sort-opt active" style="padding:4px 10px;border-radius:6px;font-size:0.78rem;font-weight:700;background:var(--accent-primary);color:#050d1a;border:none;cursor:pointer">الأحدث أولاً</button>
                    <button type="button" id="btn-sort-oldest" class="btn-sort-opt" style="padding:4px 10px;border-radius:6px;font-size:0.78rem;font-weight:700;background:transparent;color:var(--text-muted);border:none;cursor:pointer">الأقدم أولاً</button>
                </div>
            </div>
        </div>

        <h1 style="font-size:1.85rem;font-weight:800;margin-bottom:10px;color:var(--text-main);line-height:1.4">
            <?= view_e($blog['title_ar']) ?>
        </h1>
        
        <!-- Global Live Reactions Bar -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding-top:14px;border-top:1px solid var(--border-subtle)">
            <div style="font-size:0.85rem;font-weight:700;color:var(--text-muted);display:inline-flex;align-items:center;gap:6px">
                <?= ui_icon('sparkle', 'text-primary', 16) ?>
                <span>تفاعل مع البث المباشر الآن:</span>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap" id="global-reactions-bar">
                <?php
                $globalRx = $reactions['global'] ?? [];
                $reactionTypes = [
                    'fire'      => ['icon' => '🔥', 'label' => 'حماسي'],
                    'clap'      => ['icon' => '👏', 'label' => 'مذهل'],
                    'mindblown' => ['icon' => '🤯', 'label' => 'صادم'],
                    'lightbulb' => ['icon' => '💡', 'label' => 'عبقري'],
                    'heart'     => ['icon' => '❤️', 'label' => 'أحبه']
                ];
                foreach ($reactionTypes as $type => $info):
                    $cnt = $globalRx[$type] ?? 0;
                ?>
                    <button type="button" class="btn-live-reaction global-rx-btn" data-type="<?= $type ?>" data-entry-id="" title="<?= $info['label'] ?>" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:6px 14px;border-radius:50px;font-size:0.9rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s ease">
                        <span><?= $info['icon'] ?></span>
                        <span class="rx-count font-monospace" style="font-weight:700;font-size:0.82rem"><?= $cnt ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Live Interactive Grid -->
    <div class="live-grid-container" style="display:grid;grid-template-columns:minmax(0, 1fr) 380px;gap:24px;align-items:start">
        
        <!-- ================= Right Column: Live News Stream ================= -->
        <div class="live-stream-column">
            
            <!-- Pinned Highlights Box -->
            <div id="pinned-entries-container" style="display:flex;flex-direction:column;gap:14px;margin-bottom:20px">
                <?php foreach ($entries as $entry): ?>
                    <?php if (!empty($entry['is_pinned'])): ?>
                        <div class="live-entry-card pinned-entry" id="card-entry-<?= $entry['id'] ?>" data-entry-id="<?= view_e($entry['id']) ?>" data-media-url="<?= view_e($entry['media_url'] ?? '') ?>" style="background:linear-gradient(135deg,rgba(0,242,254,0.08),rgba(139,92,246,0.05));border:2px solid var(--accent-primary);padding:24px;border-radius:var(--radius-card);box-shadow:0 0 20px rgba(0,242,254,0.15);transition:all 0.3s ease">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                                <span class="badge-tag" style="background:var(--accent-primary);color:#050d1a;font-weight:800">
                                    📌 أهم أحداث وإعلانات البث
                                </span>
                                <span style="font-size:0.8rem;color:var(--text-dim)"><?= view_e(date('H:i:s', strtotime($entry['created_at']))) ?></span>
                            </div>
                            <div class="entry-content-box" style="font-size:1.12rem;font-weight:700;line-height:1.8;color:var(--text-main)">
                                <?= nl2br(view_e($entry['content_ar'])) ?>
                            </div>
                            <?php if (!empty($entry['media_url'])): ?>
                                <div class="entry-media-wrapper" style="margin-top:14px;border-radius:12px;overflow:hidden">
                                    <?php if ($entry['entry_type'] === 'video' || preg_match('/\.(mp4|webm|ogv)$/i', $entry['media_url'])): ?>
                                        <video src="<?= view_e(str_starts_with($entry['media_url'], 'http') ? $entry['media_url'] : app_url($entry['media_url'])) ?>" controls style="width:100%;max-height:420px;border-radius:12px;background:#000"></video>
                                    <?php else: ?>
                                        <img src="<?= view_e(str_starts_with($entry['media_url'], 'http') ? $entry['media_url'] : app_url($entry['media_url'])) ?>" alt="مرفق التغطية" style="width:100%;max-height:420px;object-fit:cover;border-radius:12px">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Entry Action & Reaction Bar -->
                            <div class="entry-rx-bar d-flex justify-content-between align-items-center flex-wrap gap-2" style="margin-top:14px;padding-top:12px;border-top:1px solid rgba(0,242,254,0.2)">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <?php
                                    $eRx = $reactions[(int)$entry['id']] ?? [];
                                    foreach ($reactionTypes as $type => $info):
                                        $cnt = $eRx[$type] ?? 0;
                                    ?>
                                        <button type="button" class="btn-live-reaction entry-rx-btn" data-type="<?= $type ?>" data-entry-id="<?= $entry['id'] ?>" style="background:rgba(255,255,255,0.06);border:1px solid var(--border-subtle);padding:3px 10px;border-radius:50px;font-size:0.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                            <span><?= $info['icon'] ?></span>
                                            <span class="rx-count font-monospace" style="font-weight:700"><?= $cnt ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Reply / Discuss Button -->
                                <button type="button" class="btn-reply-entry" data-entry-id="<?= $entry['id'] ?>" data-snippet="<?= htmlspecialchars(mb_substr($entry['content_ar'], 0, 70), ENT_QUOTES) ?>" style="background:rgba(0,242,254,0.12);border:1px solid var(--accent-primary);color:var(--text-main);padding:4px 12px;border-radius:50px;font-size:0.8rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all 0.2s ease">
                                    <span>💬 الرد والمناقشة</span>
                                    <span class="badge bg-primary text-dark font-monospace rounded-pill replies-count-badge" style="font-size:0.72rem"><?= (int)($entry['replies_count'] ?? 0) ?></span>
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Live Regular Stream Container -->
            <div id="live-entries-stream" style="display:flex;flex-direction:column;gap:16px">
                <?php 
                $regularEntries = array_filter($entries, function($e) { return empty($e['is_pinned']); });
                ?>
                <?php if (empty($entries)): ?>
                    <div id="no-entries-msg" style="padding:60px 20px;text-align:center;background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:var(--radius-card);color:var(--text-muted)">
                        <div style="margin-bottom:12px"><?= ui_icon('live', 'text-primary', 36) ?></div>
                        <h4 style="color:var(--text-main);margin-bottom:6px">التغطية الحية جارية الآن</h4>
                        <p style="margin-bottom:0">ستظهر التحديثات والإعلانات هنا فور نشرها من المحررين مباشرة دون الحاجة لتحديث الصفحة.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($regularEntries as $entry): ?>
                        <article class="live-entry-card" id="card-entry-<?= $entry['id'] ?>" data-entry-id="<?= view_e($entry['id']) ?>" data-media-url="<?= view_e($entry['media_url'] ?? '') ?>" style="background:var(--bg-surface);border:1px solid var(--border-subtle);border-inline-start:4px solid var(--accent-primary);padding:22px;border-radius:var(--radius-card);box-shadow:var(--shadow-sm);transition:all 0.3s ease">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:0.84rem;color:var(--text-dim);flex-wrap:wrap;gap:8px">
                                <span style="color:var(--accent-primary);font-weight:800;font-family:var(--font-numbers);display:inline-flex;align-items:center;gap:4px">
                                    <?= ui_icon('clock', '', 14) ?>
                                    <span><?= view_e(date('H:i:s', strtotime($entry['created_at']))) ?></span>
                                </span>
                                <span>بواسطة: <strong style="color:var(--text-main)"><?= view_e($entry['author_name'] ?: 'فريق التحرير المباشر') ?></strong></span>
                            </div>

                            <div class="entry-content-box" style="font-size:1.02rem;line-height:1.8;color:var(--text-main)">
                                <?= nl2br(view_e($entry['content_ar'])) ?>
                            </div>

                            <?php if (!empty($entry['media_url'])): ?>
                                <div class="entry-media-wrapper" style="margin-top:14px;border-radius:12px;overflow:hidden">
                                    <?php if ($entry['entry_type'] === 'video' || preg_match('/\.(mp4|webm|ogv)$/i', $entry['media_url'])): ?>
                                        <video src="<?= view_e(str_starts_with($entry['media_url'], 'http') ? $entry['media_url'] : app_url($entry['media_url'])) ?>" controls style="width:100%;max-height:420px;border-radius:12px;background:#000"></video>
                                    <?php else: ?>
                                        <img src="<?= view_e(str_starts_with($entry['media_url'], 'http') ? $entry['media_url'] : app_url($entry['media_url'])) ?>" alt="مرفق التغطية" loading="lazy" style="width:100%;max-height:420px;object-fit:cover;border-radius:12px" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&q=80';">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Entry Action & Reaction Bar -->
                            <div class="entry-rx-bar d-flex justify-content-between align-items-center flex-wrap gap-2" style="margin-top:14px;padding-top:10px;border-top:1px solid var(--border-subtle)">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <?php
                                    $eRx = $reactions[(int)$entry['id']] ?? [];
                                    foreach ($reactionTypes as $type => $info):
                                        $cnt = $eRx[$type] ?? 0;
                                    ?>
                                        <button type="button" class="btn-live-reaction entry-rx-btn" data-type="<?= $type ?>" data-entry-id="<?= $entry['id'] ?>" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:3px 10px;border-radius:50px;font-size:0.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                            <span><?= $info['icon'] ?></span>
                                            <span class="rx-count font-monospace" style="font-weight:700"><?= $cnt ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <!-- Reply / Discuss Button -->
                                <button type="button" class="btn-reply-entry" data-entry-id="<?= $entry['id'] ?>" data-snippet="<?= htmlspecialchars(mb_substr($entry['content_ar'], 0, 70), ENT_QUOTES) ?>" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);color:var(--text-muted);padding:4px 12px;border-radius:50px;font-size:0.8rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all 0.2s ease">
                                    <span>💬 الرد والمناقشة</span>
                                    <span class="badge bg-secondary font-monospace rounded-pill replies-count-badge" style="font-size:0.72rem"><?= (int)($entry['replies_count'] ?? 0) ?></span>
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ================= Left Column: YouTube Live Chat Sidebar ================= -->
        <aside class="live-chat-sidebar" id="live-chat-sidebar" style="background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:var(--radius-card);display:flex;flex-direction:column;height:calc(100vh - 120px);position:sticky;top:90px;box-shadow:var(--shadow-md);overflow:hidden">
            
            <!-- Chat Header -->
            <div style="padding:14px 18px;border-bottom:1px solid var(--border-subtle);display:flex;align-items:center;justify-content:space-between;background:var(--bg-surface-elevated)">
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="font-size:1.1rem">💬</span>
                    <strong style="font-size:0.95rem;color:var(--text-main)">دردشة البث المباشر</strong>
                </div>
                <span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;font-weight:700;font-size:0.75rem;padding:4px 8px;border-radius:50px">
                    ● متصل الآن
                </span>
            </div>

            <!-- Chat Messages Flow Container -->
            <div id="live-chat-messages" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;scroll-behavior:smooth">
                <?php if (empty($initialChats)): ?>
                    <div id="chat-empty-hint" style="text-align:center;color:var(--text-muted);margin:auto;font-size:0.85rem;padding:20px">
                        👋 مرحباً بك في البث المباشر!<br>شارك برأيك أو انقر "الرد والمناقشة" للتعليق على أي فقرة محددة.
                    </div>
                <?php else: ?>
                    <?php foreach ($initialChats as $c): ?>
                        <div class="chat-msg-item" data-chat-id="<?= $c['id'] ?>" style="display:flex;gap:10px;align-items:flex-start;animation:fadeIn 0.3s ease">
                            <div class="chat-avatar" style="width:30px;height:30px;border-radius:50%;background:var(--accent-primary);color:#050d1a;font-weight:800;font-size:0.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                <?= mb_substr($c['sender_name'], 0, 1) ?>
                            </div>
                            <div style="flex:1;background:var(--bg-surface-elevated);padding:8px 12px;border-radius:10px;border:1px solid var(--border-subtle)">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                                    <strong style="font-size:0.82rem;color:var(--accent-primary)"><?= htmlspecialchars($c['sender_name']) ?></strong>
                                    <small style="font-size:0.7rem;color:var(--text-dim)"><?= date('H:i', strtotime($c['created_at'])) ?></small>
                                </div>

                                <?php if (!empty($c['entry_id']) && !empty($c['reply_snippet'])): ?>
                                    <div class="chat-quote-pill" data-target-entry="<?= $c['entry_id'] ?>" style="background:rgba(0,242,254,0.08);border-inline-start:3px solid var(--accent-primary);padding:4px 8px;border-radius:4px;font-size:0.75rem;color:var(--text-dim);margin-bottom:6px;cursor:pointer" title="انقر للانتقال إلى الفقرة الأصلية">
                                        <span style="color:var(--accent-primary);font-weight:700">↩️ رداً على:</span> "<?= htmlspecialchars($c['reply_snippet']) ?>..."
                                    </div>
                                <?php endif; ?>

                                <div style="font-size:0.88rem;line-height:1.4;color:var(--text-main);word-break:break-word">
                                    <?= htmlspecialchars($c['message']) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Active Reply Mode Bar -->
            <div id="active-reply-banner" class="d-none" style="padding:8px 14px;background:rgba(0,242,254,0.1);border-top:1px solid rgba(0,242,254,0.25);border-inline-start:4px solid var(--accent-primary);display:flex;align-items:center;justify-content:space-between;gap:8px">
                <div style="font-size:0.78rem;color:var(--text-main);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                    <span style="color:var(--accent-primary);font-weight:700">↩️ الرد على فقرة:</span> <span id="reply-banner-snippet" style="color:var(--text-dim)"></span>
                </div>
                <button type="button" id="btn-cancel-reply" class="btn btn-sm btn-link text-danger p-0" style="font-size:0.75rem;text-decoration:none;font-weight:700" title="إلغاء تخصيص الرد">✕ إلغاء</button>
            </div>

            <!-- Chat Input Box -->
            <div style="padding:14px;border-top:1px solid var(--border-subtle);background:var(--bg-surface-elevated)">
                <form id="live-chat-form" action="" method="post">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="entry_id" id="chat-reply-entry-id" value="">

                    <?php if (!$currentUser): ?>
                        <div style="margin-bottom:8px">
                            <input type="text" id="chat-sender-name" class="form-control form-control-sm" placeholder="اسمك المستعار..." style="font-size:0.8rem;background:var(--bg-surface);border:1px solid var(--border-subtle);color:var(--text-main);border-radius:6px" maxlength="30">
                        </div>
                    <?php endif; ?>

                    <div style="display:flex;gap:6px">
                        <input type="text" id="chat-message-input" class="form-control form-control-sm" placeholder="اكتب تعليقك... (اضغط Enter)" style="font-size:0.85rem;background:var(--bg-surface);border:1px solid var(--border-subtle);color:var(--text-main);border-radius:6px" maxlength="300" required autocomplete="off">
                        <button type="submit" id="btn-send-chat" class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center" style="padding:0 12px;border-radius:6px" title="إرسال">
                            <?= ui_icon('send', '', 14) ?>
                        </button>
                    </div>
                </form>
            </div>
        </aside>
    </div>

</main>

<style>
@keyframes floatUpFade {
    0% { opacity: 1; transform: translateY(0) scale(1) rotate(0deg); }
    50% { opacity: 0.9; transform: translateY(-120px) scale(1.3) rotate(-10deg); }
    100% { opacity: 0; transform: translateY(-240px) scale(1.6) rotate(15deg); }
}
@keyframes cardTargetPulse {
    0% { transform: scale(1); box-shadow: 0 0 0 rgba(0,242,254,0); }
    50% { transform: scale(1.02); box-shadow: 0 0 30px rgba(0,242,254,0.4); border-color: var(--accent-primary); }
    100% { transform: scale(1); box-shadow: 0 0 0 rgba(0,242,254,0); }
}
.card-highlight-pulse {
    animation: cardTargetPulse 1.2s ease-in-out 2;
}
.floating-particle {
    position: absolute;
    bottom: 0;
    font-size: 2rem;
    animation: floatUpFade 2s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    user-select: none;
    pointer-events: none;
}
.btn-live-reaction:hover, .btn-reply-entry:hover {
    transform: scale(1.06);
    border-color: var(--accent-primary) !important;
}
.btn-live-reaction:active, .btn-reply-entry:active {
    transform: scale(0.95);
}
.chat-quote-pill:hover {
    background: rgba(0,242,254,0.18) !important;
}
@media (max-width: 991px) {
    .live-grid-container {
        grid-template-columns: 1fr !important;
    }
    .live-chat-sidebar {
        height: 500px !important;
        position: static !important;
        margin-top: 24px;
    }
}
</style>

<script>
// High-Frequency Real-Time Sync & Live Interactive Reply Suite
(function() {
    const blogId = <?= json_encode((int) $blog['id']) ?>;
    const csrfToken = document.querySelector('input[name="_csrf"]').value;
    const stream = document.getElementById('live-entries-stream');
    const pinnedContainer = document.getElementById('pinned-entries-container');
    const chatContainer = document.getElementById('live-chat-messages');
    const chatForm = document.getElementById('live-chat-form');
    const chatInput = document.getElementById('chat-message-input');
    const senderInput = document.getElementById('chat-sender-name');
    const particlesContainer = document.getElementById('floating-particles-container');

    const replyEntryIdInput = document.getElementById('chat-reply-entry-id');
    const activeReplyBanner = document.getElementById('active-reply-banner');
    const replyBannerSnippet = document.getElementById('reply-banner-snippet');
    const btnCancelReply = document.getElementById('btn-cancel-reply');

    const btnSortNewest = document.getElementById('btn-sort-newest');
    const btnSortOldest = document.getElementById('btn-sort-oldest');
    let sortMode = 'newest';

    // Local storage nickname memory
    if (senderInput) {
        const savedName = localStorage.getItem('live_chat_nickname');
        if (savedName) senderInput.value = savedName;
        senderInput.addEventListener('change', () => {
            if (senderInput.value.trim()) localStorage.setItem('live_chat_nickname', senderInput.value.trim());
        });
    }

    // Cache of current entry state
    const entryState = {};
    document.querySelectorAll('.live-entry-card').forEach(card => {
        const id = parseInt(card.getAttribute('data-entry-id') || '0', 10);
        if (id) {
            entryState[id] = {
                content: card.querySelector('.entry-content-box') ? card.querySelector('.entry-content-box').innerText.trim() : '',
                media: card.getAttribute('data-media-url') || '',
                pinned: card.classList.contains('pinned-entry') ? 1 : 0
            };
        }
    });

    // Highest known chat ID
    let maxChatId = 0;
    document.querySelectorAll('.chat-msg-item').forEach(el => {
        const cid = parseInt(el.getAttribute('data-chat-id') || '0', 10);
        if (cid > maxChatId) maxChatId = cid;
    });

    // Auto-scroll chat to bottom
    function scrollChatToBottom() {
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }
    scrollChatToBottom();

    // Floating particle effect
    function spawnFloatingParticle(icon) {
        if (!particlesContainer) return;
        const particle = document.createElement('div');
        particle.className = 'floating-particle';
        particle.textContent = icon;
        particle.style.left = Math.floor(Math.random() * 60) + 'px';
        particlesContainer.appendChild(particle);
        setTimeout(() => particle.remove(), 2000);
    }

    // Reaction handler
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-live-reaction');
        if (!btn) return;

        const type = btn.getAttribute('data-type');
        const entryId = btn.getAttribute('data-entry-id') || '';
        const iconSpan = btn.querySelector('span:first-child');
        const icon = iconSpan ? iconSpan.textContent : '🔥';

        spawnFloatingParticle(icon);

        const formData = new FormData();
        formData.append('_csrf', csrfToken);
        formData.append('reaction_type', type);
        if (entryId) formData.append('entry_id', entryId);

        fetch('<?= app_url("live-blog/") ?>' + blogId + '/reaction', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const countSpan = btn.querySelector('.rx-count');
                if (countSpan) countSpan.textContent = data.count;
            }
        })
        .catch(() => {});
    });

    // Reply to specific entry handler
    document.addEventListener('click', function(e) {
        const replyBtn = e.target.closest('.btn-reply-entry');
        if (replyBtn) {
            const entryId = replyBtn.getAttribute('data-entry-id');
            const snippet = replyBtn.getAttribute('data-snippet') || 'الفقرة المختارة';

            replyEntryIdInput.value = entryId;
            replyBannerSnippet.textContent = '"' + snippet + '..."';
            activeReplyBanner.classList.remove('d-none');
            chatInput.placeholder = 'اكتب ردك على هذه الفقرة...';
            chatInput.focus();

            // Smoothly highlight card
            const targetCard = document.getElementById('card-entry-' + entryId);
            if (targetCard) {
                targetCard.classList.remove('card-highlight-pulse');
                void targetCard.offsetWidth;
                targetCard.classList.add('card-highlight-pulse');
            }
        }

        // Click on Quote Pill in chat -> Scroll to card
        const quotePill = e.target.closest('.chat-quote-pill');
        if (quotePill) {
            const targetId = quotePill.getAttribute('data-target-entry');
            const targetCard = document.getElementById('card-entry-' + targetId);
            if (targetCard) {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                targetCard.classList.remove('card-highlight-pulse');
                void targetCard.offsetWidth;
                targetCard.classList.add('card-highlight-pulse');
            }
        }
    });

    // Cancel reply handler
    if (btnCancelReply) {
        btnCancelReply.addEventListener('click', function() {
            replyEntryIdInput.value = '';
            activeReplyBanner.classList.add('d-none');
            chatInput.placeholder = 'اكتب تعليقك... (اضغط Enter)';
        });
    }

    // Send Chat Handler
    if (chatForm) {
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            const senderName = senderInput ? senderInput.value.trim() : '';
            const entryId = replyEntryIdInput ? replyEntryIdInput.value.trim() : '';

            const formData = new FormData();
            formData.append('_csrf', csrfToken);
            formData.append('message', message);
            if (senderName) formData.append('sender_name', senderName);
            if (entryId) formData.append('entry_id', entryId);

            chatInput.value = '';
            if (btnCancelReply) btnCancelReply.click();

            fetch('<?= app_url("live-blog/") ?>' + blogId + '/chat/send', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.chat) {
                    appendChatMessage(data.chat);
                } else if (data.message) {
                    alert(data.message);
                }
            })
            .catch(() => {});
        });
    }

    function appendChatMessage(chat) {
        const hint = document.getElementById('chat-empty-hint');
        if (hint) hint.remove();

        const cid = parseInt(chat.id, 10);
        if (cid > maxChatId) maxChatId = cid;

        if (document.querySelector(`.chat-msg-item[data-chat-id="${cid}"]`)) {
            return;
        }

        const item = document.createElement('div');
        item.className = 'chat-msg-item';
        item.setAttribute('data-chat-id', cid);
        item.style.cssText = 'display:flex;gap:10px;align-items:flex-start;animation:fadeIn 0.3s ease';

        const initial = (chat.sender_name || 'U').charAt(0);
        const quoteHtml = (chat.entry_id && chat.reply_snippet) ? `
            <div class="chat-quote-pill" data-target-entry="${chat.entry_id}" style="background:rgba(0,242,254,0.08);border-inline-start:3px solid var(--accent-primary);padding:4px 8px;border-radius:4px;font-size:0.75rem;color:var(--text-dim);margin-bottom:6px;cursor:pointer" title="انقر للانتقال إلى الفقرة الأصلية">
                <span style="color:var(--accent-primary);font-weight:700">↩️ رداً على:</span> "${chat.reply_snippet}..."
            </div>
        ` : '';

        item.innerHTML = `
            <div class="chat-avatar" style="width:30px;height:30px;border-radius:50%;background:var(--accent-primary);color:#050d1a;font-weight:800;font-size:0.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                ${initial}
            </div>
            <div style="flex:1;background:var(--bg-surface-elevated);padding:8px 12px;border-radius:10px;border:1px solid var(--border-subtle)">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px">
                    <strong style="font-size:0.82rem;color:var(--accent-primary)">${chat.sender_name}</strong>
                    <small style="font-size:0.7rem;color:var(--text-dim)">${chat.created_at || 'الآن'}</small>
                </div>
                ${quoteHtml}
                <div style="font-size:0.88rem;line-height:1.4;color:var(--text-main);word-break:break-word">
                    ${chat.message}
                </div>
            </div>
        `;
        chatContainer.appendChild(item);
        scrollChatToBottom();
    }

    function getMediaHtml(entry) {
        if (!entry.media_url) return '';
        const isVideo = entry.entry_type === 'video' || entry.media_url.match(/\.(mp4|webm|ogv)$/i);
        const fullUrl = entry.media_url.startsWith('http') ? entry.media_url : '<?= app_url('') ?>' + entry.media_url;

        if (isVideo) {
            return `<div class="entry-media-wrapper" style="margin-top:14px;border-radius:12px;overflow:hidden"><video src="${fullUrl}" controls style="width:100%;max-height:420px;border-radius:12px;background:#000"></video></div>`;
        } else {
            return `<div class="entry-media-wrapper" style="margin-top:14px;border-radius:12px;overflow:hidden"><img src="${fullUrl}" alt="مرفق التغطية" loading="lazy" style="width:100%;max-height:420px;object-fit:cover;border-radius:12px"></div>`;
        }
    }

    function renderEntryElement(entry, isNewAnim = false) {
        const timeStr = entry.created_at ? entry.created_at.substring(11, 19) : 'الآن';
        const isPinned = !!parseInt(entry.is_pinned, 10);
        const snippetEscaped = (entry.content_ar || '').substring(0, 70).replace(/"/g, '&quot;');

        const article = document.createElement('article');
        article.className = 'live-entry-card' + (isPinned ? ' pinned-entry' : '') + (isNewAnim ? ' live-entry-anim-new' : '');
        article.id = 'card-entry-' + entry.id;
        article.setAttribute('data-entry-id', entry.id);
        article.setAttribute('data-media-url', entry.media_url || '');

        const rxHtml = `
            <div class="entry-rx-bar d-flex justify-content-between align-items-center flex-wrap gap-2" style="margin-top:14px;padding-top:10px;border-top:1px solid var(--border-subtle)">
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <button type="button" class="btn-live-reaction entry-rx-btn" data-type="fire" data-entry-id="${entry.id}" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:3px 10px;border-radius:50px;font-size:0.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                        <span>🔥</span> <span class="rx-count font-monospace" style="font-weight:700">0</span>
                    </button>
                    <button type="button" class="btn-live-reaction entry-rx-btn" data-type="clap" data-entry-id="${entry.id}" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:3px 10px;border-radius:50px;font-size:0.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                        <span>👏</span> <span class="rx-count font-monospace" style="font-weight:700">0</span>
                    </button>
                    <button type="button" class="btn-live-reaction entry-rx-btn" data-type="lightbulb" data-entry-id="${entry.id}" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:3px 10px;border-radius:50px;font-size:0.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                        <span>💡</span> <span class="rx-count font-monospace" style="font-weight:700">0</span>
                    </button>
                    <button type="button" class="btn-live-reaction entry-rx-btn" data-type="heart" data-entry-id="${entry.id}" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);padding:3px 10px;border-radius:50px;font-size:0.8rem;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                        <span>❤️</span> <span class="rx-count font-monospace" style="font-weight:700">0</span>
                    </button>
                </div>
                <button type="button" class="btn-reply-entry" data-entry-id="${entry.id}" data-snippet="${snippetEscaped}" style="background:var(--bg-surface-elevated);border:1px solid var(--border-subtle);color:var(--text-muted);padding:4px 12px;border-radius:50px;font-size:0.8rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:all 0.2s ease">
                    <span>💬 الرد والمناقشة</span>
                    <span class="badge bg-secondary font-monospace rounded-pill replies-count-badge" style="font-size:0.72rem">${entry.replies_count || 0}</span>
                </button>
            </div>
        `;

        if (isPinned) {
            article.style.cssText = 'background:linear-gradient(135deg,rgba(0,242,254,0.08),rgba(139,92,246,0.05));border:2px solid var(--accent-primary);padding:24px;border-radius:var(--radius-card);box-shadow:0 0 20px rgba(0,242,254,0.15);transition:all 0.3s ease';
            article.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <span class="badge-tag" style="background:var(--accent-primary);color:#050d1a;font-weight:800">
                        📌 أهم أحداث وإعلانات البث
                    </span>
                    <span style="font-size:0.8rem;color:var(--text-dim)">${timeStr}</span>
                </div>
                <div class="entry-content-box" style="font-size:1.12rem;font-weight:700;line-height:1.8;color:var(--text-main)">
                    ${entry.content_ar.replace(/\n/g, '<br>')}
                </div>
                ${getMediaHtml(entry)}
                ${rxHtml}
            `;
        } else {
            article.style.cssText = 'background:var(--bg-surface);border:1px solid var(--border-subtle);border-inline-start:4px solid var(--accent-primary);padding:22px;border-radius:var(--radius-card);box-shadow:var(--shadow-sm);transition:all 0.3s ease';
            article.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-size:0.84rem;color:var(--text-dim);flex-wrap:wrap;gap:8px">
                    <span style="color:var(--accent-primary);font-weight:800;font-family:var(--font-numbers);display:inline-flex;align-items:center;gap:4px">
                        <span>⏱️</span>
                        <span>${timeStr}</span>
                    </span>
                    <span>بواسطة: <strong style="color:var(--text-main)">${entry.author_name || 'فريق التحرير المباشر'}</strong></span>
                </div>
                <div class="entry-content-box" style="font-size:1.02rem;line-height:1.8;color:var(--text-main)">
                    ${entry.content_ar.replace(/\n/g, '<br>')}
                </div>
                ${getMediaHtml(entry)}
                ${rxHtml}
            `;
        }

        return article;
    }

    // Main 3.5s Live Polling
    function pollLiveUpdates() {
        fetch('<?= app_url("live-blog/") ?>' + blogId + '/poll?chat_since=' + maxChatId)
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) return;

                // 1. Status Update
                if (data.status) {
                    const statusBadge = document.getElementById('live-status-badge');
                    const statusText = document.getElementById('live-status-text');
                    if (data.status === 'active') {
                        if (statusBadge) statusBadge.style.background = '#f43f5e';
                        if (statusText) statusText.textContent = 'بث حي ومباشر الآن';
                    } else {
                        if (statusBadge) statusBadge.style.background = 'var(--bg-surface-elevated)';
                        if (statusText) statusText.textContent = 'تغطية مؤرشفة ومنتهية';
                    }
                }

                // 2. Process Live Chats
                if (Array.isArray(data.chats) && data.chats.length > 0) {
                    data.chats.forEach(c => appendChatMessage(c));
                }

                // 3. Update Reaction Numbers
                if (data.reactions) {
                    document.querySelectorAll('.btn-live-reaction').forEach(btn => {
                        const type = btn.getAttribute('data-type');
                        const entryId = btn.getAttribute('data-entry-id');
                        const key = entryId ? parseInt(entryId, 10) : 'global';
                        const count = (data.reactions[key] && data.reactions[key][type]) ? data.reactions[key][type] : 0;
                        const countSpan = btn.querySelector('.rx-count');
                        if (countSpan) countSpan.textContent = count;
                    });
                }

                // 4. Process Entries Sync (Add, Update, Delete)
                const serverEntries = Array.isArray(data.entries) ? data.entries : [];
                const serverIds = new Set(serverEntries.map(e => parseInt(e.id, 10)));

                document.querySelectorAll('.live-entry-card').forEach(card => {
                    const cardId = parseInt(card.getAttribute('data-entry-id') || '0', 10);
                    if (cardId && !serverIds.has(cardId)) {
                        card.style.transition = 'all 0.4s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.96) translateY(-10px)';
                        setTimeout(() => {
                            card.remove();
                            checkEmptyState(serverEntries.length);
                        }, 400);
                        delete entryState[cardId];
                    }
                });

                if (serverEntries.length === 0) {
                    checkEmptyState(0);
                    return;
                }

                const noMsg = document.getElementById('no-entries-msg');
                if (noMsg) noMsg.remove();

                serverEntries.forEach(entry => {
                    const entryId = parseInt(entry.id, 10);
                    const isPinned = !!parseInt(entry.is_pinned, 10);
                    const existingCard = document.querySelector(`.live-entry-card[data-entry-id="${entryId}"]`);

                    if (existingCard) {
                        const old = entryState[entryId] || {};
                        const contentChanged = old.content !== undefined && old.content !== entry.content_ar.trim();
                        const mediaChanged = old.media !== undefined && old.media !== (entry.media_url || '');
                        const pinChanged = old.pinned !== undefined && old.pinned !== (isPinned ? 1 : 0);

                        // Update replies counter badge
                        const replyBadge = existingCard.querySelector('.replies-count-badge');
                        if (replyBadge && entry.replies_count !== undefined) {
                            replyBadge.textContent = entry.replies_count;
                        }

                        if (contentChanged || mediaChanged || pinChanged) {
                            const contentBox = existingCard.querySelector('.entry-content-box');
                            if (contentBox) {
                                contentBox.innerHTML = entry.content_ar.replace(/\n/g, '<br>');
                            }

                            const mediaWrapper = existingCard.querySelector('.entry-media-wrapper');
                            if (mediaWrapper) mediaWrapper.remove();
                            if (entry.media_url) {
                                existingCard.insertAdjacentHTML('beforeend', getMediaHtml(entry));
                            }
                            existingCard.setAttribute('data-media-url', entry.media_url || '');

                            existingCard.style.transition = 'all 0.3s ease';
                            existingCard.style.background = 'rgba(0, 242, 254, 0.15)';
                            setTimeout(() => {
                                existingCard.style.background = isPinned ? 'linear-gradient(135deg,rgba(0,242,254,0.08),rgba(139,92,246,0.05))' : 'var(--bg-surface)';
                            }, 1200);

                            if (pinChanged) {
                                existingCard.remove();
                                const newEl = renderEntryElement(entry, true);
                                if (isPinned && pinnedContainer) {
                                    pinnedContainer.appendChild(newEl);
                                } else {
                                    if (sortMode === 'newest') stream.insertBefore(newEl, stream.firstChild);
                                    else stream.appendChild(newEl);
                                }
                            }

                            entryState[entryId] = {
                                content: entry.content_ar.trim(),
                                media: entry.media_url || '',
                                pinned: isPinned ? 1 : 0
                            };
                        }
                    } else {
                        const newEl = renderEntryElement(entry, true);
                        if (isPinned && pinnedContainer) {
                            pinnedContainer.appendChild(newEl);
                        } else {
                            if (sortMode === 'newest') stream.insertBefore(newEl, stream.firstChild);
                            else stream.appendChild(newEl);
                        }

                        entryState[entryId] = {
                            content: entry.content_ar.trim(),
                            media: entry.media_url || '',
                            pinned: isPinned ? 1 : 0
                        };
                    }
                });

                checkEmptyState(serverEntries.length);
            })
            .catch(() => {});
    }

    function checkEmptyState(count) {
        if (count === 0 && !document.getElementById('no-entries-msg') && stream.querySelectorAll('.live-entry-card').length === 0) {
            const noMsg = document.createElement('div');
            noMsg.id = 'no-entries-msg';
            noMsg.style.cssText = 'padding:60px 20px;text-align:center;background:var(--bg-surface);border:1px solid var(--border-subtle);border-radius:var(--radius-card);color:var(--text-muted)';
            noMsg.innerHTML = `
                <div style="font-size:2.5rem;margin-bottom:12px">📡</div>
                <h4 style="color:var(--text-main);margin-bottom:6px">التغطية الحية جارية الآن</h4>
                <p style="margin-bottom:0">ستظهر التحديثات والإعلانات هنا فور نشرها من المحررين مباشرة دون الحاجة لتحديث الصفحة.</p>
            `;
            stream.appendChild(noMsg);
        }
    }

    // Poll every 3.5 seconds
    setInterval(pollLiveUpdates, 3500);

    // Sorting toggles
    btnSortNewest.addEventListener('click', () => {
        if (sortMode === 'newest') return;
        sortMode = 'newest';
        btnSortNewest.style.background = 'var(--accent-primary)';
        btnSortNewest.style.color = '#050d1a';
        btnSortOldest.style.background = 'transparent';
        btnSortOldest.style.color = 'var(--text-muted)';
        reorderCards();
    });

    btnSortOldest.addEventListener('click', () => {
        if (sortMode === 'oldest') return;
        sortMode = 'oldest';
        btnSortOldest.style.background = 'var(--accent-primary)';
        btnSortOldest.style.color = '#050d1a';
        btnSortNewest.style.background = 'transparent';
        btnSortNewest.style.color = 'var(--text-muted)';
        reorderCards();
    });

    function reorderCards() {
        const cards = Array.from(stream.querySelectorAll('.live-entry-card:not(.pinned-entry)'));
        cards.sort((a, b) => {
            const idA = parseInt(a.getAttribute('data-entry-id') || '0', 10);
            const idB = parseInt(b.getAttribute('data-entry-id') || '0', 10);
            return sortMode === 'newest' ? (idB - idA) : (idA - idB);
        });
        cards.forEach(card => stream.appendChild(card));
    }
})();
</script>

<?php require_once APP_ROOT . '/views/partials/footer.php'; ?>
