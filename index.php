<?php
  // Services landing page — lists the available browser-only apps.
  // Brand: devjandreiii. Design language: restraint over decoration.
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>devjandreiii — Services</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Source+Serif+4:opsz,wght@8..60,400;8..60,500&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'media',
      theme: {
        extend: {
          colors: {
            ink:   { 950:'#14181F', 900:'#1B212C', 800:'#242C39', 700:'#3A4453', 500:'#67707E', 300:'#A6ADB8' },
            paper: { 50:'#FFFFFF', 100:'#F6F5F2', 200:'#EAE8E2' },
            accent:{ 500:'#8A6B34', 600:'#71581F' },
            good:  { 500:'#3F7A5C', 600:'#336249' },
            bad:   { 500:'#A2453F', 600:'#873832' },
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
            display: ['"Source Serif 4"', 'Georgia', 'serif'],
          },
        },
      },
    };
  </script>
</head>

<body class="min-h-screen flex flex-col bg-paper-100 dark:bg-ink-950 text-ink-900 dark:text-paper-50 font-sans antialiased">

  <header class="border-b border-ink-900/10 dark:border-paper-100/10">
    <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between">
      <a href="index.php" class="font-display text-[19px] font-semibold text-ink-900 dark:text-paper-50 tracking-tight">devjandreiii</a>
      <nav class="flex items-center gap-1 sm:gap-2" aria-label="Primary">
        <a href="index.php" class="px-3 py-1.5 rounded-md text-sm font-medium text-ink-900 dark:text-paper-50 bg-ink-900/5 dark:bg-paper-100/10 transition-colors">Services</a>
      </nav>
    </div>
  </header>

  <main class="flex-1 w-full max-w-5xl mx-auto px-4 sm:px-6 py-16">
    <div class="max-w-2xl">
      <p class="text-xs font-medium uppercase tracking-wider text-ink-500 dark:text-paper-300 mb-4">
        What we offer
      </p>

      <h1 class="font-display text-[26px] leading-snug text-ink-900 dark:text-paper-50 mb-3">
        Services
      </h1>

      <p class="text-sm text-ink-500 dark:text-paper-300 leading-relaxed">
        Browser-only tools for converting and editing documents. Files are read locally,
        transformed locally, and downloaded locally. No uploads, no accounts.
      </p>
    </div>

    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">

      <a href="algopdf/index.php" class="group flex items-center gap-4 p-5 bg-paper-50 dark:bg-ink-900 border border-ink-900/10 dark:border-paper-100/10 rounded-lg hover:border-ink-900/25 dark:hover:border-paper-100/25 transition-colors duration-150">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-ink-900/5 dark:bg-paper-100/10 flex items-center justify-center text-ink-700 dark:text-paper-200" aria-hidden="true">
          <i class="fa-solid fa-file-pdf text-3xl" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">AlgoPDF</h2>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5">Convert PDFs and images — turn pages into images, assemble images into documents, scan paper with a camera, and add watermarks.</p>
        </div>
        <i class="fa-solid fa-arrow-right text-base text-ink-300 dark:text-ink-500 group-hover:text-ink-700 dark:group-hover:text-paper-200 group-hover:translate-x-0\.5 transition-all shrink-0" aria-hidden="true"></i>
      </a>

      <a href="qrcode/index.php" class="group flex items-center gap-4 p-5 bg-paper-50 dark:bg-ink-900 border border-ink-900/10 dark:border-paper-100/10 rounded-lg hover:border-ink-900/25 dark:hover:border-paper-100/25 transition-colors duration-150">
        <div class="w-14 h-14 shrink-0 rounded-lg bg-ink-900/5 dark:bg-paper-100/10 flex items-center justify-center text-ink-700 dark:text-paper-200" aria-hidden="true">
          <i class="fa-solid fa-qrcode text-3xl" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
          <h2 class="font-medium text-base text-ink-900 dark:text-paper-50">QR Code Generator</h2>
          <p class="text-sm text-ink-500 dark:text-ink-300 mt-0.5">Generate QR codes for links, Wi-Fi, contacts, locations, and more — entirely in your browser.</p>
        </div>
        <i class="fa-solid fa-arrow-right text-base text-ink-300 dark:text-ink-500 group-hover:text-ink-700 dark:group-hover:text-paper-200 group-hover:translate-x-0\.5 transition-all shrink-0" aria-hidden="true"></i>
      </a>

    </div>
  </main>

  <footer class="border-t border-ink-900/10 dark:border-paper-100/10">
    <div class="w-full max-w-5xl mx-auto px-4 sm:px-6 py-6 text-xs text-ink-500 dark:text-paper-300">
      &copy; <span id="year"></span> devjandreiii
    </div>
  </footer>

  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
