const CACHE = 'ai-school-student-shell-v1';
const SHELL = ['./index.html', './styles.css', './js/demo-data.js', './js/api.js', './js/app.js', './assets/icon.svg'];
self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)));
  self.skipWaiting();
});
self.addEventListener('activate', (event) => {
  event.waitUntil(caches.keys().then((keys) => Promise.all(keys.filter((key) => key !== CACHE).map((key) => caches.delete(key)))));
  self.clients.claim();
});
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const pathname = new URL(request.url).pathname;
  if (request.method !== 'GET' || pathname.includes('/api/') || request.headers.has('authorization')) return;
  if (request.mode === 'navigate') {
    event.respondWith(fetch(request).catch(() => caches.match('./index.html')));
    return;
  }
  if (new URL(request.url).origin === location.origin) {
    event.respondWith(caches.match(request).then((hit) => hit || fetch(request)));
  }
});
