<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';


// If not fully authenticated, go to account
if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
    header('Location: index.php');
    exit();
} {
    $email = $_SESSION['user_email'];
    $disabled = "disabled";
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
</head>

<body>
    <div id="particles-js" style="position: absolute;height:100%;width:100%;margin:0;display:flex;"></div>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <div class="navbar">
        <img onclick="location.href='index.php';" src='logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="login">

        <div style="width: 100%; display: flex; justify-content: center; height: auto;padding:5% 20%" class="signupItems">
            <div class="loginform">
                <p class="textTop">Reset Password</p>
                <div class="form-group">
                    <input type="email" id="email" class="form-control" placeholder=" " name="email" value="<?php echo $email ?>" <?php echo $disabled ?> />
                    <label for="email" class="form-label">Email</label>
                </div>
                <div class="form-group">
                    <input type="password" id="password" class="form-control" placeholder=" " name="password" />
                    <label for="password" class="form-label">Password</label>
                </div>
                <div class="form-group">
                    <input type="password" id="retype_password" name="retype_password" class="form-control" placeholder=" " onkeyup="TextCheck()" />
                    <label for="retype_password" class="form-label">Re-type Password</label>
                </div>
                <span style="color: red;display:none" id="error">Password does not match</span>
                <button style="width: 100%;" onclick="validate()">Submit</button>

            </div>
        </div>
    </div>
</body>
<script>
    function TextCheck() {
        let password = document.getElementById('password').value;
        let retype_password = document.getElementById('retype_password').value;
        if (password === retype_password) {
            document.getElementById('error').style.display = 'none';
        } else {
            document.getElementById('error').style.display = 'block';
        }
    }

    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    function validate() {
        var selectedRows = [];
        let password = document.getElementById("password").value;
        let email = document.getElementById("email").value;
        let retype_password = document.getElementById("retype_password").value;
        
        if (password != "" && retype_password != "" && email != "") {
            if (isValidEmail(email)) {
                selectedRows.push({
                    password: password,
                    retype_password: retype_password,
                });
                console.log(selectedRows);

                //read the CSRF token from the meta tag we added

                var tokenEl = document.querySelector('meta[name="csrf-token"]');
                var csrfToken = tokenEl ? tokenEl.getAttribute('content') : '';

                // Send an HTTP request to the server-side script
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            console.log(response['success']);
                            if (response.success) {
                                location.href = 'useraccount.php';
                            } else {
                                window.alert(response['message'])
                            }

                            // console.log(xhr.responseText)
                            //location.href = 'useraccount.php'
                            // Insertion successful, update the UI accordingly

                        } else {
                            console.error(xhr.statusText);
                            console.log("send failed!!!")
                            // Insertion failed, show an error message
                        }
                    }
                };
                xhr.open("POST", "updatePassword.php");
                xhr.setRequestHeader("Content-Type", "application/json");
                //send the CSRF token in the header that csrf.php expects
                xhr.setRequestHeader("X-CSRF-Token", csrfToken);
                xhr.send(JSON.stringify(selectedRows));
                console.log(selectedRows)
            } else {
                window.alert("Invalid Email!!!")
            }
        } else {
            window.alert("Field left empty!!!");
        }

    }
</script>

</html>
<script src="particle.js"></script>