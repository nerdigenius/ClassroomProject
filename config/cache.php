<?php

declare(strict_types=1);

/**
 * Very small file-based cache (no dependencies).
 *
 * - Stores cache files in the OS temp dir (keeps repo clean).
 * - Designed for short-lived caching of expensive DB-backed endpoints.
 */

function crb_cache_dir(): string
{
    $base = rtrim((string)sys_get_temp_dir(), DIRECTORY_SEPARATOR);
    $dir  = $base . DIRECTORY_SEPARATOR . 'crb_cache';

    if (!is_dir($dir)) {
        // Best-effort: don't fail the request if cache dir creation fails.
        @mkdir($dir, 0777, true);
    }

    return $dir;
}

function crb_cache_path(string $key): string
{
    // Hash to keep filenames short and safe on Windows.
    return crb_cache_dir() . DIRECTORY_SEPARATOR . sha1($key) . '.cache';
}

/**
 * @return mixed|null Cached value (null on miss/expired).
 */
function crb_cache_get(string $key, int $ttlSeconds, ?bool &$hit = null)
{
    $hit = false;
    if ($ttlSeconds <= 0) return null;

    $path = crb_cache_path($key);
    if (!is_file($path)) return null;

    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') return null;

    $payload = @unserialize($raw);
    if (!is_array($payload) || !isset($payload['t'])) return null;

    $created = (int)$payload['t'];
    if ($created + $ttlSeconds < time()) {
        @unlink($path);
        return null;
    }

    $hit = true;
    return $payload['v'] ?? null;
}

/**
 * @param mixed $value
 */
function crb_cache_set(string $key, $value): void
{
    $path = crb_cache_path($key);
    $tmp  = $path . '.' . uniqid('tmp', true);

    $payload = serialize(['t' => time(), 'v' => $value]);

    // Atomic-ish write
    if (@file_put_contents($tmp, $payload, LOCK_EX) !== false) {
        @rename($tmp, $path);
    } else {
        @unlink($tmp);
    }
}

function crb_cache_delete(string $key): void
{
    $path = crb_cache_path($key);
    if (is_file($path)) @unlink($path);
}

