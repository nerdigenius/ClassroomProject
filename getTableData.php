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

// Check if the date parameter is set
if (!isset($_POST['date'])) {
    echo json_encode(array('error' => 'Date parameter is missing.'));
    exit();
}

// Get the date parameter
$date = $_POST['date'];

// Get the table data
include("config.php");
$sql = "SELECT `classroom`.`id`,classroom.seat_capacity,time_slots.start_time,time_slots.end_time FROM `classroom`,`time_slots`";
$result = $link->query($sql);
$rows = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rows[] = array(
            'id' => $row["id"],
            'date' => $date,
            'start_time' => $row["start_time"],
            'end_time' => $row["end_time"],
            'seat_capacity'=>$row["seat_capacity"]
        );
    }
}
echo json_encode($rows);
?>