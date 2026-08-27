/* smart-crop.js — smarter document-corner detection for jscanify.
 *
 * Replaces jscanify's `getCornerPoints` heuristic (farthest point per quadrant
 * of `minAreaRect`) with a standard `approxPolyDP` → 4-vertex polygon, then
 * sorts those 4 corners as TL / TR / BR / BL. Falls back to jscanify's
 * heuristic when the contour can't be approximated to 4 vertices.
 *
 * Exposes `SmartCrop.detectCorners(cv, jscanifyInstance, image)` → corner
 * object or `null`. `image` can be anything `cv.imread` accepts
 * (HTMLCanvasElement, HTMLImageElement, ImageData, OffscreenCanvas...).
 */
(function () {
  "use strict";

  function sortCorners(pts) {
    var cx = 0, cy = 0;
    for (var i = 0; i < 4; i++) { cx += pts[i].x; cy += pts[i].y; }
    cx /= 4; cy /= 4;

    var tl, tr, br, bl;
    var maxSum = -Infinity, minDiff = Infinity, maxDiff = -Infinity, minSum = Infinity;
    for (var i = 0; i < 4; i++) {
      var p = pts[i];
      var s = p.x + p.y;
      var d = p.x - p.y;
      if (s < minSum) { minSum = s; tl = p; }
      if (d > maxDiff) { maxDiff = d; tr = p; }
      if (s > maxSum) { maxSum = s; br = p; }
      if (d < minDiff) { minDiff = d; bl = p; }
    }
    return { topLeftCorner: tl, topRightCorner: tr, bottomRightCorner: br, bottomLeftCorner: bl };
  }

  function detectCorners(cv, scanner, image) {
    try {
      var img = cv.imread(image);
      var canny = new cv.Mat();
      var blur = new cv.Mat();
      var thresh = new cv.Mat();
      cv.Canny(img, canny, 50, 200);
      cv.GaussianBlur(canny, blur, new cv.Size(3, 3), 0, 0, cv.BORDER_DEFAULT);
      cv.threshold(blur, thresh, 0, 255, cv.THRESH_OTSU);

      var contours = new cv.MatVector();
      var hierarchy = new cv.Mat();
      cv.findContours(thresh, contours, hierarchy, cv.RETR_EXTERNAL, cv.CHAIN_APPROX_SIMPLE);

      var maxArea = 0, maxIdx = -1;
      for (var i = 0; i < contours.size(); i++) {
        var a = cv.contourArea(contours.get(i));
        if (a > maxArea) { maxArea = a; maxIdx = i; }
      }
      if (maxIdx < 0) {
        cleanup();
        return null;
      }

      var contour = contours.get(maxIdx);
      var peri = cv.arcLength(contour, true);
      var approx = new cv.Mat();
      cv.approxPolyDP(contour, approx, 0.02 * peri, true);

      var result = null;
      if (approx.rows === 4) {
        var pts = [];
        for (var i = 0; i < 4; i++) {
          pts.push({ x: approx.data32S[i * 2], y: approx.data32S[i * 2 + 1] });
        }
        result = sortCorners(pts);
      } else {
        result = scanner.getCornerPoints(contour);
      }

      cleanup();
      return (result && result.topLeftCorner && result.topRightCorner &&
              result.bottomLeftCorner && result.bottomRightCorner) ? result : null;

      function cleanup() {
        img.delete(); canny.delete(); blur.delete(); thresh.delete();
        contours.delete(); hierarchy.delete(); contour.delete(); approx.delete();
      }
    } catch (e) {
      return null;
    }
  }

  window.SmartCrop = { detectCorners: detectCorners };
})();
