<?php
declare(strict_types=1);
ob_start();


// If your project bootstraps headers/CSP, you can keep this:
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';




// 1) Clear all session data
session_unset();

// 2) Delete the session cookie (must match the cookie params used at login)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    // Expire the cookie
    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path']     ?? '/',
            'domain'   => $params['domain']   ?? '',
            'secure'   => (bool)($params['secure'] ?? false),
            'httponly' => (bool)($params['httponly'] ?? true),
            'samesite' => $params['samesite'] ?? 'Lax',
        ]
    );
}

// 3) Kill the server-side session
session_destroy();

// 4) Extra hardening: close, then rotate to a fresh anonymous session id
session_write_close();
session_start();
session_regenerate_id(true);

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'redirect' => 'index.php']);
    exit();
}

// 5) Redirect home
header('Location: index.php');
ob_end_clean();
exit();
