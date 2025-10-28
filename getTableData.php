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

// Check if the date parameter is set
if (!isset($_POST['date'])) {
    echo json_encode(array('error' => 'Date parameter is missing.'));
    exit();
}

// Get the date parameter
$date = $_POST['date'];



$sql = "SELECT `classroom`.`id`,classroom.seat_capacity,time_slots.start_time,time_slots.end_time, IFNULL(bookings.id, 0) AS booking_id
FROM `classroom`
CROSS JOIN `time_slots`
LEFT JOIN (
    SELECT `classroombookings`.`id`, `classroombookings`.`time_slot_id`, `classroombookings`.`booked_item_id`
    FROM `classroombookings`
    JOIN `time_slots` ON `classroombookings`.`time_slot_id` = `time_slots`.`id`
    WHERE `classroombookings`.`date` = '{$date}'
) AS bookings ON `classroom`.`id` = `bookings`.`booked_item_id` AND `time_slots`.`id` = `bookings`.`time_slot_id`";
$result = $link->query($sql);
$rows = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $status = ($row['booking_id'] != 0) ? 'booked' : 'available';
        $rows[] = array(
            'id' => $row["id"],
            'date' => $date,
            'start_time' => $row["start_time"],
            'end_time' => $row["end_time"],
            'seat_capacity' => $row["seat_capacity"],
            'status' => $status
        );
    }
}
  echo json_encode($rows);
