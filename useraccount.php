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

<body>
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
            <div style="margin-left: 20px;display:flex;flex-direction:column;justify-content: space-evenly;">
                <span class="username">Username: <?php echo $user_name ?></span>
                <button href="resetPassword.php" style="width: 200px;margin-bottom:10px">Reset Password</button>
                <form action="logout.php" method="POST" style="width: fit-content;"><button type="submit" style="width: 200px">Logout</button></form>
                
            </div>
        </div>
        <div>
            <span class="tableHeader">Booked Classrooms: </span>
            <table id="classRoomTable">
                <tbody style="height: fit-content;max-height:400px;">
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
                        $i = 0;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='row1" . $i . "'><td>" . $row["booked_item_id"] . "</td><td>" . $row["date"] . "</td><td>" . "<span>" . $row["start_time"] . "</span>" . " to " . $row["end_time"] . "</td><td style='border: 0; width:auto'><button class='buttonTable' onclick='DeleteClassroom(" . $i . ")'>X</button></td></tr>";
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
                <tbody style="height: fit-content;max-height:400px;">
                    <tr>
                        <th>Seat Number</th>
                        <th>Room Number</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                    <?php
                    $sql = "SELECT date,seats.seat_number,seats.room_number,time_slots.start_time,time_slots.end_time FROM `bookings` join seats on booked_item_id=seats.id join time_slots on time_slot_id=time_slots.id WHERE user_id='$user_id'";
                    $result = $link->query($sql);
                    if ($result->num_rows > 0) {
                        $i = 0;
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr id='row1" . $i . "'><td>" . $row["seat_number"] . "</td><td>" . $row["room_number"] . "</td><td>" . $row["date"] . "</td><td>" . "<span>" . $row["start_time"] . "</span>" . " to " . $row["end_time"] . "</td><td style='border: 0; width:auto'><button class='buttonTable' onclick='DeleteSeats(" . $i . ")'>X</button></td></tr>";
                            $i++;
                        }
                    } else {
                        echo "<tr><td colspan='4'>0 results</td></tr>";
                    }
                    ?>
                </tbody>

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
                <button class="popupButton" id="SeatsBtn" onclick="GoSeats()">Seats</button>
            </div>

        </div>
    </div>
</body>
<script>
    function popup() {
        document.getElementById("popupContainer").style.display = "flex";
    }

    function popupClose() {
        document.getElementById("popupContainer").style.display = "none";
    }

    function GoClassRoom() {
        location.href = 'classRoomBookings.php'
    }

    function GoSeats() {
        location.href = 'seatBookings.php'
    }

    function DeleteSeats(id) {
        var selectedRows = [];
        var classroomTable = document.querySelector("#SeatsTable");
        var row = classroomTable.querySelector("#row1" + id);
        var seat_number = row.getElementsByTagName("td")[0].innerHTML;
        var roomNumber = row.getElementsByTagName("td")[1].innerHTML;
        var date = row.getElementsByTagName("td")[2].innerHTML;
        var start_time = row.getElementsByTagName("td")[3].getElementsByTagName("span")[0].innerHTML;
        selectedRows.push({
            seat_number: seat_number,
            roomNumber: roomNumber,
            date: date,
            start_time: start_time,
        });

        console.log(selectedRows);


        // Send an HTTP request to the server-side script
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    console.log(response['success']);
                    // console.log("send success!!!")
                    if (response['success'] === true) {
                        row.remove();
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
        xhr.open("POST", "DeleteSeats.php");
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(selectedRows));
        console.log(selectedRows)
    }

    function DeleteClassroom(id) {
        var selectedRows = [];
        var classroomTable = document.querySelector("#classRoomTable");
        var row = classroomTable.querySelector("#row1" + id);
        var roomNumber = row.getElementsByTagName("td")[0].innerHTML;
        var date = row.getElementsByTagName("td")[1].innerHTML;
        var start_time = row.getElementsByTagName("td")[2].getElementsByTagName("span")[0].innerHTML;
        selectedRows.push({
            roomNumber: roomNumber,
            date: date,
            start_time: start_time,
        });

        console.log(selectedRows);


        // Send an HTTP request to the server-side script
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    console.log(response['success']);
                    console.log("send success!!!")
                    if (response['success'] === true) {
                        row.remove();
                    }
                    //location.href = 'useraccount.php'
                    // Insertion successful, update the UI accordingly

                } else {
                    console.error(xhr.statusText);
                    console.log("send failed!!!")
                    // Insertion failed, show an error message
                }
            }
        };
        xhr.open("POST", "DeleteClassRoom.php");
        xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(selectedRows));
        console.log(selectedRows)
    }
</script>
<script src="particle.js"></script>

</html>