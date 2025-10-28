<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';

header('Content-Type: application/json');

// 1. Auth guard
if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
// 2. CSRF protection
require_csrf();


    // Get user data from session variables
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];


// Check if the date parameter is set
if (!isset($_POST['date'])) {
    echo json_encode(array('error' => 'Date parameter is missing.'));
    exit();
}

// Get the date parameter
$date = $_POST['date'];



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