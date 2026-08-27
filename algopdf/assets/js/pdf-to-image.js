/* pdf-to-image.js — render each PDF page to a PNG/JPEG in the browser.
 * Uses pdf.js v6 as an ES module for sharper, faster rendering. */
import * as pdfjsLib from "https://cdn.jsdelivr.net/npm/pdfjs-dist@6.2.108/build/pdf.min.mjs";

pdfjsLib.GlobalWorkerOptions.workerSrc =
  "https://cdn.jsdelivr.net/npm/pdfjs-dist@6.2.108/build/pdf.worker.min.mjs";

(function () {
  "use strict";
  var Algo = window.Algo;
  if (!Algo) return;

  var drop = document.getElementById("p2i-drop");
  var convertBtn = document.getElementById("p2i-convert");
  var clearBtn = document.getElementById("p2i-clear");
  var statusEl = document.getElementById("p2i-status");
  var resultsEl = document.getElementById("p2i-results");
  var formatSel = document.getElementById("p2i-format");
  var scaleSel = document.getElementById("p2i-scale");
  var progressEl = document.getElementById("p2i-progress");
  var progressBar = document.getElementById("p2i-progress-bar");
  var progressLabel = document.getElementById("p2i-progress-label");
  var progressPct = document.getElementById("p2i-progress-pct");
  var spinner = document.getElementById("p2i-spinner");
  var convertLabel = document.getElementById("p2i-convert-label");
  var dropLabel = document.getElementById("p2i-drop-label");

  var selectedFile = null;
  var processing = false;
  var aborted = false;

  if (!drop || !convertBtn || !clearBtn || !statusEl || !resultsEl) return;

  function showSpinner(on) { if (spinner) spinner.classList.toggle("hidden", !on); }
  function setConvertLabel(t) { if (convertLabel) convertLabel.textContent = t; }
  function showProgress(on) { if (progressEl) progressEl.classList.toggle("hidden", !on); }
  function setProgress(pct, label) {
    progressBar.style.width = Math.max(0, Math.min(100, pct)) + "%";
    progressPct.textContent = Math.round(pct) + "%";
    if (label) progressLabel.textContent = label;
  }
  function resetControls() {
    processing = false;
    aborted = false;
    showSpinner(false);
    setConvertLabel("Convert");
    showProgress(false);
  }

  Algo.setupDropzone(drop, {
    onFiles: function (ok, all) {
      if (!ok.length) {
        Algo.setStatus(statusEl, { type: "bad", title: "Not a PDF", detail: "Please choose a PDF file." });
        return;
      }
      if (all.length > ok.length) {
        Algo.setStatus(statusEl, { type: "bad", title: "Extra files ignored", detail: "Only the first PDF was kept." });
      }
      selectedFile = ok[0];
      Algo.clearStatus(statusEl);
      resultsEl.innerHTML = "";
      convertBtn.disabled = false;
      clearBtn.hidden = false;
      if (dropLabel) dropLabel.textContent = selectedFile.name;
      Algo.setStatus(statusEl, {
        type: "info",
        title: "Ready",
        detail: selectedFile.name + " · " + Algo.formatBytes(selectedFile.size),
      });
    },
  });

  function start() {
    processing = true;
    aborted = false;
    setConvertLabel("Cancel");
    showSpinner(true);
    clearBtn.disabled = true;
    showProgress(true);
    setProgress(0, "Reading PDF…");

    var format = formatSel.value;
    var scale = parseFloat(scaleSel.value) || 2;
    var ext = format === "image/jpeg" ? "jpg" : "png";

    Algo.clearResultUrls();
    selectedFile.arrayBuffer().then(function (buf) {
      return pdfjsLib.getDocument({ data: buf }).promise;
    }).then(function (pdf) {
      var total = pdf.numPages;
      var results = [];
      var totalBytes = 0;
      var MAX_TOTAL_BYTES = 2000000000; // 2 GB safety ceiling
      var fnameBase = selectedFile.name.replace(/\.pdf$/i, "");

      function finalizeDone() {
        Algo.renderImageResults(resultsEl, results, fnameBase, statusEl);
        setProgress(100, "Done");
        Algo.setStatus(statusEl, { type: "good", title: "Done", detail: total + " page" + (total === 1 ? "" : "s") + " converted." });
        resetControls();
        convertBtn.disabled = true;
        clearBtn.disabled = false;
      }
      function finalizeCancelled() {
        Algo.renderImageResults(resultsEl, results, fnameBase, statusEl);
        Algo.setStatus(statusEl, {
          type: "info",
          title: "Cancelled",
          detail: results.length + " page" + (results.length === 1 ? "" : "s") + " converted before stopping.",
        });
        resetControls();
        convertBtn.disabled = true;
        clearBtn.disabled = false;
      }
      function finalizeMemory(i) {
        Algo.renderImageResults(resultsEl, results, fnameBase, statusEl);
        Algo.setStatus(statusEl, {
          type: "bad",
          title: "Memory limit reached",
          detail: "Stopped after " + i + " of " + total + " pages to protect your device. Lower the quality or split the PDF for the rest.",
        });
        resetControls();
        convertBtn.disabled = true;
        clearBtn.disabled = false;
      }

      async function step(i) {
        if (aborted) { finalizeCancelled(); return; }
        if (i > total) { finalizeDone(); return; }
        setProgress(((i - 1) / total) * 100, "Rendering page " + i + " of " + total + "…");
        try {
          var page = await pdf.getPage(i);
          // Honor the chosen scale, but never exceed the browser's canvas
          // limits. Use the largest scale (up to the user's choice) that fits
          // both a per-side dimension cap and a total area cap.
          var base = page.getViewport({ scale: 1 });
          var MAX_DIM = 16384;
          var MAX_AREA = 200000000;
          var dimScale = MAX_DIM / Math.max(base.width, base.height);
          var areaScale = Math.sqrt(MAX_AREA / (base.width * base.height));
          var effScale = Math.min(scale, dimScale, areaScale);
          if (effScale < 0.5) effScale = 0.5;
          var viewport = page.getViewport({ scale: effScale });
          var canvas = document.createElement("canvas");
          canvas.width = Math.ceil(viewport.width);
          canvas.height = Math.ceil(viewport.height);
          var ctx = canvas.getContext("2d");
          ctx.imageSmoothingEnabled = true;
          ctx.imageSmoothingQuality = "high";
          if (format === "image/jpeg") {
            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
          }
          await page.render({ canvasContext: ctx, viewport: viewport }).promise;
          var blob = await new Promise(function (res) {
            canvas.toBlob(function (b) { res(b); }, format, format === "image/jpeg" ? 0.95 : undefined);
          });
          results.push({ name: "page-" + String(i).padStart(3, "0") + "." + ext, blob: blob });
          totalBytes += blob.size;
          canvas.width = 0; canvas.height = 0;
          setProgress((i / total) * 100, "Rendered " + i + " of " + total + "…");

          if (totalBytes > MAX_TOTAL_BYTES) { finalizeMemory(i); return; }
          // Yield so the UI stays responsive and the browser can GC between pages.
          await new Promise(function (r) { setTimeout(r, 0); });
          return step(i + 1);
        } catch (err) {
          throw err;
        }
      }
      return step(1);
    }).catch(function (err) {
      console.error(err);
      Algo.setStatus(statusEl, { type: "bad", title: "Conversion failed", detail: (err && err.message) || "Could not read this PDF." });
      resetControls();
      convertBtn.disabled = true;
      clearBtn.disabled = false;
    });
  }

  convertBtn.addEventListener("click", function () {
    if (processing) {
      aborted = true;
      setConvertLabel("Cancelling…");
      return;
    }
    if (!selectedFile) return;

    // Hand the job to the generating interstitial, which performs the
    // conversion and download while showing the animation. Falls back to an
    // inline conversion if the IndexedDB handoff is unavailable.
    Algo.setStatus(statusEl, { type: "info", title: "Converting…", detail: "Preparing your PDF." });
    Algo.stashJob({
      kind: "pdf-to-image",
      name: (selectedFile.name.replace(/\.pdf$/i, "")) + "-images.zip",
      file: selectedFile,
      opts: { format: formatSel.value, scale: parseFloat(scaleSel.value) || 2 }
    }).then(function (jobId) {
      window.location.href =
        "generating.php?job=" + encodeURIComponent(jobId) +
        "&type=image" +
        "&next=" + encodeURIComponent("pdf-to-image.php") +
        "&label=" + encodeURIComponent("Converting " + selectedFile.name + " to images.");
    }).catch(function (err) {
      console.warn("Job handoff failed; converting inline.", err);
      start(); // inline fallback
    });
  });

  clearBtn.addEventListener("click", function () {
    selectedFile = null;
    resultsEl.innerHTML = "";
    Algo.clearResultUrls();
    Algo.clearStatus(statusEl);
    resetControls();
    convertBtn.disabled = true;
    clearBtn.hidden = true;
    if (dropLabel) dropLabel.textContent = "Drop a PDF here";
    var input = document.getElementById("p2i-input");
    if (input) input.value = "";
  });
})();
