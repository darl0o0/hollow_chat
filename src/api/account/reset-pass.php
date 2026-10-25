<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
header('Content-Type: application/json; charset=utf-8');
requireStrictPost();
$input = json_decode(file_get_contents('php://input'), true);

$user_data = check_login($con);
$id = $user_data['id'];

$old_pass = $input['old_pass'];
$new_pass = $input['new_pass'];
$new_pass1 = $input['new_pass1'];

if(empty($old_pass) || empty($new_pass) || empty($new_pass1)){
    ok(false, 'All inputs are required.');
}

if(!password_verify($old_pass, $user_data['password'])){
    ok(false, 'Wrong password.');
}

if(strlen($new_pass) < 8){
    ok(false, 'Please provide an 8 characters long password.');
}

if($new_pass !== $new_pass1){
    ok(false, 'Passwords do not match.');
}

// Update the credentials
$new_hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
$stmt_update = mysqli_prepare($con, "update users set password = ? where id = ?");
mysqli_stmt_bind_param($stmt_update, "ss", $new_hashed_password, $id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

ok(true, 'Password updated.');