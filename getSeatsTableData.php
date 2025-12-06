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
    http_response_code(400);
    echo json_encode(['error' => 'Date parameter is missing.']);
    exit();
}

// Get the date parameter
$date = (string)$_POST['date'];

// Basic sanity: enforce YYYY-MM-DD to avoid unexpected formats
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format.']);
    exit();
}

// Run query safely
$stmt = $link->prepare("
SELECT s.id, s.room_number, s.seat_number, sc.seat_capacity, ts.start_time, ts.end_time,
       CASE WHEN b.id IS NOT NULL THEN 'booked' ELSE 'available' END AS status
FROM seats s
JOIN (
  SELECT room_number, COUNT(*) AS seat_capacity
  FROM seats
  GROUP BY room_number
) sc ON s.room_number = sc.room_number
CROSS JOIN time_slots ts
LEFT JOIN bookings b
  ON s.id = b.booked_item_id
 AND ts.id = b.time_slot_id
 AND b.date = ?
");
$stmt->bind_param('s', $date);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed.']);
    exit();
}

// 5. Build response
$rows = [];
while ($row = $result->fetch_assoc()) {
    $row['date'] = $date;
    $rows[] = $row;
}

echo json_encode($rows);