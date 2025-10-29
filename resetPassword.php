<?php

declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';
$email    = $_SESSION['user_email'] ?? '';
$disabled = $email !== '' ? 'disabled' : '';
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
    <script src="particle.js" defer></script>
</head>

<body>
    <script src="assets/js/resetPassword.js" defer></script>
    <div id="particles-js" style="position: absolute;height:100%;width:100%;margin:0;display:flex;"></div>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <div class="navbar">
        <img id="appLogo" src='assets/images/logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="login">

        <div style="width: 100%; display: flex; justify-content: center; height: auto;padding:5% 20%" class="signupItems">
            <div class="loginform">
                <p class="textTop">Reset Password</p>
                <div class="form-group">
                    <input type="email" id="email" class="form-control" placeholder=" " name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $disabled ?> />
                    <label for="email" class="form-label">Email</label>
                </div>
                <div class="form-group">
                    <input type="password" id="password" class="form-control" placeholder=" " name="password"  autocomplete="new-password" />
                    <label for="password" class="form-label">Password</label>
                </div>
                <div class="form-group">
                    <input type="password" id="retype_password" name="retype_password" class="form-control" placeholder=" "  autocomplete="new-password"  />
                    <label for="retype_password" class="form-label">Re-type Password</label>
                </div>
                <span style="color: red;display:none" id="error">Password does not match</span>
                <button style="width: 100%;" id="submitBtn" >Submit</button>

            </div>
        </div>
    </div>
</body>

</html>
