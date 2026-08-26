/* image-to-pdf.js — assemble images into a single PDF, fully client-side. */
(function () {
  "use strict";
  var Algo = window.Algo;

  var drop = document.getElementById("i2p-drop");
  var buildBtn = document.getElementById("i2p-build");
  var clearBtn = document.getElementById("i2p-clear");
  var statusEl = document.getElementById("i2p-status");
  var resultsEl = document.getElementById("i2p-results");
  var sizeSel = document.getElementById("i2p-size");
  var fitSel = document.getElementById("i2p-fit");
  var progressEl = document.getElementById("i2p-progress");
  var progressBar = document.getElementById("i2p-progress-bar");
  var progressLabel = document.getElementById("i2p-progress-label");
  var progressPct = document.getElementById("i2p-progress-pct");
  var spinner = document.getElementById("i2p-spinner");
  var buildLabel = document.getElementById("i2p-build-label");

  var items = []; // { file, url }

  if (!drop || !buildBtn || !clearBtn || !statusEl || !resultsEl) return;

  function showSpinner(on) { spinner.classList.toggle("hidden", !on); }
  function setBuildLabel(t) { buildLabel.textContent = t; }
  function showProgress(on) { progressEl.classList.toggle("hidden", !on); }
  function setProgress(pct, label) {
    progressBar.style.width = Math.max(0, Math.min(100, pct)) + "%";
    progressPct.textContent = Math.round(pct) + "%";
    if (label) progressLabel.textContent = label;
  }

  function renderList() {
    if (!items.length) {
      resultsEl.innerHTML = "";
      buildBtn.disabled = true;
      clearBtn.hidden = true;
      return;
    }
    var rows = items.map(function (it, idx) {
      return (
        '<div class="flex items-center gap-3 px-4 py-2.5 border-b border-ink-900/10 dark:border-paper-100/10 last:border-0">' +
          '<img src="' + it.url + '" alt="" class="w-10 h-10 object-cover rounded-md border border-ink-900/10 dark:border-paper-100/10 flex-shrink-0 bg-ink-900/5">' +
          '<span class="text-sm text-ink-800 dark:text-paper-200 flex-1 min-w-0 truncate">' + Algo.escapeHtml(it.file.name) + "</span>" +
          '<span class="text-xs text-ink-500 dark:text-ink-300 flex-shrink-0">' + Algo.formatBytes(it.file.size) + "</span>" +
          '<button type="button" class="i2p-remove text-ink-500 dark:text-ink-300 hover:text-bad-600 dark:hover:text-bad-500 transition-colors flex-shrink-0" data-idx="' + idx + '" title="Remove" aria-label="Remove ' + Algo.escapeHtml(it.file.name) + '">' +
            '<svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>' +
          "</button>" +
        "</div>"
      );
    }).join("");

    resultsEl.innerHTML =
      '<div class="card overflow-hidden">' +
        '<div class="px-4 py-3 border-b border-ink-900/10 dark:border-paper-100/10 text-sm font-medium text-ink-900 dark:text-paper-50">' +
          items.length + " image" + (items.length === 1 ? "" : "s") +
        "</div>" +
        '<div class="max-h-72 overflow-y-auto scroll-quiet">' + rows + "</div>" +
      "</div>";

    Array.prototype.forEach.call(resultsEl.querySelectorAll(".i2p-remove"), function (btn) {
      btn.addEventListener("click", function () {
        var i = parseInt(btn.getAttribute("data-idx"), 10);
        if (items[i]) { URL.revokeObjectURL(items[i].url); items.splice(i, 1); }
        renderList();
      });
    });
  }

  Algo.setupDropzone(drop, {
    onFiles: function (ok, all) {
      if (all.length && all.length > ok.length) {
        Algo.setStatus(statusEl, { type: "bad", title: "Some files skipped", detail: "Only image files are accepted." });
      }
      if (!ok.length) return;
      ok.forEach(function (f) {
        items.push({ file: f, url: URL.createObjectURL(f) });
      });
      Algo.clearStatus(statusEl);
      buildBtn.disabled = false;
      clearBtn.hidden = false;
      renderList();
    },
  });

  buildBtn.addEventListener("click", function () {
    if (!items.length || !window.jspdf) return;

    var files = items.map(function (it) { return it.file; });
    var opts = { size: sizeSel.value, fit: fitSel.value };
    var count = files.length;

    buildBtn.disabled = true;
    clearBtn.disabled = true;
    showSpinner(true);
    showProgress(true);
    setProgress(0, "Preparing…");
    Algo.setStatus(statusEl, { type: "info", title: "Building PDF…", detail: count + " image" + (count === 1 ? "" : "s") + "." });

    // Hand the job to the generating interstitial, which performs the build
    // and download while showing the animation. Falls back to an inline
    // build if the handoff (IndexedDB) is unavailable.
    Algo.stashJob({
      kind: "image-to-pdf",
      name: "algopdf-document.pdf",
      files: files,
      opts: opts
    }).then(function (jobId) {
      window.location.href =
        "generating.php?job=" + encodeURIComponent(jobId) +
        "&next=" + encodeURIComponent("image-to-pdf.php") +
        "&label=" + encodeURIComponent("Building your PDF from " + count + " image" + (count === 1 ? "" : "s") + ".");
    }).catch(function (err) {
      console.warn("Job handoff failed; building inline.", err);
      Algo.buildImagePdf(files, {
        size: opts.size, fit: opts.fit,
        onProgress: function (pct, label) { setProgress(pct, label); }
      }).then(function (blob) {
        Algo.downloadBlob(blob, "algopdf-document.pdf");
        Algo.setStatus(statusEl, { type: "good", title: "PDF ready", detail: "Download started." });
      }).catch(function (e) {
        Algo.setStatus(statusEl, { type: "bad", title: "Build failed", detail: (e && e.message) || "Could not build the PDF." });
      }).then(function () {
        showSpinner(false);
        showProgress(false);
        buildBtn.disabled = false;
        clearBtn.disabled = false;
      });
    });
  });

  clearBtn.addEventListener("click", function () {
    items.forEach(function (it) { URL.revokeObjectURL(it.url); });
    items = [];
    resultsEl.innerHTML = "";
    Algo.clearStatus(statusEl);
    showSpinner(false);
    showProgress(false);
    buildBtn.disabled = true;
    clearBtn.hidden = true;
  });
})();
