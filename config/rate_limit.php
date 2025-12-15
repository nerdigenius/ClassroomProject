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
    global $link;
    if (!$link instanceof mysqli) {
        // If DB down, fail closed (deny) or open (allow)?
        // Security-wise, probably fail open for rate limits to avoid DoS if DB is flaky,
        // OR fail closed if strict. Let's fail open but log it, or just return false (fail closed).
        // For this hardening, let's just ensure we don't crash.
        error_log('rate_limit_ip_check: $link is not available.');
        return false; // Fail closed (block request)
    }
    rate_limit_gc();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ipHash = hash('sha256', $ip); // Anonymize slightly, though not strictly required for local logic
    $now = time();

    // Upsert logic:
    // If record exists and is valid (reset_at > now), increment attempts.
    // If record exists and expired, reset attempts to 1 and set new window.
    // If record doesn't exist, insert new.

    // Try to get current status
    $stmt = $link->prepare("SELECT attempts, reset_at FROM ip_rate_limits WHERE ip_hash = ? AND action_key = ?");
    $stmt->bind_param('ss', $ipHash, $key);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if ($row) {
        // Exists
        if ($now < $row['reset_at']) {
            // Window active
            if ($row['attempts'] >= $maxAttempts) {
                return false; // Blocked
            }
            // Increment
            $upd = $link->prepare("UPDATE ip_rate_limits SET attempts = attempts + 1 WHERE ip_hash = ? AND action_key = ?");
            $upd->bind_param('ss', $ipHash, $key);
            $upd->execute();
            return true;
        } else {
            // Expired -> Reset
            $newReset = $now + $windowSeconds;
            $upd = $link->prepare("UPDATE ip_rate_limits SET attempts = 1, reset_at = ? WHERE ip_hash = ? AND action_key = ?");
            $upd->bind_param('iss', $newReset, $ipHash, $key);
            $upd->execute();
            return true;
        }
    } else {
        // New
        $newReset = $now + $windowSeconds;
        $ins = $link->prepare("INSERT INTO ip_rate_limits (ip_hash, action_key, attempts, reset_at) VALUES (?, ?, 1, ?)");
        $ins->bind_param('ssi', $ipHash, $key, $newReset);
        $ins->execute();
        return true;
    }
}

/**
 * Enforce IP rate limit or die.
 */
function rate_limit_ip_or_fail(string $key, int $maxAttempts, int $windowSeconds): void
{
    if (rate_limit_ip_check($key, $maxAttempts, $windowSeconds)) {
        return;
    }

    http_response_code(429);
    header('Content-Type: application/json; charset=utf-8');
    // For HTML forms like login, we might want a friendly error page or flash message,
    // but the fail-safe default is JSON/Die.
    // If it's an AJAX request:
    if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
         stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
             echo json_encode(['success' => false, 'message' => 'Too many requests from this IP.']);
             exit();
    }
    
    // For standard form posts (like index.php login), we want to show the error on page usually.
    // But this function is generic. The caller can check validation manually if they want custom behavior.
    // For now, we hard stop.
    die('Too many requests. Please try again later.');
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

function rate_limit_or_fail_session(string $key, int $maxAttempts, int $windowSeconds): void
{
     if (rate_limit_check($key, $maxAttempts, $windowSeconds)) {
        return;
    }
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests.']);
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






