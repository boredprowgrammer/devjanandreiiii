<?php
/*
 * QR Code Generator — a standalone, browser-only tool.
 * No uploads, no tracking; the code is built entirely on the client.
 */
$pageTitle = 'QR Code Generator';

// HTTP hardening (mirrors AlgoPDF's posture: no framing, strict CSP, no sniffing).
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('X-XSS-Protection: 0');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), usb=()');
    header(
        "Content-Security-Policy: " .
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
        "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com; " .
        "font-src 'self' https://fonts.gstatic.com; " .
        "img-src 'self' blob: data:; " .
        "connect-src 'self' blob:; " .
        "worker-src 'self' blob:; " .
        "object-src 'none'; " .
        "base-uri 'self'; " .
        "form-action 'self'; " .
        "frame-ancestors 'none'"
    );
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="Generate QR codes for Wi-Fi, links, contacts and more — entirely in your browser. Nothing is uploaded." />
  <meta name="color-scheme" content="light dark" />
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500;8..60,600&display=swap" rel="stylesheet" />

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            ink:    { 950:"#14181F", 900:"#1B212C", 800:"#242C39", 700:"#3A4453", 500:"#67707E", 300:"#A6ADB8" },
            paper:  { 50:"#FFFFFF", 100:"#F6F5F2", 200:"#EAE8E2" },
            accent: { 500:"#8A6B34", 600:"#71581F" },
            good:   { 500:"#3F7A5C", 600:"#336249" },
            bad:    { 500:"#A2453F", 600:"#873832" },
          },
          fontFamily: {
            sans: ["Inter","ui-sans-serif","system-ui","-apple-system","Segoe UI","Roboto","Helvetica Neue","Arial","sans-serif"],
            display: ['"Source Serif 4"',"Source Serif Pro","Georgia","serif"],
          },
          spacing: { "4.5": "1.125rem" },
          borderRadius: { md: "6px", lg: "8px" },
          letterSpacing: { wider: "0.06em" },
          transitionDuration: { 150: "150ms", 200: "200ms" },
        },
      },
    };
  </script>
  <style type="text/tailwindcss">
    @layer base {
      html { -webkit-text-size-adjust: 100%; }
      body { @apply bg-paper-100 dark:bg-ink-950 text-ink-800 dark:text-paper-200 font-sans antialiased; }
      :focus-visible { outline: 2px solid #8A6B34; outline-offset: 2px; border-radius: 4px; }
      ::selection { background-color: rgba(138,107,52,0.22); }
    }
    @layer components {
      .eyebrow { @apply text-xs font-medium uppercase tracking-wider text-ink-500 dark:text-ink-300; }
      .icon-tile { @apply flex items-center justify-center rounded-lg bg-ink-900/5 dark:bg-paper-100/10 text-ink-700 dark:text-paper-200; }
      .card { @apply bg-paper-50 dark:bg-ink-900 border border-ink-900/10 dark:border-paper-100/10 rounded-lg; }
      .btn { @apply inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium px-4 py-2 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed; }
      .btn-primary { @apply bg-ink-900 dark:bg-paper-50 text-paper-50 dark:text-ink-900 hover:bg-ink-800 dark:hover:bg-paper-200; }
      .btn-secondary { @apply bg-paper-50 dark:bg-ink-800 border border-ink-900/15 dark:border-paper-100/15 text-ink-800 dark:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/5; }
      .alert { @apply px-4 py-3 rounded-md flex items-start gap-3 border; }
      .alert-bad { @apply bg-paper-50 dark:bg-ink-900 border-bad-500/30 border-l-[3px] border-l-bad-600; }
      .alert-good { @apply bg-paper-50 dark:bg-ink-900 border-good-500/30 border-l-[3px] border-l-good-600; }
      .alert-info { @apply bg-paper-50 dark:bg-ink-900 border-ink-500/30 border-l-[3px] border-l-ink-700; }
      .qr-type-btn { @apply rounded-md text-sm font-medium px-3 py-1.5 border border-ink-900/15 dark:border-paper-100/15 text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/5 transition-colors duration-150; }
      .qr-type-btn.is-active { @apply text-ink-900 dark:text-paper-50 bg-ink-900/5 dark:bg-paper-100/10 border-ink-900/25 dark:border-paper-100/25; }
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <header class="sticky top-0 z-30 border-b border-ink-900/10 dark:border-paper-100/10 bg-paper-100/90 dark:bg-ink-950/90 backdrop-blur">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
      <span class="flex items-center gap-3" aria-label="QR Code Generator">
        <span class="icon-tile w-9 h-9" aria-hidden="true">
          <i class="fa-solid fa-qrcode text-xl" aria-hidden="true"></i>
        </span>
        <span class="font-display text-[19px] font-semibold text-ink-900 dark:text-paper-50 tracking-tight">QR Code Generator</span>
      </span>

      <nav class="flex items-center gap-1 sm:gap-2" aria-label="Primary">
        <a href="../index.php" class="px-3 py-1.5 rounded-md text-sm font-medium text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/5 transition-colors">Services</a>
        <button id="theme-toggle" type="button" title="Toggle dark mode" aria-label="Toggle dark mode"
                class="w-9 h-9 rounded-md flex items-center justify-center text-ink-700 dark:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/10 transition-colors">
          <i class="fa-solid fa-sun text-xl hidden dark:block" aria-hidden="true"></i>
          <i class="fa-solid fa-moon text-xl block dark:hidden" aria-hidden="true"></i>
        </button>
      </nav>
    </div>
  </header>

  <main class="flex-1">
    <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-8">

      <div class="flex items-start gap-4">
        <div class="icon-tile w-14 h-14 shrink-0" aria-hidden="true">
          <i class="fa-solid fa-qrcode text-3xl" aria-hidden="true"></i>
        </div>
        <div>
          <p class="eyebrow mb-1">Share</p>
          <h1 class="font-display text-[26px] font-semibold text-ink-900 dark:text-paper-50">QR Code Generator</h1>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-1">Turn text, links, Wi-Fi and more into a scannable code — entirely in your browser.</p>
        </div>
      </div>

      <article class="card p-5 mt-6" aria-labelledby="qr-title">
        <h2 id="qr-title" class="sr-only">QR Code Generator</h2>

        <!-- Type selector -->
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Content type">
          <button type="button" class="qr-type-btn is-active" data-type="text" role="tab" aria-selected="true">Text</button>
          <button type="button" class="qr-type-btn" data-type="url" role="tab" aria-selected="false">Link</button>
          <button type="button" class="qr-type-btn" data-type="wifi" role="tab" aria-selected="false">Wi-Fi</button>
          <button type="button" class="qr-type-btn" data-type="email" role="tab" aria-selected="false">Email</button>
          <button type="button" class="qr-type-btn" data-type="sms" role="tab" aria-selected="false">SMS</button>
          <button type="button" class="qr-type-btn" data-type="tel" role="tab" aria-selected="false">Phone</button>
          <button type="button" class="qr-type-btn" data-type="contact" role="tab" aria-selected="false">Contact</button>
          <button type="button" class="qr-type-btn" data-type="location" role="tab" aria-selected="false">Location</button>
          <button type="button" class="qr-type-btn" data-type="event" role="tab" aria-selected="false">Event</button>
        </div>

        <!-- Fields per type -->
        <div class="mt-5 space-y-4">

          <!-- Text -->
          <div class="qr-fields" data-type="text">
            <label class="block">
              <span class="eyebrow">Text</span>
              <textarea id="t-text" rows="3" placeholder="Any text you want to encode…"
                        class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none"></textarea>
            </label>
          </div>

          <!-- URL -->
          <div class="qr-fields hidden" data-type="url">
            <label class="block">
              <span class="eyebrow">URL</span>
              <input id="t-url" type="text" placeholder="example.com/page"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <p class="text-xs text-ink-500 dark:text-ink-300 mt-1.5">A missing scheme is treated as <span class="font-medium">https://</span>.</p>
          </div>

          <!-- Wi-Fi -->
          <div class="qr-fields hidden space-y-4" data-type="wifi">
            <label class="block">
              <span class="eyebrow">Network name (SSID)</span>
              <input id="w-ssid" type="text" placeholder="MyWi-Fi"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Encryption</span>
              <select id="w-enc" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
                <option value="WPA" selected>WPA / WPA2 / WPA3</option>
                <option value="WEP">WEP</option>
                <option value="nopass">None (open)</option>
              </select>
            </label>
            <label class="block">
              <span class="eyebrow">Password</span>
              <input id="w-pass" type="text" placeholder="network password"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-ink-800 dark:text-paper-200">
              <input id="w-hidden" type="checkbox" class="rounded border-ink-900/25 dark:border-paper-100/25" />
              Network is hidden
            </label>
          </div>

          <!-- Email -->
          <div class="qr-fields hidden space-y-4" data-type="email">
            <label class="block">
              <span class="eyebrow">Address</span>
              <input id="e-addr" type="email" placeholder="someone@example.com"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Subject</span>
              <input id="e-subject" type="text" placeholder="Optional"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Message</span>
              <textarea id="e-body" rows="3" placeholder="Optional"
                        class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none"></textarea>
            </label>
          </div>

          <!-- SMS -->
          <div class="qr-fields hidden space-y-4" data-type="sms">
            <label class="block">
              <span class="eyebrow">Phone number</span>
              <input id="s-num" type="tel" placeholder="+1 555 0100"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Message</span>
              <textarea id="s-msg" rows="2" placeholder="Optional"
                        class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none"></textarea>
            </label>
          </div>

          <!-- Phone -->
          <div class="qr-fields hidden" data-type="tel">
            <label class="block">
              <span class="eyebrow">Phone number</span>
              <input id="p-num" type="tel" placeholder="+1 555 0100"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
          </div>

          <!-- Contact (vCard) -->
          <div class="qr-fields hidden space-y-4" data-type="contact">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <label class="block">
                <span class="eyebrow">Name</span>
                <input id="c-name" type="text" placeholder="Maria Lopez"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
              <label class="block">
                <span class="eyebrow">Organization</span>
                <input id="c-org" type="text" placeholder="Optional"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
              <label class="block">
                <span class="eyebrow">Phone</span>
                <input id="c-phone" type="tel" placeholder="+1 555 0100"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
              <label class="block">
                <span class="eyebrow">Email</span>
                <input id="c-email" type="email" placeholder="someone@example.com"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
              <label class="block sm:col-span-2">
                <span class="eyebrow">Website</span>
                <input id="c-url" type="text" placeholder="Optional"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
            </div>
          </div>

          <!-- Location -->
          <div class="qr-fields hidden space-y-4" data-type="location">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <label class="block">
                <span class="eyebrow">Latitude</span>
                <input id="g-lat" type="text" inputmode="decimal" placeholder="40.7128"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
              <label class="block">
                <span class="eyebrow">Longitude</span>
                <input id="g-lon" type="text" inputmode="decimal" placeholder="-74.0060"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
            </div>
            <label class="block">
              <span class="eyebrow">Label</span>
              <input id="g-label" type="text" placeholder="Optional"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
          </div>

          <!-- Event -->
          <div class="qr-fields hidden space-y-4" data-type="event">
            <label class="block">
              <span class="eyebrow">Title</span>
              <input id="v-title" type="text" placeholder="Team sync"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <label class="block">
                <span class="eyebrow">Starts</span>
                <input id="v-start" type="datetime-local"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
              <label class="block">
                <span class="eyebrow">Ends</span>
                <input id="v-end" type="datetime-local"
                       class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
              </label>
            </div>
            <label class="block">
              <span class="eyebrow">Location</span>
              <input id="v-loc" type="text" placeholder="Optional"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Description</span>
              <textarea id="v-desc" rows="2" placeholder="Optional"
                        class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none"></textarea>
            </label>
          </div>
        </div>

        <!-- Options -->
        <div class="mt-5 pt-4 border-t border-ink-900/10 dark:border-paper-100/10">
          <p class="eyebrow mb-3">Options</p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <label class="block">
              <span class="eyebrow">Error correction</span>
              <select id="opt-ec" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
                <option value="L">Low (7%)</option>
                <option value="M" selected>Medium (15%)</option>
                <option value="Q">Quartile (25%)</option>
                <option value="H">High (30%)</option>
              </select>
            </label>
            <label class="block">
              <span class="eyebrow">Export size (px)</span>
              <input id="opt-size" type="number" min="120" max="2048" step="20" value="320"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Quiet margin</span>
              <select id="opt-margin" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
                <option value="0">None</option>
                <option value="2">Small</option>
                <option value="4" selected>Standard</option>
                <option value="8">Large</option>
              </select>
            </label>
            <label class="block">
              <span class="eyebrow">Foreground</span>
              <input id="opt-fg" type="color" value="#14181F"
                     class="mt-1.5 w-full h-9 rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-1 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Background</span>
              <input id="opt-bg" type="color" value="#FFFFFF"
                     class="mt-1.5 w-full h-9 rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-1 focus:outline-none" />
            </label>
          </div>
        </div>

        <div id="qr-status" class="mt-4" aria-live="polite"></div>
      </article>

      <!-- Preview -->
      <article class="card p-5 mt-4" aria-labelledby="qr-preview-title">
        <div class="flex items-center justify-between gap-3 mb-4">
          <h2 id="qr-preview-title" class="font-medium text-base text-ink-900 dark:text-paper-50">Preview</h2>
          <span id="qr-meta" class="text-xs text-ink-500 dark:text-ink-300"></span>
        </div>

        <div id="qr-empty" class="flex flex-col items-center justify-center gap-2 py-12 text-center">
          <i class="fa-solid fa-qrcode text-4xl text-ink-300 dark:text-ink-500" aria-hidden="true"></i>
          <p class="text-sm text-ink-500 dark:text-ink-300">Fill in the fields above to generate a code.</p>
        </div>

        <div id="qr-preview-wrap" class="hidden flex-col items-center">
          <div id="qr-preview" class="rounded-md overflow-hidden border border-ink-900/10 dark:border-paper-100/10" style="width:280px;max-width:100%"></div>
          <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
            <button id="qr-png" type="button" class="btn btn-primary" disabled>Download PNG</button>
            <button id="qr-svg" type="button" class="btn btn-secondary" disabled>Download SVG</button>
            <button id="qr-copy" type="button" class="btn btn-secondary" disabled>Copy</button>
          </div>
        </div>
      </article>

      <footer class="border-t border-ink-900/10 dark:border-paper-100/10 mt-8">
        <div class="py-6 flex items-center gap-2 text-sm text-ink-500 dark:text-ink-300">
          <i class="fa-solid fa-shield-halved text-base text-good-600 flex-shrink-0" aria-hidden="true"></i>
          <span>QR codes are generated <span class="font-medium text-ink-700 dark:text-paper-200">entirely in your browser</span>. Nothing is uploaded.</span>
        </div>
      </footer>
    </section>
  </main>

  <script src="assets/vendor/qrcode.min.js"></script>
  <script src="assets/js/qrcode.js"></script>
</body>
</html>
