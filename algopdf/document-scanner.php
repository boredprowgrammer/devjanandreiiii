<?php
$current = 'home';
$pageTitle = 'Document Scanner';
// OpenCV's JS loader evaluates strings ('unsafe-eval') and fetches its wasm
// from an embedded data: URI ('data:' in connect-src). Both are scoped here,
// not app-wide, via security.php.
$cspScriptExtra = "'unsafe-eval'";
$cspConnectExtra = "data:";
include 'partials/security.php';
include 'partials/head.php';
?>

  <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-8">
    <a href="index.php" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 transition-colors">
      <i class="fa-solid fa-arrow-left text-base" aria-hidden="true"></i>
      All tools
    </a>

    <div class="mt-4 flex items-start gap-4">
      <div class="icon-tile w-14 h-14 shrink-0" aria-hidden="true">
        <i class="fa-solid fa-crop-simple text-3xl" aria-hidden="true"></i>
      </div>
      <div>
        <p class="eyebrow mb-1">Scan</p>
        <h1 class="font-display text-[26px] font-semibold text-ink-900 dark:text-paper-50">Document Scanner</h1>
        <p class="text-sm text-ink-500 dark:text-ink-300 mt-1">Detect and flatten a document from an uploaded photo or your camera, entirely in your browser.</p>
      </div>
    </div>

    <article class="card p-5 mt-6 flex flex-col" aria-labelledby="ds-title">
      <h2 id="ds-title" class="sr-only">Document Scanner</h2>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Left: input & controls -->
        <div class="flex flex-col gap-4">
          <div data-dropzone data-input="ds-input" data-accept="image/*" data-multiple="false" id="ds-drop">
            <label for="ds-input"
                   class="flex flex-col items-center justify-center gap-2 px-4 py-8 rounded-lg border border-dashed border-ink-900/20 dark:border-paper-100/20 bg-ink-900/[0.02] dark:bg-paper-100/[0.03] cursor-pointer hover:border-ink-900/40 dark:hover:border-paper-100/40 transition-colors text-center">
              <i class="fa-solid fa-cloud-arrow-up text-2xl text-ink-500 dark:text-ink-300" aria-hidden="true"></i>
              <span id="ds-drop-label" class="text-sm font-medium text-ink-800 dark:text-paper-200">Drop an image here</span>
              <span class="text-xs text-ink-500 dark:text-ink-300">or click to browse</span>
              <input id="ds-input" type="file" accept="image/*" class="sr-only" />
            </label>
          </div>

          <div class="flex flex-col rounded-lg border border-ink-900/15 dark:border-paper-100/15 bg-ink-900/[0.02] dark:bg-paper-100/[0.03] overflow-hidden">
            <video id="ds-video" class="w-full aspect-[4/3] bg-ink-900/10 dark:bg-paper-100/10 object-cover hidden" playsinline muted></video>
            <div id="ds-camera-placeholder" class="flex-1 flex flex-col items-center justify-center gap-2 px-4 py-8 text-center">
              <i class="fa-solid fa-camera text-2xl text-ink-500 dark:text-ink-300" aria-hidden="true"></i>
              <span class="text-sm text-ink-500 dark:text-ink-300">Use your camera to capture a document.</span>
            </div>
            <div class="flex items-center gap-2 p-3 border-t border-ink-900/10 dark:border-paper-100/10">
              <button id="ds-camera" type="button" class="btn btn-secondary flex-1">
                <i class="fa-solid fa-camera text-base" aria-hidden="true"></i>
                <span>Open camera</span>
              </button>
              <button id="ds-capture" type="button" class="btn btn-primary flex-1 hidden">
                <i class="fa-solid fa-circle-dot text-base" aria-hidden="true"></i>
                <span>Capture</span>
              </button>
              <button id="ds-camera-stop" type="button" class="btn btn-secondary hidden">
                <i class="fa-solid fa-xmark text-base" aria-hidden="true"></i>
                <span class="sr-only">Stop camera</span>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <label class="block">
              <span class="eyebrow">Paper width (px)</span>
              <input id="ds-width" type="number" min="1" max="10000" value="850"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
            <label class="block">
              <span class="eyebrow">Paper height (px)</span>
              <input id="ds-height" type="number" min="1" max="10000" value="1100"
                     class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
            </label>
          </div>

          <label class="block">
            <span class="eyebrow">Filter</span>
            <select id="ds-filter" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
              <option value="none">None</option>
              <option value="enhance" selected>Enhance</option>
              <option value="grayscale">Grayscale</option>
              <option value="magic-color">Magic Color</option>
              <option value="bw">B&amp;W</option>
            </select>
          </label>

          <button id="ds-scan" type="button" class="btn btn-primary" disabled>
            <i id="ds-scan-spinner" class="fa-solid fa-spinner fa-spin text-base hidden" aria-hidden="true"></i>
            <span id="ds-scan-label">Scan document</span>
          </button>
          <div id="ds-status" aria-live="polite"></div>
        </div>

        <!-- Right: source / cropper / result / downloads -->
        <div class="flex flex-col gap-4">
          <div id="ds-source-wrap" class="hidden">
            <p class="eyebrow mb-2">Source</p>
            <img id="ds-source" alt="Document to scan" class="w-full rounded-md border border-ink-900/10 dark:border-paper-100/10 bg-ink-900/[0.03] dark:bg-paper-100/[0.03]" />
          </div>

          <div id="ds-editor" class="hidden">
            <p class="eyebrow mb-2">Adjust corners</p>
            <div id="ds-stage" class="relative inline-block max-w-full">
              <canvas id="ds-edit-canvas" class="max-w-full rounded-md border border-ink-900/10 dark:border-paper-100/10 bg-ink-900/[0.03] dark:bg-paper-100/[0.03]"></canvas>
              <canvas id="ds-edit-overlay" class="absolute inset-0 w-full h-full cursor-move" style="touch-action:none;" aria-hidden="true"></canvas>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
              <button id="ds-apply" type="button" class="btn btn-primary">Apply crop</button>
              <button id="ds-edit-reset" type="button" class="btn btn-secondary">Reset to auto</button>
              <button id="ds-edit-cancel" type="button" class="btn btn-secondary">Cancel</button>
            </div>
          </div>

          <div id="ds-result-wrap" class="hidden">
            <p class="eyebrow mb-2">Scanned</p>
            <canvas id="ds-result" class="w-full rounded-md border border-ink-900/10 dark:border-paper-100/10 bg-ink-900/[0.03] dark:bg-paper-100/[0.03]"></canvas>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <button id="ds-download-png" type="button" class="btn btn-secondary hidden">Download PNG</button>
            <button id="ds-download-pdf" type="button" class="btn btn-secondary hidden">Download PDF</button>
            <button id="ds-reset" type="button" class="btn btn-secondary hidden">Start over</button>
          </div>
        </div>
      </div>
    </article>

    <p class="mt-4 flex items-center gap-2 text-xs text-ink-500 dark:text-ink-300">
      Powered by
      <a href="https://github.com/ColonelParrot/jscanify" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 font-medium text-ink-700 dark:text-paper-200 hover:underline">
        <i class="fa-solid fa-crop-simple text-base" aria-hidden="true"></i>
        jscanify
      </a>
      — open-source, pure-JavaScript document scanning.
    </p>
  </section>

  <!-- OpenCV (runtime) + jscanify. Loaded on the page so the libraries are
       guaranteed initialized; the heavy detection itself still runs off the
       main thread in scanner-worker.js, falling back here only if Workers are
       unavailable. -->
  <script async src="https://docs.opencv.org/4.7.0/opencv.js"></script>
  <script src="https://cdn.jsdelivr.net/gh/ColonelParrot/jscanify@master/src/jscanify.min.js"></script>
  <script src="assets/js/smart-crop.js"></script>
  <script src="assets/js/document-scanner.js"></script>

<?php include 'partials/footer.php'; ?>
