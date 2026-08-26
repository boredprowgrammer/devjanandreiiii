/* build-image.js — shared, fully client-side PDF→image renderer (browser-only).
 * Exposes Algo.buildPdfToImages(file, opts) -> Promise<Array<{name, blob}>>.
 * Used by pdf-to-image.php (fallback) and the generating interstitial. */
import * as pdfjsLib from "https://cdn.jsdelivr.net/npm/pdfjs-dist@6.2.108/build/pdf.min.mjs";

pdfjsLib.GlobalWorkerOptions.workerSrc =
  "https://cdn.jsdelivr.net/npm/pdfjs-dist@6.2.108/build/pdf.worker.min.mjs";

const MAX_TOTAL_BYTES = 2000000000; // 2 GB safety ceiling
const MAX_DIM = 16384;
const MAX_AREA = 200000000;

async function build(file, opts) {
  opts = opts || {};
  var format = opts.format || "image/png";
  var scale = parseFloat(opts.scale) || 2;
  var ext = format === "image/jpeg" ? "jpg" : "png";
  var fnameBase = (file.name || "document").replace(/\.pdf$/i, "");

  var buf = await file.arrayBuffer();
  var pdf = await pdfjsLib.getDocument({ data: buf }).promise;
  var total = pdf.numPages;
  var results = [];
  var totalBytes = 0;

  for (var i = 1; i <= total; i++) {
    var page = await pdf.getPage(i);
    // Honor the chosen scale, but never exceed the browser's canvas limits.
    // Use the largest scale (up to the user's choice) that fits both a
    // per-side dimension cap and a total area cap.
    var base = page.getViewport({ scale: 1 });
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

    canvas.width = 0;
    canvas.height = 0;
    if (totalBytes > MAX_TOTAL_BYTES) break; // protect the device
    // Yield so the UI stays responsive and the browser can GC between pages.
    await new Promise(function (r) { setTimeout(r, 0); });
  }
  return results;
}

window.Algo = window.Algo || {};
window.Algo.buildPdfToImages = build;
