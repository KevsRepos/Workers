const CACHE_NAME = 'workers-cache-v1';
const RUNTIME_CACHE = 'workers-runtime-v1';

// Assets to cache on install (critical for app launch)
const CRITICAL_ASSETS = [
  '/',
  '/index.html',
  '/manifest.json',
  '/img/splash_icon512.png',
  '/images/logo.png'
];

// On install: cache critical assets
self.addEventListener('install', (event) => {
  console.log('Service Worker: installing');
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('Service Worker: caching critical assets');
      return cache.addAll(CRITICAL_ASSETS).catch((err) => {
        console.warn('Some assets failed to cache:', err);
        // Don't fail installation if some assets aren't available yet
        return Promise.resolve();
      });
    })
  );
  self.skipWaiting();
});

// On activate: clean up old caches
self.addEventListener('activate', (event) => {
  console.log('Service Worker: activating');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE) {
            console.log('Service Worker: deleting old cache', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});