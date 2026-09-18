<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');
requireStrictPost();

$new_username = $_POST['username'];
$new_email = $_POST['email'];
if(!empty($new_username) && !empty($new_email)){
    
    $old_username = $user_data['username'];
    $old_email = $user_data['email'];
    if($new_username != $old_username || $new_email != $old_email){

        $query = "select * from users where username = '$new_username'";
        $query1 = "select * from users where email = '$new_email'";
        if(mysqli_num_rows(mysqli_query($con, $query))*mysqli_num_rows(mysqli_query($con, $query1)) == 0){
    
            // Update the credentials
            $id = $user_data['id'];
            $update_query = "update users set username = '$new_username', email = '$new_email' where id = '$id'";
            mysqli_query($con, $update_query);
            echo json_encode(['status' => 'ok', 'message' => 'Profile updated.']);
        }
        else{
            echo json_encode(['status' => 'error', 'message' => 'The provided credentials already exist.']);
        }
    }
    else{
        echo json_encode(['status' => 'error', 'message' => 'Cannot use the current credentials.']);
    }
}
else{
    echo json_encode(['status' => 'error', 'message' => 'All inputs are required.']);
}
die();