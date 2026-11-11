<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$user_data = check_login($con);
$id = $user_data['id'];

try{
    guard('POST');

    $method = $_SERVER['REQUEST_METHOD'];
    if(isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])){
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    }

    if($method !== 'PUT') respondError('Method not allowed', 405);

    $username = $_POST['username'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];

    $img = $user_data['img'];
    if(isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK){
        $img = processAndSaveProfilePic($_FILES['img']);
    }

    if(empty($username) || empty($email)){
        respondError('All inputs are required.', 400);
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        respondError('Please enter a valid email.', 400);
    }

    $stmt_user = mysqli_prepare($con, "select * from users where (username = ? or email = ?) and id != ?");
    mysqli_stmt_bind_param($stmt_user, "sss", $username, $email, $id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    if(mysqli_num_rows($result_user) > 0){
        respondError('Username or email is already taken.', 400);
    }

    $isUnchanged = $username == $user_data['username'] &&
                $email == $user_data['email'] &&
                $bio == $user_data['bio'] &&
                empty($img);

    if($isUnchanged){
        respondError('No changes were made.', 400);
    }

    // Update the credentials
    $stmt_update = mysqli_prepare($con, "update users set username = ?, email = ?, img = ?, bio = ? where id = ?");
    mysqli_stmt_bind_param($stmt_update, "sssss", $username, $email, $img, $bio, $id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    respondOk('Profile updated.');
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}