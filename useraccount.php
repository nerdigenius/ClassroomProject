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
    <script src="http://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script><script src="http://threejs.org/examples/js/libs/stats.min.js"></script>
    <div class="navbar">
        <img onclick="location.href='index.php';"src='logo.png' alt="My" class="appLogo">
        <h1>ClassRoom Booking System</h1>
    </div>
    <div class="userAccount">
        <div class="userinfo">
            <img src="userAccount.svg" class="userIcon" alt="">
            <div style="margin-left: 5px;">
                <p>User Email</p>
                <p>User Name</p>
            </div>
        </div>
        <div>
            <span class="tableHeader">Booked Classrooms: </span>
            <table>
                <tr>
                    <th>Room Number</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
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
            <span class="tableHeader">Booked Classrooms: </span>
            <table>
                <tr>
                    <th>Room Number</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
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
        <div style="display:flex;margin-top: 20px;justify-content:flex-end;width:100%">
            <button style="width: 100px;">Add More</button>
        </div>

    </div>


</body>
<script src="particle.js"></script>
</html>