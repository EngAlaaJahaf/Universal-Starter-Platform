<?php
function st_e($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="ar" dir="rtl" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>قصص التقنية السريعة | Tech Stories</title>
    <link rel="stylesheet" href="<?= st_e(app_url('assets/css/site.css')) ?>">
    <style>
        .stories-viewer {
            position: fixed; inset: 0; background: #050811; z-index: 1000;
            display: flex; align-items: center; justify-content: center;
        }
        .story-phone {
            width: min(420px, 96%); height: min(780px, 92vh);
            background: #0f172a; border-radius: 28px; border: 1px solid var(--border-glow);
            position: relative; overflow: hidden; box-shadow: var(--shadow-lg), var(--shadow-neon);
            display: flex; flex-direction: column; justify-content: flex-end; padding: 32px;
        }
        .story-bg {
            position: absolute; inset: 0; background-size: cover; background-position: center;
            transition: opacity 0.4s ease; z-index: 0;
        }
        .story-bg::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.85) 100%);
        }
        .story-bar-wrap {
            position: absolute; top: 16px; left: 16px; right: 16px;
            display: flex; gap: 4px; z-index: 10;
        }
        .story-bar {
            height: 3px; flex-grow: 1; background: rgba(255,255,255,0.3); border-radius: 2px;
            overflow: hidden;
        }
        .story-bar-fill {
            height: 100%; background: var(--cyan); width: 0%;
        }
        .story-nav-btn {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 44px; height: 44px; border-radius: 50%; background: rgba(0,0,0,0.5);
            border: 1px solid var(--border-subtle); color: #fff; cursor: pointer;
            display: grid; place-items: center; z-index: 20; font-size: 1.2rem;
        }
        .story-nav-btn.prev { right: 10px; }
        .story-nav-btn.next { left: 10px; }
    </style>
</head>
<body>

<div class="stories-viewer">
    <a href="<?= st_e(app_url()) ?>" style="position:absolute;top:20px;right:20px;color:#fff;font-size:2rem;z-index:30;cursor:pointer">×</a>

    <div class="story-phone" id="story-phone">
        <div class="story-bar-wrap" id="story-bars">
            <?php foreach ($stories as $i => $s): ?>
                <div class="story-bar"><div class="story-bar-fill" id="bar-fill-<?= $i ?>"></div></div>
            <?php endforeach; ?>
        </div>

        <button class="story-nav-btn prev" id="story-prev" type="button">›</button>
        <button class="story-nav-btn next" id="story-next" type="button">‹</button>

        <div class="story-bg" id="story-bg"></div>

        <div style="position:relative;z-index:5">
            <span class="badge-tag hot" id="story-cat">تقنية</span>
            <h2 id="story-title" style="font-size:1.5rem;font-weight:800;color:#fff;margin:10px 0"></h2>
            <p id="story-excerpt" style="font-size:0.9rem;color:#cbd5e1;line-height:1.6;margin-bottom:18px"></p>
            <a id="story-link" href="#" class="btn-primary-glow" style="width:100%;justify-content:center">قراءة المقال الكامل ←</a>
        </div>
    </div>
</div>

<script>
const stories = <?= json_encode($stories, JSON_UNESCAPED_UNICODE) ?>;
let currentIndex = 0;
let storyTimer = null;

function renderStory(idx) {
    if (stories.length === 0) return;
    if (idx < 0) idx = 0;
    if (idx >= stories.length) idx = stories.length - 1;
    currentIndex = idx;
    const s = stories[currentIndex];

    document.getElementById('story-bg').style.backgroundImage = `url('${s.featured_image ? s.featured_image : ''}')`;
    document.getElementById('story-cat').textContent = s.category_name || 'أخبار تقنية';
    document.getElementById('story-title').textContent = s.title;
    document.getElementById('story-excerpt').textContent = s.excerpt || '';
    document.getElementById('story-link').href = '<?= app_url('article/') ?>' + s.slug;

    // Reset and fill bars
    for (let i = 0; i < stories.length; i++) {
        const fill = document.getElementById('bar-fill-' + i);
        if (i < currentIndex) fill.style.width = '100%';
        else if (i === currentIndex) fill.style.width = '100%';
        else fill.style.width = '0%';
    }
}

document.getElementById('story-prev').addEventListener('click', () => {
    if (currentIndex > 0) renderStory(currentIndex - 1);
});
document.getElementById('story-next').addEventListener('click', () => {
    if (currentIndex < stories.length - 1) renderStory(currentIndex + 1);
    else window.location.href = '<?= app_url() ?>';
});

renderStory(0);
</script>
</body>
</html>
