<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
header('Content-Type: application/json; charset=utf-8');
requireStrictPost();

$user_data = check_login($con);
$id = $user_data['id'];

$username = $_POST['username'];
$email = $_POST['email'];
$bio = $_POST['bio'];

//Uploading a profile picture
$raw_img = $_FILES['img'];
$file_name = $raw_img['name'];
$tmp_name = $raw_img['tmp_name'];
$file_dir = $_SERVER['DOCUMENT_ROOT']."/src/assets/pfps/".$file_name;
move_uploaded_file($tmp_name, $file_dir);

$img = $user_data['img'];
if($user_data['img'] !== $raw_img['name'] && !empty($raw_img['name'])){
    $img = $raw_img['name'];
}

if(empty($username) || empty($email)){
    echo json_encode([
        'status' => 'error',
        'message' => 'All inputs are required.'
    ]);
    return;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode([
        'status' => 'error',
        'message' => 'Please enter a valid email.'
    ]);
    return;
}

$stmt_user = mysqli_prepare($con, "select * from users where (username = ? or email = ?) and id != ?");
mysqli_stmt_bind_param($stmt_user, "sss", $username, $email, $id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

if(mysqli_num_rows($result_user) > 0){
    echo json_encode([
        'status' => 'error',
        'message' => 'Username or email is already taken.'
    ]);
    return;
}

$isUnchanged = $username == $user_data['username'] &&
               $email == $user_data['email'] &&
               $bio == $user_data['bio'] &&
               empty($img);

if($isUnchanged){
    echo json_encode([
        'status' => 'error',
        'message' => 'No changes were made.'
    ]);
    return;
}

// Update the credentials
$stmt_update = mysqli_prepare($con, "update users set username = ?, email = ?, img = ?, bio = ? where id = ?");
mysqli_stmt_bind_param($stmt_update, "sssss", $username, $email, $img, $bio, $id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

echo json_encode([
    'status' => 'ok',
    'message' => 'Profile updated.'
]);
die();