<?php
include("config.php");

// Start a session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: index.php');
    exit();
} else {
    // Get user data from session variables
    $user_id = $_SESSION['user_id'];
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];
    $_SESSION['2FA_enabled']=1;
}

include 'vendor/sonata-project/google-authenticator/src/FixedBitNotation.php';
include 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticatorInterface.php';
include 'vendor/sonata-project/google-authenticator/src/GoogleAuthenticator.php';
include 'vendor/sonata-project/google-authenticator/src/GoogleQrUrl.php';

$g = new \Sonata\GoogleAuthenticator\GoogleAuthenticator();

// Generate a new secret key
$new_secret = $g->generateSecret();
$twoFA_enabled = 1; 

// Prepare the SQL statement with placeholders
$query = "UPDATE user SET secret_key = ?, 2FA_enabled = 1 WHERE id = ?";

// Create a prepared statement
if ($stmt = mysqli_prepare($link, $query)) {
    // Bind parameters to the prepared statement
    mysqli_stmt_bind_param($stmt, "si", $new_secret, $user_id);

    // Execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
        
        // Update successful, redirect to profile

    } else {
        echo "Error updating record: " . mysqli_error($link);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    echo "Prepared statement creation failed";
}





// Display the QR code and form to enter the code from Authenticator app
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2FA Setup</title>
</head>

<body>
    <?php echo '<img src="' . $g->getURL('ClassRoomBookingSystem', 'localhost', $new_secret) . '" />'; ?>

    <h3>Enter the code generated by Authenticator App</h3>
    <form id="codeForm">
        <label for="codeInput">Enter code here: </label>
        <input type="text" name="code" id="codeInput" required>
        <input type="submit" value="Confirm">
    </form>

    <script>
        const codeForm = document.getElementById('codeForm');

        codeForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const codeInput = document.getElementById('codeInput').value;

            try {
                const response = await fetch('check.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ code: codeInput })
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.success) {
                        window.alert("Code verification successful.");
                        window.location.href = 'useraccount.php';
                        // Redirect or perform necessary actions upon success
                    } else {
                        window.alert("Incorrect code. Please try again.");
                    }
                } else {
                    console.error('Failed to verify code:', result.message);
                }
            } catch (error) {
                console.error('Error occurred:', error);
            }
        });
    </script>
</body>

</html>