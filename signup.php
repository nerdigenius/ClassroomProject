<?php
include("config.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to profile page
    header('Location: useraccount.php');
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect user input
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate user input
    // ...

    // Insert user data into database
    $sql = "INSERT INTO user (name, email, password) VALUES ('$name', '$email', '$password')";
    // $conn is your database connection variable
    mysqli_query($link, $sql);

    // Store user data in session variables
    $_SESSION['user_id'] =  mysqli_insert_id($link);
      $_SESSION['user_name'] = $name;
      $_SESSION['user_email'] = $email;

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

<body id="particles-js">
    <script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="http://threejs.org/examples/js/libs/stats.min.js"></script>
    <div class="navbar">
        <img onclick="location.href='index.php';" src='logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="login">

        <div style="width: 100%; display: flex; justify-content: center; height: auto;padding:5% 20%" class="signupItems">
            <form method="post" class="loginform">
                <p class="textTop">Signup</p>
                <div class="form-group">
                    <input type="email" id="email" class="form-control"placeholder=" " name="email" />
                    <label for="email" class="form-label">Email</label>
                </div>
                <div class="form-group">
                    <input type="text" id="name" class="form-control"placeholder=" " name="name"/>
                    <label for="name" class="form-label">Name</label>
                </div>
                <div class="form-group">
                    <input type="password" id="password" class="form-control" placeholder=" "name="password" />
                    <label for="password" class="form-label">Password</label>
                </div>
                <div class="form-group">
                    <input type="password" id="retype_password" class="form-control" placeholder=" " />
                    <label for="retype_password" class="form-label">Re-type Password</label>
                </div>
                <button style="width: 100%;" type="submit">Submit</button>

            </form>
        </div>
    </div>
</body>

</html>
<script src="particle.js"></script>