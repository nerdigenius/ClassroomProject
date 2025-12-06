<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';

require_csrf();

header('Content-Type: application/json');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}




// If not fully authenticated, go to account
if (empty($_SESSION['user_id']) || empty($_SESSION['mfa_passed'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
// Get user data from session variables
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];


$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Basic shape check
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

// Helper validators
$validDate = static function (string $s): bool {
    // Accept YYYY-MM-DD only
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return false;
    [$y, $m, $d] = explode('-', $s);
    return checkdate((int)$m, (int)$d, (int)$y);
};
$validTime = static function (string $s): bool {
    // Accept HH:MM or HH:MM:SS (24h)
    if (!preg_match('/^(2[0-3]|[01]\d):[0-5]\d(:[0-5]\d)?$/', $s)) return false;
    return true;
};

// Prepare the single secure statement:
// Delete from classroombookings where (user_id, booked_item_id, date, time_slot_id)
// matches the provided user, room, date and the time_slot having start_time = ?
$sql = "
DELETE c
FROM classroombookings AS c
JOIN time_slots       AS t ON t.id = c.time_slot_id
WHERE c.user_id = ?
  AND c.booked_item_id = ?
  AND c.`date` = ?
  AND t.start_time = ?
";

// Start a transaction so you can delete multiple rows safely
$link->begin_transaction();

try {
    $stmt = $link->prepare($sql);

    $totalDeleted = 0;
    foreach ($data as $row) {
        // Extract safely and validate
        $roomNumber = $row['roomNumber'] ?? null;
        $date       = $row['date'] ?? null;
        $start_time = $row['start_time'] ?? null;

        // Validate/normalize inputs
        if (!is_numeric($roomNumber)) {
            throw new RuntimeException('Invalid roomNumber');
        }
        $roomNumber = (int)$roomNumber;

        if (!is_string($date) || !$validDate($date)) {
            throw new RuntimeException('Invalid date format (expected YYYY-MM-DD)');
        }

        if (!is_string($start_time) || !$validTime($start_time)) {
            throw new RuntimeException('Invalid time format (expected HH:MM or HH:MM:SS)');
        }
        // Normalize to HH:MM:SS for DB match
        if (strlen($start_time) === 5) {
            $start_time .= ':00';
        }

        // Bind & execute
        // Types: i (user_id), i (roomNumber), s (date), s (start_time)
        $stmt->bind_param('iiss', $user_id, $roomNumber, $date, $start_time);
        $stmt->execute();
        $totalDeleted += $stmt->affected_rows;
    }

    $link->commit();

    echo json_encode([
        'success' => true,
        'deleted_rows' => $totalDeleted,
        'message' => 'Delete operation completed'
    ]);
} catch (Throwable $e) {
    $link->rollback();
    http_response_code(400);

    // Log internal details server-side; avoid exposing them to clients.
    error_log('DeleteClassRoom error: ' . $e->getMessage());
    $payload = [
        'success' => false,
        'message' => 'Delete failed',
    ];
    if (function_exists('is_dev') && is_dev()) {
        $payload['detail'] = $e->getMessage();
    }
    echo json_encode($payload);
}
    // Do something with the data
