<?php
declare(strict_types=1);

// PDO helper
function db(): PDO {
  static $pdo=null;
  if ($pdo instanceof PDO) return $pdo;

  $dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    env('DB_HOST','127.0.0.1'),
    env('DB_PORT','3306'),
    env('DB_NAME',''),
    env('DB_CHARSET','utf8mb4')
  );

  $pdo = new PDO($dsn, env('DB_USER',''), env('DB_PASS',''), [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ]);
  return $pdo;
}

// TEMP: mysqli adapter so existing files using $link keep working
$GLOBALS['link'] = mysqli_connect(env('DB_HOST','127.0.0.1'), env('DB_USER',''), env('DB_PASS',''), env('DB_NAME',''));
if ($GLOBALS['link'] === false) { die('DB connect error: '.mysqli_connect_error()); }
mysqli_set_charset($GLOBALS['link'], env('DB_CHARSET','utf8mb4'));
