<?php
// Simple session-based rate limiting utilities.
// Stores counters in $_SESSION['rate_limits'][$key] = ['count' => int, 'reset_at' => int]

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Best-effort detection for whether the client expects JSON.
 */
function rate_limit_expects_json(): bool
{
    $xrw = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    if (!empty($xrw) && strtolower($xrw) === 'xmlhttprequest') {
        return true;
    }

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if (stripos($accept, 'application/json') !== false) {
        return true;
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        return true;
    }

    return false;
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

/**
 * Clean up expired rate limit rows (garbage collection).
 * Call this periodically or on chance.
 */
function rate_limit_gc(): void {
    if (random_int(0, 100) > 5) return; // 5% chance
    global $link;
    if (!$link instanceof mysqli) {
        // Fallback or error if DB is not available
        error_log('rate_limit_gc: $link is not a valid mysqli connection.');
        return;
    }
    $link->query("DELETE FROM ip_rate_limits WHERE reset_at < " . time());
}

/**
 * Check and update IP-based rate limit.
 *
 * @param string $key           Logical bucket name
 * @param int    $maxAttempts   Maximum attempts allowed
 * @param int    $windowSeconds Window in seconds
 * @return bool true if allowed, false if limit exceeded
 */
function rate_limit_ip_check(string $key, int $maxAttempts, int $windowSeconds): bool
{
    $status = rate_limit_ip_status($key, $maxAttempts, $windowSeconds);
    return (bool)($status['allowed'] ?? false);
}

/**
 * Check/update IP-based rate limit and return status (includes retry_after when blocked).
 *
 * @return array{allowed: bool, retry_after: int, reset_at: int}
 */
function rate_limit_ip_status(string $key, int $maxAttempts, int $windowSeconds): array
{
    global $link;
    $now = time();

    if (!$link instanceof mysqli) {
        error_log('rate_limit_ip_status: $link is not available.');
        return ['allowed' => false, 'retry_after' => max(1, $windowSeconds), 'reset_at' => $now + max(1, $windowSeconds)];
    }

    rate_limit_gc();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ipHash = hash('sha256', $ip);

    $stmt = $link->prepare("SELECT attempts, reset_at FROM ip_rate_limits WHERE ip_hash = ? AND action_key = ?");
    $stmt->bind_param('ss', $ipHash, $key);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;

    if ($row) {
        $resetAt = (int)$row['reset_at'];
        $attempts = (int)$row['attempts'];

        if ($now < $resetAt) {
            if ($attempts >= $maxAttempts) {
                return ['allowed' => false, 'retry_after' => max(1, $resetAt - $now), 'reset_at' => $resetAt];
            }

            $upd = $link->prepare("UPDATE ip_rate_limits SET attempts = attempts + 1 WHERE ip_hash = ? AND action_key = ?");
            $upd->bind_param('ss', $ipHash, $key);
            $upd->execute();
            return ['allowed' => true, 'retry_after' => 0, 'reset_at' => $resetAt];
        }

        // Expired -> reset
        $newReset = $now + $windowSeconds;
        $upd = $link->prepare("UPDATE ip_rate_limits SET attempts = 1, reset_at = ? WHERE ip_hash = ? AND action_key = ?");
        $upd->bind_param('iss', $newReset, $ipHash, $key);
        $upd->execute();
        return ['allowed' => true, 'retry_after' => 0, 'reset_at' => $newReset];
    }

    // New row
    $newReset = $now + $windowSeconds;
    $ins = $link->prepare("INSERT INTO ip_rate_limits (ip_hash, action_key, attempts, reset_at) VALUES (?, ?, 1, ?)");
    $ins->bind_param('ssi', $ipHash, $key, $newReset);
    $ins->execute();
    return ['allowed' => true, 'retry_after' => 0, 'reset_at' => $newReset];
}

/**
 * Enforce IP rate limit or die.
 */
function rate_limit_ip_or_fail(string $key, int $maxAttempts, int $windowSeconds): void
{
    $status = rate_limit_ip_status($key, $maxAttempts, $windowSeconds);
    if (!empty($status['allowed'])) {
        return;
    }

    $retryAfter = max(1, (int)($status['retry_after'] ?? $windowSeconds));
    http_response_code(429);
    header('Retry-After: ' . $retryAfter);

    $msg = 'Too many requests from this IP.';

    if (rate_limit_expects_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $msg, 'retry_after' => $retryAfter]);
        exit();
    }
    
    // Plain HTML/non-AJAX fallback: include seconds only (client-side formatting not available here)
    die($msg . ' Please try again in ' . $retryAfter . ' seconds.');
}

/**
 * Legacy session-based (kept for compatibility if needed, but discouraged for security critics)
 */
function rate_limit_check(string $key, int $maxAttempts, int $windowSeconds): bool
{
    if (!isset($_SESSION['rate_limits']) || !is_array($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }

    $bucket = $_SESSION['rate_limits'][$key] ?? ['count' => 0, 'reset_at' => 0];
    $now    = time();

    if ($now >= (int)$bucket['reset_at']) {
        $bucket = [
            'count'    => 0,
            'reset_at' => $now + $windowSeconds,
        ];
    }

    if ($bucket['count'] >= $maxAttempts) {
        $_SESSION['rate_limits'][$key] = $bucket;
        return false;
    }

    $bucket['count']++;
    $_SESSION['rate_limits'][$key] = $bucket;
    return true;
}

function rate_limit_session_retry_after(string $key, int $windowSeconds): int
{
    $now = time();
    $bucket = $_SESSION['rate_limits'][$key] ?? null;
    if (!is_array($bucket)) {
        return max(1, $windowSeconds);
    }
    $resetAt = (int)($bucket['reset_at'] ?? 0);
    if ($resetAt <= $now) {
        return max(1, $windowSeconds);
    }
    return max(1, $resetAt - $now);
}

function rate_limit_or_fail_session(string $key, int $maxAttempts, int $windowSeconds): void
{
     if (rate_limit_check($key, $maxAttempts, $windowSeconds)) {
        return;
    }
    $retryAfter = rate_limit_session_retry_after($key, $windowSeconds);
    http_response_code(429);
    header('Retry-After: ' . $retryAfter);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Too many requests.', 'retry_after' => $retryAfter]);
    exit();   
}

/**
 * Backward compatible alias. Kept for existing callers, but new code
 * should prefer rate_limit_or_fail_session() for clarity.
 */
function rate_limit_or_fail(string $key, int $maxAttempts, int $windowSeconds): void
{
   rate_limit_or_fail_session($key, $maxAttempts, $windowSeconds); 
}






