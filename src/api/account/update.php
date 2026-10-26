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

$img = $user_data['img'];
if(isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK){
    $img = processAndSaveProfilePic($_FILES['img']);
}

if(empty($username) || empty($email)){
    ok(false, 'All inputs are required.');
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    ok(false, 'Please enter a valid email.');
}

$stmt_user = mysqli_prepare($con, "select * from users where (username = ? or email = ?) and id != ?");
mysqli_stmt_bind_param($stmt_user, "sss", $username, $email, $id);
mysqli_stmt_execute($stmt_user);
$result_user = mysqli_stmt_get_result($stmt_user);

if(mysqli_num_rows($result_user) > 0){
    ok(false, 'Username or email is already taken.');
}

$isUnchanged = $username == $user_data['username'] &&
               $email == $user_data['email'] &&
               $bio == $user_data['bio'] &&
               empty($img);

if($isUnchanged){
    ok(false, 'No changes were made.');
}

// Update the credentials
$stmt_update = mysqli_prepare($con, "update users set username = ?, email = ?, img = ?, bio = ? where id = ?");
mysqli_stmt_bind_param($stmt_update, "sssss", $username, $email, $img, $bio, $id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

ok(true, 'Profile updated.');