<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

guard('DELETE');

$input = json_decode(file_get_contents('php://input'), true);

$user_data = check_login($con);
$id = $user_data['id'];

if($_SERVER['REQUEST_METHOD'] == "POST"){

    // Credentials
    $email = $input['email'];
    $password = $input['password'];

    if(empty($email) || empty($password)){
        ok(false, 'All inputs are required.');
    }

    if($email !== $user_data['email'] || !password_verify($password, $user_data['password'])){
        ok(false, 'Invalid email or password.');
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
    ok(true, 'Account Deleted.');
}