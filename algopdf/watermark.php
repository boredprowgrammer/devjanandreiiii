<?php
$current = 'home';
$pageTitle = 'Watermark PDF';
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
        <i class="fa-solid fa-stamp text-3xl" aria-hidden="true"></i>
      </div>
      <div>
        <p class="eyebrow mb-1">Protect</p>
        <h1 class="font-display text-[26px] font-semibold text-ink-900 dark:text-paper-50">Watermark PDF</h1>
        <p class="text-sm text-ink-500 dark:text-ink-300 mt-1">Tile a repeated watermark across every page, entirely in your browser.</p>
      </div>
    </div>

    <article class="card p-5 mt-6 flex flex-col" aria-labelledby="wm-title">
      <h2 id="wm-title" class="sr-only">Watermark PDF</h2>

      <div class="mt-1"
           data-dropzone
           data-input="wm-input"
           data-accept="application/pdf"
           data-multiple="false"
           id="wm-drop">
        <label for="wm-input"
               class="flex flex-col items-center justify-center gap-2 px-4 py-8 rounded-lg border border-dashed border-ink-900/20 dark:border-paper-100/20 bg-ink-900/[0.02] dark:bg-paper-100/[0.03] cursor-pointer hover:border-ink-900/40 dark:hover:border-paper-100/40 transition-colors text-center">
          <i class="fa-solid fa-cloud-arrow-up text-2xl text-ink-500 dark:text-ink-300" aria-hidden="true"></i>
          <span id="wm-drop-label" class="text-sm font-medium text-ink-800 dark:text-paper-200">Drop a PDF here</span>
          <span class="text-xs text-ink-500 dark:text-ink-300">or click to browse · max 100&nbsp;MB</span>
          <input id="wm-input" type="file" accept="application/pdf" class="sr-only" />
        </label>
      </div>

      <label class="block mt-4">
        <span class="eyebrow">Watermark text</span>
        <input id="wm-text" type="text" value="CONFIDENTIAL" placeholder="e.g. CONFIDENTIAL"
               class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
      </label>

      <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
        <label class="block">
          <span class="eyebrow">Font size (pt)</span>
          <input id="wm-size" type="number" min="8" max="120" value="28"
                 class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none" />
        </label>
        <label class="block">
          <span class="eyebrow">Opacity</span>
          <input id="wm-opacity" type="range" min="0.05" max="1" step="0.05" value="0.18"
                 class="mt-3.5 w-full accent-ink-700 dark:accent-paper-200" />
        </label>
        <label class="block">
          <span class="eyebrow">Rotation</span>
          <select id="wm-rotation" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
            <option value="0">0°</option>
            <option value="30">30°</option>
            <option value="45" selected>45°</option>
            <option value="60">60°</option>
            <option value="90">90°</option>
          </select>
        </label>
        <label class="block">
          <span class="eyebrow">Color</span>
          <input id="wm-color" type="color" value="#9aa0a6"
                 class="mt-1.5 w-full h-9 rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-1 focus:outline-none" />
        </label>
        <label class="block">
          <span class="eyebrow">Density</span>
          <select id="wm-density" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
            <option value="0.7">Tight</option>
            <option value="1" selected>Normal</option>
            <option value="1.4">Loose</option>
          </select>
        </label>
        <label class="block">
          <span class="eyebrow">Apply to</span>
          <select id="wm-pages" class="mt-1.5 w-full rounded-md border border-ink-900/15 dark:border-paper-100/15 bg-paper-50 dark:bg-ink-800 px-3 py-2 text-sm text-ink-800 dark:text-paper-200 focus:outline-none">
            <option value="all" selected>All pages</option>
            <option value="first">First page only</option>
            <option value="odd">Odd pages</option>
            <option value="even">Even pages</option>
          </select>
        </label>
      </div>

      <div id="wm-status" class="mt-4" aria-live="polite"></div>

      <div id="wm-progress" class="mt-4 hidden">
        <div class="flex items-center justify-between text-xs text-ink-500 dark:text-ink-300 mb-1.5">
          <span id="wm-progress-label">Applying…</span>
          <span id="wm-progress-pct">0%</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-ink-900/10 dark:bg-paper-100/10 overflow-hidden">
          <div id="wm-progress-bar" class="h-full bg-ink-700 dark:bg-paper-200 transition-all duration-150" style="width:0%"></div>
        </div>
      </div>

      <div class="mt-4 flex items-center gap-3">
        <button id="wm-apply" type="button" class="btn btn-primary" disabled>
          <i class="fa-solid fa-spinner fa-spin text-base hidden" aria-hidden="true"></i>
          <span id="wm-apply-label">Apply watermark</span>
        </button>
        <button id="wm-clear" type="button" class="btn btn-secondary" hidden>Clear</button>
      </div>

      <div id="wm-results" class="mt-4"></div>
    </article>
  </section>

<?php include 'partials/footer.php'; ?>
