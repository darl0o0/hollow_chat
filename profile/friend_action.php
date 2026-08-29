<?php
session_start();
include("../connection.php");
include("../functions.php");
$user_data = check_login($con);
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $user1 = $user_data['id'];
    $user2 = $_POST['user2_id'];
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
    if($_POST['from'] == 'pr'){
        header("Location: ./?u="."$user2");
    }
    else{
        header("Location: ../inbox");
    }
}