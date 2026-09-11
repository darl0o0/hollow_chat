<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$data = [];

//List of conversations
$user2_chat = "";
$chats_query = "select * from conversations where (user1 = '$id' or user2 = '$id')";
$chat_row = mysqli_fetch_all(mysqli_query($con, $chats_query));
$num_of_chats = mysqli_num_rows(mysqli_query($con, $chats_query));

if($num_of_chats > 0){

    // Listing conversations
    for($i = 0; $i < $num_of_chats ; $i++){

        // Define the other user's id
        $user2_chat = ($chat_row[$i][0] == $id)? $chat_row[$i][1] : $chat_row[$i][0];
        $query = "select * from users where id = '$user2_chat'";
        $chat = mysqli_fetch_all(mysqli_query($con, $query));
        
        // Select the last message for the current conversation
        $query_last_msg = "select * from messages where (sid = '$id' and rid = '$user2_chat') or (sid = '$user2_chat' and rid = '$id') order by created_at desc limit 1";

        // Last message
        $last_msg = mysqli_fetch_all(mysqli_query($con, $query_last_msg))[0];

        // If user was deleted
        if(mysqli_num_rows(mysqli_query($con, $query)) > 0){
            $username = $chat[0][1];
            $img = $chat[0][5];
            $isOnline = $chat[0][6];
        }
        else{
            $username = null;
            $img = 'pfp.jpg';
            $isOnline = null;
        }
        
        //List conversations
        $data[] = [
            'id' => $user2_chat,
            'username' => $username,
            'img' => $img,
            'isOnline' => $isOnline,
            'lastMsg' => [
                'sid' => $last_msg[0],
                'msg' => $last_msg[2],
                'isRead' => $last_msg[3],
                'time' => $last_msg[4]
            ]
        ];
    }
}
else{
    $data = null;
}
echo json_encode(['status' => 'ok', 'response' => $data]);
die();