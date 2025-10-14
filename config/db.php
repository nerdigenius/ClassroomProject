<?php
declare(strict_types=1);

/**
 * Return a singleton PDO handle configured for MySQL with safe defaults.
 */
function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  // --- Read connection settings from env (with sane defaults) ---
  $dbHost    = env('DB_HOST','127.0.0.1');
  $dbPort    = env('DB_PORT','3306');
  $dbName    = env('DB_NAME','');
  $dbCharset = env('DB_CHARSET','utf8mb4');
  $dbUser    = env('DB_USER','');
  $dbPass    = env('DB_PASS','');

  if ($dbName === '') {
    throw new RuntimeException('DB_NAME is not set.');
  }

  // DSN includes charset so the connection speaks utf8mb4 end-to-end
  $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $dbHost, $dbPort, $dbName, $dbCharset);

  // Optional session-level tuning to apply right after connect
  $initCmds = [];
  if ($mode = env('DB_SQL_MODE', '')) { $initCmds[] = "SET SESSION sql_mode='{$mode}'"; }
  if ($tz   = env('DB_TIMEZONE', '')) { $initCmds[] = "SET time_zone='{$tz}'"; }
  if ($coll = env('DB_COLLATE',  '')) { $initCmds[] = "SET collation_connection='{$coll}'"; }

  // Safe PDO options: exceptions, real prepares, assoc fetches
  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];
  if ($timeout = (int)(env('DB_TIMEOUT','0') ?? 0)) {
    $options[PDO::ATTR_TIMEOUT] = $timeout; // seconds
  }

  // Create the connection
  $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

  // Apply init commands individually (safer than MYSQL_ATTR_INIT_COMMAND with multiple statements)
  foreach ($initCmds as $sql) {
    $pdo->exec($sql);
  }

  return $pdo;
}

/**
 * TEMP: mysqli adapter so existing files using $link keep working.
 * Enables mysqli exceptions and applies the same charset/tuning.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // throw exceptions on error

$GLOBALS['link'] = mysqli_connect(
  env('DB_HOST','127.0.0.1'),
  env('DB_USER',''),
  env('DB_PASS',''),
  env('DB_NAME',''),
  (int)env('DB_PORT','3306')
);

if ($GLOBALS['link'] === false) {
  die('DB connect error: ' . mysqli_connect_error());
}

// Ensure connection charset is utf8mb4
mysqli_set_charset($GLOBALS['link'], env('DB_CHARSET','utf8mb4'));

// Optional per-session tuning for mysqli connection
if ($mode = env('DB_SQL_MODE','')) {
  mysqli_query($GLOBALS['link'], "SET SESSION sql_mode='{$mode}'");
}
if ($tz = env('DB_TIMEZONE','')) {
  mysqli_query($GLOBALS['link'], "SET time_zone='{$tz}'");
}
if ($coll = env('DB_COLLATE','')) {
  mysqli_query($GLOBALS['link'], "SET collation_connection='{$coll}'");
}
