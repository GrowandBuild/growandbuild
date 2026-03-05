const CACHE_NAME = 'meus-produtos-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/css/app.css',
    '/js/app.js',
    '/images/favicon.png',
    '/images/icon-192x192.png',
    '/images/icon-512x512.png',
    '/offline.html'
];

// Instalação do Service Worker
self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Cache aberto');
                return cache.addAll(urlsToCache);
            })
    );
});

// Ativação do Service Worker
self.addEventListener('activate', function(event) {
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Cache antigo removido:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Interceptação de requisições
self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Cache hit - retorna resposta do cache
                if (response) {
                    return response;
                }

                // Clone da requisição
                var fetchRequest = event.request.clone();

                return fetch(fetchRequest).then(
                    function(response) {
                        // Verifica se resposta é válida
                        if(!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone da resposta
                        var responseToCache = response.clone();

                        caches.open(CACHE_NAME)
                            .then(function(cache) {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    }
                ).catch(function() {
                    // Retorna página offline quando falhar
                    if (event.request.destination === 'document') {
                        return caches.match('/offline.html');
                    }
                });
            })
    );
});

// Sincronização em background
self.addEventListener('sync', function(event) {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

function doBackgroundSync() {
    // Lógica de sincronização quando voltar online
    console.log('Sincronização em background executada');
}

// Push notifications
self.addEventListener('push', function(event) {
    const options = {
        body: event.data ? event.data.text() : 'Nova notificação do Meus Produtos',
        icon: '/images/icon-192x192.png',
        badge: '/images/icon-192x192.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Abrir App',
                icon: '/images/icon-192x192.png'
            },
            {
                action: 'close',
                title: 'Fechar',
                icon: '/images/icon-192x192.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('Meus Produtos', options)
    );
});

// Notificação click
self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});
