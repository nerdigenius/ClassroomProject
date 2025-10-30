<?php
declare(strict_types=1);
$envFile = __DIR__ . '/.env.php';
$ENV = require(file_exists($envFile) ? $envFile : __DIR__ . '/.env.example.php');

function env(string $k, ?string $d=null): ?string { global $ENV; return array_key_exists($k,$ENV)?(string)$ENV[$k]:$d; }
function is_dev(): bool { return strtolower((string)env('APP_ENV','prod')) === 'dev'; }

