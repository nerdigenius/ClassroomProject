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

    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
    <script src="assets/js/genqrcode.js" defer></script>
    <script src="particle.js" defer></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

</head>

<body>
    <div id="particles-js"></div>
    
    <div class="navbar">
        <img id="appLogo" src='assets/images/logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    
    
    <div class="login qr-login">
        <h1>2FA Setup</h1>
        <!-- Flash container -->
        <div id="flashBox"></div>
        <div class="qr-block">
            <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES) ?>" alt="Scan this QR">
        </div>

        <p class="username" >Scan the QR in Google Authenticator App</p>
        <p class="username" >Then enter the 6-digit code below to complete setup.</p>
        <p class="username" >Download <img src="assets/images/authenticator.svg" class="authenticator-inline-icon" alt=""> GoogleAuthenticator <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&pcampaignid=web_share" class="download-link-large"  target="_blank" >here</a></p>



        <form id="codeForm" method="POST" action="genqrcode.php" class="loginItems code-form">
            <?= csrf_field(); ?>
            <div class="form-group">
                <input type="text" id="codeInput" name="code" class="form-control code-input" inputmode="numeric" placeholder=" " required />
                <label for="codeInput" class="form-label" for="code">Enter 6-digit code:</label>
                
            </div>
            <button type="submit" class="btn-fit">Confirm</button>
        </form>
    </div>
</body>

</html>