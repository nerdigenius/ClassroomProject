<?php
// config/csrf.php
// Long-lived CSRF token with a TTL; works for forms and AJAX.
// (Token refreshes automatically after the TTL expires.)

declare(strict_types=1);



const CSRF_TTL_SECONDS = 7200; // 2 hours

/**
 * Get the current CSRF token, minting a new one if missing or expired.
 * - Stores the token and its issue time in the session.
 * - 32 random bytes -> 64 hex chars; safe to embed in HTML/JSON.
 */

function csrf_token(): string
{
    $now = time();
    if (
        empty($_SESSION['csrf_token']) ||
        empty($_SESSION['csrf_issued_at']) ||
        ($now - (int)$_SESSION['csrf_issued_at']) > CSRF_TTL_SECONDS
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_issued_at'] = $now;
        $_SESSION['csrf_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    return $_SESSION['csrf_token'];
}

/**
 * Convenience helper for server-rendered forms.
 * Usage: inside <form> ... <?= csrf_field(); ?> ...
 */

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}

/**
 * Convenience helper for adding a meta tag for fetch/AJAX.
 * Usage: in <head> ... <?= csrf_meta(); ?> ...
 */

function csrf_meta(): string
{
    return '<meta name="csrf-token" content="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}

/**
 * Extract a CSRF token from the current request.
 * Order of precedence:
 *   1) X-CSRF-Token header (best for fetch/AJAX)
 *   2) POST form field named "csrf_token"
 *   3) JSON body property "csrf_token" (when Content-Type: application/json)
 */

function csrf_token_from_request(): ?string
{
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) return $_SERVER['HTTP_X_CSRF_TOKEN'];
    if (isset($_POST['csrf_token'])) return (string)$_POST['csrf_token'];

    $ctype = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ctype, 'application/json') !== false) {
        static $raw = null;                    // avoid re-reading php://input
        if ($raw === null) $raw = file_get_contents('php://input');
        $j = json_decode($raw, true);
        if (is_array($j) && isset($j['csrf_token'])) return (string)$j['csrf_token'];
    }
    return null;
}

/**
 * Validate CSRF for mutating methods (POST/PUT/PATCH/DELETE by default).
 * Returns:
 *   - true  if method is non-mutating OR token matches and is fresh
 *   - false otherwise
 */

function csrf_validate(array $methods = ['POST', 'PUT', 'PATCH', 'DELETE']): bool
{
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';


    // Skip checks for non-mutating methods unless a custom list says otherwise.

    if (!in_array($method, $methods, true)) {
        return true;
    }

    // Get token from header/form/JSON (whichever is present).

    $token = csrf_token_from_request();
    if (empty($token)) return false;

    // Basic presence & session state.


    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_issued_at'])) return false;

    if (isset($_SESSION['csrf_user_agent']) && $_SESSION['csrf_user_agent'] !== '') {
        $currUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if ($currUA !== $_SESSION['csrf_user_agent']) {
            return false;
        }
    }




    $fresh = (time() - (int)$_SESSION['csrf_issued_at']) <= CSRF_TTL_SECONDS;
    return $fresh && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Hard-fail helper for endpoints:
 * Call at the top of any handler that changes state.
 * Example:
 *   require_once __DIR__.'/config/bootstrap.php';
 *   require_once __DIR__.'/config/csrf.php';
 *   require_csrf(); // before reading inputs
 */

function require_csrf(array $methods = ['POST', 'PUT', 'PATCH', 'DELETE']): void
{
    if (!csrf_validate($methods)) {
        http_response_code(403);
        exit('Bad Request: CSRF validation failed.');
    }
}
