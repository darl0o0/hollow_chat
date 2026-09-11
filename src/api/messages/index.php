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

if($_SERVER['REQUEST_METHOD'] == 'GET'){
    
    // Select all messages of both users
    $rid = $_GET['rid'] ?? '';
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
}
echo json_encode(['status' => 'ok', 'response' => $data]);
die();