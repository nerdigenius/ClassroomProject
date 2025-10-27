<?php

declare(strict_types=1);

// Secure session
// -------------------------------
// Detect if the original request was HTTPS (proxy-aware)
// -------------------------------
// If you're behind a reverse proxy/CDN that terminates TLS, it forwards the original
// scheme via X-Forwarded-Proto. We treat the request as "secure" if either PHP
// sees HTTPS directly or the proxy says it was HTTPS.

$proto  = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
$https  = $_SERVER['HTTPS'] ?? null;
$secure = (!empty($https) && $https !== 'off') || ($proto === 'https');

// -------------------------------
// Optional Composer autoload
// -------------------------------

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) require_once $autoload;

// Bring in env() / is_dev() and DB handles

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/db.php';

// -------------------------------
// Error handling & banner hygiene
// -------------------------------
// Hide tech/version banners in production responses.

if (!is_dev()) {
  @header_remove('X-Powered-By');
  ini_set('expose_php', '0');
}

// Send HSTS only in production and only when the request is actually HTTPS.
// This forces browsers to always use HTTPS for your domain (and subdomains).

if (!is_dev() && $secure) {
  header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// In dev, show errors; in prod, hide from users and log to a file instead.

if (is_dev()) {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(0);
  ini_set('log_errors', '1');
  ini_set('error_log', env('ERROR_LOG', __DIR__ . '/../php-error.log'));
}

// -------------------------------
// Session hardening (safer cookies & IDs)
// -------------------------------
// Name the session cookie (from env), and apply anti-fixation/anti-leak settings

session_name(env('SESSION_NAME', 'crb_session'));
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.sid_length', '64');
ini_set('session.sid_bits_per_character', '6');

// Set cookie attributes. "secure" is based on our HTTPS detection above.
// SameSite=Lax reduces CSRF risk while keeping normal navigation working.

session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',
  'domain'   => env('SESSION_COOKIE_DOMAIN', ''),
  'secure'   => $secure,
  'httponly' => true,
  'samesite' => env('SESSION_SAMESITE', 'Lax'),
]);

// Start the session after configuring everything above.

if (session_status() === PHP_SESSION_NONE) session_start();

// -------------------------------
// Security headers (per request)
// -------------------------------
// Stop MIME sniffing

header("X-Content-Type-Options: nosniff");

// Content Security Policy: allow only your own origin by default,
// specific script CDNs, inline styles (for now), and restrict where
// you can post forms and set the base URL.

$csp  = "default-src 'self'; ";
$csp .= "img-src 'self' data: https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth%3A%2F%2Ftotp%2FClassRoomBookingSystem%40localhost%3Fsecret%3DZCIT5XQ2N4EJFRDM&ecc=M; ";
$csp .= "style-src 'self' 'unsafe-inline'; ";
$csp .= "script-src 'self' https://ajax.googleapis.com https://cdn.jsdelivr.net https://threejs.org; ";
$csp .= "connect-src 'self'; ";
$csp .= "frame-ancestors 'self'; ";
$csp .= "base-uri 'self'; form-action 'self';";

// If we’re on HTTPS, tell browsers to auto-upgrade any http:// subresources.

if ($secure) $csp .= " upgrade-insecure-requests;";
header("Content-Security-Policy: $csp");

// Send only trimmed referrers cross-site, keep full referrers same-site.

header("Referrer-Policy: strict-origin-when-cross-origin");

// Disable powerful browser features you don’t use by default.

header("Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()");

// Prevent caching of authenticated/sensitive pages.

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
