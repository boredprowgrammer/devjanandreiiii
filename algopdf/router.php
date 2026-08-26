<?php
/*
 * router.php — AlgoPDF front controller / router.
 *
 * Serves existing static files (assets, sw.js, vendor libs) directly so they
 * never 404, and maps "clean" URLs to the application pages.
 *
 * Running with the PHP built-in server:
 *   cd algopdf && php -S localhost:8000 router.php
 *
 * On Apache, .htaccess rewrites non-file requests here; existing files (incl.
 * assets) are served by Apache directly, but this router also serves them so
 * the behaviour is identical under either server.
 */

// The app may be mounted under a sub-path (e.g. /algopdf) or run standalone.
// ALGOPDF_BASE is set by the site front controller when delegating.
$base = isset($_SERVER['ALGOPDF_BASE']) ? rtrim($_SERVER['ALGOPDF_BASE'], '/') : '';
$rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = $rawUri;
if ($base !== '' && strncmp($rawUri, $base, strlen($base)) === 0) {
    $uri = substr($rawUri, strlen($base));
    if ($uri === '') {
        $uri = '/';
    }
}
$scriptDir = rtrim(__DIR__, '/\\');

// --- 1. Block sensitive files up front (partials + dotfiles) -----------------
$basename = basename($uri);
if ($basename !== '' && $basename[0] === '.') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Forbidden');
}
if (preg_match('#(^|/)partials(/|$)#', $uri)) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Forbidden');
}

// --- 2. Clean route table ----------------------------------------------------
$routes = [
    '/'                  => 'index.php',
    '/home'              => 'index.php',
    '/about'             => 'about.php',
    '/privacy'           => 'privacy.php',
    '/pdf-to-image'      => 'pdf-to-image.php',
    '/image-to-pdf'      => 'image-to-pdf.php',
    '/watermark'         => 'watermark.php',
];

$path = $uri;
if (isset($routes[$path])) {
    include $scriptDir . '/' . $routes[$path];
    exit;
}

// --- 3. Serve existing static files (assets, sw.js, vendor) ------------------
// NB: .php files are executed (step 4), never streamed raw.
$requested = $scriptDir . $path;
$reqExt = strtolower(pathinfo($requested, PATHINFO_EXTENSION));
if ($path !== '' && $reqExt !== 'php' && is_file($requested) && is_readable($requested)) {
    $mimes = [
        'js'   => 'text/javascript; charset=utf-8',
        'mjs'  => 'text/javascript; charset=utf-8',
        'css'  => 'text/css; charset=utf-8',
        'svg'  => 'image/svg+xml',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'json' => 'application/json; charset=utf-8',
        'map'  => 'application/json; charset=utf-8',
        'wasm' => 'application/wasm',
    ];
    $ext = strtolower(pathinfo($requested, PATHINFO_EXTENSION));
    $mime = $mimes[$ext] ?? (mime_content_type($requested) ?: 'application/octet-stream');

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($requested));
    header('Cache-Control: public, max-age=3600');
    readfile($requested);
    exit;
}

// --- 4. .php fallback (e.g. /about.php) --------------------------------------
if (substr($path, -4) === '.php' && is_file($scriptDir . $path) && is_readable($scriptDir . $path)) {
    include $scriptDir . $path;
    exit;
}

// --- 5. Nothing matched -----------------------------------------------------
http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo "404 Not Found: " . $path . "\n";
