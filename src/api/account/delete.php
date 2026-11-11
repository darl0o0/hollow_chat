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
    guard('DELETE');

    $input = json_decode(file_get_contents('php://input'), true);

    // Credentials
    $email = $input['email'];
    $password = $input['password'];

    if(empty($email) || empty($password)){
        respondError('All inputs are required.', 400);
    }

    if($email !== $user_data['email'] || !password_verify($password, $user_data['password'])){
        respondError('Invalid email or password.', 400);
    }

    //Delete user
    $stmt_delete_user = mysqli_prepare($con, "delete from users where id = ?");
    mysqli_stmt_bind_param($stmt_delete_user, "s", $id);
    mysqli_stmt_execute($stmt_delete_user);
    mysqli_stmt_close($stmt_delete_user);

    // Delete friends
    $stmt_delete_friend = mysqli_prepare($con, "delete from friends where user1 = ? or user2 = ?");
    mysqli_stmt_bind_param($stmt_delete_friend, "ss", $id, $id);
    mysqli_stmt_execute($stmt_delete_friend);
    mysqli_stmt_close($stmt_delete_friend);

    // Destroy cookies
    $_SESSION = array();

    if(ini_get("session.use_cookies")){
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    respondOk('Account Deleted.');
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}