<?php
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
header('Content-Type: application/json; charset=utf-8');
requireStrictPost();

$user_data = check_login($con);
$id = $user_data['id'];

$stmt_set_offline = mysqli_prepare($con, "update users set online = 0 where id = ?");
mysqli_stmt_bind_param($stmt_set_offline, "s", $id);
mysqli_stmt_execute($stmt_set_offline);
mysqli_stmt_close($stmt_set_offline);

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
ok(true, 'Logged out');