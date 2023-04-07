<?php
include("config.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$email;
$password;
$retype_password;
// Validate user input
// ...
$json = file_get_contents('php://input');
$data = json_decode($json, true);
// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    $email = $_SESSION['user_email'];
    foreach ($data as $row) {
        $password = trim($row['password']);
        $retype_password = trim($row['retype_password']);
    }
} else {
    foreach ($data as $row) {
        $email = trim($row['email']);
        $password = trim($row['password']);
        $retype_password = trim($row['retype_password']);
    }
}




$sql2 = "SELECT id from user where email='$email'";
$id_result = $link->query($sql2);

$sql = "UPDATE user SET `password` = '$password' WHERE email='$email'";
if ($email != "" and $password != "" and $retype_password != "") {
    if ($id_result->num_rows > 0) {
        if (mysqli_query($link, $sql)) {
            // Store user data in session variables
            $response = array('success' => true, 'message' => 'Password reset successful');
            echo json_encode($response);
            exit();
        } else {
            $response = array('success' => false, 'message' => 'Password reset failed: ' . mysqli_error($link));
            echo json_encode($response);
        }
    }
    else{
        $response = array('success' => false, 'message' => 'Password reset failed email not found ');
    echo json_encode($response);
    }
} else {
    $response = array('success' => false, 'message' => 'Password reset failed field left blank ');
    echo json_encode($response);
}
