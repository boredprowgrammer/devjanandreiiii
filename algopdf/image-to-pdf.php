<?php
$current = 'home';
$pageTitle = 'Image to PDF';
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
        <i class="fa-solid fa-file-pdf text-3xl" aria-hidden="true"></i>
      </div>
      <div>
        <p class="eyebrow mb-1">Assemble</p>
        <h1 class="font-display text-[26px] font-semibold text-ink-900 dark:text-paper-50">Image to PDF</h1>
        <p class="text-sm text-ink-500 dark:text-ink-300 mt-1">Combine images into one document.</p>
      </div>
    </div>

    <article class="card p-5 mt-6 flex flex-col" aria-labelledby="i2p-title">
      <h2 id="i2p-title" class="sr-only">Image to PDF converter</h2>

      <div class="mt-1"
           data-dropzone
           data-input="i2p-input"
           data-accept="image/*"
           data-multiple="true"
           id="i2p-drop">
        <label for="i2p-input"
               class="flex flex-col items-center justify-center gap-2 px-4 py-8 rounded-lg border border-dashed border-ink-900/20 dark:border-paper-100/20 bg-ink-900/[0.02] dark:bg-paper-100/[0.03] cursor-pointer hover:border-ink-900/40 dark:hover:border-paper-100/40 transition-colors text-center">
          <i class="fa-solid fa-cloud-arrow-up text-2xl text-ink-500 dark:text-ink-300" aria-hidden="true"></i>
          <span class="text-sm font-medium text-ink-800 dark:text-paper-200">Drop images here</span>
          <span class="text-xs text-ink-500 dark:text-ink-300">or click to browse · PNG, JPEG, WebP</span>
          <input id="i2p-input" type="file" accept="image/*" multiple class="sr-only" />
        </label>
      </div>

      <div class="mt-4 grid grid-cols-2 gap-3">
        <label class="block">
          <span class="eyebrow">Page size</span>
          <select id="i2p-size" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
            <option value="original" selected>Original (per image)</option>
            <option value="a4">Fit to A4</option>
            <option value="letter">Fit to Letter</option>
          </select>
        </label>
        <label class="block">
          <span class="eyebrow">Layout</span>
          <select id="i2p-fit" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
            <option value="contain" selected>Contain (no crop)</option>
            <option value="fill">Fill (crop edges)</option>
          </select>
        </label>
      </div>

      <div id="i2p-status" class="mt-4" aria-live="polite"></div>

      <div id="i2p-progress" class="mt-4 hidden">
        <div class="flex items-center justify-between text-xs text-ink-500 dark:text-ink-300 mb-1.5">
          <span id="i2p-progress-label">Building…</span>
          <span id="i2p-progress-pct">0%</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-ink-900/10 dark:bg-paper-100/10 overflow-hidden">
          <div id="i2p-progress-bar" class="h-full bg-ink-700 dark:bg-paper-200 transition-all duration-150" style="width:0%"></div>
        </div>
      </div>

      <div class="mt-4 flex items-center gap-3">
        <button id="i2p-build" type="button" class="btn btn-primary" disabled>
          <i id="i2p-spinner" class="fa-solid fa-spinner fa-spin text-base hidden" aria-hidden="true"></i>
          <span id="i2p-build-label">Build PDF</span>
        </button>
        <button id="i2p-clear" type="button" class="btn btn-secondary" hidden>Clear</button>
      </div>

      <div id="i2p-results" class="mt-4"></div>
    </article>
  </section>

<?php include 'partials/footer.php'; ?>
