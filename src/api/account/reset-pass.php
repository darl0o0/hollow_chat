<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
header('Content-Type: application/json; charset=utf-8');
requireStrictPost();

$user_data = check_login($con);
$id = $user_data['id'];

$old_pass = $_POST['old_pass'];
$new_pass = $_POST['new_pass'];
$new_pass1 = $_POST['new_pass1'];

if(empty($old_pass) || empty($new_pass) || empty($new_pass1)){
    echo json_encode([ 
        'status' => 'error',
        'message' => 'All inputs are required.'
    ]);
    return;
}

if(!password_verify($old_pass, $user_data['password'])){
    echo json_encode([ 
        'status' => 'error',
        'message' => 'Wrong password.'
    ]);
    return;
}

if(strlen($new_pass) < 8){
    echo json_encode([ 
        'status' => 'error',
        'message' => 'Please provide an 8 characters long password.'
    ]);
    return;
}

if($new_pass !== $new_pass1){
    echo json_encode([ 
        'status' => 'error',
        'message' => 'Passwords do not match.'
    ]);
    return;
}

// Update the credentials
$new_hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
$stmt_update = mysqli_prepare($con, "update users set password = ? where id = ?");
mysqli_stmt_bind_param($stmt_update, "ss", $new_hashed_password, $id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

echo json_encode([
    'status' => 'ok',
    'message' => 'Password updated.'
]);
die();