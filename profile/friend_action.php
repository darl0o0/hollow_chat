<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
requireStrictPost();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $user1 = $user_data['id'];
    $user2 = $_POST['user2'];

    // Execute the action received from the profile page
    // 0 = send friend request
    // 1 = cancel or decline request
    // 2 = accept request
    switch($_POST['action']){
        case "0":
            $query = "insert into friends (user1, user2, status) values ('$user1', '$user2', 'waiting')";
        break;
        case "1":
            $query = "delete from friends where (user1 = '$user1' and user2 = '$user2') or (user1 = '$user2' and user2 = '$user1')";
        break;
        case "2":
            $query = "update friends set status = 'done' where (user1 = '$user1' and user2 = '$user2') or (user1 = '$user2' and user2 = '$user1')";
        break;
    }
    mysqli_query($con, $query);

    // Redirect back to where the action came from
    // if($_POST['from'] == 'pr'){
    //     header("Location: /profile?u="."$user2");
    // }
    // else{
    //     header("Location: /inbox");
    // }
}