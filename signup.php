<?php
require_once __DIR__ . '/config/bootstrap.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to profile page
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
    <link rel="stylesheet" href="style.css">
    <title>ClassRoomBooking</title>
</head>

<body>
    <div id="particles-js" style="position: absolute;height:100%;width:100%;margin:0;display:flex;"></div>
    <script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="http://threejs.org/examples/js/libs/stats.min.js"></script>
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
                    <input type="password" id="retype_password" name="retype_password" class="form-control" placeholder=" " onkeyup="TextCheck()" required />
                    <label for="retype_password" class="form-label">Re-type Password</label>
                </div>
                <span style="color: red;display:none" id="error">Password does not match</span>

                <div class="form-group">
                    <label for="enableAuthenticator" class="username" style="font-size: 18px;">Enable Authenticator?</label>
                    <input type="checkbox" id="enableAuthenticator" name="enableAuthenticator" value="true" />
                </div>
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
        let username = document.getElementById("name").value;
        let password = document.getElementById("password").value;
        let email = document.getElementById("email").value;
        let retype_password = document.getElementById("retype_password").value;
        let enableAuthenticator = document.getElementById("enableAuthenticator").checked;

        if (username != "" && password != "" && retype_password != "" && email != "" && password == retype_password) {
            if (isValidEmail(email)) {
                selectedRows.push({
                    username: username,
                    password: password,
                    retype_password: retype_password,
                    email: email,
                    enableAuthenticator: enableAuthenticator
                });
                console.log(selectedRows);


                // Send an HTTP request to the server-side script
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            var response = JSON.parse(xhr.responseText);
                            console.log(response['success']);
                            if (response.success) {
                                location.href = enableAuthenticator?'genqrcode.php':'useraccount.php';
                            } else {
                                window.alert(response['message'])
                            }

                            //console.log(xhr.responseText)
                            //location.href = 'useraccount.php'
                            // Insertion successful, update the UI accordingly

                        } else {
                            console.error(xhr.statusText);
                            console.log("send failed!!!")
                            // Insertion failed, show an error message
                        }
                    }
                };

                xhr.open("POST", "signupValidation.php");
                xhr.setRequestHeader("Content-Type", "application/json");
                xhr.send(JSON.stringify(selectedRows));
                console.log(selectedRows)
            } else {
                window.alert("Invalid Email!!!")
            }
        } else {
            window.alert("Field left empty or Password do not match !!!");
        }

    }
</script>

</html>
<script src="particle.js"></script>