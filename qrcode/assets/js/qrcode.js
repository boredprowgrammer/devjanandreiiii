/* qrcode.js — QR Code Generator, fully client-side and self-contained.
 * Uses the vendored qrcode-generator library (assets/vendor/qrcode.min.js). */
(function () {
  "use strict";

  /* ---------- Theme ---------- */
  var root = document.documentElement;
  var toggle = document.getElementById("theme-toggle");
  function applyTheme(theme) { root.classList.toggle("dark", theme === "dark"); }
  var saved = null;
  try { saved = localStorage.getItem("qrcode-theme"); } catch (e) {}
  var prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
  applyTheme(saved === "dark" || saved === "light" ? saved : (prefersDark ? "dark" : "light"));
  if (toggle) {
    toggle.addEventListener("click", function () {
      var next = root.classList.contains("dark") ? "light" : "dark";
      applyTheme(next);
      try { localStorage.setItem("qrcode-theme", next); } catch (e) {}
    });
  }

  /* ---------- Helpers ---------- */
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }
  function downloadBlob(blob, filename) {
    var url = URL.createObjectURL(blob);
    var a = document.createElement("a");
    a.href = url; a.download = filename;
    document.body.appendChild(a); a.click(); a.remove();
    setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
  }
  var ICONS = {
    bad: '<svg class="w-4.5 h-4.5 text-bad-600 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>',
    good: '<svg class="w-4.5 h-4.5 text-good-600 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
    info: '<svg class="w-4.5 h-4.5 text-ink-700 dark:text-paper-200 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>',
  };
  function setStatus(container, opts) {
    if (!container) return;
    var cls = opts.type === "bad" ? "alert-bad" : opts.type === "good" ? "alert-good" : "alert-info";
    container.innerHTML =
      '<div class="alert ' + cls + '" role="status">' +
        (ICONS[opts.type] || ICONS.info) +
        '<div class="flex-1 min-w-0">' +
          (opts.title ? '<p class="text-sm font-medium text-ink-900 dark:text-paper-50">' + escapeHtml(opts.title) + "</p>" : "") +
          (opts.detail ? '<p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5 break-words">' + escapeHtml(opts.detail) + "</p>" : "") +
        "</div></div>";
  }
  function clearStatus(container) { if (container) container.innerHTML = ""; }

  var QR = window.qrcode;
  var typeButtons = Array.prototype.slice.call(document.querySelectorAll(".qr-type-btn"));
  var fieldGroups = Array.prototype.slice.call(document.querySelectorAll(".qr-fields"));
  var statusEl = document.getElementById("qr-status");
  var emptyEl = document.getElementById("qr-empty");
  var previewWrap = document.getElementById("qr-preview-wrap");
  var previewEl = document.getElementById("qr-preview");
  var metaEl = document.getElementById("qr-meta");
  var pngBtn = document.getElementById("qr-png");
  var svgBtn = document.getElementById("qr-svg");
  var copyBtn = document.getElementById("qr-copy");

  if (!QR || !typeButtons.length || !previewEl) return;

  // Encode UTF-8 correctly for non-ASCII content (e.g. names, passwords).
  QR.stringToBytes = QR.stringToBytesFuncs["UTF-8"];

  var currentType = "text";
  var lastSVG = null;

  /* ---------- Build the payload string for each type ---------- */
  function val(id) { var el = document.getElementById(id); return el ? el.value : ""; }

  function escWiFi(s) {
    return String(s)
      .replace(/\\/g, "\\\\")
      .replace(/;/g, "\\;")
      .replace(/,/g, "\\,")
      .replace(/"/g, '\\"')
      .replace(/:/g, "\\:");
  }
  function cleanTel(s) { return String(s).replace(/[^\d+]/g, ""); }
  function ensureScheme(url) {
    url = (url || "").trim();
    if (!url) return "";
    if (/^[a-z][a-z0-9+.-]*:/i.test(url)) return url;
    return "https://" + url;
  }
  function fmtICS(dt) {
    if (!dt) return "";
    var d = new Date(dt);
    if (isNaN(d.getTime())) return "";
    return d.toISOString().replace(/[-:]/g, "").replace(/\.\d{3}/, "");
  }

  function buildPayload(type) {
    switch (type) {
      case "text":
        return val("t-text");

      case "url":
        return ensureScheme(val("t-url"));

      case "wifi": {
        var ssid = val("w-ssid");
        if (!ssid) return "";
        var enc = val("w-enc") || "WPA";
        var pass = val("w-pass");
        var hidden = document.getElementById("w-hidden").checked ? "true" : "false";
        var out = "WIFI:T:" + enc + ";S:" + escWiFi(ssid) + ";";
        if (enc !== "nopass") out += "P:" + escWiFi(pass) + ";";
        out += "H:" + hidden + ";;";
        return out;
      }

      case "email": {
        var addr = val("e-addr");
        if (!addr) return "";
        var subj = val("e-subject");
        var body = val("e-body");
        var q = [];
        if (subj) q.push("subject=" + encodeURIComponent(subj));
        if (body) q.push("body=" + encodeURIComponent(body));
        return "mailto:" + addr + (q.length ? "?" + q.join("&") : "");
      }

      case "sms": {
        var num = cleanTel(val("s-num"));
        if (!num) return "";
        return "SMSTO:" + num + ":" + val("s-msg");
      }

      case "tel": {
        var p = cleanTel(val("p-num"));
        return p ? "tel:" + p : "";
      }

      case "contact": {
        var name = val("c-name");
        if (!name) return "";
        var lines = ["BEGIN:VCARD", "VERSION:3.0", "N:" + name, "FN:" + name];
        if (val("c-org")) lines.push("ORG:" + val("c-org"));
        if (cleanTel(val("c-phone"))) lines.push("TEL;TYPE=CELL:" + cleanTel(val("c-phone")));
        if (val("c-email")) lines.push("EMAIL:" + val("c-email"));
        if (val("c-url")) lines.push("URL:" + ensureScheme(val("c-url")));
        lines.push("END:VCARD");
        return lines.join("\r\n");
      }

      case "location": {
        var lat = (val("g-lat") || "").trim();
        var lon = (val("g-lon") || "").trim();
        if (!lat || !lon) return "";
        var label = (val("g-label") || "").trim();
        var coord = lat + "," + lon;
        return "geo:" + coord + (label ? "?q=" + encodeURIComponent(label + " @" + coord) : "");
      }

      case "event": {
        var title = val("v-title");
        if (!title) return "";
        var lines = ["BEGIN:VCALENDAR", "VERSION:2.0", "BEGIN:VEVENT", "SUMMARY:" + title];
        if (val("v-loc")) lines.push("LOCATION:" + val("v-loc"));
        var start = fmtICS(val("v-start"));
        var end = fmtICS(val("v-end"));
        if (start) lines.push("DTSTART:" + start);
        if (end) lines.push("DTEND:" + end);
        if (val("v-desc")) lines.push("DESCRIPTION:" + val("v-desc").replace(/\n/g, "\\n"));
        lines.push("END:VEVENT", "END:VCALENDAR");
        return lines.join("\r\n");
      }
    }
    return "";
  }

  /* ---------- Rendering ---------- */
  function svgFor(qr, fg, bg, margin) {
    var count = qr.getModuleCount();
    var size = count + margin * 2;
    var parts = [];
    parts.push(
      '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + size + " " + size +
      '" shape-rendering="crispEdges" width="100%" height="100%" role="img" aria-label="QR code">'
    );
    parts.push('<rect width="' + size + '" height="' + size + '" fill="' + bg + '"/>');
    parts.push('<g fill="' + fg + '">');
    for (var r = 0; r < count; r++) {
      for (var c = 0; c < count; c++) {
        if (qr.isDark(r, c)) {
          parts.push('<rect x="' + (c + margin) + '" y="' + (r + margin) + '" width="1" height="1"/>');
        }
      }
    }
    parts.push("</g></svg>");
    return parts.join("");
  }

  function canvasFor(qr, fg, bg, margin, px) {
    var count = qr.getModuleCount();
    var size = count + margin * 2;
    var cell = Math.max(1, Math.floor(px / size));
    var dim = size * cell;
    var canvas = document.createElement("canvas");
    canvas.width = dim; canvas.height = dim;
    var ctx = canvas.getContext("2d");
    ctx.fillStyle = bg; ctx.fillRect(0, 0, dim, dim);
    ctx.fillStyle = fg;
    for (var r = 0; r < count; r++) {
      for (var c = 0; c < count; c++) {
        if (qr.isDark(r, c)) ctx.fillRect((c + margin) * cell, (r + margin) * cell, cell, cell);
      }
    }
    return canvas;
  }

  function buildQR() {
    var payload = buildPayload(currentType);
    var ec = (val("opt-ec") || "M").toUpperCase();
    var margin = parseInt(val("opt-margin"), 10) || 0;
    var fg = val("opt-fg") || "#14181F";
    var bg = val("opt-bg") || "#FFFFFF";

    if (!payload) {
      lastSVG = null;
      emptyEl.classList.remove("hidden"); emptyEl.classList.add("flex");
      previewWrap.classList.add("hidden"); previewWrap.classList.remove("flex");
      clearStatus(statusEl); metaEl.textContent = "";
      setButtons(false);
      return null;
    }

    var qr;
    try {
      qr = QR(0, ec); qr.addData(payload); qr.make();
    } catch (err) {
      lastSVG = null;
      emptyEl.classList.remove("hidden"); emptyEl.classList.add("flex");
      previewWrap.classList.add("hidden"); previewWrap.classList.remove("flex");
      setButtons(false); metaEl.textContent = "";
      setStatus(statusEl, {
        type: "bad", title: "Too much data",
        detail: "This content is too long for a QR code at the selected error-correction level. Try a higher level or less text.",
      });
      return null;
    }

    lastSVG = svgFor(qr, fg, bg, margin);
    previewEl.innerHTML = lastSVG;
    metaEl.textContent = qr.getModuleCount() + " × " + qr.getModuleCount() + " modules";
    emptyEl.classList.add("hidden"); emptyEl.classList.remove("flex");
    previewWrap.classList.remove("hidden"); previewWrap.classList.add("flex");
    clearStatus(statusEl);
    setButtons(true);
    return qr;
  }

  var t = null;
  function scheduleUpdate() { clearTimeout(t); t = setTimeout(buildQR, 120); }
  function setButtons(on) { pngBtn.disabled = svgBtn.disabled = copyBtn.disabled = !on; }

  function pngBlob(cb) {
    var px = Math.max(120, Math.min(2048, parseInt(val("opt-size"), 10) || 320));
    var margin = parseInt(val("opt-margin"), 10) || 0;
    var fg = val("opt-fg") || "#14181F";
    var bg = val("opt-bg") || "#FFFFFF";
    var payload = buildPayload(currentType);
    if (!payload) return;
    var qr = QR(0, (val("opt-ec") || "M").toUpperCase());
    qr.addData(payload); qr.make();
    var canvas = canvasFor(qr, fg, bg, margin, px);
    canvas.toBlob(function (blob) { cb(blob); }, "image/png");
  }

  /* ---------- Downloads ---------- */
  pngBtn.addEventListener("click", function () {
    pngBlob(function (blob) { if (blob) downloadBlob(blob, "qrcode.png"); });
  });
  svgBtn.addEventListener("click", function () {
    if (!lastSVG) return;
    downloadBlob(new Blob([lastSVG], { type: "image/svg+xml;charset=utf-8" }), "qrcode.svg");
  });
  copyBtn.addEventListener("click", function () {
    if (!lastSVG || !navigator.clipboard || !window.ClipboardItem) {
      setStatus(statusEl, { type: "info", title: "Copy unavailable", detail: "Your browser doesn't support image copy. Use Download instead." });
      return;
    }
    pngBlob(function (blob) {
      if (!blob) return;
      navigator.clipboard.write([new window.ClipboardItem({ "image/png": blob })])
        .then(function () { setStatus(statusEl, { type: "good", title: "Copied", detail: "QR code image copied to clipboard." }); })
        .catch(function () { setStatus(statusEl, { type: "bad", title: "Copy failed", detail: "Couldn't access the clipboard. Try Download instead." }); });
    });
  });

  /* ---------- Type switching ---------- */
  function selectType(type) {
    currentType = type;
    typeButtons.forEach(function (b) {
      var active = b.getAttribute("data-type") === type;
      b.classList.toggle("is-active", active);
      b.setAttribute("aria-selected", active ? "true" : "false");
    });
    fieldGroups.forEach(function (g) {
      g.classList.toggle("hidden", g.getAttribute("data-type") !== type);
    });
    buildQR();
  }
  typeButtons.forEach(function (b) {
    b.addEventListener("click", function () { selectType(b.getAttribute("data-type")); });
  });

  document.addEventListener("input", scheduleUpdate);
  document.addEventListener("change", scheduleUpdate);

  // Seed a friendly example so the code isn't empty on first load.
  var seed = document.getElementById("t-text");
  if (seed && !seed.value) seed.value = "Hello from QR Code Generator";
  buildQR();
})();
