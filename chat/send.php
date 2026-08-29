<?php

//Initialization
session_start();
include("../connection.php");
include("../functions.php");
$user_data = check_login($con);
if(isset($_SESSION['id'])){
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')){
        $username = $user_data['username'];
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
        
        curl_exec($ch);
        if($_SERVER['REQUEST_METHOD'] == "POST"){
            $query = "insert into messages (sid, rid, msg, isRead) values ('$sid', '$rid', '$msg', 0)";
            mysqli_query($con, $query);
        }
        curl_close($ch);
        echo "sent";
    }
    else{
        include("../forbidden/index.php");
    }
}
else{
    header("Location: ../signin");
}