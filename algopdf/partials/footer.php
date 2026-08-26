<?php
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    exit('Forbidden');
}
?>
  </main>

  <footer class="border-t border-ink-900/10 dark:border-paper-100/10 mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <p class="flex items-center gap-2 text-sm text-ink-500 dark:text-ink-300">
          <svg class="w-4 h-4 text-good-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" />
            <path d="m9 12 2 2 4-4" />
          </svg>
          <span>AlgoPDF processes files <span class="font-medium text-ink-700 dark:text-paper-200">entirely in your browser</span>. Nothing is uploaded.</span>
        </p>
        <nav class="flex items-center gap-4 text-sm" aria-label="Footer">
          <a href="about.php" class="text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 transition-colors">About</a>
          <a href="privacy.php" class="text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 transition-colors">Privacy</a>
          <span class="text-ink-300 dark:text-ink-300">v1.0.0</span>
        </nav>
      </div>
    </div>
  </footer>

  <!-- Libraries (jsDelivr). jsPDF + JSZip + pdf-lib are UMD globals; pdf.js
       is loaded as an ES module inside pdf-to-image.js. app.js must run first. -->
  <script src="https://cdn.jsdelivr.net/npm/jspdf@4.2.1/dist/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js"></script>

  <!-- Application logic -->
  <script src="assets/js/app.js"></script>
  <script src="assets/js/build-pdf.js"></script>
  <script src="assets/js/image-to-pdf.js"></script>
  <script src="assets/js/watermark.js"></script>
  <script type="module" src="assets/js/pdf-to-image.js"></script>
  <script src="assets/js/sw-register.js" defer></script>
</body>
</html>
