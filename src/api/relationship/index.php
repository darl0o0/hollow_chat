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
    $qid = $_GET['user2'];

    $stmt_user = mysqli_prepare($con, "select * from friends where 
    (user1 = ? and user2 = ?) or 
    (user1 = ? and user2 = ?)");
    mysqli_stmt_bind_param($stmt_user, "ssss", $id, $qid, $qid, $id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    $row_user = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);
    
    // If user is not friend set action to 0 (Add friend)
    if(!$row_user){
        $data = [
            'relationship' => false
        ];
    }
    else{
        if($row_user['status'] == 'waiting'){
    
            // If user sent you a request set action to 2 (Accept request)
            if($row_user['user2'] == $id){
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
        elseif($row_user['status'] == 'done'){
            $data = [
                'relationship' => true
            ];
        }
    }

    ok(true, $data);
}

elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
    requireStrictPost();
    $qid = $_POST['user2'];

    // Execute the action received from the profile page
    // 0 = send friend request
    // 1 = cancel or decline request
    // 2 = accept request
    switch($_POST['action']){
        case "0":
            $stmt_send = mysqli_prepare($con, "insert into friends (user1, user2, status) values (?, ?, 'waiting')");
            mysqli_stmt_bind_param($stmt_send, "ss", $id, $qid);
            mysqli_stmt_execute($stmt_send);
            mysqli_stmt_close($stmt_send);
        break;
        case "1":
            $stmt_remove = mysqli_prepare($con, "delete from friends where (user1 = ? and user2 = ?) or (user1 = ? and user2 = ?)");
            mysqli_stmt_bind_param($stmt_remove, "ssss", $id, $qid, $qid, $id);
            mysqli_stmt_execute($stmt_remove);
            mysqli_stmt_close($stmt_remove);
        break;
        case "2":
            $stmt_add = mysqli_prepare($con, "update friends set status = 'done' where (user1 = ? and user2 = ?) or (user1 = ? and user2 = ?)");
            mysqli_stmt_bind_param($stmt_add, "ssss", $id, $qid, $qid, $id);
            mysqli_stmt_execute($stmt_add);
            mysqli_stmt_close($stmt_add);
        break;
    }
    
    ok(true, $data);
}