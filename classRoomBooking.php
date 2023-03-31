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
        <h1>Select the classrooms you want to book</h1>
        <div>
            <label for="setDate">Select a date:</label>
            <input type="date" id="setDate" name="setDate" onchange="toggleTable()">
        </div>
        <div div id="classroomTable" style="display:none;">

            <span class="tableHeader">Classrooms list:</span>

            <table>

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
        </div>
    </div>
    <script>
        window.onload = function() {
            var today = new Date().toISOString().split('T')[0];
            document.getElementById("setDate").setAttribute('min', today);
        }

        function toggleTable() {
            var dateInput = document.getElementById("setDate");
            var classroomTable = document.getElementById("classroomTable");
            var classroomTableBody = document.getElementById("classroomTableBody");
            if (dateInput.value !== "") {
                classroomTable.style.display = "block";
                var selectedDate = dateInput.value;
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        data = JSON.parse(xhr.responseText);
                        let tableHTML = " <tr><th style='border:0'>Room Number</th><th style='border:0'>Date</th><th style='border:0'>Time</th><th style='border:0'>Seat Capacity</th><th style='width:20px;background-color:white;border:0'></th></tr>";
                        console.log(data[0]);
                        for (let i = 0; i < data.length; i++) {
                            const row = "<tr>" +
                                "<td id='row1" + i + "'>" + data[i].id + "</td>" +
                                "<td id='row2" + i + "'>" + data[i].date + "</td>" +
                                "<td id='row3" + i + "'>" + data[i].start_time + " to " + data[i].end_time + "</td>" +
                                "<td id='row4" + i + "'>" + data[i].seat_capacity + "</td>" +
                                "<td style='border: 0; width:auto'>" + "<input type='checkbox' name='' id='checkbox"+i+"'"+"onclick='toggleHighlight("+i+")'>" + "</td>"
                            "</tr>";

                            tableHTML += row;
                        }

                        classroomTableBody.innerHTML = tableHTML;
                    }
                };
                xhr.open("POST", "getTableData.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.send("date=" + selectedDate);
            } else {
                classroomTable.style.display = "none";
                classroomTableBody.innerHTML = "";
            }
        }

        function toggleHighlight(id) {
            var checkbox = document.getElementById("checkbox" + id);
            if (checkbox.checked) {
                document.getElementById("row1" + id).classList.add("highlighted");
                document.getElementById("row2" + id).classList.add("highlighted");
                document.getElementById("row3" + id).classList.add("highlighted");
                document.getElementById("row4" + id).classList.add("highlighted");
            } else {
                document.getElementById("row1" + id).classList.remove("highlighted");
                document.getElementById("row2" + id).classList.remove("highlighted");
                document.getElementById("row3" + id).classList.remove("highlighted");
                document.getElementById("row4" + id).classList.remove("highlighted");
            }
        }
    </script>
</body>

</html>