<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Access the posted data sent from the client-side
    $receivedData = json_decode(file_get_contents('php://input'), true);

    if (isset($receivedData['code'])) {
        // Extract the code received from the client-side
        $code = $receivedData['code'];

        // Start a session if it hasn't been started already
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

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

                $g = new \Google\Authenticator\GoogleAuthenticator();

                
               

                // Check the received code against the retrieved secret
                if ($g->checkCode($secret, $code)) {

                    echo json_encode(['success' => true, 'message' => 'Code verification successful']);
                    // Perform further actions if the code is correct
                } else {
                    echo json_encode(['success' => false, 'message' => 'Incorrect or expired code']);
                    // Handle incorrect/expired code scenario
                }
            } else {
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
