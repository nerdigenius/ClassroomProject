<?php

declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';
header('Content-Type: application/json');




// Read raw request body and parse JSON

$json = file_get_contents('php://input');
$data = json_decode($json, true);
require_csrf();

// Must be valid JSON object
if (!is_array($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request body'
    ]);
    exit();
}

// Extract / normalize fields
$name            = isset($data['username']) ? trim($data['username']) : '';
$email           = isset($data['email']) ? trim($data['email']) : '';
$password        = $data['password'] ?? '';
$retype_password = $data['retype_password'] ?? '';

$checkStmt = $link->prepare("SELECT id FROM user WHERE email = ? LIMIT 1");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already in use']);
    exit();
}
$checkStmt->close();


if ($name != "" and $email != "" and $password != "" and $retype_password != "") {
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit();
    }
    if ($password === $retype_password) {
        // Hashed the password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Using prepared statement to prevent SQL injection
        $stmt = $link->prepare("INSERT INTO user (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            // Store user data in session variables
            $_SESSION['user_id'] = mysqli_insert_id($link);
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['mfa_passed'] = 1;
            // Success response
            $response = array('success' => true, 'message' => 'SignUp successful');
            echo json_encode($response);
            exit();
        } else {
            // Failure response for execution failure
            $response = array('success' => false, 'message' => 'SignUp failed try again later');
            echo json_encode($response);
        }
        $stmt->close();
    } else {
        // Failure response for passwords not matching
        $response = array('success' => false, 'message' => 'Passwords do not match');
        echo json_encode($response);
    }
} else {
    // Failure response for empty fields
    $response = array('success' => false, 'message' => 'SignUp failed field left blank ');
    echo json_encode($response);
}
