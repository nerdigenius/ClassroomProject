<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';

// Check if the user is already logged in
if (!empty($_SESSION['user_id']) && !empty($_SESSION['mfa_passed'])) {
    header('Location: useraccount.php'); exit();
}
if(empty($_SESSION['2FA_enabled'])){
    $_SESSION['2FA_enabled'] = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?= csrf_meta(); ?>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <title>ClassRoomBooking</title>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js" defer></script>
    <script src="particle.js" defer></script>
    <script src="assets/js/signup.js" defer></script>
</head>

<body>
    <div id="particles-js"></div>
    
    <div class="navbar">
        <img id="appLogo" src='assets/images/logo.png' alt="My" class="appLogo" loading="lazy" decoding="async">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="login">

        <div class="signupItems">
            <div class="loginform">
                <p class="textTop">Signup</p>
                <div class="form-group">
                    <input type="email" id="email" class="form-control" placeholder=" " name="email" required />
                    <label for="email" class="form-label">Email</label>
                </div>
                <div class="form-group">
                    <input type="text" id="name" class="form-control" placeholder=" " name="name" required />
                    <label for="name" class="form-label">Name</label>
                </div>
                <div class="form-group">
                    <input type="password" id="password" class="form-control" placeholder=" " name="password" required />
                    <label for="password" class="form-label">Password</label>
                </div>
                <div class="form-group">
                    <input type="password" id="retype_password" name="retype_password" class="form-control" placeholder=" "  required />
                    <label for="retype_password" class="form-label">Re-type Password</label>
                </div>
                <span id="error">Password does not match</span>

                <div class="form-group">
                    <label for="enableAuthenticator" class="username">Enable Authenticator?</label>
                    <input type="checkbox" id="enableAuthenticator" name="enableAuthenticator" value="true" />
                </div>
                <button class="btn-fullwidth" id="submitBtn" >Submit</button>

            </div>
        </div>
    </div>
</body>


</html>
