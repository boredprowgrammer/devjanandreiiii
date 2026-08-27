<?php
$current = 'home';
$pageTitle = 'PDF to Image';
include 'partials/security.php';
include 'partials/head.php';
?>

  <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-8">
    <a href="index.php" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 transition-colors">
      <i class="fa-solid fa-arrow-left text-base" aria-hidden="true"></i>
      All tools
    </a>

    <div class="mt-4 flex items-start gap-4">
      <div class="icon-tile w-14 h-14 shrink-0" aria-hidden="true">
        <i class="fa-solid fa-file-image text-3xl" aria-hidden="true"></i>
      </div>
      <div>
        <p class="eyebrow mb-1">Extract</p>
        <h1 class="font-display text-[26px] font-semibold text-ink-900 dark:text-paper-50">PDF to Image</h1>
        <p class="text-sm text-ink-500 dark:text-ink-300 mt-1">Turn every page into a PNG or JPEG.</p>
      </div>
    </div>

    <article class="card p-5 mt-6 flex flex-col" aria-labelledby="p2i-title">
      <h2 id="p2i-title" class="sr-only">PDF to Image converter</h2>

      <div class="mt-1"
           data-dropzone
           data-input="p2i-input"
           data-accept="application/pdf"
           data-multiple="false"
           id="p2i-drop">
        <label for="p2i-input"
               class="flex flex-col items-center justify-center gap-2 px-4 py-8 rounded-lg border border-dashed border-ink-900/20 dark:border-paper-100/20 bg-ink-900/[0.02] dark:bg-paper-100/[0.03] cursor-pointer hover:border-ink-900/40 dark:hover:border-paper-100/40 transition-colors text-center">
          <i class="fa-solid fa-cloud-arrow-up text-2xl text-ink-500 dark:text-ink-300" aria-hidden="true"></i>
          <span id="p2i-drop-label" class="text-sm font-medium text-ink-800 dark:text-paper-200">Drop a PDF here</span>
          <span class="text-xs text-ink-500 dark:text-ink-300">or click to browse · max 100&nbsp;MB</span>
          <input id="p2i-input" type="file" accept="application/pdf" class="sr-only" />
        </label>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3">
        <label class="block">
          <span class="eyebrow">Format</span>
          <select id="p2i-format" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
            <option value="image/png">PNG</option>
            <option value="image/jpeg">JPEG</option>
          </select>
        </label>
        <label class="block">
          <span class="eyebrow">Quality</span>
          <select id="p2i-scale" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
            <option value="1">1× (fast)</option>
            <option value="1.5">1.5×</option>
            <option value="2">2×</option>
            <option value="3">3× (sharp)</option>
            <option value="4">4× (high)</option>
            <option value="8" selected>8× (max, best)</option>
          </select>
        </label>
      </div>

      <div id="p2i-status" class="mt-4" aria-live="polite"></div>

      <div id="p2i-progress" class="mt-4 hidden">
        <div class="flex items-center justify-between text-xs text-ink-500 dark:text-ink-300 mb-1.5">
          <span id="p2i-progress-label">Processing…</span>
          <span id="p2i-progress-pct">0%</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-ink-900/10 dark:bg-paper-100/10 overflow-hidden">
          <div id="p2i-progress-bar" class="h-full bg-ink-700 dark:bg-paper-200 transition-all duration-150" style="width:0%"></div>
        </div>
      </div>

      <div class="mt-4 flex items-center gap-3">
        <button id="p2i-convert" type="button" class="btn btn-primary" disabled>
          <i id="p2i-spinner" class="fa-solid fa-spinner fa-spin text-base hidden" aria-hidden="true"></i>
          <span id="p2i-convert-label">Convert</span>
        </button>
        <button id="p2i-clear" type="button" class="btn btn-secondary" hidden>Clear</button>
      </div>

      <div id="p2i-results" class="mt-4"></div>
    </article>
  </section>

<?php include 'partials/footer.php'; ?>
