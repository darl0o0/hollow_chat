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
    guard('PUT');

    $input = json_decode(file_get_contents('php://input'), true);


    $old_pass = $input['old_pass'];
    $new_pass = $input['new_pass'];
    $new_pass1 = $input['new_pass1'];

    if(empty($old_pass) || empty($new_pass) || empty($new_pass1)){
        respondError('All inputs are required.', 400);
    }

    if(!password_verify($old_pass, $user_data['password'])){
        respondError('Wrong password.', 400);
    }

    if(strlen($new_pass) < 8){
        respondError('Please provide an 8 characters long password.', 400);
    }

    if($new_pass !== $new_pass1){
        respondError('Passwords do not match.', 400);
    }

    // Update the credentials
    $new_hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
    $stmt_update = mysqli_prepare($con, "update users set password = ? where id = ?");
    mysqli_stmt_bind_param($stmt_update, "ss", $new_hashed_password, $id);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    respondOk('Password updated.');
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}