<?php
include("config.php");

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

$json = file_get_contents('php://input');
$data = json_decode($json, true);

foreach ($data as $row) {
     $roomNumber = $row['roomNumber'];
     $seat_number=$row['seat_number'];
     $date = $row['date'];
     $start_time = $row['start_time'];

    $time_slot_query = "SELECT id FROM time_slots WHERE start_time = '$start_time'";
    $time_slot_result = mysqli_query($link, $time_slot_query);
    $time_slot_row = mysqli_fetch_assoc($time_slot_result);
    $time_slot_id = $time_slot_row['id'];
    $booked_item_id_query = "SELECT id FROM seats WHERE room_number = '$roomNumber' and seat_number = '$seat_number'";
    $booked_item_id_result = mysqli_query($link, $booked_item_id_query);
    $booked_item_id_row = mysqli_fetch_assoc($booked_item_id_result);
     $booked_item_id = $booked_item_id_row['id'];
     $insertion_query = "DELETE  FROM `bookings` WHERE user_id='$user_id' and booked_item_id='$booked_item_id' and date = '$date' and time_slot_id ='$time_slot_id'";

    $query_result = mysqli_query($link, $insertion_query);
    if ($query_result) {
        $response = array('success' => true, 'message' => 'Delete successful');
        echo json_encode($response);
    } else {
        $response = array('success' => false, 'message' => 'Delete failed: ' . mysqli_error($link));
        echo json_encode($response);
    }

    // Do something with the data
}
