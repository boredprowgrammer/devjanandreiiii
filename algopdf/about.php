<?php
$current = 'about';
$pageTitle = 'About AlgoPDF';
include 'partials/security.php';
include 'partials/head.php';
?>

  <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 sm:pt-14">
    <p class="eyebrow mb-4">About</p>
    <h1 class="font-display text-[26px] sm:text-[34px] leading-tight font-semibold text-ink-900 dark:text-paper-50">
      A quiet tool that does one thing well.
    </h1>
    <p class="mt-3 text-sm text-ink-500 dark:text-ink-300">
      AlgoPDF converts between PDFs and images without ever sending your files to a server.
      Everything runs in your browser, on your device, using open-source libraries.
    </p>

    <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div class="card p-5">
        <div class="icon-tile w-9 h-9" aria-hidden="true">
          <i class="fa-solid fa-shield-halved text-lg" aria-hidden="true"></i>
        </div>
        <h2 class="mt-3 font-medium text-base text-ink-900 dark:text-paper-50">Private by design</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">No uploads, no accounts, no analytics. Files are read from your device and written back to it.</p>
      </div>
      <div class="card p-5">
        <div class="icon-tile w-9 h-9" aria-hidden="true">
          <i class="fa-solid fa-bolt text-lg" aria-hidden="true"></i>
        </div>
        <h2 class="mt-3 font-medium text-base text-ink-900 dark:text-paper-50">Fast & offline</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">No network round-trips. Once the page loads, conversion works without a connection.</p>
      </div>
      <div class="card p-5">
        <div class="icon-tile w-9 h-9" aria-hidden="true">
          <i class="fa-solid fa-table-cells text-lg" aria-hidden="true"></i>
        </div>
        <h2 class="mt-3 font-medium text-base text-ink-900 dark:text-paper-50">Open components</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">Built on PDF.js, jsPDF, and JSZip — mature libraries that run entirely client-side.</p>
      </div>
      <div class="card p-5">
        <div class="icon-tile w-9 h-9" aria-hidden="true">
          <i class="fa-solid fa-file-lines text-lg" aria-hidden="true"></i>
        </div>
        <h2 class="mt-3 font-medium text-base text-ink-900 dark:text-paper-50">Two conversions</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">PDF to image (PNG/JPEG, per page) and image to PDF (single document, any order).</p>
      </div>
    </div>

    <section class="mt-12">
      <p class="eyebrow mb-4">Developer</p>
      <div class="card p-5 flex flex-col sm:flex-row items-start gap-4">
        <div class="icon-tile w-14 h-14 shrink-0" aria-hidden="true">
          <i class="fa-solid fa-user text-3xl" aria-hidden="true"></i>
        </div>
        <div class="min-w-0">
          <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">Jan Andrei</h2>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5">Developer &amp; maintainer of AlgoPDF.</p>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-2 max-w-2xl">
            I built AlgoPDF to make document conversion private and simple — no uploads, no accounts,
            everything runs in your browser. It is open to feedback and contributions.
          </p>
          <div class="mt-3 flex items-center gap-4 text-sm">
            <a href="mailto:you@example.com" class="text-accent-600 dark:text-accent-500 hover:underline">Email</a>
            <a href="https://github.com/boredprowgrammer" class="text-accent-600 dark:text-accent-500 hover:underline">GitHub</a>
          </div>
        </div>
      </div>

      <h3 class="font-medium text-base text-ink-900 dark:text-paper-50 mt-10">Built with</h3>
      <div class="mt-3 card divide-y divide-ink-900/10 dark:divide-paper-100/10 overflow-hidden">
        <div class="px-4 py-2.5 flex items-center justify-between gap-4 text-sm">
          <span class="text-ink-800 dark:text-paper-200">PDF rendering</span>
          <span class="text-ink-500 dark:text-ink-300 font-medium">PDF.js (pdfjs-dist) 6.2.108</span>
        </div>
        <div class="px-4 py-2.5 flex items-center justify-between gap-4 text-sm">
          <span class="text-ink-800 dark:text-paper-200">PDF generation</span>
          <span class="text-ink-500 dark:text-ink-300 font-medium">jsPDF 4.2.1</span>
        </div>
        <div class="px-4 py-2.5 flex items-center justify-between gap-4 text-sm">
          <span class="text-ink-800 dark:text-paper-200">Image archiving</span>
          <span class="text-ink-500 dark:text-ink-300 font-medium">JSZip 3.10.1</span>
        </div>
        <div class="px-4 py-2.5 flex items-center justify-between gap-4 text-sm">
          <span class="text-ink-800 dark:text-paper-200">Interface &amp; tokens</span>
          <span class="text-ink-500 dark:text-ink-300 font-medium">Tailwind CSS · LORCAPP UI</span>
        </div>
        <div class="px-4 py-2.5 flex items-center justify-between gap-4 text-sm">
          <span class="text-ink-800 dark:text-paper-200">Offline support</span>
          <span class="text-ink-500 dark:text-ink-300 font-medium">Service Worker (sw.js)</span>
        </div>
      </div>
    </section>
  </section>

<?php include 'partials/footer.php'; ?>
