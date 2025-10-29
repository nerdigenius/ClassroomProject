<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';

header('Content-Type: application/json');

require_csrf();

$_SESSION['mfa_attempts'] = $_SESSION['mfa_attempts'] ?? 0;
$_SESSION['mfa_last']     = $_SESSION['mfa_last']     ?? 0;

function mfa_too_many_attempts(): bool
{
    // 5 attempts within 30 seconds -> throttle
    return $_SESSION['mfa_attempts'] >= 5 && (time() - $_SESSION['mfa_last']) < 30;
}

if (mfa_too_many_attempts()) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Try again later.']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Access the posted data sent from the client-side
    $receivedData = json_decode(file_get_contents('php://input'), true);

    if (isset($receivedData['code'])) {
        // Extract the code received from the client-side
        $code = $receivedData['code'];



        // Check if the user is logged in
        if (!isset($_SESSION['user_id'])) {
            // Redirect to login page if not logged in
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit();
        } else {
            // Get user ID from the session
            $user_id = $_SESSION['user_id'];

            // Prepare a query to fetch the secret key from the database for the user
            $stmt = $link->prepare("SELECT secret_key FROM user WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($secret);

            if ($stmt->fetch()) {
                // Include the necessary Google Authenticator files
                include_once 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
                include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
                include_once 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
                include_once 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

                $g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();




                // Check the received code against the retrieved secret
                if ($g->checkCode($secret, $code)) {
                    $_SESSION['mfa_passed'] = true;
                    $_SESSION['mfa_attempts'] = 0;
                    $_SESSION['mfa_last']     = time();
                    echo json_encode(['success' => true, 'message' => 'Code verification successful']);
                    // Perform further actions if the code is correct

                } else {
                    $_SESSION['mfa_attempts']++;
                    $_SESSION['mfa_last'] = time();
                    echo json_encode(['success' => false, 'message' => 'Incorrect or expired code']);
                    exit();
                }
            } else {
                http_response_code(401);

                echo json_encode(['success' => false, 'message' => 'User not found']);
                // Handle scenario where user ID is invalid or not found
            }

            // Close the statement
            $stmt->close();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Incomplete data received']);
        // Handle incomplete data scenario
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    // Handle other HTTP request methods if needed
}
