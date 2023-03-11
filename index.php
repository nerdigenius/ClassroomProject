<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClassRoomBooking</title>
    <link rel="stylesheet" href="style.css">

</head>

<body id="particles-js">
    <script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script><script src="http://threejs.org/examples/js/libs/stats.min.js"></script>

    <div  class="navbar">
        <img onclick="location.href='index.php';"src='logo.png' alt="My"class="appLogo">
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
        <div  class="loginItems">
            <div style="width: 100%; display: flex; justify-content: center; height: 50%">
                <form action="" class="loginform">
                    <div class="form-group">
                        <input type="email" id="email" class="form-control" placeholder=" " />
                        <label for="email" class="form-label">Email</label>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" class="form-control" placeholder=" " />
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <a href="">Forgot Password</a>
                </form>
            </div>
            <div style="width: 100%;display: flex;justify-content: space-evenly;align-items:center;flex-direction: column;height: 50%;">
                <button id='login' onclick="Login()">Login</button>
                <button id="signup" onclick="Signup()">Sign Up</button>
                <button>Google</button>
            </div>
        </div>
    </div>
</body>
<script>
    function Signup() {
        location.href = './signup.php';
    }
    function Login(){
        location.href = './useraccount.php';
    }
</script>
<script src="particle.js"></script>

</html>