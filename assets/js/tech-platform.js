/**
 * AsabTech - Next-Gen Interactive Engine
 * Command Palette, Audio Player, Reactions, Bookmarks Drawer, Live Ticker, Polls & Zen Mode
 */

(function () {
  'use strict';

  // --- 1. Global Utilities & Toast Notifications ---
  function showToast(message, icon = '⚡') {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(10px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }

  window.showToast = showToast;

  // --- 2. Theme Management (Dark / Light) ---
  const themeKey = 'tech-platform-theme';
  const root = document.documentElement;
  const themeBtn = document.getElementById('theme-toggle');

  const sunSvg = `<svg class="ui-icon ui-icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>`;
  const moonSvg = `<svg class="ui-icon ui-icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>`;

  function initTheme() {
    const saved = localStorage.getItem(themeKey) || root.getAttribute('data-theme') || 'dark';
    root.setAttribute('data-theme', saved);
    updateThemeIcon(saved);
  }

  function toggleTheme() {
    const current = root.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    root.setAttribute('data-theme', next);
    try {
      localStorage.setItem(themeKey, next);
      document.cookie = "site_theme=" + next + ";path=/;max-age=31536000;SameSite=Lax";
    } catch(e) {}
    updateThemeIcon(next);
    showToast(next === 'dark' ? 'تم تفعيل الوضع الليلي' : 'تم تفعيل الوضع النهاري', next === 'dark' ? moonSvg : sunSvg);
  }

  function updateThemeIcon(theme) {
    if (themeBtn) {
      themeBtn.innerHTML = theme === 'dark' ? sunSvg : moonSvg;
      themeBtn.setAttribute('title', theme === 'dark' ? 'التبديل إلى الوضع النهاري' : 'التبديل إلى الوضع الليلي');
    }
  }

  if (themeBtn) {
    themeBtn.addEventListener('click', toggleTheme);
  }
  initTheme();

  // --- 3. Live Tech Pulse & Financials Stream ---
  const defaultTickerData = [
    { s: 'NVDA', v: '$225.16', c: '+3.4%', up: true },
    { s: 'AAPL', v: '$305.93', c: '+1.1%', up: true },
    { s: 'MSFT', v: '$495.40', c: '-0.3%', up: false },
    { s: 'GOOGL', v: '$345.90', c: '+2.2%', up: true },
    { s: 'BTC', v: '$62,980', c: '+4.8%', up: true },
    { s: 'ETH', v: '$1,880', c: '+2.9%', up: true },
    { s: 'AI_INDEX', v: '3,850.5', c: '+5.1%', up: true }
  ];

  function renderTickerItems(items) {
    const track = document.getElementById('pulse-ticker-track');
    if (!track || !Array.isArray(items) || items.length === 0) return;
    const itemsHtml = items.map(item => `
      <div class="ticker-item" title="${item.name || item.s}">
        <span class="symbol">${item.s}</span>
        <span class="val">${item.v}</span>
        <span class="change ${item.up ? 'up' : 'down'}">${item.c}</span>
      </div>
    `).join('');
    // Duplicate track for seamless infinite scroll animation
    track.innerHTML = itemsHtml + itemsHtml;
  }

  function initTicker() {
    const track = document.getElementById('pulse-ticker-track');
    if (!track) return;

    // 1. Render immediate cached or default data to avoid delay
    let cached = null;
    try {
      cached = JSON.parse(sessionStorage.getItem('tech_live_market_pulse'));
    } catch(e) {}

    if (cached && Array.isArray(cached.data)) {
      renderTickerItems(cached.data);
    } else {
      renderTickerItems(defaultTickerData);
    }

    // 2. Fetch fresh live market data asynchronously
    const baseUrl = window.APP_BASE_URL || '';
    fetch(baseUrl + '/api/v1/market-pulse')
      .then(res => res.json())
      .then(res => {
        if (res && res.ok && Array.isArray(res.data) && res.data.length > 0) {
          renderTickerItems(res.data);
          try {
            sessionStorage.setItem('tech_live_market_pulse', JSON.stringify({
              time: Date.now(),
              data: res.data
            }));
          } catch(e) {}
        }
      })
      .catch(err => {
        console.debug('Market pulse live fetch fallback active:', err);
      });
  }
  initTicker();

  // --- 3b. Breaking Headlines Rotator (topbar) ---
  (function initBreakingRotator() {
    const viewport = document.getElementById('breaking-viewport');
    if (!viewport) return;
    const items = Array.prototype.slice.call(viewport.querySelectorAll('.breaking-item'));
    if (items.length < 2) return;
    const prevBtn = document.getElementById('breaking-prev');
    const nextBtn = document.getElementById('breaking-next');
    let idx = 0;
    let timer = null;
    const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function show(n) {
      idx = (n + items.length) % items.length;
      items.forEach((el, i) => el.classList.toggle('active', i === idx));
    }
    function start() {
      if (reduceMotion || timer) return;
      timer = setInterval(() => show(idx + 1), 5000);
    }
    function stop() {
      if (timer) { clearInterval(timer); timer = null; }
    }
    if (prevBtn) prevBtn.addEventListener('click', () => { stop(); show(idx - 1); start(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { stop(); show(idx + 1); start(); });
    const bar = viewport.closest('.breaking-news');
    if (bar) {
      bar.addEventListener('mouseenter', stop);
      bar.addEventListener('mouseleave', start);
    }
    show(0);
    start();
  })();

  // --- 4. Bookmarks & Reading List Drawer ---
  const bookmarkKey = 'tech-platform-bookmarks';

  function getBookmarks() {
    try {
      return JSON.parse(localStorage.getItem(bookmarkKey)) || [];
    } catch {
      return [];
    }
  }

  function saveBookmarks(list) {
    localStorage.setItem(bookmarkKey, JSON.stringify(list));
    updateBookmarkBadge();
    renderBookmarksList();
  }

  function updateBookmarkBadge() {
    const badges = document.querySelectorAll('.bookmark-badge-count');
    const count = getBookmarks().length;
    badges.forEach(b => {
      b.textContent = count;
      b.style.display = count > 0 ? 'grid' : 'none';
    });
  }

  function toggleBookmark(article) {
    let list = getBookmarks();
    const index = list.findIndex(item => item.id == article.id);
    if (index > -1) {
      list.splice(index, 1);
      showToast('تمت إزالة المقال من قائمة القراءة', '🗑️');
    } else {
      list.unshift(article);
      showToast('تم حفظ المقال في قائمة القراءة للمتابعة لاحقاً', '🔖');
    }
    saveBookmarks(list);
    updateBookmarkButtons();
  }

  function updateBookmarkButtons() {
    const list = getBookmarks();
    document.querySelectorAll('.card-bookmark-btn, .bookmark-toggle-btn').forEach(btn => {
      const id = btn.getAttribute('data-article-id');
      const isSaved = list.some(item => item.id == id);
      if (isSaved) {
        btn.classList.add('saved');
        btn.innerHTML = '★';
      } else {
        btn.classList.remove('saved');
        btn.innerHTML = '☆';
      }
    });
  }

  function renderBookmarksList() {
    const container = document.getElementById('drawer-bookmarks-list');
    if (!container) return;
    const list = getBookmarks();
    if (list.length === 0) {
      container.innerHTML = `
        <div style="text-align:center;padding:40px 10px;color:var(--text-muted)">
          <div style="font-size:2.5rem;margin-bottom:10px">🔖</div>
          <p>قائمة القراءة فارغة حالياً.</p>
          <small>انقر على علامة النجمة في أي مقال لحفظه وقراءته لاحقاً.</small>
        </div>
      `;
      return;
    }
    container.innerHTML = list.map(item => `
      <div class="trending-card" style="margin-bottom:10px;background:var(--bg-surface-elevated);position:relative">
        <div class="trending-info" style="flex-grow:1">
          <a href="${item.url}" style="font-weight:700;font-size:0.92rem;display:block;margin-bottom:4px">${item.title}</a>
          <span style="color:var(--cyan);font-size:0.75rem">${item.category || 'تقنية'}</span>
        </div>
        <button class="remove-bookmark-btn" data-id="${item.id}" style="color:var(--rose);cursor:pointer;padding:4px 8px;font-size:1.1rem" title="حذف">×</button>
      </div>
    `).join('');

    container.querySelectorAll('.remove-bookmark-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const id = btn.getAttribute('data-id');
        let list = getBookmarks().filter(i => i.id != id);
        saveBookmarks(list);
        updateBookmarkButtons();
        updateBookmarkBadge();
        renderBookmarksList();
        showToast('تم الحذف من القائمة', '🗑️');
      });
    });
  }

  // Drawer Open/Close with direct & delegated click handlers
  window.openBookmarksDrawer = function() {
    const backdrop = document.getElementById('bookmarks-drawer-backdrop');
    if (backdrop) {
      backdrop.classList.add('open');
      renderBookmarksList();
    }
  };

  window.closeBookmarksDrawer = function() {
    const backdrop = document.getElementById('bookmarks-drawer-backdrop');
    if (backdrop) {
      backdrop.classList.remove('open');
    }
  };

  document.addEventListener('click', (e) => {
    const openBtn = e.target.closest('.open-bookmarks-drawer');
    if (openBtn) {
      e.preventDefault();
      window.openBookmarksDrawer();
      return;
    }
    const closeBtn = e.target.closest('#close-bookmarks-drawer, .close-bookmarks-drawer');
    if (closeBtn) {
      e.preventDefault();
      window.closeBookmarksDrawer();
      return;
    }
    const backdrop = document.getElementById('bookmarks-drawer-backdrop');
    if (backdrop && e.target === backdrop) {
      window.closeBookmarksDrawer();
    }
  });

  // Bind Bookmark Click Events
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.card-bookmark-btn, .bookmark-toggle-btn');
    if (btn) {
      e.preventDefault();
      e.stopPropagation();
      const article = {
        id: btn.getAttribute('data-article-id'),
        title: btn.getAttribute('data-title'),
        url: btn.getAttribute('data-url'),
        category: btn.getAttribute('data-category')
      };
      toggleBookmark(article);
    }
  });

  updateBookmarkBadge();
  updateBookmarkButtons();

  // --- 5. Command Palette (Spotlight Search Modal - Ctrl+K) ---
  const cmdModal = document.getElementById('cmd-modal-backdrop');
  const cmdInput = document.getElementById('cmd-search-input');
  const cmdResults = document.getElementById('cmd-results-list');
  const cmdTriggers = document.querySelectorAll('.cmd-search-trigger, .open-cmd-palette');

  function openCommandPalette() {
    if (!cmdModal) return;
    cmdModal.classList.add('open');
    if (cmdInput) {
      cmdInput.value = '';
      cmdInput.focus();
      renderDefaultCmdItems();
    }
  }

  function closeCommandPalette() {
    if (cmdModal) cmdModal.classList.remove('open');
  }

  function renderDefaultCmdItems() {
    if (!cmdResults) return;
    cmdResults.innerHTML = `
      <div style="font-size:0.75rem;font-weight:700;color:var(--text-dim);margin:8px 12px;text-transform:uppercase">أوامر سريعة وتصنيفات</div>
      <a class="cmd-item" href="${window.APP_BASE_URL || '/'}">
        <span>🏠 الصفحة الرئيسية</span>
        <kbd>↵</kbd>
      </a>
      <a class="cmd-item" href="${window.APP_BASE_URL || '/'}search?q=ذكاء+اصطناعي">
        <span>🤖 أخبار الذكاء الاصطناعي (AI)</span>
        <span style="font-size:0.75rem;color:var(--cyan)">تريند</span>
      </a>
      <a class="cmd-item" href="${window.APP_BASE_URL || '/'}search?q=أمن+سيبراني">
        <span>🛡️ الأمن السيبراني والحماية</span>
        <span style="font-size:0.75rem;color:var(--purple)">حماية</span>
      </a>
      <a class="cmd-item" href="${window.APP_BASE_URL || '/'}search?q=هواتف">
        <span>📱 الهواتف والأجهزة الذكية</span>
        <span style="font-size:0.75rem;color:var(--amber)">مراجعات</span>
      </a>
    `;
  }

  cmdTriggers.forEach(t => t.addEventListener('click', (e) => {
    e.preventDefault();
    openCommandPalette();
  }));

  if (cmdModal) {
    cmdModal.addEventListener('click', (e) => {
      if (e.target === cmdModal) closeCommandPalette();
    });
  }

  // Keyboard shortcut Ctrl+K / Cmd+K / Esc (Supporting Arabic & English keyboard layouts)
  document.addEventListener('keydown', (e) => {
    const isK = e.code === 'KeyK' || e.key.toLowerCase() === 'k' || e.key === 'ك' || e.keyCode === 75;
    if ((e.ctrlKey || e.metaKey) && isK) {
      e.preventDefault();
      e.stopPropagation();
      if (cmdModal && cmdModal.classList.contains('open')) {
        closeCommandPalette();
      } else {
        openCommandPalette();
      }
    } else if (e.key === 'Escape' && cmdModal && cmdModal.classList.contains('open')) {
      e.preventDefault();
      closeCommandPalette();
    }
  });

  // Real-time search in Command Palette
  let searchTimeout = null;
  if (cmdInput) {
    cmdInput.addEventListener('input', () => {
      clearTimeout(searchTimeout);
      const query = cmdInput.value.trim();
      if (!query) {
        renderDefaultCmdItems();
        return;
      }
      searchTimeout = setTimeout(() => {
        // Fetch or filter articles
        fetch((window.APP_BASE_URL || '/') + 'search?q=' + encodeURIComponent(query))
          .then(res => res.text())
          .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const items = doc.querySelectorAll('.news-card, .trending-card, .item');
            if (items.length === 0) {
              cmdResults.innerHTML = `<div style="padding:24px;text-align:center;color:var(--text-muted)">لا توجد نتائج مطابقة لـ "${query}"</div>`;
              return;
            }
            let out = `<div style="font-size:0.75rem;font-weight:700;color:var(--cyan);margin:8px 12px">نتائج البحث الفوري (${items.length})</div>`;
            items.forEach((it, idx) => {
              if (idx > 5) return;
              const link = it.querySelector('a') ? it.querySelector('a').getAttribute('href') : '#';
              const title = it.querySelector('h2, h3, h4, strong') ? it.querySelector('h2, h3, h4, strong').textContent.trim() : query;
              out += `
                <a class="cmd-item" href="${link}">
                  <span style="font-weight:600">${title}</span>
                  <span style="font-size:0.75rem;color:var(--text-dim)">قراءة ↗</span>
                </a>
              `;
            });
            cmdResults.innerHTML = out;
          })
          .catch(() => {
            cmdResults.innerHTML = `
              <a class="cmd-item" href="${(window.APP_BASE_URL || '/')}search?q=${encodeURIComponent(query)}">
                <span>بحث كامل عن: "${query}"</span>
                <kbd>↵</kbd>
              </a>
            `;
          });
      }, 250);
    });
  }

  // --- 6. Smart Audio Player Widget (Speech Synthesis + Waveform Animation) ---
  const audioWidget = document.getElementById('smart-audio-player');
  if (audioWidget) {
    const playBtn = document.getElementById('audio-play-btn');
    const speedBtn = document.getElementById('audio-speed-btn');
    const speeds = [1, 1.25, 1.5];
    let currentSpeedIndex = 0;
    let isPlaying = false;
    let synth = window.speechSynthesis;
    let utterance = null;

    function getArticleText() {
      const contentEl = document.querySelector('.reader-content') || document.querySelector('.article-content');
      return contentEl ? contentEl.innerText : '';
    }

    function togglePlay() {
      if (!synth) {
        showToast('القارئ الصوتي غير مدعوم في متصفحك', '⚠️');
        return;
      }
      if (isPlaying) {
        synth.cancel();
        isPlaying = false;
        audioWidget.classList.remove('playing');
        if (playBtn) playBtn.innerHTML = '▶';
        showToast('تم إيقاف القراءة الصوتية', '⏸️');
      } else {
        const text = getArticleText();
        if (!text) return;
        synth.cancel();
        utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'ar-SA';
        utterance.rate = speeds[currentSpeedIndex];

        utterance.onend = () => {
          isPlaying = false;
          audioWidget.classList.remove('playing');
          if (playBtn) playBtn.innerHTML = '▶';
        };

        utterance.onerror = () => {
          isPlaying = false;
          audioWidget.classList.remove('playing');
          if (playBtn) playBtn.innerHTML = '▶';
        };

        synth.speak(utterance);
        isPlaying = true;
        audioWidget.classList.add('playing');
        if (playBtn) playBtn.innerHTML = '⏸';
        showToast('جارِ قراءة المقال صوتياً بصوت الذكاء الاصطناعي...', '🎙️');
      }
    }

    if (playBtn) playBtn.addEventListener('click', togglePlay);

    if (speedBtn) {
      speedBtn.addEventListener('click', () => {
        currentSpeedIndex = (currentSpeedIndex + 1) % speeds.length;
        const newSpeed = speeds[currentSpeedIndex];
        speedBtn.textContent = newSpeed + 'x';
        if (isPlaying && utterance) {
          togglePlay();
          togglePlay();
        }
        showToast(`تم تغيير سرعة القارئ إلى ${newSpeed}x`, '⚡');
      });
    }
  }

  // --- 7. Live Micro-Reactions System (Real Database Persistence) ---
  document.querySelectorAll('.reaction-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const countEl = this.querySelector('.count');
      const type = this.getAttribute('data-reaction');
      const articleId = this.getAttribute('data-article-id');

      if (!articleId || !type) return;

      const wasActive = this.classList.contains('active');
      this.classList.toggle('active');

      if (!wasActive) {
        showToast('شكراً لتفاعلك ومشاركتك!', '✨');
        createParticleEffect(this);
      }

      fetch(`/api/reaction/${articleId}/${type}`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data && data.ok) {
          countEl.textContent = data.count;
          if (data.reacted) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        }
      })
      .catch(() => {
        // Fallback local update
        let current = parseInt(countEl.textContent, 10) || 0;
        countEl.textContent = wasActive ? Math.max(0, current - 1) : current + 1;
      });
    });
  });

  function createParticleEffect(element) {
    const rect = element.getBoundingClientRect();
    for (let i = 0; i < 6; i++) {
      const particle = document.createElement('div');
      particle.textContent = '✨';
      particle.style.position = 'fixed';
      particle.style.left = `${rect.left + rect.width / 2}px`;
      particle.style.top = `${rect.top}px`;
      particle.style.pointerEvents = 'none';
      particle.style.fontSize = '1.2rem';
      particle.style.zIndex = '9999';
      particle.style.transition = 'all 0.6s cubic-bezier(0.2, 0.8, 0.2, 1)';
      document.body.appendChild(particle);

      const angle = (Math.PI / 3) * i;
      const dist = 40 + Math.random() * 30;
      setTimeout(() => {
        particle.style.transform = `translate(${Math.cos(angle) * dist}px, ${-Math.sin(angle) * dist - 20}px) scale(0.2)`;
        particle.style.opacity = '0';
      }, 20);

      setTimeout(() => particle.remove(), 700);
    }
  }

  // --- 8. Interactive Live Tech Poll ---
  const pollContainer = document.getElementById('interactive-tech-poll');
  if (pollContainer) {
    const pollId = pollContainer.getAttribute('data-poll-id') || 'poll_1';
    const votedKey = `poll_voted_${pollId}`;
    const opts = pollContainer.querySelectorAll('.poll-opt');

    function applyVotePercentages(selectedIdx) {
      const total = 1420 + (selectedIdx !== undefined ? 1 : 0);
      const baseVotes = [650, 480, 290];
      if (selectedIdx !== undefined) baseVotes[selectedIdx]++;

      opts.forEach((opt, idx) => {
        const percent = Math.round((baseVotes[idx] / total) * 100);
        const bar = opt.querySelector('.poll-bar');
        const percentSpan = opt.querySelector('.poll-percent');
        if (bar) bar.style.width = percent + '%';
        if (percentSpan) percentSpan.textContent = percent + '%';
      });
    }

    if (localStorage.getItem(votedKey)) {
      pollContainer.classList.add('voted');
      applyVotePercentages();
    }

    opts.forEach((opt, idx) => {
      opt.addEventListener('click', () => {
        if (localStorage.getItem(votedKey)) {
          showToast('لقد قمت بالتصويت بالفعل!', 'ℹ️');
          return;
        }
        localStorage.setItem(votedKey, idx);
        pollContainer.classList.add('voted');
        applyVotePercentages(idx);
        showToast('تم تسجيل صوتك بنجاح! شكراً لمشاركتك 📊', '✅');
      });
    });
  }

  // --- 9. Zen / Focus Reader Mode ---
  const zenToggle = document.getElementById('zen-mode-toggle');
  if (zenToggle) {
    zenToggle.addEventListener('click', () => {
      document.body.classList.toggle('zen-mode-active');
      const active = document.body.classList.contains('zen-mode-active');
      showToast(active ? 'تم تفعيل نمط القراءة الهادئ (بدون مشتتات) 📖' : 'تم الخروج من نمط القراءة الهادئ', '👁️');
    });
  }

  // --- 10. Reading Progress Bar ---
  const progressEl = document.getElementById('reading-progress');
  if (progressEl) {
    window.addEventListener('scroll', () => {
      const max = document.documentElement.scrollHeight - window.innerHeight;
      const progress = max > 0 ? (window.scrollY / max) : 0;
      progressEl.style.transform = `scaleX(${progress})`;
    }, { passive: true });
  }

  // --- 12. User Profile Dropdown Menu ---
  window.toggleSiteUserMenu = function(e) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    const btn = e.currentTarget;
    const dropdown = btn ? btn.closest('.site-user-dropdown') : null;
    const menu = dropdown ? dropdown.querySelector('.site-user-menu-box') : null;
    if (!menu) return;
    const isShown = menu.style.display === 'block';
    document.querySelectorAll('.site-user-menu-box').forEach(m => m.style.display = 'none');
    menu.style.display = isShown ? 'none' : 'block';
  };

  // --- 13. Smart Floating Scroll Navigation (Top & Bottom) ---
  const btnScrollTop = document.getElementById('btnScrollTop');
  const btnScrollBottom = document.getElementById('btnScrollBottom');

  function updateScrollButtons() {
    const scrollY = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    const scrollHeight = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
    const clientHeight = window.innerHeight || document.documentElement.clientHeight;
    const maxScroll = scrollHeight - clientHeight;

    if (btnScrollTop) {
      if (scrollY > 150) {
        btnScrollTop.classList.add('visible');
      } else {
        btnScrollTop.classList.remove('visible');
      }
    }

    if (btnScrollBottom) {
      if (maxScroll > 150 && (maxScroll - scrollY) > 80) {
        btnScrollBottom.classList.add('visible');
      } else {
        btnScrollBottom.classList.remove('visible');
      }
    }
  }

  if (btnScrollTop) {
    btnScrollTop.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  if (btnScrollBottom) {
    btnScrollBottom.addEventListener('click', (e) => {
      e.preventDefault();
      const scrollHeight = Math.max(document.documentElement.scrollHeight, document.body.scrollHeight);
      window.scrollTo({ top: scrollHeight, behavior: 'smooth' });
    });
  }

  window.addEventListener('scroll', updateScrollButtons, { passive: true });
  window.addEventListener('resize', updateScrollButtons, { passive: true });
  window.addEventListener('load', updateScrollButtons, { passive: true });
  document.addEventListener('DOMContentLoaded', updateScrollButtons);
  setTimeout(updateScrollButtons, 150);
  setTimeout(updateScrollButtons, 600);

  // --- 8. Interactive Tech Poll Handler (AJAX & Real Database Persistence) ---
  const pollSection = document.getElementById('interactive-tech-poll');
  if (pollSection) {
    const pollId = pollSection.getAttribute('data-poll-id');
    const pollKey = 'voted_poll_' + pollId;
    const opts = pollSection.querySelectorAll('.poll-opt');
    const statusMsg = document.getElementById('poll-status-msg');
    const totalCountEl = document.getElementById('poll-total-count');

    let isSubmitting = false;
    let localVotedId = localStorage.getItem(pollKey) || (pollSection.getAttribute('data-has-voted') === '1' ? pollSection.getAttribute('data-voted-id') : null);

    if (localVotedId) {
      if (statusMsg) statusMsg.innerHTML = '<span style="color:var(--accent-primary)">✔️ تم تسجيل صوتك بنجاح</span>';
      opts.forEach(opt => {
        if (opt.getAttribute('data-opt-id') === String(localVotedId)) {
          opt.classList.add('voted');
        }
      });
    }

    opts.forEach(opt => {
      opt.addEventListener('click', function () {
        if (isSubmitting) return;

        const optId = this.getAttribute('data-opt-id');
        if (localVotedId) {
          if (window.showToast) showToast('لقد قمت بالتصويت في هذا الاستطلاع مسبقاً!', '📊');
          return;
        }

        isSubmitting = true;
        this.style.opacity = '0.7';

        const formData = new FormData();
        formData.append('poll_id', pollId);
        formData.append('option_id', optId);

        const baseUrl = window.APP_BASE_URL || '/';
        fetch(baseUrl.replace(/\/$/, '') + '/poll/vote', {
          method: 'POST',
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            isSubmitting = false;
            opt.style.opacity = '1';

            if (data.success) {
              localVotedId = optId;
              localStorage.setItem(pollKey, optId);

              if (statusMsg) statusMsg.innerHTML = '<span style="color:var(--accent-primary)">✔️ تم تسجيل صوتك بنجاح</span>';
              if (totalCountEl) totalCountEl.textContent = `إجمالي الأصوات: ${Number(data.total_votes).toLocaleString()} مشاركاً`;

              // Update all bars and percentages live
              if (Array.isArray(data.options)) {
                data.options.forEach(o => {
                  const el = pollSection.querySelector(`.poll-opt[data-opt-id="${o.id}"]`);
                  if (el) {
                    const bar = el.querySelector('.poll-bar');
                    const percentEl = el.querySelector('.poll-percent');
                    if (bar) bar.style.width = `${o.percent}%`;
                    if (percentEl) percentEl.textContent = `${o.percent}%`;
                    if (String(o.id) === String(optId)) {
                      el.classList.add('voted');
                    } else {
                      el.classList.remove('voted');
                    }
                  }
                });
              }

              if (window.showToast) {
                showToast('شكراً لمشاركتك! تم تسجيل صوتك بنجاح.', '🎉');
              }
            } else {
              if (window.showToast) {
                showToast(data.error || 'حدث خطأ أثناء تسجيل الصوت.', '⚠️');
              }
            }
          })
          .catch(err => {
            isSubmitting = false;
            opt.style.opacity = '1';
            console.error('Poll vote error:', err);
          });
      });
    });
  }

  // --- 9. User Menu Dropdown Handler (Touch & Click Support) ---
  const userDropdownWrap = document.querySelector('.user-dropdown-wrap');
  const userAvatarBtn = document.querySelector('.user-avatar-btn');
  if (userDropdownWrap && userAvatarBtn) {
    userAvatarBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const willOpen = !userDropdownWrap.classList.contains('is-open');
      userDropdownWrap.classList.toggle('is-open', willOpen);
      userAvatarBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    document.addEventListener('click', (e) => {
      if (!userDropdownWrap.contains(e.target)) {
        userDropdownWrap.classList.remove('is-open');
        userAvatarBtn.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        userDropdownWrap.classList.remove('is-open');
        userAvatarBtn.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // --- 10. Progressive Web App (PWA) Engine ---
  let deferredPrompt = null;
  const PWA_DISMISS_KEY = 'tech_pwa_prompt_dismissed';

  // A. Auto-Register Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      const swUrl = (window.APP_BASE_URL || '/').replace(/\/$/, '') + '/sw.js';
      navigator.serviceWorker.register(swUrl, { scope: '/' })
        .then((reg) => {
          reg.onupdatefound = () => {
            const installingWorker = reg.installing;
            if (installingWorker) {
              installingWorker.onstatechange = () => {
                if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                  if (window.showToast) {
                    showToast('تم تحديث بيانات التطبيق، أعد التحميل للاطلاع على أحدث محتوى.', '🔄');
                  }
                }
              };
            }
          };
        })
        .catch((err) => {
          console.warn('[PWA] Service Worker registration failed:', err);
        });
    });
  }

  // B. Handle beforeinstallprompt Event & Show Smart Install Banner
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;

    const dismissedTime = localStorage.getItem(PWA_DISMISS_KEY);
    if (dismissedTime && (Date.now() - parseInt(dismissedTime, 10)) < (7 * 24 * 60 * 60 * 1000)) {
      return;
    }

    setTimeout(() => {
      showPwaInstallBanner();
    }, 3000);
  });

  // B2. iOS Safari fallback: beforeinstallprompt is not supported on iOS at all,
  // so show a native-style guide banner pointing to "Add to Home Screen".
  const isIOSDevice = /iphone|ipad|ipod/i.test(navigator.userAgent) ||
                      (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  const isStandaloneApp = window.matchMedia('(display-mode: standalone)').matches ||
                          window.navigator.standalone === true;

  function showIosInstallGuide() {
    if (document.getElementById('pwa-install-banner')) return;
    const dismissedTime = localStorage.getItem(PWA_DISMISS_KEY);
    if (dismissedTime && (Date.now() - parseInt(dismissedTime, 10)) < (7 * 24 * 60 * 60 * 1000)) return;

    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.className = 'pwa-install-banner';
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-label', 'إضافة التطبيق إلى الشاشة الرئيسية');

    const iconUrl = (window.APP_BASE_URL || '/').replace(/\/$/, '') + '/assets/images/icons/icon-96x96.png';

    banner.innerHTML = `
      <div class="pwa-banner-header">
        <img src="${iconUrl}" alt="أيقونة التطبيق" class="pwa-banner-icon" />
        <div class="pwa-banner-info">
          <div class="pwa-banner-title">
            <span>أضِف التطبيق للشاشة الرئيسية</span>
            <span>📱</span>
          </div>
          <p class="pwa-banner-desc">اضغط زر المشاركة في المتصفح، ثم اختر «إضافة إلى الشاشة الرئيسية» للاستخدام كتطبيق.</p>
        </div>
      </div>
      <div class="pwa-banner-actions">
        <button type="button" class="pwa-btn-dismiss" id="pwa-btn-dismiss-action">حسناً</button>
      </div>
    `;

    document.body.appendChild(banner);

    const dismissBtn = document.getElementById('pwa-btn-dismiss-action');
    if (dismissBtn) {
      dismissBtn.addEventListener('click', () => {
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(120%)';
        setTimeout(() => banner.remove(), 300);
        localStorage.setItem(PWA_DISMISS_KEY, Date.now().toString());
      });
    }
  }

  if (isIOSDevice && !isStandaloneApp && 'serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      setTimeout(() => { showIosInstallGuide(); }, 2500);
    });
  }

  function showPwaInstallBanner() {
    if (!deferredPrompt || document.getElementById('pwa-install-banner')) return;

    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.className = 'pwa-install-banner';
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-label', 'تثبيت تطبيق المنصة');

    const iconUrl = (window.APP_BASE_URL || '/').replace(/\/$/, '') + '/assets/images/icons/icon-96x96.png';

    banner.innerHTML = `
      <div class="pwa-banner-header">
        <img src="${iconUrl}" alt="أيقونة التطبيق" class="pwa-banner-icon" />
        <div class="pwa-banner-info">
          <div class="pwa-banner-title">
            <span>تثبيت تطبيق الأخبار</span>
            <span>📱</span>
          </div>
          <p class="pwa-banner-desc">ثبّت التطبيق على جهازك لتصفح فوري، إشعارات عاجلة، وقراءة دون إنترنت.</p>
        </div>
      </div>
      <div class="pwa-banner-actions">
        <button type="button" class="pwa-btn-install" id="pwa-btn-install-action">
          <span>⚡</span>
          <span>تثبيت الآن</span>
        </button>
        <button type="button" class="pwa-btn-dismiss" id="pwa-btn-dismiss-action">لاحقاً</button>
      </div>
    `;

    document.body.appendChild(banner);

    const installBtn = document.getElementById('pwa-btn-install-action');
    const dismissBtn = document.getElementById('pwa-btn-dismiss-action');

    if (installBtn) {
      installBtn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        banner.remove();
        deferredPrompt.prompt();
        const choiceResult = await deferredPrompt.userChoice;
        if (choiceResult && choiceResult.outcome === 'accepted') {
          if (window.showToast) showToast('جاري تثبيت التطبيق...', '🎉');
        }
        deferredPrompt = null;
      });
    }

    if (dismissBtn) {
      dismissBtn.addEventListener('click', () => {
        banner.style.opacity = '0';
        banner.style.transform = 'translateY(120%)';
        setTimeout(() => banner.remove(), 300);
        localStorage.setItem(PWA_DISMISS_KEY, Date.now().toString());
      });
    }
  }

  // C. Handle App Installed Event
  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    const banner = document.getElementById('pwa-install-banner');
    if (banner) banner.remove();
    if (window.showToast) {
      showToast('🎉 تهانينا! تم تثبيت تطبيق عصب التقنية بنجاح.', '🚀');
    }
  });

  // --- 11. Article Reader Comfort & Typography Controls ---
  const readerContent = document.querySelector('.reader-article-content');
  const btnFontInc = document.getElementById('btn-font-inc');
  const btnFontDec = document.getElementById('btn-font-dec');
  const btnFontReset = document.getElementById('btn-font-reset');
  const btnZenMode = document.getElementById('btn-zen-mode');

  if (readerContent) {
    const FONT_STORAGE_KEY = 'reader_font_size_scale';
    let currentScale = parseFloat(localStorage.getItem(FONT_STORAGE_KEY)) || 1.0;

    function applyFontSize(scale) {
      currentScale = Math.min(1.4, Math.max(0.85, scale));
      readerContent.style.fontSize = (1.24 * currentScale) + 'rem';
      readerContent.style.lineHeight = (2.3 * Math.min(1.15, Math.max(0.95, currentScale))).toString();
      localStorage.setItem(FONT_STORAGE_KEY, currentScale.toString());
    }

    if (currentScale !== 1.0) {
      applyFontSize(currentScale);
    }

    if (btnFontInc) {
      btnFontInc.addEventListener('click', () => {
        applyFontSize(currentScale + 0.08);
        if (window.showToast) showToast('تم تكبير حجم خط القراءة', '🔠');
      });
    }

    if (btnFontDec) {
      btnFontDec.addEventListener('click', () => {
        applyFontSize(currentScale - 0.08);
        if (window.showToast) showToast('تم تصغير حجم خط القراءة', '🔠');
      });
    }

    if (btnFontReset) {
      btnFontReset.addEventListener('click', () => {
        applyFontSize(1.0);
        if (window.showToast) showToast('تمت استعادة حجم الخط القياسي', '✨');
      });
    }

    if (btnZenMode) {
      btnZenMode.addEventListener('click', () => {
        const isZen = document.body.classList.toggle('zen-reading-mode');
        btnZenMode.classList.toggle('active', isZen);
        if (window.showToast) {
          showToast(isZen ? 'تم تفعيل وضع القراءة الهادئ (Zen Mode) 📖' : 'تم العودة للمظهر الكامل للموقع', isZen ? '🧘' : '📰');
        }
      });
    }
  }

  // --- 12. Global Password Visibility Toggle ---
  function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId) || (btn ? btn.closest('.password-input-wrap')?.querySelector('input') : null);
    if (!input) return;
    const isPassword = input.getAttribute('type') === 'password';
    input.setAttribute('type', isPassword ? 'text' : 'password');
    const icon = btn.querySelector('i') || btn.querySelector('svg');
    if (icon) {
      if (icon.tagName.toLowerCase() === 'i') {
        icon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
      } else if (icon.tagName.toLowerCase() === 'svg') {
        if (isPassword) {
          icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
          icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
      }
    }
  }
  window.togglePasswordVisibility = togglePasswordVisibility;

  // ====================================================================
  // 13. Reader Read/Unread Tracker + Relative Time Engine
  //     (Auto-only: articles are marked read on click/open. No user toggle.)
  // ====================================================================
  const readerCfg = window.TNP_READER || {};
  const readerStorageKey = 'tnp_read_articles';
  const readerState = (function () {
    let ids = new Set();
    try {
      const raw = localStorage.getItem(readerStorageKey);
      if (raw) {
        const arr = JSON.parse(raw);
        if (Array.isArray(arr)) ids = new Set(arr.map(Number));
      }
    } catch (e) { /* localStorage unavailable */ }
    return {
      has(id) { return id != null && ids.has(Number(id)); },
      set(id, read) {
        id = Number(id);
        if (!id || !read) return;
        ids.add(id);
        try { localStorage.setItem(readerStorageKey, JSON.stringify(Array.from(ids))); } catch (e) {}
        applyReadState();
      }
    };
  })();

  // --- Relative time formatters ---
  function readerTimeAgo(ts) {
    const diff = Math.max(0, Math.floor(Date.now() / 1000) - ts);
    const units = [
      [31536000, ['سنة', 'سنتين', 'سنوات']],
      [2592000, ['شهر', 'شهرين', 'أشهر']],
      [604800, ['أسبوع', 'أسبوعين', 'أسابيع']],
      [86400, ['يوم', 'يومين', 'أيام']],
      [3600, ['ساعة', 'ساعتين', 'ساعات']],
      [60, ['دقيقة', 'دقيقتين', 'دقائق']]
    ];
    for (const [secs, labels] of units) {
      if (diff >= secs) {
        const n = Math.floor(diff / secs);
        if (n === 1) return 'قبل ' + labels[0];
        if (n === 2) return 'قبل ' + labels[1];
        return 'قبل ' + n + ' ' + labels[2];
      }
    }
    return 'الآن';
  }

  function readerAbsTime(ts) {
    const d = new Date(ts * 1000);
    const pad = (x) => String(x).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
  }

  // Live-update any rendered <time> element with relative text
  function readerRefreshTimes() {
    document.querySelectorAll('.news-time-ago-static, .news-time-holder time.news-time-ago').forEach((t) => {
      const iso = t.getAttribute('datetime') || t.dataset.ts;
      const ts = iso ? Math.floor(new Date(iso).getTime() / 1000) : 0;
      if (!ts || ts > Date.now() / 1000 + 3600) return;
      if (t.classList.contains('news-time-ago-static') && readerCfg.timeAgoMode === 'absolute') return;
      t.textContent = readerTimeAgo(ts);
    });
  }

  // --- Card rendering ---
  function renderCardTime(card) {
    if (!readerCfg.timeAgoEnabled) return;
    const raw = card.getAttribute('data-published-at');
    if (!raw) return;
    const ts = parseInt(raw, 10) || 0;
    if (!ts) return;

    let holder = card.querySelector('.news-time-holder');
    if (!holder) {
      holder = document.createElement('span');
      holder.className = 'news-time-holder';
      if (card.classList.contains('related-story-item')) {
        // Place the chip inline inside the meta line of the info column
        const info = card.querySelector('.related-story-info');
        (info || card).appendChild(holder);
      } else {
        const footer = card.querySelector('.card-footer');
        const body = card.querySelector('.card-body');
        const host = footer || body || card;
        if (footer || body) host.insertBefore(holder, host.firstChild);
        else host.appendChild(holder);
      }
    }
    holder.innerHTML = '';
    const timeEl = document.createElement('time');
    timeEl.className = 'news-time-ago';
    timeEl.setAttribute('datetime', new Date(ts * 1000).toISOString());
    timeEl.dataset.ts = String(ts);
    if (readerCfg.timeAgoMode === 'both') {
      timeEl.title = readerAbsTime(ts);
      timeEl.textContent = readerTimeAgo(ts);
    } else if (readerCfg.timeAgoMode === 'absolute') {
      timeEl.textContent = readerAbsTime(ts);
    } else {
      timeEl.textContent = readerTimeAgo(ts);
    }
    holder.appendChild(timeEl);
  }

  function ensureUnreadDot(card) {
    if (card.querySelector('.tnp-unread-dot')) return;
    const link = card.querySelector('h2 a, h3 a, h4 a, h5 a');
    if (!link) return;
    const dot = document.createElement('span');
    dot.className = 'tnp-unread-dot';
    dot.setAttribute('aria-hidden', 'true');
    link.insertBefore(dot, link.firstChild);
  }

  function ensureNewBadge(card) {
    if (card.querySelector('.news-new-badge')) return;
    const raw = card.getAttribute('data-published-at');
    const hours = parseInt(card.getAttribute('data-new-hours') || readerCfg.newHours || 24, 10) || 24;
    const ts = raw ? parseInt(raw, 10) : 0;
    if (!ts) return;
    if ((Date.now() / 1000) - ts > hours * 3600) return;
    const host = card.querySelector('.card-img-wrap, .related-story-thumb-wrap') || card;
    const badge = document.createElement('span');
    badge.className = 'news-new-badge';
    badge.textContent = 'جديد';
    host.insertBefore(badge, host.firstChild);
  }

  // Apply read/unread visual state (card class + dot + badge visibility)
  function applyReadStateToCard(card) {
    const raw = card.getAttribute('data-article-id');
    if (!raw) return;
    const read = readerState.has(raw);
    card.classList.toggle('article-is-read', read);
    card.classList.toggle('article-is-unread', !read);
    // Hide the "new" badge once the article has been read
    const badge = card.querySelector('.news-new-badge');
    if (badge) badge.classList.toggle('is-read', read);
  }

  function applyReadState() {
    document.querySelectorAll('[data-article-id]').forEach(applyReadStateToCard);
  }

  function processReaderTargets() {
    document.querySelectorAll('[data-article-id]').forEach((el) => {
      if (el.getAttribute('data-role') === 'article-page') return;
      if (el.classList.contains('tny-processed')) { applyReadStateToCard(el); return; }
      el.classList.add('tny-processed');
      if (readerCfg.unreadEnabled !== false) {
        ensureNewBadge(el);
        ensureUnreadDot(el);
      }
      renderCardTime(el);
      applyReadStateToCard(el);
    });
  }

  // Article page: auto-mark read on open (fully automatic, no user toggle)
  (function initArticlePage() {
    const card = document.querySelector('[data-role="article-page"]');
    if (!card) return;
    const raw = card.getAttribute('data-article-id');
    if (!raw) return;
    if (readerCfg.unreadEnabled !== false && readerCfg.autoMark) {
      readerState.set(raw, true);
    }
  })();

  // Delegated: clicking any article link auto-marks it as read
  if (readerCfg.unreadEnabled !== false && readerCfg.autoMark) {
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a[href*="/article/"]');
      if (!link) return;
      const card = link.closest('[data-article-id]');
      const raw = card ? card.getAttribute('data-article-id') : null;
      if (raw) readerState.set(raw, true);
    });
  }

  // Kick off + periodic refresh
  document.addEventListener('DOMContentLoaded', () => {
    processReaderTargets();
    readerRefreshTimes();
  });
  setTimeout(() => { processReaderTargets(); readerRefreshTimes(); }, 50);
  setInterval(() => { processReaderTargets(); readerRefreshTimes(); }, 30000);

  // ====================================================================
  // 14. AI Assistant Chat Widget («مرشد عصب التقنية»)
  // ====================================================================
  (function initAiAssistant() {
    const wrapper = document.getElementById('aiAssistant');
    if (!wrapper) return;

    const panel = document.getElementById('aiPanel');
    const launcher = document.getElementById('aiLauncher');
    const closeBtn = document.getElementById('aiPanelClose');
    const input = document.getElementById('aiInput');
    const sendBtn = document.getElementById('aiSend');
    const messages = document.getElementById('aiMessages');
    const suggestions = document.getElementById('aiSuggestions');

    const limit = parseInt(wrapper.getAttribute('data-limit') || '0', 10);
    const bypass = wrapper.getAttribute('data-bypass') === '1';
    const csrf = window.APP_CSRF || '';
    const base = (window.APP_BASE_URL || '/').replace(/\/$/, '');
    const cf = window.AI_ASSISTANT || {};
    const loggedIn = cf.loggedIn === 1;
    const sourcesOn = cf.sources === 1;
    const pageSlug = (window.location.pathname.match(/\/article\/([A-Za-z0-9\-_]+)/) || [])[1] || '';
    const storageKey = 'ai_assistant_open';
    const historyKey = 'ai_assistant_history';
    let history = [];
    let sending = false;

    try {
      const raw = localStorage.getItem(historyKey);
      const arr = raw ? JSON.parse(raw) : [];
      if (Array.isArray(arr)) history = arr.slice(-8);
    } catch (e) { history = []; }

    function persistHistory() {
      try { localStorage.setItem(historyKey, JSON.stringify(history.slice(-8))); } catch (e) {}
    }

    function quotaLabel(q) {
      const daily = (q && typeof q.daily === 'number') ? q.daily : limit;
      const used = (q && typeof q.used === 'number') ? q.used : parseInt(wrapper.getAttribute('data-used') || '0', 10);
      const boost = (q && typeof q.boost === 'number') ? q.boost : parseInt(wrapper.getAttribute('data-boost') || '0', 10);
      const dailyRemaining = daily > 0 ? Math.max(0, daily - used) : null;
      let label;
      if (dailyRemaining === null) {
        label = boost > 0 ? (boost + ' رسالة إضافية متاحة') : 'بلا حد يومي';
      } else {
        label = dailyRemaining + '/' + daily + ' متبقية اليوم' + (boost > 0 ? ' (+' + boost + ' إضافية)' : '');
      }
      return label;
    }

    function updateQuota(q) {
      const el = wrapper.querySelector('[data-counter]');
      if (!el) return;
      if (bypass) { el.style.display = 'none'; return; }
      const daily = (q && typeof q.daily === 'number') ? q.daily : limit;
      const used = (q && typeof q.used === 'number') ? q.used : parseInt(wrapper.getAttribute('data-used') || '0', 10);
      const boost = (q && typeof q.boost === 'number') ? q.boost : parseInt(wrapper.getAttribute('data-boost') || '0', 10);
      el.textContent = quotaLabel({ daily, used, boost });
      el.style.color = '';
      el.removeAttribute('title');
      if (daily > 0 && used >= daily && boost <= 0) {
        el.style.color = '#f87171';
        el.title = 'انتهت أسئلتك؛ عُد غداً أو اطلب زيادة من الإدارة';
      }
    }

    function scrollToBottom() {
      if (messages) messages.scrollTop = messages.scrollHeight;
    }

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    // Clean up markup the model sometimes smuggles out (e.g. <a href=...> or
    // leftover `" target="_blank" rel="noopener noreferrer">` fragments) into
    // plain markdown-friendly text before rendering. Everything is escaped
    // later, so this is purely a readability pass.
    function normalizeModelMarkup(text) {
      let t = String(text || '');
      t = t.replace(/<a\s+[^>]*?href\s*=\s*["']?([^"'\s>]+)["']?[^>]*>([\s\S]*?)<\/a>/gi, (m, u, l) => {
        return '[' + (l || '').trim() + '](' + u + ')';
      });
      t = t.replace(/<\/?a\b[^>]*>/gi, '');
      t = t.replace(/\s+(?:target|rel|class|style|aria-[a-z0-9-]+|data-[a-z0-9-]+)\s*=\s*"[^"]*"/gi, '');
      t = t.replace(/(https?:\/\/[^\s<>"']+)"\s*>/gi, '$1');
      return t;
    }

    // Inline markdown transforms (receive ALREADY-escaped text).
    function inlineMarkdown(t) {
      t = t.replace(/`([^`\n]+)`/g, '<code>$1</code>');
      t = t.replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>');
      t = t.replace(/__([^_\n]+)__/g, '<strong>$1</strong>');
      t = t.replace(/(^|[^*\w])\*([^*\n]+)\*(?!\*)(?=[^*\w]|$)/g, '$1<em>$2</em>');
      t = t.replace(/~~([^~\n]+)~~/g, '<del>$1</del>');
      t = t.replace(/\[([^\]]+)\]\(\/(?:article|tutorial|category)\/[^)\s]+\)/g, function (m, label) {
        const href = m.match(/\((\/[^)\s]+)\)/)[1];
        return '<a href="' + base + href + '">' + label + '</a>';
      });
      t = t.replace(/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');
      t = t.replace(/(?:(?:https?|ftp):\/\/[^\s<>"']+)/g, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
      return t;
    }

    function renderCodeBlock(raw) {
      const pre = document.createElement('pre');
      pre.className = 'ai-code';
      const code = document.createElement('code');
      code.textContent = raw.replace(/\n$/, '');
      pre.appendChild(code);
      const cp = document.createElement('button');
      cp.type = 'button';
      cp.className = 'ai-code-copy';
      cp.textContent = 'نسخ الكود';
      cp.addEventListener('click', () => copyText(raw, cp));
      pre.appendChild(cp);
      return pre;
    }

    // Block-level markdown -> safe DOM. Returns a DocumentFragment of nodes.
    function renderMarkdown(src) {
      const text = normalizeModelMarkup(src);
      const frag = document.createDocumentFragment();
      const lines = text.split(/\r?\n/);
      let html = '';
      let inCode = false;
      let codeBuf = [];

      const closeList = (tag) => {
        if (html === '') return;
        html += '</' + tag + '>';
      };

      function flushParagraph() {
        if (html.trim() !== '') {
          html = html.replace(/<br>\s*$/, '');
          const div = document.createElement('p');
          div.className = 'ai-para';
          div.innerHTML = inlineMarkdown(escapeHtml(html));
          frag.appendChild(div);
          html = '';
        }
      }

      for (let i = 0; i < lines.length; i++) {
        const line = lines[i];

        if (/^\s*```/.test(line)) {
          if (inCode) {
            const pre = renderCodeBlock(codeBuf.join('\n'));
            frag.appendChild(pre);
            codeBuf = [];
            inCode = false;
          } else {
            flushParagraph();
            inCode = true;
          }
          continue;
        }
        if (inCode) {
          codeBuf.push(line);
          continue;
        }

        const h = line.match(/^(#{1,6})\s+(.+)$/);
        if (h) {
          flushParagraph();
          const level = Math.min(6, h[1].length);
          const el = document.createElement('h' + level);
          el.className = 'ai-h';
          el.innerHTML = inlineMarkdown(escapeHtml(h[2]));
          frag.appendChild(el);
          continue;
        }
        if (/^\s*(-{3,}|\*{3,}|_{3,})\s*$/.test(line)) {
          flushParagraph();
          frag.appendChild(document.createElement('hr'));
          continue;
        }
        if (/^>\s?/.test(line)) {
          flushParagraph();
          let q = [];
          while (i < lines.length && /^>\s?/.test(lines[i])) {
            q.push(lines[i].replace(/^>\s?/, ''));
            i++;
          }
          i--;
          const bq = document.createElement('blockquote');
          bq.className = 'ai-quote';
          bq.innerHTML = inlineMarkdown(escapeHtml(q.join('<br>')));
          frag.appendChild(bq);
          continue;
        }
        if (/^\s*[-*+]\s+/.test(line)) {
          flushParagraph();
          let items = [];
          while (i < lines.length && /^\s*[-*+]\s+/.test(lines[i])) {
            items.push(lines[i].replace(/^\s*[-*+]\s+/, ''));
            i++;
          }
          i--;
          const ul = document.createElement('ul');
          ul.className = 'ai-ul';
          items.forEach((it) => {
            const li = document.createElement('li');
            li.innerHTML = inlineMarkdown(escapeHtml(it));
            ul.appendChild(li);
          });
          frag.appendChild(ul);
          continue;
        }
        if (/^\s*\d+[\.\)]\s+/.test(line)) {
          flushParagraph();
          let items = [];
          while (i < lines.length && /^\s*\d+[\.\)]\s+/.test(lines[i])) {
            items.push(lines[i].replace(/^\s*\d+[\.\)]\s+/, ''));
            i++;
          }
          i--;
          const ol = document.createElement('ol');
          ol.className = 'ai-ol';
          items.forEach((it) => {
            const li = document.createElement('li');
            li.innerHTML = inlineMarkdown(escapeHtml(it));
            ol.appendChild(li);
          });
          frag.appendChild(ol);
          continue;
        }

        if (line.trim() === '') {
          flushParagraph();
          continue;
        }
        html += (html === '' ? '' : '<br>') + line;
      }
      flushParagraph();
      if (inCode && codeBuf.length) {
        frag.appendChild(renderCodeBlock(codeBuf.join('\n')));
      }
      return frag;
    }

    // The model sometimes stubbornly appends its own «المصادر:» listing even
    // though we render structured sources; drop that redundant trailing block
    // (relative/absolute/broken forms) to avoid the garbled duplicated list.
    function stripRedundantSources(text, hasStructured) {
      if (!hasStructured) return text;
      const rawLines = String(text).split(/\r?\n/);
      let cut = -1;
      for (let i = 0; i < rawLines.length; i++) {
        if (/^\s*(المصادر|المراجع|Sources?|References)\s*:+/i.test(rawLines[i])) cut = i;
      }
      if (cut > -1) {
        text = rawLines.slice(0, cut).join('\n');
      }
      const lines = text.split(/\r?\n/);
      let end = lines.length;
      while (end > 0) {
        const last = lines[end - 1].trim();
        if (!last) { end--; continue; }
        if (/^(?:[-*]\s*)?(https?:\/\/|\[[^\]]+\]\([^)\s]+\)\s*$)/i.test(last)) { end--; continue; }
        break;
      }
      return lines.slice(0, end).join('\n').replace(/\n{3,}/g, '\n\n').trim();
    }

    function timeLabel() {
      try { return new Date().toLocaleTimeString('ar', { hour: '2-digit', minute: '2-digit' }); } catch (e) { return ''; }
    }

    function copyText(text, btn) {
      const done = () => {
        if (btn) { const old = btn.textContent; btn.textContent = 'تم النسخ ✓'; setTimeout(() => { btn.textContent = old; }, 1500); }
        if (window.showToast) showToast('تم نسخ النص.');
      };
      const fallback = () => {
        try {
          const ta = document.createElement('textarea');
          ta.value = text;
          ta.style.position = 'fixed';
          ta.style.opacity = '0';
          document.body.appendChild(ta);
          ta.select();
          if (document.execCommand('copy')) done(); else if (window.showToast) showToast('تعذر النسخ.');
          ta.remove();
        } catch (e) { if (window.showToast) showToast('تعذر النسخ.'); }
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(done).catch(fallback);
      } else {
        fallback();
      }
    }

    const reactionsKey = 'ai_assistant_reactions';
    const ridCounterKey = 'ai_assistant_rid_counter';
    let _ridCounter = 0;
    try { _ridCounter = parseInt(localStorage.getItem(ridCounterKey) || '0', 10) || 0; } catch (e) {}
    function bumpRid() { _ridCounter++; try { localStorage.setItem(ridCounterKey, String(_ridCounter)); } catch (e) {} return 'm' + _ridCounter; }
    function loadReactions() { try { return JSON.parse(localStorage.getItem(reactionsKey) || '{}') || {}; } catch (e) { return {}; } }
    function storeReaction(rid, v) {
      const m = loadReactions();
      if (v === '') delete m[rid]; else m[rid] = v;
      try { localStorage.setItem(reactionsKey, JSON.stringify(m)); } catch (e) {}
    }

    function sendReaction(rid, convId, value, groupEl) {
      const state = loadReactions();
      const next = state[rid] === value ? '' : value;
      groupEl.querySelectorAll('[data-reaction]').forEach((b) => {
        const k = b.getAttribute('data-reaction');
        b.classList.remove('active-like', 'active-love', 'active-dislike');
        if (k === next) b.classList.add('active-' + k);
      });
      storeReaction(rid, next);
      if (!convId) return;
      fetch(base + '/ai-assistant/reaction', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ conv_id: convId, value: next })
      })
        .then(res => res.json().catch(() => ({ success: false })))
        .then(data => {
          if (!data || !data.success) {
            if (window.showToast) showToast('تعذر حفظ التقييم.', '⚠️');
            groupEl.querySelectorAll('[data-reaction]').forEach((b) => {
              const k = b.getAttribute('data-reaction');
              b.classList.remove('active-like', 'active-love', 'active-dislike');
              if (k === state[rid]) b.classList.add('active-' + state[rid]);
            });
          }
        })
        .catch(() => { if (window.showToast) showToast('تعذر حفظ التقييم.', '⚠️'); });
    }

    function reactionButtons(rid, convId) {
      const wrap = document.createElement('div');
      wrap.className = 'ai-msg-actions';
      const cfg = [
        ['like', 'like', 'محبب'],
        ['love', 'love', 'رائع'],
        ['dislike', 'dislike', 'غير جيد']
      ];
      const state = loadReactions();
      cfg.forEach(([key, title]) => {
        const b = document.createElement('button');
        b.type = 'button';
        const isActive = state[rid] === key;
        b.className = 'ai-react' + (isActive ? ' active-' + key : '');
        b.setAttribute('data-reaction', key);
        b.title = title;
        b.innerHTML = key === 'like' ? '👍' : (key === 'love' ? '❤️' : '👎');
        b.addEventListener('click', () => sendReaction(rid, convId, key, wrap));
        wrap.appendChild(b);
      });
      return wrap;
    }

    function appendMessage(role, contentHtml, sources, rawText, convId, existingRid) {
      const rid = existingRid || bumpRid();
      const row = document.createElement('div');
      row.className = 'ai-msg ' + (role === 'user' ? 'ai-msg-user' : 'ai-msg-ai');
      const bubble = document.createElement('div');
      bubble.className = 'ai-msg-bubble ' + (role === 'user' ? 'ai-msg-bubble-user' : 'ai-msg-bubble-ai');
      const text = document.createElement('div');
      text.className = 'ai-msg-text';
      if (contentHtml instanceof Node) text.appendChild(contentHtml); else text.innerHTML = contentHtml;
      bubble.appendChild(text);
      if (Array.isArray(sources) && sources.length > 0 && role === 'ai' && sourcesOn) {
        const src = document.createElement('div');
        src.className = 'ai-msg-src';
        src.textContent = 'المصادر: ';
        sources.forEach((s, i) => {
          const a = document.createElement('a');
          a.href = s.url;
          a.textContent = (i + 1) + '. ' + s.title;
          a.style.marginInlineStart = '6px';
          src.appendChild(a);
        });
        bubble.appendChild(src);
      }
      // Actions bar: time + copy (+ like/dislike/love for assistant answers).
      if (rawText || role === 'ai') {
        const meta = document.createElement('div');
        meta.className = 'ai-msg-meta';
        const tm = document.createElement('span');
        tm.className = 'ai-msg-time';
        tm.textContent = timeLabel();
        meta.appendChild(tm);
        const actions = document.createElement('div');
        actions.className = 'ai-msg-actions';
        if (rawText) {
          const cb = document.createElement('button');
          cb.type = 'button';
          cb.className = 'ai-copy';
          cb.title = 'نسخ الرسالة';
          cb.innerHTML = '<i class="bi bi-copy"></i> نسخ';
          cb.addEventListener('click', () => copyText(rawText, cb));
          actions.appendChild(cb);
        }
        if (role === 'ai') {
          actions.appendChild(reactionButtons(rid, convId));
        }
        if (actions.childNodes.length) meta.appendChild(actions);
        bubble.appendChild(meta);
      }
      row.appendChild(bubble);
      if (messages) {
        if (suggestions) suggestions.style.display = 'none';
        messages.appendChild(row);
        scrollToBottom();
      }
      return rid;
    }

    function showTyping() {
      const row = document.createElement('div');
      row.className = 'ai-msg ai-msg-ai';
      row.id = 'aiTypingRow';
      row.innerHTML = '<div class="ai-msg-bubble ai-msg-bubble-ai"><div class="ai-typing" style="display:flex"><span></span><span></span><span></span></div></div>';
      if (messages) {
        messages.appendChild(row);
        scrollToBottom();
      }
    }

    function removeTyping() {
      const row = document.getElementById('aiTypingRow');
      if (row) row.remove();
    }

    function setBusy(state) {
      sending = state;
      if (sendBtn) sendBtn.disabled = state;
      if (input) input.disabled = state;
    }

    function disableQuota() {
      const el = wrapper.querySelector('[data-counter]');
      const daily = parseInt(wrapper.getAttribute('data-daily') || limit, 10);
      if (el) {
        el.textContent = quotaLabel({ daily, used: daily, boost: 0 });
        el.style.color = '#f87171';
        el.title = 'انتهت أسئلتك لهذا اليوم؛ عُد غداً';
      }
      suggestions ? suggestions.style.display = 'none' : null;
    }

    function sendMessage(question) {
      const text = (question != null ? String(question) : (input ? input.value : '')).trim();
      if (sending) return;
      if (text.length < 2 || text.length > 500) {
        if (window.showToast) showToast('الرجاء كتابة سؤال بين 2 و500 حرف.', '⚠️');
        return;
      }

      const userRid = appendMessage('user', escapeHtml(text), null);

      // Guests see the launcher, but the assistant is members-only: answer
      // instantly with a notice instead of burning a provider call.
      if (!loggedIn) {
        const loginUrl = (cf.loginUrl || base + '/login');
        const registerUrl = (cf.registerUrl || base + '/register');
        appendMessage('ai',
          escapeHtml('هذه الميزة متاحة للأعضاء المسجلين فقط. ') +
          '<a href="' + loginUrl + '" class="ai-msg-login">تسجيل الدخول</a>' +
          ((registerUrl ? ' أو ' + '<a href="' + registerUrl + '" class="ai-msg-login">إنشاء حساب</a>' : '')), null);
        return;
      }

      history.push({ role: 'user', content: text, rid: userRid });
      persistHistory();

      if (input) input.value = '';
      setBusy(true);
      showTyping();

      fetch(base + '/ai-assistant/ask', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-Token': csrf,
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ question: text, history: history.slice(-8), page: pageSlug })
      })
        .then(res => res.json().catch(() => ({ success: false, error: 'استجابة غير صالحة من الخادم.' })))
        .then(data => {
          removeTyping();
          setBusy(false);

          if (data && data.success) {
            const sources = data.sources || [];
            const clean = (sourcesOn && sources.length > 0)
              ? stripRedundantSources(data.answer || '', true)
              : (data.answer || '');
            const aiRid = appendMessage('ai', renderMarkdown(clean), sources, clean, data.convId || 0);
            history.push({ role: 'assistant', content: clean, convId: data.convId || 0, rid: aiRid });
            persistHistory();
            updateQuota(data.quota || { used: data.used, daily: data.limit, boost: data.boost || 0 });
          } else if (data && data.auth) {
            const loginUrl = (data.loginUrl || cf.loginUrl || base + '/login');
            const registerUrl = (data.registerUrl || cf.registerUrl || base + '/register');
            appendMessage('ai',
              escapeHtml('هذه الميزة متاحة للأعضاء المسجلين فقط. ') +
              '<a href="' + loginUrl + '" class="ai-msg-login">تسجيل الدخول</a>' +
              ((registerUrl ? ' أو ' + '<a href="' + registerUrl + '" class="ai-msg-login">إنشاء حساب</a>' : '')), null);
          } else {
            const err = (data && data.error) ? data.error : 'تعذر الحصول على إجابة. حاول مجدداً بعد قليل.';
            appendMessage('ai', escapeHtml(err), null);
            if (data && data.quota) {
              const q = data.quota;
              if (q.daily > 0 && q.used >= q.daily && q.boost <= 0) disableQuota();
              updateQuota(q);
            } else if (data && data.limit > 0 && typeof data.used === 'number' && data.used >= data.limit) {
              disableQuota();
              updateQuota({ daily: data.limit, used: data.used, boost: 0 });
            }
          }
        })
        .catch(err => {
          console.error('AI Assistant request failed:', err);
          removeTyping();
          setBusy(false);
          appendMessage('ai', escapeHtml('تعذر الاتصال بالخادم. تأكد من اتصالك بالإنترنت وحاول مجدداً.'), null);
        });
    }

    function setOpen(open) {
      wrapper.classList.toggle('is-open', open);
      if (panel) panel.hidden = !open;
      if (launcher) launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
      try { localStorage.setItem(storageKey, open ? '1' : '0'); } catch (e) {}
      if (open && input) input.focus();
    }

    function buildWelcomeRow() {
      const row = document.createElement('div');
      row.className = 'ai-msg ai-msg-ai';
      const bub = document.createElement('div');
      bub.className = 'ai-msg-bubble ai-msg-bubble-ai';
      const t = document.createElement('div');
      t.className = 'ai-msg-text';
      t.textContent = (cf.welcome || 'مرحباً 👋 اسألني عن آخر أخبار التقنية والمقالات المنشورة في المنصة.');
      bub.appendChild(t);
      row.appendChild(bub);
      return row;
    }

    function resetChat() {
      history = [];
      persistHistory();
      if (!messages) return;
      messages.querySelectorAll('.ai-msg, .ai-suggestions').forEach((n) => n.remove());
      messages.appendChild(buildWelcomeRow());
      if (suggestions) {
        suggestions.style.display = '';
        messages.appendChild(suggestions);
      }
      scrollToBottom();
      if (window.showToast) showToast('بدأت محادثة جديدة.');
      if (input) input.focus();
    }

    if (launcher) launcher.addEventListener('click', () => setOpen(!wrapper.classList.contains('is-open')));
    if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
    const resetBtn = document.getElementById('aiReset');
    if (resetBtn) resetBtn.addEventListener('click', () => resetChat());
    if (input) {
      input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          sendMessage();
        }
      });
      input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(120, input.scrollHeight) + 'px';
      });
    }
    if (sendBtn) sendBtn.addEventListener('click', () => sendMessage());
    if (suggestions) {
      suggestions.addEventListener('click', (e) => {
        const chip = e.target.closest('[data-q]');
        if (!chip) return;
        sendMessage(chip.getAttribute('data-q'));
      });
    }

    // Restore last open state + re-paint the member's previous chat so it
    // survives a page refresh (last 8 exchanges kept in localStorage).
    let wasOpen = '0';
    try { wasOpen = localStorage.getItem(storageKey) || '0'; } catch (e) {}
    history.forEach((m) => {
      if (m.role === 'user') {
        appendMessage('user', escapeHtml(m.content || ''), null, m.content || '', 0, m.rid);
      } else {
        appendMessage('ai', renderMarkdown(m.content || ''), null, m.content || '', m.convId || 0, m.rid);
      }
    });
    if (history.length && suggestions) suggestions.style.display = 'none';
    setOpen(wasOpen === '1' || bypass);
    scrollToBottom();
  })();

})();


