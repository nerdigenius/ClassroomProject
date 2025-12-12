<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/config/rate_limit.php';


header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'This endpoint only accepts POST.'
    ]);
    exit();
}

// Throttle password reset attempts per session to prevent abuse
// e.g. max 5 password changes every 30 minutes.
rate_limit_or_fail('password_reset', 5, 1800);

if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Please log in again.'
    ]);
    exit();
}

//Must be JSON (prevents form submits / random hits)

$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    http_response_code(415); // Unsupported Media Type
    echo json_encode([
        'success' => false,
        'message' => 'Request must be JSON.'
    ]);
    exit();
}

require_csrf();

// 4. Read and validate body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data) || empty($data[0])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Bad request format.'
    ]);
    exit();
}

$row = $data[0];

// We never trust client email for auth-sensitive stuff
$email           = $_SESSION['user_email'];
$password        = trim((string)($row['password'] ?? ''));
$retype_password = trim((string)($row['retype_password'] ?? ''));

if ($password === '' || $retype_password === '') {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Password fields cannot be empty.'
    ]);
    exit();
}

if ($password !== $retype_password) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Passwords do not match.'
    ]);
    exit();
}

// Enforce a minimum password length consistent with signup.
if (strlen($password) < 8) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 8 characters.'
    ]);
    exit();
}

// Confirm account exists
$sqlCheck = "SELECT id FROM user WHERE email = ? LIMIT 1";
$stmtCheck = $link->prepare($sqlCheck);
if (!$stmtCheck) {
    error_log('updatePassword prepare check failed: ' . $link->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
    exit();
}
$stmtCheck->bind_param('s', $email);
$stmtCheck->execute();
$res = $stmtCheck->get_result();
$userRow = $res ? $res->fetch_assoc() : null;

if (!$userRow) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Account not found.']);
    exit();
}

// Hash + update
$newHash = password_hash($password, PASSWORD_DEFAULT);
if ($newHash === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Hash failed.']);
    exit();
}

$sqlUpdate = "UPDATE user SET password = ? WHERE email = ? LIMIT 1";
$stmtUpdate = $link->prepare($sqlUpdate);
if (!$stmtUpdate) {
    error_log('updatePassword prepare update failed: ' . $link->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
    exit();
}
$stmtUpdate->bind_param('ss', $newHash, $email);
$ok = $stmtUpdate->execute();

// Optional: rotate session ID after password change
session_regenerate_id(true);

echo json_encode([
    'success' => ($ok && $stmtUpdate->affected_rows >= 0),
    'message' => $ok ? 'Password reset successful' : 'Password update did not apply.'
]);
exit();