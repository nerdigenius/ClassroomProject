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
    <link rel="stylesheet" href="style.css">
    <title>ClassRoomBooking</title>
    <script src="assets/js/seatBookings.js" defer></script>
     <?= csrf_meta(); ?>
     
</head>

<body>
    <div id="particles-js" style="position: absolute;height:100%;width:100%;margin:0;display:flex;"></div>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <script src="particle.js" defer></script>
    
    <div class="navbar">
        <img onclick="location.href='index.php';" src='logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="userAccount">
        <h1>Select the Seats you want to book</h1>
        <div>
            <label for="setDate">Select a date:</label>
            <input type="date" id="setDate" name="setDate" >
        </div>
        <div div id="classroomTable" style="display:none;">

            <span class="tableHeader">Classrooms list:</span>

            <table id="mainTable">

                <tbody id="classroomTableBody">

                    <tr style='border:none'>
                        <th>Room Number</th>
                        <th>Seat Number</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Seat Capacity</th>
                        <th style='width:20px;background-color:white;outline:none'></th>
                    </tr>



                </tbody>

            </table>
            <div style="display: flex;width:100%;justify-content: flex-end;margin-top:40px">
                <button style="width: 100px;justify-content:flex-end" id="submitBtn">Done!</button>
            </div>
        </div>
    </div>

</body>

</html>