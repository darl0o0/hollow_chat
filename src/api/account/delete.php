<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
requireStrictPost();

if($_SERVER['REQUEST_METHOD'] == "POST"){

    // Credentials
    $email = $_POST['email'];
    $password = $_POST['password'];
    if(!empty($email) && !empty($password)){

        if($email === $user_data['email'] && $password === $user_data['password']){
    
            //Delete user
            $id = $user_data['id'];
            $query_delete_user = "delete from users where id = '$id'";
            $query_delete_friend = "delete from friends where user1 = '$id' or user2 = '$id'";
            mysqli_query($con, $query_delete_user);
            mysqli_query($con, $query_delete_friend);
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
            echo json_encode(['status' => 'ok', 'message' => 'Account Deleted.']);
            die();
        }
        else{
            echo json_encode(['status' => 'error', 'message' => 'Wrong credentials.']);
        }
    }
    else{
        echo json_encode(['status' => 'error', 'message' => 'All inputs are required.']);
    }
}