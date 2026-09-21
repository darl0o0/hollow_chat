<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
requireStrictGet();
header('Content-Type: application/json; charset=utf-8');

$user_data = check_login($con);
$id = $user_data['id'];
$data = [];

//List of conversations
$stmt_conversations = mysqli_prepare($con, "select * from conversations where (user1 = ? or user2 = ?)");
mysqli_stmt_bind_param($stmt_conversations, "ss", $id, $id);
mysqli_stmt_execute($stmt_conversations);
$result_conversations = mysqli_stmt_get_result($stmt_conversations);

$conversations = mysqli_fetch_all($result_conversations, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_conversations);

if(count($conversations) > 0){

    foreach($conversations as $conversation){

        // Select user
        $id_user2 = ($conversation['user1'] == $id)? $conversation['user2'] : $conversation['user1'];

        $stmt_user = mysqli_prepare($con, "select id, username, img, online from users where id = ?");
        mysqli_stmt_bind_param($stmt_user, "s", $id_user2);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);

        $row_user2 = mysqli_fetch_assoc($result_user);
        mysqli_stmt_close($stmt_user);

        // Last message
        $stmt_last_msg = mysqli_prepare($con, "select * from messages where (sid = ? and rid = ?) or (sid = ? and rid = ?) order by created_at desc limit 1");
        mysqli_stmt_bind_param($stmt_last_msg, "ssss", $id, $id_user2, $id_user2, $id);
        mysqli_stmt_execute($stmt_last_msg);
        $result_last_msg = mysqli_stmt_get_result($stmt_last_msg);

        $row_last_msg = mysqli_fetch_assoc($result_last_msg);
        mysqli_stmt_close($stmt_last_msg);

        if($row_user2){
            $username = $row_user2['username'];
            $img = $row_user2['img'];
            $isOnline = filter_var($row_user2['online'], FILTER_VALIDATE_BOOL);
        }
        else{
            $username = null;
            $img = 'pfp.jpg';
            $isOnline = null;
        }
        
        //List conversations
        $data[] = [
            'id' => $id_user2,
            'username' => $username,
            'img' => $img,
            'isOnline' => $isOnline,
            'lastMsg' => [
                'sid' => $row_last_msg['sid'],
                'msg' => $row_last_msg['msg'],
                'isRead' => filter_var($row_last_msg['isRead'], FILTER_VALIDATE_BOOL),
                'time' => $row_last_msg['created_at']
            ]
        ];
    }
}
else{
    $data = null;
}
echo json_encode([
    'status' => 'ok',
    'response' => $data
]);
die();