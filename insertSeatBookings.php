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
  echo $roomNumber = $row['roomNumber'];
  echo "<br>";
  echo $seat_number = $row['seat_number'];
  echo "<br>";
  echo $date = $row['date'];
  echo "<br>";
  echo $start_time = $row['start_time'];
  echo "<br>";
  echo $seatCapacity = $row['seatCapacity'];
  echo "<br>";
  $time_slot_query = "SELECT id FROM time_slots WHERE start_time = '$start_time'";
  $time_slot_result = mysqli_query($link, $time_slot_query);
  $time_slot_row = mysqli_fetch_assoc($time_slot_result);
  $time_slot_id = $time_slot_row['id'];
  $id_query = "SELECT `id` FROM `seats` WHERE room_number =1 and seat_number=1";
  $id_result = mysqli_query($link, $id_query);
  $id_row = mysqli_fetch_assoc($id_result);
  $id= $id_row['id'];
  $insertion_query="INSERT INTO `bookings`(`user_id`, `booked_item_id`, `time_slot_id`, `date`) VALUES ('$user_id','$id','$time_slot_id','$date')";
  if (mysqli_query($link, $insertion_query)) {
    echo "Success inserts";
  }
  else{
    echo "failed inserts";
  }

  // Do something with the data
}
