/* build-pdf.js — shared, fully client-side image→PDF builder.
 * Exposes Algo.buildImagePdf(files, opts) -> Promise<Blob>.
 * Used by image-to-pdf.php and the generating interstitial. */
(function () {
  "use strict";

  function mimeFor(file) {
    var t = (file.type || "").toLowerCase();
    if (t === "image/png") return "PNG";
    if (t === "image/jpeg" || t === "image/jpg") return "JPEG";
    if (t === "image/webp") return "WEBP";
    return "JPEG";
  }

  function loadImage(file) {
    return new Promise(function (resolve, reject) {
      var url = URL.createObjectURL(file);
      var img = new Image();
      img.onload = function () { resolve({ img: img, url: url, mime: mimeFor(file) }); };
      img.onerror = function () { URL.revokeObjectURL(url); reject(new Error("Could not decode " + file.name)); };
      img.src = url;
    });
  }

  function build(files, opts) {
    opts = opts || {};
    var size = opts.size || "original";
    var fit = opts.fit || "contain";
    var onProgress = opts.onProgress || function () {};

    if (!window.jspdf || !window.jspdf.jsPDF) {
      return Promise.reject(new Error("The PDF library failed to load."));
    }
    var jsPDFCtor = window.jspdf.jsPDF;

    onProgress(0, "Preparing…");

    return Promise.all(files.map(loadImage)).then(function (imgs) {
      var doc = null;
      var total = imgs.length;

      imgs.forEach(function (it, idx) {
        var iw = it.img.naturalWidth, ih = it.img.naturalHeight;
        var pageW, pageH;

        if (size === "original") {
          pageW = iw; pageH = ih;
          if (doc === null) {
            doc = new jsPDFCtor({ orientation: pageW >= pageH ? "landscape" : "portrait", unit: "px", format: [pageW, pageH] });
          } else {
            doc.addPage([pageW, pageH]);
          }
          // Pass the already-loaded element (not the object URL) so embedding
          // is synchronous and never depends on a URL that we might revoke.
          doc.addImage(it.img, it.mime, 0, 0, pageW, pageH, undefined, "FAST");
        } else {
          var dims = size === "a4" ? [595.28, 841.89] : [612, 792];
          pageW = dims[0]; pageH = dims[1];
          if (doc === null) {
            doc = new jsPDFCtor({ orientation: pageW >= pageH ? "landscape" : "portrait", unit: "pt", format: [pageW, pageH] });
          } else {
            doc.addPage([pageW, pageH]);
          }

          // White page base so transparent PNGs and cropped edges stay clean.
          doc.setFillColor(255, 255, 255);
          doc.rect(0, 0, pageW, pageH, "F");

          var dw, dh, dx, dy;
          if (fit === "fill") {
            // Cover: crop the overflow so the page is fully filled, at full quality.
            var rFill = Math.max(pageW / iw, pageH / ih);
            dw = iw * rFill; dh = ih * rFill;
            dx = (pageW - dw) / 2; dy = (pageH - dh) / 2;
            var canvas = document.createElement("canvas");
            canvas.width = Math.round(pageW);
            canvas.height = Math.round(pageH);
            var ctx = canvas.getContext("2d");
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = "high";
            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(it.img, dx, dy, dw, dh);
            var dataUrl = canvas.toDataURL(
              it.mime === "JPEG" ? "image/jpeg" : (it.mime === "WEBP" ? "image/webp" : "image/png"),
              it.mime === "JPEG" ? 0.95 : 1
            );
            doc.addImage(dataUrl, it.mime, 0, 0, pageW, pageH, undefined, "FAST");
          } else {
            // Contain: embed the original image untouched — no re-encoding, full fidelity.
            var rContain = Math.min(pageW / iw, pageH / ih);
            dw = iw * rContain; dh = ih * rContain;
            dx = (pageW - dw) / 2; dy = (pageH - dh) / 2;
            doc.addImage(it.img, it.mime, dx, dy, dw, dh, undefined, "FAST");
          }
        }

        onProgress(((idx + 1) / total) * 100, "Added " + (idx + 1) + " of " + total + "…");
      });

      return doc.output("blob");
    });
  }

  window.Algo = window.Algo || {};
  window.Algo.buildImagePdf = build;
})();
