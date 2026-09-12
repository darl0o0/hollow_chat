<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
requireStrictPost();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = $_POST['id'];
    $query = "update users set online = 0 where id = '$id'";
    mysqli_query($con, $query);
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
    echo json_encode(['status' => 'ok']);
    die();
}