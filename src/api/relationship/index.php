<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
$id = $user_data['id'];

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    requireStrictGet();
    header('Content-Type: application/json; charset=utf-8');
    
    $data = [];
    $user2 = $_GET['user2'];
    
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
}

elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
    requireStrictPost();
    $user2 = $_POST['user2'];

    // Execute the action received from the profile page
    // 0 = send friend request
    // 1 = cancel or decline request
    // 2 = accept request
    switch($_POST['action']){
        case "0":
            $query = "insert into friends (user1, user2, status) values ('$id', '$user2', 'waiting')";
        break;
        case "1":
            $query = "delete from friends where (user1 = '$id' and user2 = '$user2') or (user1 = '$user2' and user2 = '$id')";
        break;
        case "2":
            $query = "update friends set status = 'done' where (user1 = '$id' and user2 = '$user2') or (user1 = '$user2' and user2 = '$id')";
        break;
    }
    mysqli_query($con, $query);
    die();
}