<?php
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    exit('Forbidden');
}
/**
 * head.php — document head + top navigation for AlgoPDF.
 * Expects $pageTitle and $current to be defined by the including page.
 */
$pageTitle = isset($pageTitle) ? $pageTitle : 'AlgoPDF';
$current   = isset($current) ? $current : '';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="AlgoPDF — secure, browser-only PDF conversion. Convert PDF to images and images to PDF. Your files never leave your device." />
  <meta name="color-scheme" content="light dark" />
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> · AlgoPDF</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
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
          maxWidth: { "7xl": "80rem" },
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
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <header class="sticky top-0 z-30 border-b border-ink-900/10 dark:border-paper-100/10 bg-paper-100/90 dark:bg-ink-950/90 backdrop-blur">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
      <a href="index.php" class="flex items-center gap-3 group" aria-label="AlgoPDF home">
        <span class="icon-tile w-9 h-9" aria-hidden="true">
          <i class="fa-solid fa-file-circle-check text-xl" aria-hidden="true"></i>
        </span>
        <span class="font-display text-[19px] font-semibold text-ink-900 dark:text-paper-50 tracking-tight">AlgoPDF</span>
      </a>

      <nav class="flex items-center gap-1 sm:gap-2" aria-label="Primary">
        <a href="../index.php"
           class="px-3 py-1.5 rounded-md text-sm font-medium <?php echo $current === 'services' ? 'text-ink-900 dark:text-paper-50 bg-ink-900/5 dark:bg-paper-100/10' : 'text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/5'; ?> transition-colors">Services</a>
        <a href="index.php"
           class="px-3 py-1.5 rounded-md text-sm font-medium <?php echo $current === 'home' ? 'text-ink-900 dark:text-paper-50 bg-ink-900/5 dark:bg-paper-100/10' : 'text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/5'; ?> transition-colors">Convert</a>
        <a href="about.php"
           class="px-3 py-1.5 rounded-md text-sm font-medium <?php echo $current === 'about' ? 'text-ink-900 dark:text-paper-50 bg-ink-900/5 dark:bg-paper-100/10' : 'text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/5'; ?> transition-colors">About</a>
        <a href="privacy.php"
           class="px-3 py-1.5 rounded-md text-sm font-medium <?php echo $current === 'privacy' ? 'text-ink-900 dark:text-paper-50 bg-ink-900/5 dark:bg-paper-100/10' : 'text-ink-500 dark:text-ink-300 hover:text-ink-800 dark:hover:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/5'; ?> transition-colors">Privacy</a>

        <button id="theme-toggle" type="button" title="Toggle dark mode" aria-label="Toggle dark mode"
                class="ml-1 w-9 h-9 rounded-md flex items-center justify-center text-ink-700 dark:text-paper-200 hover:bg-ink-900/5 dark:hover:bg-paper-100/10 transition-colors">
          <i class="fa-solid fa-sun text-xl hidden dark:block" aria-hidden="true"></i>
          <i class="fa-solid fa-moon text-xl block dark:hidden" aria-hidden="true"></i>
        </button>
      </nav>
    </div>
  </header>

  <main class="flex-1">
    <div id="offline-banner" class="hidden bg-ink-900 text-paper-50 text-xs text-center py-1.5 px-4" role="status">
      You are offline — you can continue using AlgoPDF. Everything runs on your device.
    </div>
