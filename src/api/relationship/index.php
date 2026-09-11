<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$user2 = $_GET['user2'];
$data = [];

$query1 = "select * from friends where 
(user1 = '$id' and user2 = '$user2') or 
(user1 = '$user2' and user2 = '$id')";
$num = mysqli_num_rows(mysqli_query($con, $query1));

// If user is not friend set action to 0 (Add friend)
if($num == 0){
    $data = [
        'relationship' => false
    ];
}
else{
    if(mysqli_fetch_all(mysqli_query($con, $query1))[0][2] == 'waiting'){

        // If user sent you a request set action to 2 (Accept request)
        if(mysqli_fetch_all(mysqli_query($con, $query1))[0][1] == $id){
            $data = [
                'relationship' => 'pending'
            ];
        }

        // If a request is already sent by the current user set action to 1 (Cancel request)
        else{
            $data = [
                'relationship' => 'sent'
            ];
        }
    }

    // If user is already a friend set action to 1 (Remove friend)
    elseif(mysqli_fetch_all(mysqli_query($con, $query1))[0][2] == 'done'){
        $data = [
            'relationship' => true
        ];
    }
}
echo json_encode(['status' => 'ok', 'response' => $data]);
die();