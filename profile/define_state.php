<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
requireStrictPost();

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);

    $id = $user_data['id'];
    $user2 = $data['user2'];

    $query1 = "select * from friends where 
    (user1 = '$id' and user2 = '$user2') or 
    (user1 = '$user2' and user2 = '$id')";
    $num = mysqli_num_rows(mysqli_query($con, $query1));

    // If user is not friend set action to 0 (Add friend)
    if($num == 0){
        $action = 0;
        $friend_action = '<i class="fa fa-user-plus" aria-hidden="true"></i>Add Friend';
    }
    else{
        if(mysqli_fetch_all(mysqli_query($con, $query1))[0][2] == 'waiting'){

            // If user sent you a request set action to 2 (Accept request)
            if(mysqli_fetch_all(mysqli_query($con, $query1))[0][1] == $id){
                $action = 2;
                $friend_action = '<i class="fa fa-check" aria-hidden="true"></i> Accept Request';
            }

            // If a request is already sent by the current user set action to 1 (Cancel request)
            else{
                $action = 1;
                $friend_action = '<i class="fa fa-user-times" aria-hidden="true"></i>Cancel Request';
            }
        }

        // If user is already a friend set action to 1 (Remove friend)
        elseif(mysqli_fetch_all(mysqli_query($con, $query1))[0][2] == 'done'){
            $action = 1;
            $friend_action = '<i class="fa fa-user-times" aria-hidden="true"></i>Remove Friend';
        }
    }
}
echo json_encode([$action, $friend_action]);
?>