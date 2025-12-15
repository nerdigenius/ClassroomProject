<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/config/rate_limit.php';

// Always return JSON
header('Content-Type: application/json');

// 1. Auth guard
if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
// 2. CSRF protection
require_csrf();

// 3. Session-based throttle for classroom availability lookups:
// at most 120 checks every 5 minutes for this browser session.
rate_limit_or_fail_session('get_classroom_table', 120, 300);





// Get user data from session variables
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Check if the date parameter is set
if (!isset($_POST['date'])) {
    echo json_encode(array('error' => 'Date parameter is missing.'));
    exit();
}

// Get the date parameter
$date = $_POST['date'];

// Basic sanity: yyyy-mm-dd
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format.']);
    exit();
}

// Query safely
$stmt = $link->prepare("
SELECT classroom.id,
       classroom.seat_capacity,
       time_slots.start_time,
       time_slots.end_time,
       IFNULL(bookings.id, 0) AS booking_id
FROM classroom
CROSS JOIN time_slots
LEFT JOIN (
    SELECT classroombookings.id,
           classroombookings.time_slot_id,
           classroombookings.booked_item_id
    FROM classroombookings
    JOIN time_slots ON classroombookings.time_slot_id = time_slots.id
    WHERE classroombookings.date = ?
) AS bookings
  ON classroom.id = bookings.booked_item_id
 AND time_slots.id = bookings.time_slot_id
");
$stmt->bind_param('s', $date);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed.']);
    exit();
}

// 6. Build response
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = [
        'id'            => $row['id'],
        'date'          => $date,
        'start_time'    => $row['start_time'],
        'end_time'      => $row['end_time'],
        'seat_capacity' => $row['seat_capacity'],
        'status'        => ($row['booking_id'] != 0 ? 'booked' : 'available'),
    ];
}

http_response_code(200);
echo json_encode($rows);
exit();
