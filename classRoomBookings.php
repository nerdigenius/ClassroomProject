<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';


// If not fully authenticated, go to account
if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
  header('Location: index.php');
  exit();
} else {
    // Get user data from session variables
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];
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
    <script src="assets/js/classRoomBookings.js" defer></script>
</head>

<body>
    <div id="particles-js"></div>
    
    <div class="navbar">
        <img id="appLogo" src='assets/images/logo.png' alt="My" class="appLogo" loading="lazy" decoding="async">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="userAccount">
        <h1>Select the classrooms you want to book</h1>
        <div>
            <label for="setDate">Select a date:</label>
            <input type="date" id="setDate" name="setDate" >
        </div>
        <div div id="classroomTable">

            <span class="tableHeader">Classrooms list:</span>

            <table id="mainTable">

                <tbody id="classroomTableBody">

                    <tr style='border:none'>
                        <th>Room Number</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Seat Capacity</th>
                        <th style='width:20px;background-color:white;outline:none'></th>
                    </tr>



                </tbody>

            </table>
            <div class="flex-end-row">
                <button class="btn-small" id="submitBtn">Done!</button>
            </div>
        </div>
    </div>
    
</body>

</html>