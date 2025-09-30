<?php
return [
  'APP_ENV'   => 'dev',        // dev | prod
  'APP_URL'   => 'http://localhost',

  // DB
  'DB_DRIVER' => 'mysql',
  'DB_HOST'   => '127.0.0.1',
  'DB_PORT'   => '3306',
  'DB_NAME'   => 'classroombooking',
  'DB_USER'   => 'root',
  'DB_PASS'   => '',
  'DB_CHARSET'=> 'utf8mb4',
  'DB_COLLATE'=> 'utf8mb4_unicode_ci',

  // Session
  'SESSION_NAME' => 'crb_session',

  // Logging
  'ERROR_LOG' => __DIR__ . '/php-error.log',
];
