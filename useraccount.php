<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';



// If not fully authenticated, go to login
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
    <script src="assets/js/useraccount.js" defer></script>
</head>

<body>
    <div id="particles-js"></div>
    
    <div class="navbar">
        <img id="appLogo" src='assets/images/logo.png' alt="My" class="appLogo" loading="lazy" decoding="async">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="userAccount">
        <div class="userinfo">
            <img src="assets/images/userAccount.svg" class="userIcon" alt="" loading="lazy" decoding="async">
            <div class="user-info-details">
                <span class="username">Username:<?= htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') ?></span>
                <button id="reset_password" class="btn-reset-password">Reset Password</button>
                <form action="logout.php" method="POST" class="logout-form"> <?= csrf_field(); ?><button type="submit" class="btn-logout">Logout</button></form>
                <?php if ($_SESSION['2FA_enabled'] == 0) {
                    echo '<a href="genqrcode.php">Enable 2FA?</a>';
                } ?>

            </div>
        </div>
        <div>
            <span class="tableHeader">Booked Classrooms: </span>
            <table id="classRoomTable">
                <tbody class="tbody-limited">
                    <tr>
                        <th>Room Number</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                    <?php
                    // $sql = "SELECT * FROM `classroombookings` join `time_slots` on classroombookings.time_slot_id=time_slots.id where user_id='$user_id'";
                    // $result = $link->query($sql);

                    // Prepare the SQL statement with placeholders
                    $sql = "SELECT * FROM `classroombookings` 
                    JOIN `time_slots` ON classroombookings.time_slot_id = time_slots.id 
                    WHERE user_id = ?";

                    // Prepare the statement
                    $stmt = $link->prepare($sql);

                    // Bind the parameter
                    $stmt->bind_param("i", $user_id); // Assuming user_id is an integer

                    // Execute the query
                    $stmt->execute();

                    // Get the result
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $i = 0;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='row1" . $i . "'><td>" . $row["booked_item_id"] . "</td><td>" . $row["date"] . "</td><td>" . "<span>" . $row["start_time"] . "</span>" . " to " . $row["end_time"] . "</td><td style='border: 0; width:auto'><button class='buttonTable delete-classroom-btn' >X</button></td></tr>";
                            $i++;
                        }
                    } else {
                        echo "<tr><td colspan='3'>0 results</td></tr>";
                    }
                    ?>
                </tbody>

            </table>
        </div>
        <div>
            <span class="tableHeader">Booked Class Seats: </span>
            <table id="SeatsTable">
                <tbody class="tbody-limited">
                    <tr>
                        <th>Seat Number</th>
                        <th>Room Number</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                    
                    <?php
                    // $sql = "SELECT date,seats.seat_number,seats.room_number,time_slots.start_time,time_slots.end_time FROM `bookings` join seats on booked_item_id=seats.id join time_slots on time_slot_id=time_slots.id WHERE user_id='$user_id'";
                    // $result = $link->query($sql);


                    // Prepare the SQL statement with placeholders
                    $sql = "SELECT date, seats.seat_number, seats.room_number, time_slots.start_time, time_slots.end_time 
                            FROM `bookings` 
                            JOIN seats ON booked_item_id = seats.id 
                            JOIN time_slots ON time_slot_id = time_slots.id 
                            WHERE user_id = ?";

                    // Prepare the statement
                    $stmt = $link->prepare($sql);

                    // Bind the parameter
                    $stmt->bind_param("i", $user_id); // Assuming user_id is an integer

                    // Execute the query
                    $stmt->execute();

                    // Get the result
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $i = 0;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='row2" . $i . "'><td>" . $row["seat_number"] . "</td><td>" . $row["room_number"] . "</td><td>" . $row["date"] . "</td><td>" . "<span>" . $row["start_time"] . "</span>" . " to " . $row["end_time"] . "</td><td style='border: 0; width:auto'><button class='buttonTable delete-seat-btn'>X</button></td></tr>";
                            $i++;
                        }
                    } else {
                        echo "<tr><td colspan='4'>0 results</td></tr>";
                    }
                    ?>
                </tbody>

            </table>
        </div>
        <div class="footer-actions">
            <button class="btn-small" id="popup">Add More</button>
        </div>

    </div>

    <div class="popupContainer" id="popupContainer" >
        <div class="popup">
            <span class="popupSpan">What kind of bookings do you want to make?</span>

            <div class="popup-buttons-row">
                <button class="popupButton" id="ClassRoomBtn" >Classroom</button>
                <button class="popupButton" id="SeatsBtn">Seats</button>
            </div>

        </div>
    </div>
</body>


</html>