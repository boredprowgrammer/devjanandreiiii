/* document-scanner.js — detect + let the user fine-tune the document
 * corners, then flatten via jscanify (OpenCV). The corner DETECTION runs off
 * the main thread in scanner-worker.js; the (fast) re-warp with custom points
 * runs on the main thread. Everything stays local. */
(function () {
  "use strict";

  function init() {
    var Algo = window.Algo;

    var drop       = document.getElementById("ds-drop");
    var input      = document.getElementById("ds-input");
    var video      = document.getElementById("ds-video");
    var camBtn     = document.getElementById("ds-camera");
    var captureBtn = document.getElementById("ds-capture");
    var stopBtn    = document.getElementById("ds-camera-stop");
    var camHolder  = document.getElementById("ds-camera-placeholder");
    var sourceImg  = document.getElementById("ds-source");
    var sourceWrap = document.getElementById("ds-source-wrap");
    var widthEl    = document.getElementById("ds-width");
    var heightEl   = document.getElementById("ds-height");
    var statusEl   = document.getElementById("ds-status");
    var scanBtn    = document.getElementById("ds-scan");
    var scanSpin   = document.getElementById("ds-scan-spinner");
    var scanLabel  = document.getElementById("ds-scan-label");
    var pngBtn     = document.getElementById("ds-download-png");
    var pdfBtn     = document.getElementById("ds-download-pdf");
    var resetBtn   = document.getElementById("ds-reset");
    var resultCv   = document.getElementById("ds-result");
    var resultWrap = document.getElementById("ds-result-wrap");
    var filterSel  = document.getElementById("ds-filter");

    // Editor elements
    var editor      = document.getElementById("ds-editor");
    var editCanvas  = document.getElementById("ds-edit-canvas");
    var editOverlay = document.getElementById("ds-edit-overlay");
    var applyBtn    = document.getElementById("ds-apply");
    var editReset   = document.getElementById("ds-edit-reset");
    var editCancel  = document.getElementById("ds-edit-cancel");

    if (!drop || !scanBtn || !sourceImg || !statusEl || !editor) return;

    var stream = null;
    var objectUrl = null;
    var scanWorker = null;
    var editorState = null;
    var activeHandle = null;

    function clamp(v, lo, hi) { v = parseInt(v, 10); if (isNaN(v)) return lo; return Math.max(lo, Math.min(hi, v)); }
    function setScanLabel(t) { if (scanLabel) scanLabel.textContent = t; }

    function applyFilter(canvas) {
      var mode = filterSel ? filterSel.value : "none";
      if (mode === "none" || typeof cv === "undefined" || !cv.Mat) return;
      try {
        var src = cv.imread(canvas);
        var out = new cv.Mat();
        if (mode === "enhance") {
          if (typeof cv.createCLAHE === "function") {
            var gray = new cv.Mat();
            cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
            var clahe = cv.createCLAHE();
            clahe.setClipLimit(2.0);
            clahe.apply(gray, out);
            cv.cvtColor(out, out, cv.COLOR_GRAY2RGBA);
            gray.delete();
          } else if (typeof cv.equalizeHist === "function") {
            var eqGray = new cv.Mat();
            cv.cvtColor(src, eqGray, cv.COLOR_RGBA2GRAY);
            cv.equalizeHist(eqGray, out);
            cv.cvtColor(out, out, cv.COLOR_GRAY2RGBA);
            eqGray.delete();
          } else {
            var fallbackGray = new cv.Mat();
            cv.cvtColor(src, fallbackGray, cv.COLOR_RGBA2GRAY);
            out = fallbackGray;
            cv.cvtColor(out, out, cv.COLOR_GRAY2RGBA);
          }
        } else if (mode === "grayscale") {
          cv.cvtColor(src, out, cv.COLOR_RGBA2GRAY);
          cv.cvtColor(out, out, cv.COLOR_GRAY2RGBA);
        } else if (mode === "magic-color") {
          applyMagicColor(canvas);
          out = null;
        } else if (mode === "bw") {
          var bwGray = new cv.Mat();
          cv.cvtColor(src, bwGray, cv.COLOR_RGBA2GRAY);
          if (typeof cv.threshold === "function") {
            cv.threshold(bwGray, out, 0, 255, cv.THRESH_BINARY | cv.THRESH_OTSU);
          } else {
            out = bwGray;
          }
          cv.cvtColor(out, out, cv.COLOR_GRAY2RGBA);
          bwGray.delete();
        }
        if (out) {
          cv.imshow(canvas, out);
          out.delete();
        }
        src.delete();
      } catch (e) {
        Algo.setStatus(statusEl, { type: "bad", title: "Filter failed", detail: (e && e.message) || "Could not apply the selected filter." });
      }
    }

    function applyMagicColor(canvas) {
      if (typeof cv === "undefined" || !cv.Mat) return;
      try {
        var src = cv.imread(canvas);
        var rgb = new cv.Mat();
        if (typeof cv.COLOR_RGBA2RGB === "number" || cv.COLOR_RGBA2RGB) {
          cv.cvtColor(src, rgb, cv.COLOR_RGBA2RGB);
        } else if (typeof cv.COLOR_RGBA2BGR === "number" || cv.COLOR_RGBA2BGR) {
          cv.cvtColor(src, rgb, cv.COLOR_RGBA2BGR);
          cv.cvtColor(rgb, rgb, cv.COLOR_BGR2RGB);
        } else {
          rgb = src.clone();
        }

        var lab = new cv.Mat();
        cv.cvtColor(rgb, lab, cv.COLOR_RGB2Lab);

        var channels = new cv.MatVector();
        cv.split(lab, channels);
        var L = channels.get(0);
        var a = channels.get(1);
        var b = channels.get(2);

        var enhancedL = new cv.Mat();
        if (typeof cv.createCLAHE === "function") {
          var clahe = cv.createCLAHE();
          clahe.setClipLimit(2.0);
          clahe.apply(L, enhancedL);
        } else {
          enhancedL = L.clone();
        }

        var blurred = new cv.Mat();
        cv.GaussianBlur(enhancedL, blurred, new cv.Size(3, 3), 0);
        cv.addWeighted(enhancedL, 1.6, blurred, -0.6, 0, enhancedL);
        blurred.delete();

        cv.normalize(enhancedL, enhancedL, 0, 255, cv.NORM_MINMAX);

        channels.set(0, enhancedL);
        cv.merge(channels, lab);

        var outRgb = new cv.Mat();
        cv.cvtColor(lab, outRgb, cv.COLOR_Lab2RGB);

        var out = new cv.Mat();
        if (src.channels() === 4) {
          cv.cvtColor(outRgb, out, cv.COLOR_RGB2RGBA);
        } else {
          out = outRgb;
        }
        cv.imshow(canvas, out);

        src.delete(); rgb.delete(); lab.delete(); channels.delete();
        L.delete(); a.delete(); b.delete();
        enhancedL.delete(); outRgb.delete(); out.delete();
      } catch (e) {
        Algo.setStatus(statusEl, { type: "bad", title: "Magic Color failed", detail: (e && e.message) || "Could not apply Magic Color." });
      }
    }

    /* ---------- Source handling ---------- */
    function clearObjectUrl() { if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; } }
    function setSource(src) {
      clearObjectUrl();
      if (src instanceof Blob) { objectUrl = URL.createObjectURL(src); sourceImg.src = objectUrl; }
      else { sourceImg.src = src; }
    }
    sourceImg.addEventListener("load", function () {
      sourceWrap.classList.remove("hidden");
      scanBtn.disabled = false;
      Algo.clearStatus(statusEl);
    });
    sourceImg.addEventListener("error", function () {
      Algo.setStatus(statusEl, { type: "bad", title: "Image error", detail: "The selected image could not be loaded." });
    });

    Algo.setupDropzone(drop, {
      accept: "image/*",
      onFiles: function (ok, all) {
        if (all.length && all.length > ok.length) {
          Algo.setStatus(statusEl, { type: "bad", title: "Some files skipped", detail: "Only image files are accepted." });
        }
        if (!ok.length) return;
        setSource(ok[0]);
      }
    });

    /* ---------- Camera ---------- */
    function stopCamera() {
      if (stream) { stream.getTracks().forEach(function (t) { t.stop(); }); stream = null; }
      if (video) { video.srcObject = null; video.classList.add("hidden"); }
      if (camHolder) camHolder.classList.remove("hidden");
      if (camBtn) camBtn.classList.remove("hidden");
      if (captureBtn) captureBtn.classList.add("hidden");
      if (stopBtn) stopBtn.classList.add("hidden");
    }
    if (camBtn) {
      camBtn.addEventListener("click", function () {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
          Algo.setStatus(statusEl, { type: "bad", title: "Camera unavailable", detail: "This browser does not support camera capture." });
          return;
        }
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" }, audio: false })
          .then(function (s) { stream = s; video.srcObject = s; return video.play(); })
          .then(function () {
            video.classList.remove("hidden");
            camHolder.classList.add("hidden");
            camBtn.classList.add("hidden");
            captureBtn.classList.remove("hidden");
            stopBtn.classList.remove("hidden");
          })
          .catch(function (e) {
            Algo.setStatus(statusEl, { type: "bad", title: "Camera unavailable", detail: (e && e.message) || "Could not access the camera." });
          });
      });
    }
    if (captureBtn) {
      captureBtn.addEventListener("click", function () {
        var w = video.videoWidth, h = video.videoHeight;
        if (!w || !h) return;
        var c = document.createElement("canvas");
        c.width = w; c.height = h;
        c.getContext("2d").drawImage(video, 0, 0, w, h);

        applyFilter(c);

        setSource(c.toDataURL("image/png"));
        stopCamera();
      });
    }
    if (stopBtn) stopBtn.addEventListener("click", stopCamera);

    /* ---------- Library loading (fallback only) ---------- */
    function loadScript(url) {
      return new Promise(function (resolve, reject) {
        var s = document.createElement("script");
        s.src = url; s.async = true;
        s.onload = resolve;
        s.onerror = function () { reject(new Error("Failed to load " + url)); };
        document.head.appendChild(s);
      });
    }
    function waitForOpenCv(timeout) {
      return new Promise(function (resolve, reject) {
        var start = Date.now();
        (function check() {
          if (typeof cv !== "undefined" && typeof cv.Mat === "function") { resolve(window.cv); return; }
          if (Date.now() - start > (timeout || 30000)) { reject(new Error("OpenCV failed to load. Check your connection and reload.")); return; }
          setTimeout(check, 60);
        })();
      });
    }
    function ensureMainLibs() {
      if (typeof window.cv !== "undefined" && typeof cv.Mat === "function" &&
          typeof jscanify !== "undefined") return Promise.resolve();
      return loadScript("https://docs.opencv.org/4.7.0/opencv.js")
        .then(function () { return waitForOpenCv(); })
        .then(function () {
          return loadScript("https://cdn.jsdelivr.net/gh/ColonelParrot/jscanify@master/src/jscanify.min.js");
        });
    }
    function getWorker() {
      if (scanWorker) return scanWorker;
      if (typeof Worker === "undefined") return null;
      try { scanWorker = new Worker("assets/js/scanner-worker.js"); }
      catch (e) { scanWorker = null; return null; }
      return scanWorker;
    }

    /* ---------- OpenCV detection / warp (main thread, fast for warp) ---------- */
    function detectPointsMain(canvas) {
      try {
        var scanner = new jscanify();
        return SmartCrop.detectCorners(cv, scanner, canvas);
      } catch (e) {
        return null;
      }
    }
    function warpMain(canvas, w, h, points) {
      return (new jscanify()).extractPaper(canvas, w, h, points);
    }

    /* ---------- Scan: detect corners, then open editor ---------- */
    function buildSourceCanvas(src, cb) {
      var img = new Image();
      img.onload = function () {
        try {
          var maxDim = 1600;
          var nw = img.naturalWidth || img.width;
          var nh = img.naturalHeight || img.height;
          var scale = Math.min(1, maxDim / Math.max(nw, nh));
          var cw = Math.max(1, Math.round(nw * scale));
          var ch = Math.max(1, Math.round(nh * scale));
          var c = document.createElement("canvas");
          c.width = cw; c.height = ch;
          c.getContext("2d").drawImage(img, 0, 0, cw, ch);
          applyFilter(c);
          cb(null, c);
        } catch (e) { cb(e); }
      };
      img.onerror = function () { cb(new Error("decode")); };
      img.src = src;
    }

    function finish() {
      scanBtn.disabled = false;
      if (scanSpin) scanSpin.classList.add("hidden");
      setScanLabel("Scan document");
    }

    function afterDetect(canvas, w, h, points) {
      if (!points) {
        Algo.setStatus(statusEl, { type: "bad", title: "No document detected", detail: "Try a clearer, well-lit photo with the page edges visible." });
        finish();
        return;
      }
      finish();
      openEditor(canvas, w, h, points);
    }

    function fallbackDetect(canvas, w, h) {
      setScanLabel("Loading scanner…");
      ensureMainLibs()
        .then(function () {
          if (typeof jscanify === "undefined" || typeof cv === "undefined" || !cv.Mat) throw new Error("The scanning library failed to load.");
          setScanLabel("Detecting…");
          var pts = detectPointsMain(canvas);
          afterDetect(canvas, w, h, pts);
        })
        .catch(function (err) {
          Algo.setStatus(statusEl, { type: "bad", title: "Scanner not ready", detail: err.message });
          finish();
        });
    }

    function detectPointsWorker(bitmap, cb) {
      var worker = getWorker();
      if (!worker) { cb(null); return; }
      var settled = false;
      function onMsg(ev) {
        var d = ev.data;
        if (!d || d.type !== "points-result") return;
        if (settled) return; settled = true;
        worker.removeEventListener("message", onMsg);
        if (d.ok && d.points) cb(d.points); else cb(null);
      }
      worker.addEventListener("message", onMsg);
      worker.addEventListener("error", function () {
        if (settled) return; settled = true;
        worker.removeEventListener("message", onMsg);
        cb(null);
      }, { once: true });
      worker.postMessage({ type: "points", bitmap: bitmap }, [bitmap]);
    }

    scanBtn.addEventListener("click", function () {
      if (!sourceImg.src || scanBtn.disabled) return;
      scanBtn.disabled = true;
      if (scanSpin) scanSpin.classList.remove("hidden");
      setScanLabel("Scanning…");
      Algo.clearStatus(statusEl);

      buildSourceCanvas(sourceImg.src, function (err, canvas) {
        if (err) {
          Algo.setStatus(statusEl, { type: "bad", title: "Image error", detail: "The source image could not be prepared." });
          finish();
          return;
        }
        var w = clamp(widthEl.value, 100, 5000);
        var h = clamp(heightEl.value, 100, 5000);
        createImageBitmap(canvas).then(function (bmp) {
          detectPointsWorker(bmp, function (pts) {
            if (pts) afterDetect(canvas, w, h, pts);
            else fallbackDetect(canvas, w, h);
          });
        }).catch(function () {
          fallbackDetect(canvas, w, h);
        });
      });
    });

    /* ---------- Corner editor ---------- */
    var HANDLE_ORDER = ["topLeftCorner", "topRightCorner", "bottomRightCorner", "bottomLeftCorner"];
    var HANDLE_COLOR = {
      topLeftCorner: "#8A6B34", topRightCorner: "#3F7A5C",
      bottomRightCorner: "#A2453F", bottomLeftCorner: "#2f6f9e"
    };

    function clonePoints(p) {
      return {
        topLeftCorner:    { x: p.topLeftCorner.x, y: p.topLeftCorner.y },
        topRightCorner:   { x: p.topRightCorner.x, y: p.topRightCorner.y },
        bottomRightCorner:{ x: p.bottomRightCorner.x, y: p.bottomRightCorner.y },
        bottomLeftCorner: { x: p.bottomLeftCorner.x, y: p.bottomLeftCorner.y }
      };
    }

    function drawOverlay() {
      var ctx = editOverlay.getContext("2d");
      ctx.clearRect(0, 0, editOverlay.width, editOverlay.height);
      var p = editorState.points;
      // Outline
      ctx.lineWidth = 4; ctx.strokeStyle = "rgba(0,0,0,0.45)";
      ctx.beginPath();
      ctx.moveTo(p.topLeftCorner.x, p.topLeftCorner.y);
      ctx.lineTo(p.topRightCorner.x, p.topRightCorner.y);
      ctx.lineTo(p.bottomRightCorner.x, p.bottomRightCorner.y);
      ctx.lineTo(p.bottomLeftCorner.x, p.bottomLeftCorner.y);
      ctx.closePath(); ctx.stroke();
      ctx.lineWidth = 2; ctx.strokeStyle = "rgba(255,255,255,0.95)"; ctx.stroke();
      // Handles
      HANDLE_ORDER.forEach(function (key) {
        var pt = p[key];
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, 11, 0, Math.PI * 2);
        ctx.fillStyle = HANDLE_COLOR[key]; ctx.fill();
        ctx.lineWidth = 2; ctx.strokeStyle = "#fff"; ctx.stroke();
      });
    }

    function pointerPos(ev) {
      var rect = editOverlay.getBoundingClientRect();
      var sx = editOverlay.width / rect.width;
      var sy = editOverlay.height / rect.height;
      return { x: (ev.clientX - rect.left) * sx, y: (ev.clientY - rect.top) * sy };
    }
    function nearestHandle(pos) {
      var p = editorState.points;
      var scale = editOverlay.width / editOverlay.getBoundingClientRect().width;
      var best = null, bestD = 22 * scale;
      HANDLE_ORDER.forEach(function (key) {
        var pt = p[key];
        var d = Math.hypot(pos.x - pt.x, pos.y - pt.y);
        if (d < bestD) { bestD = d; best = key; }
      });
      return best;
    }

    editOverlay.addEventListener("pointerdown", function (ev) {
      var pos = pointerPos(ev);
      var key = nearestHandle(pos);
      if (key) {
        activeHandle = key;
        try { editOverlay.setPointerCapture(ev.pointerId); } catch (e) {}
        ev.preventDefault();
      }
    });
    editOverlay.addEventListener("pointermove", function (ev) {
      if (!activeHandle) return;
      var pos = pointerPos(ev);
      pos.x = Math.max(0, Math.min(editorState.W, pos.x));
      pos.y = Math.max(0, Math.min(editorState.H, pos.y));
      editorState.points[activeHandle] = pos;
      drawOverlay();
    });
    function endDrag(ev) {
      if (activeHandle) {
        activeHandle = null;
        try { editOverlay.releasePointerCapture(ev.pointerId); } catch (e) {}
      }
    }
    editOverlay.addEventListener("pointerup", endDrag);
    editOverlay.addEventListener("pointercancel", endDrag);

    function openEditor(sourceCanvas, w, h, points) {
      editorState = { points: clonePoints(points), auto: clonePoints(points), W: w, H: h, source: sourceCanvas };
      editCanvas.width = sourceCanvas.width;
      editCanvas.height = sourceCanvas.height;
      editCanvas.getContext("2d").drawImage(sourceCanvas, 0, 0);
      editOverlay.width = sourceCanvas.width;
      editOverlay.height = sourceCanvas.height;
      drawOverlay();
      sourceWrap.classList.add("hidden");
      resultWrap.classList.add("hidden");
      if (pngBtn) pngBtn.classList.add("hidden");
      if (pdfBtn) pdfBtn.classList.add("hidden");
      if (resetBtn) resetBtn.classList.add("hidden");
      editor.classList.remove("hidden");
      Algo.setStatus(statusEl, { type: "info", title: "Adjust the corners", detail: "Drag the handles to fine-tune the document edges, then Apply crop." });
      editor.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }

    if (applyBtn) {
      applyBtn.addEventListener("click", function () {
        setScanLabel("Applying…");
        ensureMainLibs()
          .then(function () {
            if (typeof jscanify === "undefined") throw new Error("Scanning library not loaded.");
            var r = warpMain(editorState.source, editorState.W, editorState.H, editorState.points);
            if (!r) {
              Algo.setStatus(statusEl, { type: "bad", title: "Crop failed", detail: "Could not warp the image with those corners." });
              finish();
              return;
            }
            editor.classList.add("hidden");
            showResult(r);
            Algo.setStatus(statusEl, { type: "good", title: "Document scanned", detail: "Preview the flattened document, then download it." });
            finish();
          })
          .catch(function (err) {
            Algo.setStatus(statusEl, { type: "bad", title: "Crop failed", detail: (err && err.message) || "Could not apply the crop." });
            finish();
          });
      });
    }
    if (editReset) {
      editReset.addEventListener("click", function () {
        if (!editorState) return;
        editorState.points = clonePoints(editorState.auto);
        drawOverlay();
      });
    }
    if (editCancel) {
      editCancel.addEventListener("click", function () {
        editor.classList.add("hidden");
        sourceWrap.classList.remove("hidden");
        Algo.clearStatus(statusEl);
      });
    }

    /* ---------- Show result + downloads ---------- */
    function showResult(canvas) {
      resultCv.width = canvas.width;
      resultCv.height = canvas.height;
      resultCv.getContext("2d").drawImage(canvas, 0, 0);
      sourceWrap.classList.add("hidden");
      editor.classList.add("hidden");
      resultWrap.classList.remove("hidden");
      if (pngBtn) pngBtn.classList.remove("hidden");
      if (pdfBtn) pdfBtn.classList.remove("hidden");
      if (resetBtn) resetBtn.classList.remove("hidden");
      lastResult = canvas;
    }
    var lastResult = null;

    if (pngBtn) {
      pngBtn.addEventListener("click", function () {
        if (!lastResult) return;
        lastResult.toBlob(function (blob) { Algo.downloadBlob(blob, "scanned-document.png"); }, "image/png");
      });
    }
    if (pdfBtn) {
      pdfBtn.addEventListener("click", function () {
        if (!lastResult) { Algo.setStatus(statusEl, { type: "bad", title: "Nothing to export", detail: "Scan a document first." }); return; }
        if (!window.jspdf || !window.jspdf.jsPDF) {
          Algo.setStatus(statusEl, { type: "bad", title: "PDF unavailable", detail: "The PDF library failed to load." });
          return;
        }
        var w = lastResult.width, h = lastResult.height;
        var pdf = new window.jspdf.jsPDF({ orientation: w >= h ? "landscape" : "portrait", unit: "px", format: [w, h] });
        pdf.addImage(lastResult.toDataURL("image/png"), "PNG", 0, 0, w, h);
        pdf.save("scanned-document.pdf");
      });
    }
    if (resetBtn) {
      resetBtn.addEventListener("click", function () {
        clearObjectUrl();
        sourceImg.removeAttribute("src");
        sourceWrap.classList.add("hidden");
        resultWrap.classList.add("hidden");
        editor.classList.add("hidden");
        scanBtn.disabled = true;
        if (pngBtn) pngBtn.classList.add("hidden");
        if (pdfBtn) pdfBtn.classList.add("hidden");
        if (resetBtn) resetBtn.classList.add("hidden");
        Algo.clearStatus(statusEl);
        lastResult = null;
      });
    }
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", init);
  else init();
})();
