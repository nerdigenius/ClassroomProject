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
        <h1>Select the Seats you want to book</h1>
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
                        <th>Seat Number</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Seat Capacity</th>
                        <th style='width:20px;background-color:white;outline:none'></th>
                    </tr>



                </tbody>

            </table>
            <div style="display: flex;width:100%;justify-content: flex-end;margin-top:40px">
                <button style="width: 100px;justify-content:flex-end" onclick="submitForm()">Done!</button>
            </div>
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
                        let tableHTML = " <tr><th style='border:0'>Room Number</th><th style='border:0'>Seat Number</th><th style='border:0'>Date</th><th style='border:0'>Time</th><th style='border:0'>Seat Capacity</th><th style='width:20px;background-color:white;border:0'></th></tr>";
                        let row = "";
                        console.log(data)
                        for (let i = 0; i < data.length; i++) {
                            if (data[i].status === "booked") {
                                row = "<tr>" +
                                    "<td style='background-color: grey;' id='row1" + i + "'>" + data[i].room_number + "</td>" +
                                    "<td style='background-color: grey;' id='row2" + i + "'>" + data[i].seat_number + "</td>" +
                                    "<td style='background-color: grey;' id='row3" + i + "'>" + data[i].date + "</td>" +
                                    "<td style='background-color: grey;' id='row4" + i + "'>" + "<span id='row31" + i + "'>" + data[i].start_time + "</span>" + " to " + data[i].end_time + "</td>" +
                                    "<td style='background-color: grey;' id='row5" + i + "'>" + data[i].seat_capacity + "</td>" +
                                    "<td style='border: 0; width:auto'>" + "<input type='checkbox' name='' id='checkbox" + i + "'" + "onclick='toggleHighlight(" + i + ")' disabled>" + "</td>"

                                "</tr>";
                            } else {
                                row = "<tr>" +
                                    "<td  id='row1" + i + "'>" + data[i].room_number + "</td>" +
                                    "<td  id='row2" + i + "'>" + data[i].seat_number + "</td>" +
                                    "<td  id='row3" + i + "'>" + data[i].date + "</td>" +
                                    "<td  id='row4" + i + "'>" + "<span id='row31" + i + "'>" + data[i].start_time + "</span>" + " to " + data[i].end_time + "</td>" +
                                    "<td  id='row5" + i + "'>" + data[i].seat_capacity + "</td>" +
                                    "<td style='border: 0; width:auto'>" + "<input type='checkbox' name='' id='checkbox" + i + "'" + "onclick='toggleHighlight(" + i + ")'>" + "</td>"

                                "</tr>";
                            }


                            tableHTML += row;
                        }
                        classroomTableBody.innerHTML = tableHTML;
                    }
                };
                xhr.open("POST", "getSeatsTableData.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                console.log(selectedDate);
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
                document.getElementById("row5" + id).classList.add("highlighted");
            } else {
                document.getElementById("row1" + id).classList.remove("highlighted");
                document.getElementById("row2" + id).classList.remove("highlighted");
                document.getElementById("row3" + id).classList.remove("highlighted");
                document.getElementById("row4" + id).classList.remove("highlighted");
                document.getElementById("row5" + id).classList.remove("highlighted");
            }
        }

        function submitForm() {
            var selectedRows = [];
            var classroomTableBody = document.getElementById("classroomTableBody");
            var rows = classroomTableBody.getElementsByTagName("tr");
            for (var i = 1; i < rows.length; i++) {
                var row = rows[i];
                var checkbox = row.getElementsByTagName("input")[0];
                if (checkbox.checked) {
                    var roomNumber = row.getElementsByTagName("td")[0].innerHTML;
                    var seat_number = row.getElementsByTagName("td")[1].innerHTML;
                    var date = row.getElementsByTagName("td")[2].innerHTML;
                    var start_time = row.getElementsByTagName("td")[3].getElementsByTagName("span")[0].innerHTML;
                    var seatCapacity = row.getElementsByTagName("td")[4].innerHTML;
                    selectedRows.push({
                        roomNumber: roomNumber,
                        seat_number:seat_number,
                        date: date,
                        start_time: start_time,
                        seatCapacity: seatCapacity
                    });
                }
            }
            console.log("mairebaap")

            

            //Send an HTTP request to the server-side script
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        console.log(xhr.responseText);
                        console.log("send success!!!")
                        // location.href='useraccount.php'
                        // Insertion successful, update the UI accordingly
                    } else {
                        console.error(xhr.statusText);
                        console.log("send failed!!!")
                        // Insertion failed, show an error message
                    }
                }
            };
            xhr.open("POST", "insertSeatBookings.php");
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.send(JSON.stringify(selectedRows));
            console.log(selectedRows)
        }
    </script>
</body>

</html>