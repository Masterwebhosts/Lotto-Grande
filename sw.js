const CACHE_NAME = "lotto-grande-cache-v5";
const OFFLINE_URL = "/offline.html";

const ASSETS = [
  "/",
  "/index.php",
  "/manifest.json",
  "/js/app.js",
  "/icons/icon-192.png",
  "/icons/icon-512.png",
  OFFLINE_URL
];

self.addEventListener("install", (e) => {
  e.waitUntil(caches.open(CACHE_NAME).then(c => c.addAll(ASSETS)));
  self.skipWaiting();
});

self.addEventListener("activate", (e) => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.map(k => k !== CACHE_NAME && caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Network-first strategy
self.addEventListener("fetch", (e) => {
  if (e.request.method !== "GET") return;
  e.respondWith(
    fetch(e.request)
      .then((r) => {
        const clone = r.clone();
        caches.open(CACHE_NAME).then(c => c.put(e.request, clone));
        return r;
      })
      .catch(() =>
        caches.match(e.request).then((res) => res || caches.match(OFFLINE_URL))
      )
  );
});
