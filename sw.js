/**
 * Tech News Platform - Advanced Progressive Web App Service Worker
 * Version: 2.0.0
 */

const CACHE_NAME = 'tech-news-pwa-v2';
const OFFLINE_URL = '/offline';

// Core assets to pre-cache immediately on install
const PRECACHE_ASSETS = [
  '/',
  '/offline',
  '/manifest.json',
  '/assets/css/site.css',
  '/assets/js/tech-platform.js',
  '/assets/images/icons/icon-192x192.png',
  '/assets/images/icons/icon-512x512.png',
  '/assets/images/icons/favicon-32x32.png'
];

// 1. Install Event: Pre-cache core shell & activate immediately
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(PRECACHE_ASSETS).catch((err) => {
        console.warn('[PWA SW] Pre-cache partial warning:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

// 2. Activate Event: Clean up legacy caches & take control of clients
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            console.log('[PWA SW] Removing old cache:', key);
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// 3. Fetch Event: Smart routing & caching strategies
self.addEventListener('fetch', (event) => {
  const req = event.request;

  // Only handle GET requests
  if (req.method !== 'GET') return;

  const url = new URL(req.url);

  // Do not intercept or cache Admin panel or API mutation routes
  if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api/')) {
    return;
  }

  // Strategy A: HTML Navigation (Network-First with Offline Fallback)
  if (req.mode === 'navigate' || (req.headers.get('accept') && req.headers.get('accept').includes('text/html'))) {
    event.respondWith(
      fetch(req)
        .then((networkRes) => {
          if (networkRes && networkRes.status === 200) {
            const resCopy = networkRes.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(req, resCopy));
          }
          return networkRes;
        })
        .catch(async () => {
          const cachedRes = await caches.match(req);
          if (cachedRes) return cachedRes;
          const offlineFallback = await caches.match(OFFLINE_URL);
          if (offlineFallback) return offlineFallback;
          return new Response('Offline - No connection', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' }
          });
        })
    );
    return;
  }

  // Strategy B: Static Assets (CSS, JS, Images, Fonts) - Stale-While-Revalidate
  if (
    url.pathname.match(/\.(css|js|woff2?|ttf|eot|svg|png|jpe?g|webp|ico|gif)$/i) ||
    url.pathname.startsWith('/assets/') ||
    url.pathname.startsWith('/uploads/')
  ) {
    event.respondWith(
      caches.match(req).then((cachedRes) => {
        const fetchPromise = fetch(req)
          .then((networkRes) => {
            if (networkRes && networkRes.status === 200) {
              const resCopy = networkRes.clone();
              caches.open(CACHE_NAME).then((cache) => cache.put(req, resCopy));
            }
            return networkRes;
          })
          .catch(() => cachedRes);

        return cachedRes || fetchPromise;
      })
    );
    return;
  }

  // Strategy C: Default Network with Cache Fallback
  event.respondWith(
    fetch(req)
      .then((networkRes) => {
        if (networkRes && networkRes.status === 200) {
          const resCopy = networkRes.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(req, resCopy));
        }
        return networkRes;
      })
      .catch(() => caches.match(req))
  );
});

// 4. Push Notification Event
self.addEventListener('push', (event) => {
  let data = {
    title: 'منصة الأخبار التقنية',
    body: 'لديك خبر تقني عاجل جديد!',
    url: '/',
    icon: '/assets/images/icons/icon-192x192.png',
    badge: '/assets/images/icons/favicon-32x32.png'
  };

  try {
    if (event.data) {
      data = Object.assign(data, event.data.json());
    }
  } catch (e) {
    if (event.data) {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: data.icon || '/assets/images/icons/icon-192x192.png',
    badge: data.badge || '/assets/images/icons/favicon-32x32.png',
    dir: 'rtl',
    lang: 'ar',
    vibrate: [100, 50, 100],
    data: {
      url: data.url || '/'
    },
    actions: [
      { action: 'open', title: 'قراءة الخبر 📖' },
      { action: 'close', title: 'إغلاق ✕' }
    ]
  };

  event.waitUntil(self.registration.showNotification(data.title, options));
});

// 5. Notification Click Event
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'close') return;

  const targetUrl = (event.notification.data && event.notification.data.url) || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      for (let client of windowClients) {
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});
