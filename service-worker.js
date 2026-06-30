const CACHE_NAME = 'drfeelgood-v2';

self.addEventListener('install', (event) => {
    console.log('Service Worker Installed');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('Service Worker Activated');
    // Drop old caches when the version changes
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const req = event.request;

    // Only handle same-origin GET requests. Let POSTs (API calls), cross-origin
    // requests, etc. go straight to the network untouched.
    if (req.method !== 'GET' || new URL(req.url).origin !== self.location.origin) {
        return;
    }

    event.respondWith(
        fetch(req)
            // Network first so pages/data stay fresh; cache is only a fallback.
            .catch(() => caches.match(req).then(cached => {
                if (cached) return cached;
                // Nothing cached and the network failed (e.g. offline) — return a
                // clean error response instead of letting the promise reject,
                // which is what produced the "Failed to fetch" console error.
                return new Response('', { status: 504, statusText: 'Offline' });
            }))
    );
});
