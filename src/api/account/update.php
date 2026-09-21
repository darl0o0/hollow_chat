<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
header('Content-Type: application/json; charset=utf-8');
requireStrictPost();

$user_data = check_login($con);
$id = $user_data['id'];

$old_username = $user_data['username'];
$old_email = $user_data['email'];

$new_username = $_POST['username'];
$new_email = $_POST['email'];

if(empty($new_username) || empty($new_email)){
    echo json_encode([
        'status' => 'error',
        'message' => 'All inputs are required.'
    ]);
    return;
}

$stmt_user = mysqli_prepare($con, "select * from users where username = ? or email = ?");
mysqli_stmt_bind_param($stmt_user, "ss", $new_username, $new_email);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

$row_user = mysqli_fetch_all($result_user, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_user);

if(($new_username == $old_username && $new_email == $old_email) || count($row_user) > 0){
    echo json_encode([
        'status' => 'error',
        'message' => 'Cannot use the provided credentials.'
    ]);
    return;
}

// Update the credentials
$stmt_update = mysqli_prepare($con, "update users set username = ?, email = ? where id = ?");
mysqli_stmt_bind_param($stmt_update, "sss", $new_username, $new_email, $id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

echo json_encode([
    'status' => 'ok',
    'message' => 'Profile updated.'
]);
die();