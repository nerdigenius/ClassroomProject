<?php

declare(strict_types=1);

/**
 * Return a singleton PDO handle configured for MySQL with safe defaults.
 */
function db(): PDO
{
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  // --- Read connection settings from env (with sane defaults) ---
  $dbHost    = env('DB_HOST', '127.0.0.1');
  $dbPort    = env('DB_PORT', '3306');
  $dbName    = env('DB_NAME', '');
  $dbCharset = env('DB_CHARSET', 'utf8mb4');
  $dbUser    = env('DB_USER', '');
  $dbPass    = env('DB_PASS', '');

  if ($dbName === '') {
    throw new RuntimeException('DB_NAME is not set.');
  }

  // DSN includes charset so the connection speaks utf8mb4 end-to-end
  $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $dbHost, $dbPort, $dbName, $dbCharset);

  // Optional session-level tuning to apply right after connect

  $mode = env('DB_SQL_MODE', '');
  $tz   = env('DB_TIMEZONE', '');
  $coll = env('DB_COLLATE',  '');

  // Safe PDO options: exceptions, real prepares, assoc fetches
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  if ($timeout = (int)(env('DB_TIMEOUT', '0') ?? 0)) {
    $options[PDO::ATTR_TIMEOUT] = $timeout; // seconds
  }

  try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
  } catch (PDOException $e) {
    error_log('DB connect error (PDO): ' . $e->getMessage());
    http_response_code(500);
    exit('Internal Server Error');
  }


  if ($mode !== '') {
    $pdo->exec("SET SESSION sql_mode=" . $pdo->quote($mode));
  }
  if ($tz !== '') {
    $pdo->exec("SET time_zone=" . $pdo->quote($tz));
  }
  if ($coll !== '') {
    $pdo->exec("SET collation_connection=" . $pdo->quote($coll));
  }
  return $pdo;
}

/**
 * TEMP: mysqli adapter so existing files using $link keep working.
 * Enables mysqli exceptions and applies the same charset/tuning.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // throw exceptions on error
try {
  $GLOBALS['link'] = mysqli_connect(
    env('DB_HOST', '127.0.0.1'),
    env('DB_USER', ''),
    env('DB_PASS', ''),
    env('DB_NAME', ''),
    (int)env('DB_PORT', '3306')
  );
} catch (mysqli_sql_exception $e) {
  error_log('DB connect error (mysqli): ' . $e->getMessage());
  http_response_code(500);
  exit('Internal Server Error');
}





// Ensure connection charset is utf8mb4
if (!mysqli_set_charset($GLOBALS['link'], env('DB_CHARSET','utf8mb4'))) {
  error_log('Failed to set mysqli charset');
}

// Optional per-session tuning for mysqli connection
if ($mode = env('DB_SQL_MODE', '')) {
  mysqli_query($GLOBALS['link'], "SET SESSION sql_mode='{$mode}'");
}
if ($tz = env('DB_TIMEZONE', '')) {
  mysqli_query($GLOBALS['link'], "SET time_zone='{$tz}'");
}
if ($coll = env('DB_COLLATE', '')) {
  mysqli_query($GLOBALS['link'], "SET collation_connection='{$coll}'");
}
