/* scanner-worker.js — off-main-thread corner DETECTION for the scanner.
 * Receives a downscaled ImageBitmap, finds the document contour with OpenCV,
 * and returns the four corner points. The (fast) re-warp with edited points
 * happens on the main thread so the UI never freezes during detection. */
self.onmessage = function (e) {
  var msg = e.data || {};
  if (msg.type !== "points") return;
  var bmp = msg.bitmap;

  function fail(err) {
    self.postMessage({ type: "points-result", ok: false, error: String((err && err.message) || err) });
  }
  function load(list, i) {
    if (i >= list.length) return run();
    try { importScripts(list[i]); }
    catch (err) { return fail(err); }
    load(list, i + 1);
  }
  function waitForOpenCv() {
    return new Promise(function (resolve, reject) {
      if (typeof cv !== "undefined" && typeof cv.Mat === "function") return resolve();
      var start = Date.now();
      (function tick() {
        if (typeof cv !== "undefined" && typeof cv.Mat === "function") return resolve();
        if (Date.now() - start > 30000) return reject(new Error("OpenCV failed to load in worker."));
        setTimeout(tick, 60);
      })();
    });
  }
  function run() {
    waitForOpenCv().then(function () {
      try {
        var scanner = new jscanify();
        var pts = SmartCrop.detectCorners(cv, scanner, canvas);
        if (!pts) return fail("No document detected");
        self.postMessage({ type: "points-result", ok: true, points: pts });
      } catch (err) {
        fail(err);
      }
    }).catch(fail);
  }

  load([
    "https://docs.opencv.org/4.7.0/opencv.js",
    "https://cdn.jsdelivr.net/gh/ColonelParrot/jscanify@master/src/jscanify.min.js",
    "./smart-crop.js"
  ], 0);
};
