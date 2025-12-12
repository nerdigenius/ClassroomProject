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

// 3. Basic per-session throttle to avoid booking spam
// e.g. at most 30 classroom booking submissions every 5 minutes per session.
rate_limit_or_fail('book_classroom', 30, 300);

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Support both old shape: [ {...}, {...} ]
// and new shape: { csrf_token: "...", rows: [ {...}, {...} ] }
if (!is_array($data) || empty($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or empty JSON payload']);
    exit();
}

$rowsPayload = isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : $data;

// Get user data from session variables
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_id = (int) $_SESSION['user_id'];
$results = [];
$allOK   = true;

try {
    // Start a transaction for atomic inserts
    $link->begin_transaction();

    // Prepare reusable statements
    $slotStmt = $link->prepare("SELECT id FROM time_slots WHERE start_time = ? LIMIT 1");
    $insStmt  = $link->prepare("INSERT INTO classroombookings (user_id, booked_item_id, time_slot_id, date)
                                VALUES (?, ?, ?, ?)");

    foreach ($rowsPayload as $row) {
        $roomNumber  = $row['roomNumber']  ?? null;
        $date        = $row['date']        ?? null;
        $start_time  = $row['start_time']  ?? null;

        // ✅ Validate input
        if (!is_numeric($roomNumber)) {
            $results[] = ['roomNumber' => $roomNumber, 'success' => false, 'message' => 'Invalid room number'];
            $allOK = false;
            continue;
        }
        if (empty($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $results[] = ['roomNumber' => $roomNumber, 'success' => false, 'message' => 'Invalid date format'];
            $allOK = false;
            continue;
        }
        if (empty($start_time) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $start_time)) {
            $results[] = ['roomNumber' => $roomNumber, 'success' => false, 'message' => 'Invalid start time'];
            $allOK = false;
            continue;
        }

        // ✅ Get time slot ID safely
        $slotStmt->bind_param('s', $start_time);
        $slotStmt->execute();
        $slotRes = $slotStmt->get_result();
        $slot    = $slotRes ? $slotRes->fetch_assoc() : null;

        if (!$slot) {
            $results[] = ['roomNumber' => $roomNumber, 'success' => false, 'message' => 'Time slot not found'];
            $allOK = false;
            continue;
        }

        $time_slot_id = (int) $slot['id'];

        // ✅ Insert booking
        $insStmt->bind_param('iiis', $user_id, $roomNumber, $time_slot_id, $date);
        if ($insStmt->execute()) {
            $results[] = [
                'roomNumber' => $roomNumber,
                'success' => true,
                'message' => 'Booking created successfully'
            ];
        } else {
            $allOK = false;
            $results[] = [
                'roomNumber' => $roomNumber,
                'success' => false,
                'message' => 'Insert failed (possible duplicate booking)'
            ];
        }
    }

    // Commit or rollback depending on results
    if ($allOK) {
        $link->commit();
    } else {
        $link->rollback();
    }
} catch (Throwable $e) {
    $link->rollback();
    error_log('insertclassroombooking error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit();
}

// ✅ Respond once with JSON
http_response_code($allOK ? 200 : 207); // 207 Multi-Status if partial success
echo json_encode([
    'success' => $allOK,
    'results' => $results
]);