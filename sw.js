// Service Worker for Budget Tracker PWA
const CACHE_NAME = 'budget-tracker-v1';
const ASSETS_TO_CACHE = [
  './',
  './index.html',
  './budget-tracker.html',
  './manifest.json',
  './firebase-config.js',
  './icons/icon-192.png',
  './icons/icon-512.png',
  './icons/apple-touch-icon.png',
  './icons/icon.svg',
  'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      // Best-effort caching: cache local files first
      return cache.addAll(ASSETS_TO_CACHE.filter(url => !url.startsWith('http'))).catch(err => {
        console.warn('Pre-caching warning:', err);
      });
    }).then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  // Only handle GET requests
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Skip Firebase API requests from service worker cache to ensure real-time data
  if (url.hostname.includes('googleapis.com') && !url.pathname.includes('/css')) {
    return;
  }
  if (url.hostname.includes('firebaseio.com') || url.hostname.includes('identitytoolkit')) {
    return;
  }

  // Stale-While-Revalidate strategy for app shell
  event.respondWith(
    caches.match(event.request).then((cachedResponse) => {
      const fetchPromise = fetch(event.request).then((networkResponse) => {
        if (networkResponse && networkResponse.status === 200) {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });
        }
        return networkResponse;
      }).catch(() => {
        // Fallback to cached index.html for navigation requests
        if (event.request.mode === 'navigate') {
          return caches.match('./index.html') || caches.match('./budget-tracker.html');
        }
      });

      return cachedResponse || fetchPromise;
    })
  );
});
