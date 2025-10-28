<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';

// Must be logged in and MFA passed
if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
    header('Location: index.php');
    exit();
}

$user_id    = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];

include 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

$g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();

// Generate or reuse secret
if (empty($_SESSION['pending_2fa_secret'])) {
    $_SESSION['pending_2fa_secret'] = $g->generateSecret();
}
$new_secret = $_SESSION['pending_2fa_secret'];

$query = "UPDATE user SET secret_key = ?, `2FA_enabled` = 1 WHERE id = ?";

// ---------- AJAX POST branch ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    require_csrf();

    $code = preg_replace('/\D/', '', $_POST['code'] ?? '');

    if (strlen($code) !== 6) {
        echo json_encode([
            'ok' => false,
            'flash' => ['type' => 'error', 'text' => 'Code must be 6 digits.']
        ]);
        exit();
    }

    if ($g->checkCode($new_secret, $code)) {
        if ($stmt = mysqli_prepare($link, $query)) {
            mysqli_stmt_bind_param($stmt, "si", $new_secret, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                unset($_SESSION['pending_2fa_secret']);
                $_SESSION['2FA_enabled'] = 1;
                echo json_encode([
                    'ok' => true,
                    'redirect' => 'useraccount.php',
                    'flash' => ['type' => 'success', 'text' => '2FA enabled 🎉 Redirecting...']
                ]);
                exit();
            }
        }
        echo json_encode([
            'ok' => false,
            'flash' => ['type' => 'error', 'text' => 'Database error. Please try again.']
        ]);
        exit();
    }

    echo json_encode([
        'ok' => false,
        'flash' => ['type' => 'error', 'text' => 'Invalid code. Please try again.']
    ]);
    exit();
}

// ---------- GET branch: render page ----------
$qrUrl = \Sonata\GoogleAuthenticator\GoogleQrUrl::generate(
    $user_email,
    $new_secret,
    'ClassRoomBookingSystem'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>2FA Setup</title>

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="assets/css/genqrcode.css">

  <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
  <script src="assets/js/genqrcode.js" defer></script>
</head>
<body>
  <div class="twofa-wrapper">
    <h1>2FA Setup</h1>

    <div class="qr-block">
      <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES) ?>" alt="Scan this QR">
    </div>

    <h3>Scan the QR in Google Authenticator / Authy</h3>
    <p>Then enter the 6-digit code below to complete setup.</p>

    <!-- Flash container -->
    <div id="flashBox"></div>

    <form id="codeForm" method="POST" action="genqrcode.php">
      <?= csrf_field(); ?>
      <label for="codeInput">Enter 6-digit code:</label>
      <input type="text" id="codeInput" name="code" inputmode="numeric" required>
      <button type="submit">Confirm</button>
    </form>
  </div>
</body>
</html>
