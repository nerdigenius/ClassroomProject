<?php
// Simple session-based rate limiting utilities.
// Stores counters in $_SESSION['rate_limits'][$key] = ['count' => int, 'reset_at' => int]

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check and update a per-session rate limit bucket.
 *
 * @param string $key           Logical bucket name (e.g. "signup", "book_classroom")
 * @param int    $maxAttempts   Maximum allowed attempts in the window
 * @param int    $windowSeconds Sliding window length in seconds
 *
 * @return bool true if the call is allowed, false if the limit is exceeded
 */
function rate_limit_check(string $key, int $maxAttempts, int $windowSeconds): bool
{
    if (!isset($_SESSION['rate_limits']) || !is_array($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }

    $bucket = $_SESSION['rate_limits'][$key] ?? ['count' => 0, 'reset_at' => 0];
    $now    = time();

    // Reset window if expired
    if ($now >= (int)$bucket['reset_at']) {
        $bucket = [
            'count'    => 0,
            'reset_at' => $now + $windowSeconds,
        ];
    }

    if ($bucket['count'] >= $maxAttempts) {
        // Store back and deny
        $_SESSION['rate_limits'][$key] = $bucket;
        return false;
    }

    // Increment and allow
    $bucket['count']++;
    $_SESSION['rate_limits'][$key] = $bucket;
    return true;
}

/**
 * Enforce a rate limit and send a 429 response on failure.
 *
 * @param string $key
 * @param int    $maxAttempts
 * @param int    $windowSeconds
 */
function rate_limit_or_fail(string $key, int $maxAttempts, int $windowSeconds): void
{
    if (rate_limit_check($key, $maxAttempts, $windowSeconds)) {
        return;
    }

    // On failure, expose remaining wait time (in seconds) for nicer UI feedback.
    $retryAfter = 0;
    if (isset($_SESSION['rate_limits'][$key]) && is_array($_SESSION['rate_limits'][$key])) {
        $bucket = $_SESSION['rate_limits'][$key];
        if (!empty($bucket['reset_at'])) {
            $retryAfter = max(0, (int)$bucket['reset_at'] - time());
        }
    }

    http_response_code(429);
    header('Content-Type: application/json; charset=utf-8');
    if ($retryAfter > 0) {
        header('Retry-After: ' . $retryAfter);
    }

    echo json_encode([
        'success'     => false,
        'message'     => 'Too many requests. Please slow down and try again later.',
        'retry_after' => $retryAfter,
    ]);
    exit();
}






