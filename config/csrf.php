<?php
// config/csrf.php
// Reusable (non-rotating) CSRF token with a TTL. Works for forms and AJAX.
declare(strict_types=1);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const CSRF_TTL_SECONDS = 7200; // 2 hours

function csrf_token(): string {
    $now = time();
    if (
        empty($_SESSION['csrf_token']) ||
        empty($_SESSION['csrf_issued_at']) ||
        ($now - (int)$_SESSION['csrf_issued_at']) > CSRF_TTL_SECONDS
    ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_issued_at'] = $now;
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}

function csrf_meta(): string {
    return '<meta name="csrf-token" content="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') .
        '">';
}

/**
 * Validate token sent via form field or header.
 * Accepts only on mutating methods unless you pass a custom list.
 */
function csrf_validate(array $methods = ['POST','PUT','PATCH','DELETE']): bool {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($method, $methods, true)) {
        return true; // no CSRF needed for e.g. GET
    }

    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

    if (empty($token)) return false;
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_issued_at'])) return false;

    $fresh = (time() - (int)$_SESSION['csrf_issued_at']) <= CSRF_TTL_SECONDS;
    return $fresh && hash_equals($_SESSION['csrf_token'], $token);
}

/** Hard-fail helper */
function require_csrf(array $methods = ['POST','PUT','PATCH','DELETE']): void {
    if (!csrf_validate($methods)) {
        http_response_code(400);
        exit('Bad Request: CSRF validation failed.');
    }
}
