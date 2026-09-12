<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
$id = $user_data['id'];

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    requireStrictGet();
    header('Content-Type: application/json; charset=utf-8');

    $data = [];
    $rid = $_GET['rid'] ?? '';
    
    // Select all messages of both users
    $query_messages = "select * from messages where (sid = '$id' and rid = '$rid') or (sid = '$rid' and rid = '$id')";
    $num_of_msgs = mysqli_num_rows(mysqli_query($con, $query_messages));
    
    if($num_of_msgs > 0){
        $result = mysqli_fetch_all(mysqli_query($con, $query_messages));
        for($i = 0; $i < $num_of_msgs; $i++){
            $data[] = [
                'sid' => $result[$i][0],
                'msg' => $result[$i][2],
                'isRead' => $result[$i][3]
            ];
        }
    }
    elseif($rid != ''){
        $data = null;
    }
    echo json_encode(['status' => 'ok', 'response' => $data]);
    die();
}

elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
    requireStrictPost();

    // Curl request data
    $sid = $_POST['sid'];
    $rid = $_POST['rid'];
    $msg = $_POST['msg'];
    $socketData = json_encode([
        'sid' => $sid,
        'rid' => $rid,
        'message' => $msg
    ]);

    $num_msg_query = "select * from messages where (sid = '$sid' and rid = '$rid') or (sid = '$rid' and rid = '$sid')";
    $num_of_msg = mysqli_num_rows(mysqli_query($con, $num_msg_query));

    if($num_of_msg == 0){
        mysqli_query($con, "insert into conversations (user1, user2) values ('$sid', '$rid')");
    }

    //Send a curl request to the Node.js server (server.js)
    $ch = curl_init('http://localhost:3000/broadcast');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $socketData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($socketData)
    ]);
    
    // Execute curl request
    curl_exec($ch);

    $query = "insert into messages (sid, rid, msg, isRead) values ('$sid', '$rid', '$msg', 0)";
    mysqli_query($con, $query);
    
    curl_close($ch);
    echo json_encode(['status' => 'ok', 'message' => 'sent']);
}