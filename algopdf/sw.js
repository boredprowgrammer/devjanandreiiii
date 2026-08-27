/* sw.js — AlgoPDF offline support.
 * Precaches the app shell plus every CDN dependency (Tailwind, pdf.js v6,
 * jsPDF, JSZip, Google Fonts) on first install, then serves cache-first so the
 * tool keeps working with no network connection. */
const CACHE = "algopdf-v1";

const LOCAL = [
  "./",
  "index.php",
  "about.php",
  "privacy.php",
  "pdf-to-image.php",
  "image-to-pdf.php",
  "assets/js/app.js",
  "assets/js/pdf-to-image.js",
  "assets/js/image-to-pdf.js",
  "assets/js/sw-register.js",
];

const CDN = [
  "https://cdn.tailwindcss.com",
  "https://cdn.jsdelivr.net/npm/jspdf@4.2.1/dist/jspdf.umd.min.js",
  "https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js",
  "https://cdn.jsdelivr.net/npm/pdfjs-dist@6.2.108/build/pdf.min.mjs",
  "https://cdn.jsdelivr.net/npm/pdfjs-dist@6.2.108/build/pdf.worker.min.mjs",
  "https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&display=swap",
];

self.addEventListener("install", function (event) {
  event.waitUntil((async function () {
    const cache = await caches.open(CACHE);
    await cache.addAll(LOCAL);
    // Best-effort: if a CDN asset can't be reached at install time, the
    // runtime fetch handler will still cache it once the network is available.
    await Promise.all(CDN.map(function (u) {
      return cache.add(u).catch(function () {});
    }));
  })());
  self.skipWaiting();
});

self.addEventListener("activate", function (event) {
  event.waitUntil((async function () {
    const keys = await caches.keys();
    await Promise.all(keys.map(function (k) {
      if (k !== CACHE) return caches.delete(k);
    }));
    await self.clients.claim();
  })());
});

self.addEventListener("fetch", function (event) {
  const req = event.request;
  if (req.method !== "GET") return;

  // Navigations: try the network first (for freshness), fall back to cache.
  if (req.mode === "navigate") {
    event.respondWith((async function () {
      try {
        const fresh = await fetch(req);
        const cache = await caches.open(CACHE);
        cache.put(req, fresh.clone());
        return fresh;
      } catch (err) {
        return (await caches.match(req)) ||
               (await caches.match("index.php")) ||
               (await caches.match("./")) ||
               Response.error();
      }
    })());
    return;
  }

  // Static assets + CDN: serve from cache immediately (offline), and refresh
  // the cache in the background when online (stale-while-revalidate).
  event.respondWith((async function () {
    const cached = await caches.open(CACHE).then(function (c) { return c.match(req); });

    // Kick off a background refresh of the cache without touching the response
    // body the browser is about to consume. Clone synchronously here — before
    // the returned (original) response is handed to the browser — otherwise the
    // browser may consume the body first and the clone throws "already used".
    const refresh = fetch(req).then(function (fresh) {
      if (fresh && (fresh.ok || fresh.type === "opaque")) {
        const copy = fresh.clone();
        caches.open(CACHE).then(function (c) { c.put(req, copy); });
      }
      return fresh;
    }).catch(function () { return cached; });

    return cached || refresh;
  })());
});
