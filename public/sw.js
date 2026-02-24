self.addEventListener('install', (event) => {
    console.log('Service Worker: Installed');
    event.waitUntil(
        caches.open('sales-app-v1').then((cache) => {
            console.log('Service Worker: Caching Files');
            return cache.addAll([
                '/',
                '/offline.html',
                '/logo.png'
            ]).catch(() => {
                // Ignore failure to cache some files
                console.warn('Failed to cache key assets');
            });
        })
    );
});

self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activated');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cache) => {
                    if (cache !== 'sales-app-v1') {
                        console.log('Service Worker: Clearing Old Cache');
                        return caches.delete(cache);
                    }
                })
            );
        })
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        fetch(event.request)
            .catch(() => caches.match(event.request))
    );
});
