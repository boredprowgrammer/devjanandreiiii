/* watermark.js — tile a repeated watermark across a PDF, fully client-side.
 * Uses pdf-lib so the result stays a real (vector) PDF and stays small. */
(function () {
  "use strict";
  var Algo = window.Algo;
  if (!Algo) return;

  var PDFLib = window.PDFLib;
  if (!PDFLib) {
    // Library missed at load; surfaced when the user tries to convert.
    Algo._noPdfLib = true;
  }

  var drop = document.getElementById("wm-drop");
  var applyBtn = document.getElementById("wm-apply");
  var clearBtn = document.getElementById("wm-clear");
  var statusEl = document.getElementById("wm-status");
  var resultsEl = document.getElementById("wm-results");
  var textEl = document.getElementById("wm-text");
  var sizeEl = document.getElementById("wm-size");
  var opacityEl = document.getElementById("wm-opacity");
  var rotationEl = document.getElementById("wm-rotation");
  var colorEl = document.getElementById("wm-color");
  var densityEl = document.getElementById("wm-density");
  var pagesEl = document.getElementById("wm-pages");
  var progressEl = document.getElementById("wm-progress");
  var progressBar = document.getElementById("wm-progress-bar");
  var progressLabel = document.getElementById("wm-progress-label");
  var progressPct = document.getElementById("wm-progress-pct");
  var spinner = document.getElementById("wm-spinner");
  var applyLabel = document.getElementById("wm-apply-label");
  var dropLabel = document.getElementById("wm-drop-label");

  var selectedFile = null;
  var processing = false;

  if (!drop || !applyBtn || !clearBtn || !statusEl || !resultsEl) return;

  function showSpinner(on) { spinner.classList.toggle("hidden", !on); }
  function showProgress(on) { progressEl.classList.toggle("hidden", !on); }
  function setProgress(pct, label) {
    progressBar.style.width = Math.max(0, Math.min(100, pct)) + "%";
    progressPct.textContent = Math.round(pct) + "%";
    if (label) progressLabel.textContent = label;
  }
  function resetControls() {
    processing = false;
    showSpinner(false);
    showProgress(false);
    applyLabel.textContent = "Apply watermark";
  }

  function hexToUnit(hex) {
    var h = hex.replace("#", "");
    if (h.length === 3) h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2];
    return {
      r: parseInt(h.slice(0, 2), 16) / 255,
      g: parseInt(h.slice(2, 4), 16) / 255,
      b: parseInt(h.slice(4, 6), 16) / 255,
    };
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
      applyBtn.disabled = false;
      clearBtn.hidden = false;
      if (dropLabel) dropLabel.textContent = selectedFile.name;
      Algo.setStatus(statusEl, {
        type: "info",
        title: "Ready",
        detail: selectedFile.name + " · " + Algo.formatBytes(selectedFile.size),
      });
    },
  });

  applyBtn.addEventListener("click", function () {
    if (processing) return;
    if (!selectedFile) return;
    if (!window.PDFLib) {
      Algo.setStatus(statusEl, { type: "bad", title: "Library unavailable", detail: "The PDF library failed to load." });
      return;
    }
    var text = (textEl.value || "").trim();
    if (!text) {
      Algo.setStatus(statusEl, { type: "bad", title: "Add watermark text", detail: "Enter the text to tile across the pages." });
      return;
    }

    processing = true;
    applyBtn.disabled = true;
    clearBtn.disabled = true;
    showSpinner(true);
    showProgress(true);
    setProgress(0, "Reading PDF…");

    var size = Math.max(8, Math.min(120, parseInt(sizeEl.value, 10) || 28));
    var opacity = Math.max(0.05, Math.min(1, parseFloat(opacityEl.value) || 0.18));
    var angle = parseFloat(rotationEl.value) || 0;
    var density = parseFloat(densityEl.value) || 1;
    var applyTo = pagesEl.value;
    var color = hexToUnit(colorEl.value || "#9aa0a6");

    selectedFile.arrayBuffer().then(function (buf) {
      return window.PDFLib.PDFDocument.load(buf);
    }).then(async function (pdf) {
      var pages = pdf.getPages();
      var font = await pdf.embedFont(window.PDFLib.StandardFonts.Helvetica);
      var stepX = size * 7 * density;
      var stepY = size * 4.5 * density;
      var total = pages.length;

      function shouldApply(index) {
        if (applyTo === "all") return true;
        if (applyTo === "first") return index === 0;
        if (applyTo === "odd") return (index + 1) % 2 === 1;
        if (applyTo === "even") return (index + 1) % 2 === 0;
        return true;
      }

      for (var i = 0; i < total; i++) {
        setProgress(Math.round((i / total) * 100), "Watermarking page " + (i + 1) + " of " + total + "…");
        if (!shouldApply(i)) continue;
        var page = pages[i];
        var dim = page.getSize();
        var w = dim.width, h = dim.height;
        // Cover the whole page (and over-scan so rotated tiles fill the corners).
        for (var y = -h; y < h * 2; y += stepY) {
          for (var x = -w; x < w * 2; x += stepX) {
            page.drawText(text, {
              x: x,
              y: y,
              size: size,
              font: font,
              color: window.PDFLib.rgb(color.r, color.g, color.b),
              opacity: opacity,
              rotate: window.PDFLib.degrees(angle),
            });
          }
        }
        // Yield so the UI stays responsive on large documents.
        await new Promise(function (r) { setTimeout(r, 0); });
      }

      setProgress(100, "Saving…");
      var bytes = await pdf.save();
      var base = selectedFile.name.replace(/\.pdf$/i, "");
      Algo.downloadBlob(new Blob([bytes], { type: "application/pdf" }), base + "-watermarked.pdf");
      Algo.setStatus(statusEl, { type: "good", title: "Watermark applied", detail: "Download started · " + total + " page" + (total === 1 ? "" : "s") + "." });
    }).catch(function (err) {
      console.error(err);
      Algo.setStatus(statusEl, { type: "bad", title: "Failed to watermark", detail: (err && err.message) || "Could not process this PDF." });
    }).then(function () {
      showSpinner(false);
      showProgress(false);
      applyBtn.disabled = false;
      clearBtn.disabled = false;
      processing = false;
    });
  });

  clearBtn.addEventListener("click", function () {
    selectedFile = null;
    resultsEl.innerHTML = "";
    Algo.clearStatus(statusEl);
    resetControls();
    applyBtn.disabled = true;
    clearBtn.hidden = true;
    if (dropLabel) dropLabel.textContent = "Drop a PDF here";
    var input = document.getElementById("wm-input");
    if (input) input.value = "";
  });
})();
