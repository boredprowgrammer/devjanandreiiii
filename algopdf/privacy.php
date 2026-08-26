<?php
$current = 'privacy';
$pageTitle = 'Privacy at AlgoPDF';
include 'partials/security.php';
include 'partials/head.php';
?>

  <section class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 sm:pt-14">
    <p class="eyebrow mb-4">Privacy</p>
    <h1 class="font-display text-[26px] sm:text-[34px] leading-tight font-semibold text-ink-900 dark:text-paper-50">
      Your files never leave your device.
    </h1>
    <p class="mt-3 text-sm text-ink-500 dark:text-ink-300">
      AlgoPDF is designed so that privacy is the default, not a setting. Here is exactly what happens to your data.
    </p>

    <div class="mt-8 card divide-y divide-ink-900/10 dark:divide-paper-100/10 overflow-hidden">
      <div class="p-5">
        <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">What we process</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">Files you choose are read by JavaScript running in your browser tab. They are converted in memory and offered as a download.</p>
      </div>
      <div class="p-5">
        <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">What we send</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">Nothing. There is no upload endpoint, no form action to a server, and no analytics or third-party tracking on this site.</p>
      </div>
      <div class="p-5">
        <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">What we store</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">Only your theme preference (light/dark) is kept in your browser's local storage. No file contents are ever persisted.</p>
      </div>
      <div class="p-5">
        <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">How it's delivered</h2>
        <p class="mt-1 text-sm text-ink-500 dark:text-ink-300">The page is served with strict HTTP security headers (CSP, no framing, no MIME sniffing) and depends only on trusted content-delivery networks for libraries and fonts.</p>
      </div>
    </div>

    <div class="mt-6 alert alert-good" role="status">
          <i class="fa-solid fa-shield-halved text-lg text-good-600 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
      <div class="flex-1">
        <p class="text-sm font-medium text-ink-900 dark:text-paper-50">You can verify it yourself</p>
        <p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5">Open your browser's network panel while converting — you will see no requests carrying your file.</p>
      </div>
    </div>
  </section>

<?php include 'partials/footer.php'; ?>
