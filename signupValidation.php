<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/config/csrf.php';




// Validate user input
// ...
$json = file_get_contents('php://input');
$data = json_decode($json, true);


foreach ($data as $row) {
    $name =trim($row['username']);
    $email = trim($row['email']);
    $password =$row['password'];
    $retype_password =$row['retype_password'];
}


if ($name != "" and $email != "" and $password != "" and $retype_password != "") {
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
            // Success response
            $response = array('success' => true, 'message' => 'SignUp successful');
            echo json_encode($response);
            exit();
        } else {
            // Failure response for execution failure
            $response = array('success' => false, 'message' => 'SignUp failed at insert: ' . $stmt->error);
            echo json_encode($response);
        }
        $stmt->close();
    }else {
        // Failure response for passwords not matching
        $response = array('success' => false, 'message' => 'Passwords do not match');
        echo json_encode($response);
    }





    // $sql = "INSERT INTO user (name, email, password) VALUES ('$name', '$email', '$password')";

    // if (mysqli_query($link, $sql)) {
    //     // Store user data in session variables
    //     $_SESSION['user_id'] =  mysqli_insert_id($link);
    //     $_SESSION['user_name'] = $name;
    //     $_SESSION['user_email'] = $email;
    //     $response = array('success' => true, 'message' => 'SignUp successful');
    //     echo json_encode($response);
    //     exit();
    // } else {
    //     $response = array('success' => false, 'message' => 'SignUp failed at insert: ' . mysqli_error($link));
    //     echo json_encode($response);
    // }
} else {
    // Failure response for empty fields
    $response = array('success' => false, 'message' => 'SignUp failed field left blank ');
    echo json_encode($response);
}
