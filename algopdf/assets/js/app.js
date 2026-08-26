/* app.js — shared UI logic for AlgoPDF.
 * Exposes a small `Algo` namespace used by the conversion modules, plus
 * dark-mode handling and the generic drag/drop file picker.
 */
(function () {
  "use strict";

  /* ---------- Theme ---------- */
  var root = document.documentElement;
  var toggle = document.getElementById("theme-toggle");

  function applyTheme(theme) {
    root.classList.toggle("dark", theme === "dark");
  }

  var saved = null;
  try { saved = localStorage.getItem("algopdf-theme"); } catch (e) {}
  var prefersDark = window.matchMedia &&
    window.matchMedia("(prefers-color-scheme: dark)").matches;
  applyTheme(saved === "dark" || saved === "light" ? saved : (prefersDark ? "dark" : "light"));

  if (toggle) {
    toggle.addEventListener("click", function () {
      var next = root.classList.contains("dark") ? "light" : "dark";
      applyTheme(next);
      try { localStorage.setItem("algopdf-theme", next); } catch (e) {}
    });
  }

  /* ---------- Helpers ---------- */
  function formatBytes(bytes) {
    if (!bytes && bytes !== 0) return "";
    if (bytes < 1024) return bytes + " B";
    var units = ["KB", "MB", "GB"];
    var v = bytes / 1024;
    var i = -1;
    do { v /= 1024; i++; } while (v >= 1024 && i < units.length - 1);
    return v.toFixed(v < 10 ? 1 : 0) + " " + units[i];
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function downloadBlob(blob, filename) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement("a");
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
  }

  function ensurePdfWorker() {
    if (window.pdfjsLib && !pdfjsLib.GlobalWorkerOptions.workerSrc) {
      pdfjsLib.GlobalWorkerOptions.workerSrc =
        "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";
    }
  }

  var ICONS = {
    bad: '<svg class="w-4.5 h-4.5 text-bad-600 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>',
    good: '<svg class="w-4.5 h-4.5 text-good-600 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
    info: '<svg class="w-4.5 h-4.5 text-ink-700 dark:text-paper-200 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>',
  };

  function setStatus(container, opts) {
    if (!container) return;
    var cls = opts.type === "bad" ? "alert-bad" : opts.type === "good" ? "alert-good" : "alert-info";
    var html =
      '<div class="alert ' + cls + '" role="status">' +
        (ICONS[opts.type] || ICONS.info) +
        '<div class="flex-1 min-w-0">' +
          (opts.title ? '<p class="text-sm font-medium text-ink-900 dark:text-paper-50">' + escapeHtml(opts.title) + "</p>" : "") +
          (opts.detail ? '<p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5 break-words">' + escapeHtml(opts.detail) + "</p>" : "") +
        "</div>" +
      "</div>";
    container.innerHTML = html;
  }

  function clearStatus(container) {
    if (container) container.innerHTML = "";
  }

  var resultUrls = [];
  function clearResultUrls() {
    resultUrls.forEach(function (u) { try { URL.revokeObjectURL(u); } catch (e) {} });
    resultUrls = [];
  }

  /* Render a list of converted image blobs with per-file + ZIP download. */
  function renderImageResults(container, results, baseName, statusEl) {
    if (!container) return;
    clearResultUrls();
    baseName = (baseName || "algopdf").replace(/[^\w.-]+/g, "_");
    var items = results.map(function (r) {
      var u = URL.createObjectURL(r.blob);
      resultUrls.push(u);
      return (
        '<div class="flex items-center gap-3 px-4 py-2.5 border-b border-ink-900/10 dark:border-paper-100/10 last:border-0">' +
          '<img src="' + u + '" alt="" loading="lazy" class="w-12 h-12 object-cover rounded-md border border-ink-900/10 dark:border-paper-100/10 flex-shrink-0 bg-ink-900/5" />' +
          '<span class="text-sm text-ink-800 dark:text-paper-200 flex-1 min-w-0 truncate">' + escapeHtml(r.name) + "</span>" +
          '<span class="text-xs text-ink-500 dark:text-ink-300 flex-shrink-0">' + formatBytes(r.blob.size) + "</span>" +
          '<a href="' + u + '" download="' + escapeHtml(r.name) + '" class="text-sm font-medium text-accent-600 dark:text-accent-500 hover:underline flex-shrink-0">Save</a>' +
        "</div>"
      );
    }).join("");

    var actions =
      '<div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-ink-900/10 dark:border-paper-100/10">' +
        '<span class="text-sm font-medium text-ink-900 dark:text-paper-50">' + results.length + " file" + (results.length === 1 ? "" : "s") + "</span>" +
        (results.length > 1
          ? '<button type="button" id="p2i-zip" class="btn btn-secondary">Download all (ZIP)</button>'
          : "") +
      "</div>";

    container.innerHTML =
      '<div class="card overflow-hidden">' + actions + '<div class="max-h-64 overflow-y-auto scroll-quiet">' + items + "</div></div>";

    var zipBtn = document.getElementById("p2i-zip");
    if (zipBtn) {
      zipBtn.addEventListener("click", function () {
        if (!window.JSZip) {
          setStatus(statusEl, { type: "bad", title: "ZIP unavailable", detail: "The archive library failed to load." });
          return;
        }
        setStatus(statusEl, { type: "info", title: "Packaging ZIP…", detail: "" });
        var zip = new JSZip();
        results.forEach(function (r) { zip.file(r.name, r.blob); });
        zip.generateAsync({ type: "blob" }).then(function (blob) {
          downloadBlob(blob, baseName + ".zip");
          setStatus(statusEl, { type: "good", title: "ZIP ready", detail: "Download started." });
        }).catch(function (err) {
          setStatus(statusEl, { type: "bad", title: "ZIP failed", detail: err.message || "Unknown error." });
        });
      });
    }
  }

  /* ---------- Job handoff (pass files between pages, fully client-side) ---------- */
  // Stores a job (including File objects) in IndexedDB so another page can
  // pick it up and finish the work — nothing is ever uploaded.
  var DB_NAME = "algopdf-jobs", STORE = "jobs";

  function openJobDb() {
    return new Promise(function (resolve, reject) {
      if (!window.indexedDB) { reject(new Error("IndexedDB unavailable")); return; }
      var req = indexedDB.open(DB_NAME, 1);
      req.onupgradeneeded = function () {
        if (!req.result.objectStoreNames.contains(STORE)) {
          req.result.createObjectStore(STORE, { keyPath: "id" });
        }
      };
      req.onsuccess = function () { resolve(req.result); };
      req.onerror = function () { reject(req.error); };
    });
  }

  function stashJob(job) {
    job.id = "pending-" + Date.now() + "-" + Math.random().toString(36).slice(2, 8);
    return openJobDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var tx = db.transaction(STORE, "readwrite");
        tx.objectStore(STORE).put(job);
        tx.oncomplete = function () { resolve(job.id); };
        tx.onerror = function () { reject(tx.error); };
      }).then(function (id) { db.close(); return id; });
    });
  }

  function takeJob(id) {
    return openJobDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var tx = db.transaction(STORE, "readwrite");
        var store = tx.objectStore(STORE);
        var result = null;
        var getReq = store.get(id);
        // Read, then delete within the SAME active transaction. Doing the
        // delete inside tx.oncomplete throws TransactionInactiveError because
        // the transaction has already finished.
        getReq.onsuccess = function () {
          result = getReq.result;
          if (result) store.delete(id);
        };
        tx.oncomplete = function () {
          db.close();
          resolve(result ? result : null);
        };
        tx.onerror = function () { db.close(); reject(tx.error); };
      });
    });
  }

  /* ---------- Dropzone ---------- */
  function matchesAccept(file, accept) {
    if (!accept || accept === "*") return true;
    if (accept.indexOf("/") === -1) return new RegExp(accept.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + "$", "i").test(file.name);
    if (accept.endsWith("/*")) return file.type.indexOf(accept.slice(0, accept.indexOf("/*") + 1)) === 0;
    return file.type === accept;
  }

  function setupDropzone(dropEl, opts) {
    if (!dropEl) return;
    var input = document.getElementById(dropEl.getAttribute("data-input"));
    if (!input) return;
    var accept = dropEl.getAttribute("data-accept") || opts.accept || "*";

    function handle(files) {
      var list = Array.prototype.slice.call(files || []);
      var ok = list.filter(function (f) { return matchesAccept(f, accept); });
      if (opts.onFiles) opts.onFiles(ok, list);
    }

    ["dragenter", "dragover"].forEach(function (ev) {
      dropEl.addEventListener(ev, function (e) {
        e.preventDefault(); e.stopPropagation();
        dropEl.classList.add("border-ink-900/40", "dark:border-paper-100/40");
      });
    });
    ["dragleave", "drop"].forEach(function (ev) {
      dropEl.addEventListener(ev, function (e) {
        e.preventDefault(); e.stopPropagation();
        dropEl.classList.remove("border-ink-900/40", "dark:border-paper-100/40");
      });
    });
    dropEl.addEventListener("drop", function (e) {
      if (e.dataTransfer && e.dataTransfer.files) handle(e.dataTransfer.files);
    });
    input.addEventListener("change", function () {
      handle(input.files);
      input.value = "";
    });
  }

  // Offline indicator — reflects the live network state.
  var offlineBanner = document.getElementById("offline-banner");
  function updateOnline() {
    if (offlineBanner) offlineBanner.classList.toggle("hidden", navigator.onLine);
  }
  if (offlineBanner) {
    updateOnline();
    window.addEventListener("online", updateOnline);
    window.addEventListener("offline", updateOnline);
  }

  window.Algo = {
    formatBytes: formatBytes,
    escapeHtml: escapeHtml,
    downloadBlob: downloadBlob,
    ensurePdfWorker: ensurePdfWorker,
    setStatus: setStatus,
    clearStatus: clearStatus,
    renderImageResults: renderImageResults,
    clearResultUrls: clearResultUrls,
    setupDropzone: setupDropzone,
    matchesAccept: matchesAccept,
    stashJob: stashJob,
    takeJob: takeJob,
  };
})();
