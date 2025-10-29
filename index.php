<?php

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';

/**
 * - bootstrap.php calls session_start() and defines $link (mysqli)
 * - twoFactor.php will set $_SESSION['mfa_passed']=1 and (re)issue the final login
 * - user table has UNIQUE email, columns: id, name, email, password, 2FA_enabled (TINYINT 0/1)
 */

//reset helper function

function full_reset_session(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

// If already fully authenticated (incl. MFA), send to account
if (!empty($_SESSION['user_id']) && !empty($_SESSION['mfa_passed'])) {
    header('Location: useraccount.php');
    exit();
}

// If user came back to the login page (GET) while in a partial state
// (password OK, 2FA enabled, but MFA not yet passed) -> reset everything.
if (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    !empty($_SESSION['user_id']) &&
    !empty($_SESSION['2FA_enabled']) &&
    empty($_SESSION['mfa_passed'])
) {
    full_reset_session();
}

// Basic per-IP / per-session throttle (bump for failures only)
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['login_last']     = $_SESSION['login_last']     ?? 0;

function too_many_attempts(): bool
{
    // 5 attempts within 10 minutes -> throttle
    return $_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['login_last']) < 600;
}

function flash_and_redirect(string $type, string $text): void
{
    $_SESSION['flash'] = ['type' => $type, 'text' => $text];
    header('Location: ' . ($_SERVER['REQUEST_URI'] ?? 'index.php'), true, 303); // PRG
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_csrf();

    if (too_many_attempts()) {
        flash_and_redirect('error', 'Please try again later.');
    }
    // Collect user input
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '' || strlen($password) > 1024) {
        $_SESSION['login_attempts']++;
        $_SESSION['login_last'] = time();
        flash_and_redirect('error', 'Invalid email or password.');
    }

    $sql = "SELECT id, name, email, password, `2FA_enabled` FROM user WHERE email = ? LIMIT 1";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        error_log('Login prepare failed: ' . $link->error);
        flash_and_redirect('error', 'Something went wrong. Please try again.');
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result ? $result->fetch_assoc() : null;

    // Anti-enumeration: verify against a dummy hash if user not found
    $dummy_hash = password_hash('not-the-password', PASSWORD_DEFAULT);
    $hash_to_check = $user['password'] ?? $dummy_hash;

    if (!password_verify($password, trim((string)$hash_to_check)) || !$user) {
        $_SESSION['login_attempts']++;
        $_SESSION['login_last'] = time();
        flash_and_redirect('error', 'Invalid email or password.');
    }

    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        if ($upd = $link->prepare("UPDATE user SET password = ? WHERE id = ?")) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $uid = (int)$user['id'];
            $upd->bind_param('si', $newHash, $uid);
            $upd->execute();
        }
    }

    // Success: reset throttle and harden session
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_last']     = time();
    session_regenerate_id(true);

    $_SESSION['user_id']      = (int)$user['id'];
    $_SESSION['user_name']    = $user['name'];
    $_SESSION['user_email']   = $user['email'];
    $_SESSION['2FA_enabled']  = (int)$user['2FA_enabled'];

    if (!empty($_SESSION['2FA_enabled'])) {
        header('Location: twoFactor.php');
        exit();
    }

    $_SESSION['mfa_passed'] = 1;
    header('Location: useraccount.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassRoomBooking</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <?php echo csrf_meta(); ?>
    <script src="particle.js" defer></script>
</head>

<body>
    <div id="particles-js" style="position: absolute;height:100%;width:100%;margin:0;display:flex;"></div>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="assets/js/index.js"></script>
    

    <div class="navbar">
        <img  id='appLogo' src='assets/images/logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>


    <div class="login">
        <div class="loginLogo">
            <div>
                <p class="textClass">Book your classroom now!</p>
            </div>
            <div class="loginLogoBackGround">
                <img src="assets/images/loginLogo.svg" style="width: 100%; height: auto" />
            </div>

            <div class="textClass">
                <p>Signup or login to continue</p>
            </div>
        </div>
        <form class="loginItems" method="post">
            <?php if (!empty($_SESSION['flash'])): ?>
                <?php $f = $_SESSION['flash'];
                unset($_SESSION['flash']); ?>
                <div class="flash <?= htmlspecialchars((string)$f['type'], ENT_QUOTES) ?>">
                    <?= htmlspecialchars((string)$f['text'], ENT_QUOTES) ?>
                </div>
            <?php endif; ?>
            <?= csrf_field(); ?>
            <div style="width: 100%; display: flex; justify-content: center; height: 50%">
                <div class="loginform">
                    <div class="form-group">
                        <input type="email" id="email" class="form-control" placeholder=" " name="email" />
                        <label for="email" class="form-label">Email</label>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" class="form-control" placeholder=" " name="password" />
                        <label for="password" class="form-label">Password</label>

                    </div>
                    <button
                        type="button"
                        id="togglePassword"
                        aria-label="Show password"
                        class="pw-button">
                        <svg
                            id="pwIcon"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            width="18" height="18"
                            aria-hidden="true"
                            focusable="false"
                            class="pw-icon-eye">
                            <path
                                fill="none"
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z">
                            </path>
                            <circle
                                cx="12"
                                cy="12"
                                r="3"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">
                            </circle>
                            <!-- slash -->
                            <path id="pwSlash" style="display:none;" d="M3 3l18 18" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        </svg>

                        <span id="pwText" style="margin: 0 10px;">Show Password</span>
                    </button>
                </div>
            </div>
            <div style="width: 100%;display: flex;justify-content: space-evenly;align-items:center;flex-direction: column;height: 50%;">
                <button id='login' type="submit" name="submit">Login</button>
                <button id="signup" type="submit" formaction="signup.php" formmethod="get" formnovalidate>Sign Up</button>

            </div>
        </form>
        <form id="signup-form" action="signup.php" method="get"></form>
    </div>
</body>



</html>