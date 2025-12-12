<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';
require_once __DIR__ . '/config/rate_limit.php';

require_csrf();

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

// Throttle delete operations to prevent abuse
// e.g. at most 60 seat delete actions every 10 minutes.
rate_limit_or_fail('delete_seat_booking', 60, 600);
// Get user data from session variables
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];


$json = file_get_contents('php://input');
$data = json_decode($json, true);

$valid_date = static function ($s) {
  $dt = DateTime::createFromFormat('Y-m-d', $s);
  return $dt && $dt->format('Y-m-d') === $s;
};
$valid_time = static function ($s) {
  // accept "HH:MM" or "HH:MM:SS"
  if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $s) !== 1) return false;
  $fmt = strlen($s) === 5 ? 'H:i' : 'H:i:s';
  $dt = DateTime::createFromFormat($fmt, $s);
  return $dt && $dt->format($fmt) === $s;
};
$valid_room_or_seat = static function ($s) {
  // adjust to your schema; here we accept digits/letters/dashes
  return is_string($s) && strlen($s) <= 64 && preg_match('/^[A-Za-z0-9\-]+$/', $s) === 1;
};

mysqli_begin_transaction($link);

try {
  // Prepare once, reuse in the loop
  $stmtTime = mysqli_prepare($link, "SELECT id FROM time_slots WHERE start_time = ?");
  $stmtSeat = mysqli_prepare($link, "SELECT id FROM seats WHERE room_number = ? AND seat_number = ?");
  $stmtDel  = mysqli_prepare($link, "DELETE FROM bookings 
                                     WHERE user_id = ? AND booked_item_id = ? AND date = ? AND time_slot_id = ?");

  if (!$stmtTime || !$stmtSeat || !$stmtDel) {
    throw new RuntimeException('Failed to prepare statements');
  }

  $results = [];
  foreach ($data as $i => $row) {
    // Defensive checks for required keys
    $roomNumber = $row['roomNumber']  ?? null;
    $seat_number = $row['seat_number'] ?? null;
    $date = $row['date'] ?? null;
    $start_time = $row['start_time'] ?? null;

    if (!$roomNumber || !$seat_number || !$date || !$start_time) {
      $results[] = ['index' => $i, 'success' => false, 'message' => 'Missing required fields'];
      continue;
    }
    if (!$valid_room_or_seat($roomNumber) || !$valid_room_or_seat($seat_number)) {
      $results[] = ['index' => $i, 'success' => false, 'message' => 'Invalid room or seat format'];
      continue;
    }
    if (!$valid_date($date) || !$valid_time($start_time)) {
      $results[] = ['index' => $i, 'success' => false, 'message' => 'Invalid date or time format'];
      continue;
    }

    // Look up time_slot_id
    mysqli_stmt_bind_param($stmtTime, 's', $start_time);
    if (!mysqli_stmt_execute($stmtTime)) {
      throw new RuntimeException('Failed to execute time slot lookup');
    }
    mysqli_stmt_bind_result($stmtTime, $time_slot_id);
    $time_slot_id = null;
    mysqli_stmt_store_result($stmtTime);
    if (mysqli_stmt_num_rows($stmtTime) > 0) {
      mysqli_stmt_fetch($stmtTime);
    }
    mysqli_stmt_free_result($stmtTime);

    if ($time_slot_id === null) {
      $results[] = ['index' => $i, 'success' => false, 'message' => 'Unknown time slot'];
      continue;
    }

    // Look up seat id
    mysqli_stmt_bind_param($stmtSeat, 'ss', $roomNumber, $seat_number);
    if (!mysqli_stmt_execute($stmtSeat)) {
      throw new RuntimeException('Failed to execute seat lookup');
    }
    mysqli_stmt_bind_result($stmtSeat, $booked_item_id);
    $booked_item_id = null;
    mysqli_stmt_store_result($stmtSeat);
    if (mysqli_stmt_num_rows($stmtSeat) > 0) {
      mysqli_stmt_fetch($stmtSeat);
    }
    mysqli_stmt_free_result($stmtSeat);

    if ($booked_item_id === null) {
      $results[] = ['index' => $i, 'success' => false, 'message' => 'Seat not found'];
      continue;
    }

    // Perform the delete
    mysqli_stmt_bind_param($stmtDel, 'iisi', $user_id, $booked_item_id, $date, $time_slot_id);
    if (!mysqli_stmt_execute($stmtDel)) {
      throw new RuntimeException('Failed to execute delete');
    }

    $affected = mysqli_stmt_affected_rows($stmtDel);
    if ($affected > 0) {
      $results[] = ['index' => $i, 'success' => true, 'message' => 'Delete successful'];
    } else {
      // Nothing matched — not necessarily an error
      $results[] = ['index' => $i, 'success' => false, 'message' => 'No matching booking to delete'];
    }
  }

  mysqli_commit($link);
  echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
  mysqli_rollback($link);
  http_response_code(500);

  // Avoid leaking internal error details to the client.
  error_log('DeleteSeats error: ' . $e->getMessage());
  $payload = ['success' => false, 'message' => 'Server error'];
  if (function_exists('is_dev') && is_dev()) {
    $payload['detail'] = $e->getMessage();
  }
  echo json_encode($payload);
} finally {
  if (isset($stmtTime) && $stmtTime) mysqli_stmt_close($stmtTime);
  if (isset($stmtSeat) && $stmtSeat) mysqli_stmt_close($stmtSeat);
  if (isset($stmtDel) && $stmtDel)   mysqli_stmt_close($stmtDel);
}
