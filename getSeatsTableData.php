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
$sql = "SELECT s.id, s.room_number, s.seat_number, sc.seat_capacity, ts.start_time, ts.end_time,
CASE WHEN b.id IS NOT NULL THEN 'booked' ELSE 'available' END AS status
FROM seats s
JOIN (
SELECT room_number, COUNT(*) AS seat_capacity
FROM seats
GROUP BY room_number
) sc ON s.room_number = sc.room_number
CROSS JOIN time_slots ts
LEFT JOIN bookings b ON s.id = b.booked_item_id AND ts.id = b.time_slot_id AND b.date = '{$date}';
";
$result = $link->query($sql);
// Generate JSON response
$rows = array();
while ($row = $result->fetch_assoc()) {
    $row['date'] = $date;
    $rows[] = $row;
}

$json_response = json_encode($rows);

// Output the JSON response
header('Content-Type: application/json');
echo $json_response;


?>