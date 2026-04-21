const CACHE_NAME = 'workers-cache-v1';
const RUNTIME_CACHE = 'workers-runtime-v1';

// Assets to cache on install (critical for app launch)
const CRITICAL_ASSETS = [
  '/',
  '/index.html',
  '/manifest.json',
  '/img/splash_icon512.png'
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

// Fetch strategy: Network-first with fallback to cache
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip external origins and API calls to different domain
  if (url.origin !== self.location.origin) {
    return;
  }

  // Network-first strategy: try network, fall back to cache
  event.respondWith(
    fetch(request)
      .then((response) => {
        // Cache successful responses for later use
        if (response.ok) {
          const cache = request.url.includes('/api/') ? RUNTIME_CACHE : CACHE_NAME;
          caches.open(cache).then((c) => {
            c.put(request, response.clone());
          });
        }
        return response;
      })
      .catch(() => {
        // Network failed, try cache
        return caches.match(request).then((cachedResponse) => {
          if (cachedResponse) {
            console.log('Service Worker: serving from cache', request.url);
            return cachedResponse;
          }
          // No cache available, return offline page or error
          console.warn('Service Worker: no cache for', request.url);
          return new Response('Offline - resource not available', {
            status: 503,
            statusText: 'Service Unavailable',
          });
        });
      })
  );
});
