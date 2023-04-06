<?php
// Start a session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
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
</head>

<body >
    <div id="particles-js" style="position: absolute;height:100%;width:100%;margin:0;display:flex;"></div>
    <script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="http://threejs.org/examples/js/libs/stats.min.js"></script>
    <script src="particle.js"></script>
    <div class="navbar">
        <img onclick="location.href='index.php';" src='logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="userAccount">
        <div class="userinfo">
            <img src="userAccount.svg" class="userIcon" alt="">
            <div style="margin-left: 5px;">
                <p><?php echo $user_email ?></p>
                <p><?php echo $user_name ?></p>
                <form action="logout.php" method="POST" style="width: fit-content;"><button type="submit" style="width: 100px">Logout</button></form>
            </div>
        </div>
        <div >
            <span class="tableHeader">Booked Classrooms: </span>
            <table>
                <tr>
                    <th>Room Number</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
                <?php
                include("config.php");
                $sql = "SELECT * FROM `classroombookings` join `time_slots` on classroombookings.time_slot_id=time_slots.id where user_id='$user_id'";
                $result = $link->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["date"] . "</td><td>" . $row["start_time"] . " to " . $row["end_time"] . "</td><td style='border: 0; width:auto'><button class='buttonTable'>X</button></td></tr>";
                    }
                } else {
                    echo "0 results";
                }
                ?>
                <tr>
                    <td>4008</td>
                    <td>12.10.24</td>
                    <td>2:00 pm to 3:00 pm</td>
                    <td style="border: 0; width:auto"><button class="buttonTable">X</button></td>
                </tr>
                <tr>
                    <td>2018</td>
                    <td>14.10.24</td>
                    <td>11:00 am to 1:00 pm</td>
                    <td style="border: 0; width:auto"><button class="buttonTable">X</button></td>

                </tr>
            </table>
        </div>
        <div>
            <span class="tableHeader">Booked Class Seats: </span>
            <table>
                <tr>
                    <th>Seat Number</th>
                    <th>Room Number</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
                <?php
                $sql = "SELECT * FROM `bookings` join `time_slots` on bookings.time_slot_id=time_slots.id join seats on bookings.booked_item_id =seats.id where user_id='$user_id'";
                $result = $link->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row["id"] . "</td><td>" . $row["room_number"] . "</td><td>" . $row["date"] . "</td><td>" . $row["start_time"] . " to " . $row["end_time"] . "</td><td style='border: 0; width:auto'><button class='buttonTable'>X</button></td></tr>";
                    }
                } else {
                    echo "0 results";
                }
                ?>
                <tr>
                    <td>6</td>
                    <td>6008</td>
                    <td>12.10.24</td>
                    <td>2:00 pm to 3:00 pm</td>
                    <td style="border: 0; width:auto"><button class="buttonTable">X</button></td>
                </tr>
                <tr>
                    <td>9</td>
                    <td>2018</td>
                    <td>14.10.24</td>
                    <td>11:00 am to 1:00 pm</td>
                    <td style="border: 0; width:auto"><button class="buttonTable">X</button></td>

                </tr>
            </table>
        </div>
        <div style="display:flex;margin-top: 20px;justify-content:flex-end;width:100%">
            <button style="width: 100px;" onclick="popup()">Add More</button>
        </div>

    </div>

    <div class="popupContainer" id="popupContainer" onclick="popupClose()">
        <div class="popup">
            <span class="popupSpan">What kind of bookings do you want to make?</span>
            
            <div style="display: flex;justify-content:space-evenly">
            <button class="popupButton" id="ClassRoomBtn" onclick="GoClassRoom()">Classroom</button>
            <button class="popupButton" id="SeatsBtn">Seats</button>
            </div>
            
        </div>
    </div>
</body>
<script>
    function popup(){
        document.getElementById("popupContainer").style.display = "flex";
    }
    function popupClose(){
        document.getElementById("popupContainer").style.display = "none";
    }
    function GoClassRoom(){
        location.href='classRoomBookings.php'
    }

</script>
<script src="particle.js"></script>

</html>