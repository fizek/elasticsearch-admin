var VERSION = 'develop';
var CACHE_KEY_PREFIX = 'elasticsearch-admin-';
var CACHE_KEY = CACHE_KEY_PREFIX + VERSION;
var CACHE_FILES = [
    'offline',
    'favicon-red-64.png',
    'favicon-yellow-64.png',
    'favicon-green-64.png',
    'favicon-gray-64.png',
    'favicon-red-144.png',
    'favicon-yellow-144.png',
    'favicon-green-144.png',
    'favicon-gray-144.png',
    'favicon-red-512.png',
    'favicon-yellow-512.png',
    'favicon-green-512.png',
    'favicon-gray-512.png',
];

self.addEventListener('install', function(InstallEvent) {
    self.skipWaiting();

    if('waitUntil' in InstallEvent) {
        InstallEvent.waitUntil(
            cacheAddAll()
        );
    }
});

self.addEventListener('activate', function(ExtendableEvent) {
    if('waitUntil' in ExtendableEvent) {
        ExtendableEvent.waitUntil(
            caches.keys()
            .then(function(cacheNames) {
                return Promise.all(
                    cacheNames.map(function(cacheName) {
                        if(cacheName !== CACHE_KEY && -1 !== cacheName.indexOf(CACHE_KEY_PREFIX)) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(function() {
                return self.clients.claim();
            })
        );
    }
});

self.addEventListener('fetch', function(FetchEvent) {
    var request = FetchEvent.request;

    if(
        request.url.indexOf('.css') !== -1 ||
        request.url.indexOf('.js') !== -1 ||
        request.url.indexOf('.png') !== -1
    ) {
        FetchEvent.respondWith(
            caches.match(request)
            .then(function(response) {
                if(response) {
                    return response;
                }

                if ('GET' === request.method) {
                    caches.open(CACHE_KEY)
                    .then(function(cache) {
                        return fetch(request)
                        .then(function(response) {
                            cache.put(request, response);
                        });
                    });
                }
                return fetch(request);
            })
        );
    } else {
        if ('GET' === request.method) {
            FetchEvent.respondWith(
                fetch(request).catch(function() {
                    return caches.open(CACHE_KEY).then(function(cache) {
                        return cache.match('offline');
                    });
                })
            );
        }
    }
});

self.addEventListener('message', function(message) {
    switch(message.data.command) {
        case 'reload':
            messageToClient('reload', true);
            break;
    }
});

self.addEventListener('pushsubscriptionchange', function(PushSubscriptionChangeEvent) {
    fetch(self.registration.scope + '/pushsubscriptionchange', {
        'method': 'post',
        'body': JSON.stringify(PushSubscriptionChangeEvent)
    });

    self.registration.showNotification('pushsubscriptionchange', {
        'data': {'url': self.registration.scope},
        'tag': 'pushsubscriptionchange',
        'body': 'pushsubscriptionchange'
    });
});

self.addEventListener('push', function(PushEvent) {
    if('waitUntil' in PushEvent) {
        if(PushEvent.data) {
            var json = PushEvent.data.json();
            PushEvent.waitUntil(
                self.registration.showNotification(json.title, {
                    'data': {'url': self.registration.scope},
                    'tag': json.tag,
                    'body': json.body
                })
            );
        }
    }
});

self.addEventListener('notificationclick', function(NotificationEvent) {
    NotificationEvent.notification.close();

    return clients.openWindow(NotificationEvent.notification.data.url);
});

function cacheAddAll() {
    caches.delete(CACHE_KEY);
    return caches.open(CACHE_KEY)
    .then(function(cache) {
        return cache.addAll(CACHE_FILES);
    });
}

function messageToClient(type, content) {
    self.clients.matchAll()
    .then(function(clients) {
        clients.map(function(client) {
            client.postMessage({'type': type, 'content': content});
        });
    });
}
