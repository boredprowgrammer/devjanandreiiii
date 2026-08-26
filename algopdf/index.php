<?php
$current = 'home';
$pageTitle = 'AlgoPDF';
include 'partials/security.php';
include 'partials/head.php';
?>

  <!-- Hero -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 pt-10 sm:pt-14">
    <p class="eyebrow mb-4">PDF conversion</p>
    <h1 class="font-display text-[26px] sm:text-[34px] leading-tight font-semibold text-ink-900 dark:text-paper-50 max-w-2xl">
      Convert PDFs and images, without sending a single byte to a server.
    </h1>
    <p class="mt-3 text-sm text-ink-500 dark:text-ink-300 max-w-2xl">
      AlgoPDF runs entirely in your browser. Files are read locally, transformed locally, and
      downloaded locally. No uploads, no accounts, no tracking.
    </p>
  </section>

  <!-- App tiles (launchpad) -->
  <section class="max-w-7xl mx-auto px-4 sm:px-6 mt-8">
    <p class="eyebrow mb-4">Tools</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

      <a href="pdf-to-image.php" class="group flex items-center gap-4 p-5 bg-paper-50 dark:bg-ink-900 border border-ink-900/10 dark:border-paper-100/10 rounded-lg hover:border-ink-900/25 dark:hover:border-paper-100/25 transition-colors duration-150">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-ink-900/5 dark:bg-paper-100/10 flex items-center justify-center text-ink-700 dark:text-paper-200" aria-hidden="true">
          <i class="fa-solid fa-file-image text-3xl" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">PDF to Image</h2>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5">Turn every page into a PNG or JPEG.</p>
        </div>
        <i class="fa-solid fa-arrow-right text-base text-ink-300 dark:text-ink-500 group-hover:text-ink-700 dark:group-hover:text-paper-200 group-hover:translate-x-0\.5 transition-all shrink-0" aria-hidden="true"></i>
      </a>

      <a href="image-to-pdf.php" class="group flex items-center gap-4 p-5 bg-paper-50 dark:bg-ink-900 border border-ink-900/10 dark:border-paper-100/10 rounded-lg hover:border-ink-900/25 dark:hover:border-paper-100/25 transition-colors duration-150">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-ink-900/5 dark:bg-paper-100/10 flex items-center justify-center text-ink-700 dark:text-paper-200" aria-hidden="true">
          <i class="fa-solid fa-file-pdf text-3xl" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">Image to PDF</h2>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5">Combine images into one document.</p>
        </div>
        <i class="fa-solid fa-arrow-right text-base text-ink-300 dark:text-ink-500 group-hover:text-ink-700 dark:group-hover:text-paper-200 group-hover:translate-x-0\.5 transition-all shrink-0" aria-hidden="true"></i>
      </a>

      <a href="watermark.php" class="group flex items-center gap-4 p-5 bg-paper-50 dark:bg-ink-900 border border-ink-900/10 dark:border-paper-100/10 rounded-lg hover:border-ink-900/25 dark:hover:border-paper-100/25 transition-colors duration-150">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-ink-900/5 dark:bg-paper-100/10 flex items-center justify-center text-ink-700 dark:text-paper-200" aria-hidden="true">
          <i class="fa-solid fa-stamp text-3xl" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">Watermark PDF</h2>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5">Tile a repeated watermark on every page.</p>
        </div>
        <i class="fa-solid fa-arrow-right text-base text-ink-300 dark:text-ink-500 group-hover:text-ink-700 dark:group-hover:text-paper-200 group-hover:translate-x-0\.5 transition-all shrink-0" aria-hidden="true"></i>
      </a>
    </div>
  </section>

<?php include 'partials/footer.php'; ?>
