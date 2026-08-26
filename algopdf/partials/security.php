<?php
/**
 * security.php — centralized HTTP hardening for AlgoPDF.
 *
 * All conversion happens in the browser; nothing is uploaded to the server.
 * These headers harden delivery: strict CSP (no inline scripts, no external
 * origins except the font + library CDNs), no framing, no MIME sniffing,
 * no referrer leak, and hidden PHP errors so paths are never disclosed.
 */
if (realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    exit('Forbidden');
}

// Never leak paths/stack traces to the client.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: no-referrer');
    header('Cross-Origin-Resource-Policy: same-origin');
    header('X-Permitted-Cross-Domain-Policies: none');
    header('X-DNS-Prefetch-Control: off');
    header('X-XSS-Protection: 0');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), usb=()');
    // Effective only over HTTPS; ignored on plain HTTP, safe to send regardless.
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header(
        "Content-Security-Policy: " .
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; " .
        "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com; " .
        "font-src 'self' https://fonts.gstatic.com; " .
        "img-src 'self' blob: data:; " .
        "connect-src 'self' blob: https://cdn.jsdelivr.net; " .
        "worker-src 'self' blob: https://cdn.jsdelivr.net; " .
        "object-src 'none'; " .
        "base-uri 'self'; " .
        "form-action 'self'; " .
        "frame-ancestors 'none'"
    );
}
