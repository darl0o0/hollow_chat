<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
$id = $user_data['id'];
$messages = "";
requireStrictPost();

if($_SERVER['REQUEST_METHOD'] == "POST"){

    // Select all messages of both users
    $data = json_decode(file_get_contents('php://input'), true);
    $rid = $data['rid'] ?? '';
    $query_messages = "select * from messages where (sid = '$id' and rid = '$rid') or (sid = '$rid' and rid = '$id')";
    $num_of_msgs = mysqli_num_rows(mysqli_query($con, $query_messages));

    if($num_of_msgs > 0){
        $result = mysqli_fetch_all(mysqli_query($con, $query_messages));
        for($i = 0; $i < $num_of_msgs; $i++){

            // List your messages in white and the other user's messages in black
            if($result[$i][0] == $id){
                $isRead = $result[$i][3]? '#e9e4c8':'none';
                $messages .= "<div class='msg-wrap'><div class='isRead unread' style='background: $isRead;'></div><div class='s m'>".$result[$i][2]."</div></div>";
            }
            elseif($result[$i][0] == $rid){
                $messages .= "<div class='r m'>".$result[$i][2]."</div>";
            }
        }
    }
    elseif($rid != ''){
        $messages = "<center>Start chatting now.</center>";
    }
}
echo $messages;