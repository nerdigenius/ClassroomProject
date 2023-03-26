<?php
include("config.php");
// Start a session if it hasn't been started already
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
    $email = $_POST['email'];
    $password = $_POST['password'];
  
    // Validate user credentials
    $sql = "SELECT * FROM user WHERE email = '$email' AND password = '$password'";
    // $link is the database connection variable
    $result = mysqli_query($link, $sql);
  
    if (mysqli_num_rows($result) == 1) {
      // Login successful
      $user = mysqli_fetch_assoc($result);
  
      // Store user data in session variables
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['name'];
      $_SESSION['user_email'] = $user['email'];
  
      // Redirect to profile page
      header('Location: useraccount.php');
      exit();
    } else {
      // Login failed
      echo "<script>alert('Invalid email or password. Please try again.');</script>";
    }
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
</head>

<body id="particles-js">
    <script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="http://threejs.org/examples/js/libs/stats.min.js"></script>

    <div class="navbar">
        <img onclick="location.href='index.php';" src='logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>

    <div class="login">
        <div class="loginLogo">
            <div>
                <p class="textClass">Book your classroom now!</p>
            </div>
            <div class="loginLogoBackGround">
                <img src="loginLogo.svg" style="width: 100%; height: auto" />
            </div>

            <div class="textClass">
                <p>Signup or login to continue</p>
            </div>
        </div>
        <form class="loginItems" method="post">
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
                    <a href="">Forgot Password</a>
                </div>
            </div>
            <div style="width: 100%;display: flex;justify-content: space-evenly;align-items:center;flex-direction: column;height: 50%;">
                <button id='login' type="submit" name="submit">Login</button>
                <button id="signup" type="button" onclick="Signup()">Sign Up</button>
            </div>
        </form>
    </div>
</body>
<script>
    function Signup(){
        location.href='signup.php'
    }
</script>
<script src="particle.js"></script>

</html>