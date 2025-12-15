<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/config/rate_limit.php';


header('Content-Type: application/json');

// 1. Auth guard
if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}
// 2. CSRF protection
require_csrf();

// 3. Session-based throttle to avoid booking spam:
// at most 30 seat booking submissions every 5 minutes for this browser session.
rate_limit_or_fail_session('book_seat', 30, 300);


$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Support both old shape: [ {...}, {...} ]
// and new shape: { csrf_token: \"...\", rows: [ {...}, {...} ] }
if (!is_array($data) || empty($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or empty JSON payload']);
    exit();
}

$rowsPayload = isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : $data;

$user_id = (int)$_SESSION['user_id'];
$results = [];
$allOK   = true;

try {
    // Begin transaction
    $link->begin_transaction();

    // Prepare reusable statements
    $getSlot = $link->prepare("SELECT id FROM time_slots WHERE start_time = ? LIMIT 1");
    $getSeat = $link->prepare("SELECT id FROM seats WHERE room_number = ? AND seat_number = ? LIMIT 1");
    $insert  = $link->prepare("INSERT INTO bookings (user_id, booked_item_id, time_slot_id, date)
                               VALUES (?, ?, ?, ?)");

    foreach ($rowsPayload as $row) {
        $roomNumber = $row['roomNumber'] ?? null;
        $seatNumber = $row['seat_number'] ?? null;
        $date       = $row['date'] ?? null;
        $start_time = $row['start_time'] ?? null;

        // Basic validation
        if (!is_numeric($roomNumber) || !is_numeric($seatNumber)) {
            $results[] = [
                'roomNumber' => $roomNumber,
                'seatNumber' => $seatNumber,
                'success' => false,
                'message' => 'Invalid room or seat number'
            ];
            $allOK = false;
            continue;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date ?? '')) {
            $results[] = [
                'roomNumber' => $roomNumber,
                'seatNumber' => $seatNumber,
                'success' => false,
                'message' => 'Invalid date format'
            ];
            $allOK = false;
            continue;
        }

        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $start_time ?? '')) {
            $results[] = [
                'roomNumber' => $roomNumber,
                'seatNumber' => $seatNumber,
                'success' => false,
                'message' => 'Invalid start time'
            ];
            $allOK = false;
            continue;
        }

        // Get time slot ID
        $getSlot->bind_param('s', $start_time);
        $getSlot->execute();
        $slotRes = $getSlot->get_result();
        $slot    = $slotRes ? $slotRes->fetch_assoc() : null;

        if (!$slot) {
            $results[] = [
                'roomNumber' => $roomNumber,
                'seatNumber' => $seatNumber,
                'success' => false,
                'message' => 'Time slot not found'
            ];
            $allOK = false;
            continue;
        }
        $time_slot_id = (int)$slot['id'];

        // Get seat ID
        $getSeat->bind_param('ii', $roomNumber, $seatNumber);
        $getSeat->execute();
        $seatRes = $getSeat->get_result();
        $seat    = $seatRes ? $seatRes->fetch_assoc() : null;

        if (!$seat) {
            $results[] = [
                'roomNumber' => $roomNumber,
                'seatNumber' => $seatNumber,
                'success' => false,
                'message' => 'Seat not found'
            ];
            $allOK = false;
            continue;
        }
        $seat_id = (int)$seat['id'];

        // Insert booking
        $insert->bind_param('iiis', $user_id, $seat_id, $time_slot_id, $date);
        if ($insert->execute()) {
            $results[] = [
                'roomNumber' => $roomNumber,
                'seatNumber' => $seatNumber,
                'success' => true,
                'message' => 'Booking successful'
            ];
        } else {
            $allOK = false;
            $results[] = [
                'roomNumber' => $roomNumber,
                'seatNumber' => $seatNumber,
                'success' => false,
                'message' => 'Insert failed (maybe duplicate booking)'
            ];
        }
    }

    // Commit or rollback transaction
    if ($allOK) {
        $link->commit();
    } else {
        $link->rollback();
    }

    http_response_code($allOK ? 200 : 207);
    echo json_encode(['success' => $allOK, 'results' => $results]);
} catch (Throwable $e) {
    $link->rollback();
    error_log('insertseatbookings error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit();
}