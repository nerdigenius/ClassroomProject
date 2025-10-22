<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';

// Check if the user is already logged in
if (!empty($_SESSION['user_id']) && !empty($_SESSION['mfa_passed'])) {
    header('Location: useraccount.php'); exit();
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
    <title>ClassRoomBooking</title>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
      <script src="assets/js/signup.js" defer></script>
</head>

<body>
    <div id="particles-js" style="position: absolute;height:100%;width:100%;margin:0;display:flex;"></div>
    
    <div class="navbar">
        <img onclick="location.href='index.php';" src='logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="login">

        <div style="width: 100%; display: flex; justify-content: center; height: auto;padding:5% 20%" class="signupItems">
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
                <span style="color: red;display:none" id="error">Password does not match</span>

                <div class="form-group">
                    <label for="enableAuthenticator" class="username" style="font-size: 18px;">Enable Authenticator?</label>
                    <input type="checkbox" id="enableAuthenticator" name="enableAuthenticator" value="true" />
                </div>
                <button style="width: 100%;" id="submitBtn" >Submit</button>

            </div>
        </div>
    </div>
</body>


</html>
<script src="particle.js"></script>