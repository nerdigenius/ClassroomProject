<?php
include("config.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



// Validate user input
// ...
$json = file_get_contents('php://input');
$data = json_decode($json, true);


foreach ($data as $row) {
    $name =trim($row['username']);
    $email = trim($row['email']);
    $password = trim($row['password']);
    $retype_password =trim($row['retype_password']);
}

$sql = "INSERT INTO user (name, email, password) VALUES ('$name', '$email', '$password')";

if ($name != "" and $email != "" and $password != "" and $retype_password != "") {
    if (mysqli_query($link, $sql)) {
        // Store user data in session variables
        $_SESSION['user_id'] =  mysqli_insert_id($link);
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $response = array('success' => true, 'message' => 'SignUp successful');
        echo json_encode($response);
        exit();
    } else {
        $response = array('success' => false, 'message' => 'SignUp failed at insert: ' . mysqli_error($link));
        echo json_encode($response);
    }
} else {
    $response = array('success' => false, 'message' => 'SignUp failed field left blank ');
    echo json_encode($response);
}
