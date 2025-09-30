<?php
declare(strict_types=1);

// (Optional) Composer autoload if you use vendor/
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) require_once $autoload;

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/db.php';

// Errors
if (is_dev()) {
  ini_set('display_errors','1'); error_reporting(E_ALL);
} else {
  ini_set('display_errors','0'); error_reporting(0);
  ini_set('log_errors','1'); ini_set('error_log', env('ERROR_LOG', __DIR__.'/../php-error.log'));
}

// Secure session
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_name(env('SESSION_NAME','crb_session'));
session_set_cookie_params(['lifetime'=>0,'path'=>'/','domain'=>'','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
if (session_status()===PHP_SESSION_NONE) session_start();

// Basic headers
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self' https://ajax.googleapis.com https://cdn.jsdelivr.net https://threejs.org; connect-src 'self'; frame-ancestors 'self';");
