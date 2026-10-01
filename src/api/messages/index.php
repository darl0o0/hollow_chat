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
    $qid = $_GET['qid'] ?? '';

    $isGroup = filter_var($_GET['isGroup'] ?? false, FILTER_VALIDATE_BOOL);
    
    // Select all messages of both users
    if($isGroup){
        $stmt_messages = mysqli_prepare($con, "
            select m.*, u.username, u.img 
            from messages m join users u
            on m.sid = u.id
            where m.rid = ? order by m.created_at asc
        ");
        mysqli_stmt_bind_param($stmt_messages, "s", $qid);
    }
    else{
        $stmt_messages = mysqli_prepare($con, "
        select m.*, u.username, u.img 
        from messages m join users u
        on m.sid = u.id
        where (m.sid = ? and m.rid = ?) or (m.sid = ? and m.rid = ?)
        ");
        mysqli_stmt_bind_param($stmt_messages, "ssss", $id, $qid, $qid, $id);
    }

    mysqli_stmt_execute($stmt_messages);
    $result_messages = mysqli_stmt_get_result($stmt_messages);

    $messages = mysqli_fetch_all($result_messages, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_messages);
    
    if(count($messages) > 0){

        foreach($messages as $message){
            $data[] = [
                'sid' => $message['sid'],
                'msg' => $message['msg'],
                'isRead' => $message['isRead'],
                'user' => [
                    'username' => $message['username'],
                    'img' => $message['img']
                ]
            ];
        }
    }
    elseif($qid != ''){
        $data = null;
    }
    else{
        $data[] = [
            'sid' => $message['sid'],
            'msg' => $message['msg'],
            'isRead' => $message['isRead']
        ];
    }
    echo json_encode([
        'status' => 'ok',
        'response' => $data
    ]);
    die();
}
elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
    requireStrictPost();

    // Curl request data
    $sid = $_POST['sid'];
    $msg = $_POST['msg'];
    $is_group = $_POST['isGroup'];
    $group_id = $_POST['groupId'];

    $qid = filter_var($is_group, FILTER_VALIDATE_BOOL) ? $group_id : $_POST['qid'];

    $socketData = json_encode([
        'sid' => $sid,
        'rid' => json_decode($_POST['qid'], true) ?? $_POST['qid'],
        'msg' => $msg,
        'isGroup' => filter_var($is_group, FILTER_VALIDATE_BOOL),
        'groupId' => $group_id
    ]);

    $stmt_msg_count = mysqli_prepare($con, "select count(*) from messages where (sid = ? and rid = ?) or (sid = ? and rid = ?)");
    mysqli_stmt_bind_param($stmt_msg_count, "ssss", $sid, $qid, $qid, $sid);
    mysqli_stmt_execute($stmt_msg_count);
    $result_msg_count = mysqli_stmt_get_result($stmt_msg_count);

    $row_count = mysqli_fetch_row($result_msg_count);
    $num_of_msg = $row_count[0];
    mysqli_stmt_close($stmt_msg_count);

    if($num_of_msg == 0){
        $stmt_insert_conversation = mysqli_prepare($con, "insert into conversations (user1, user2, is_group) values (?, ?, 0)");
        mysqli_stmt_bind_param($stmt_insert_conversation, "ss", $sid, $qid);
        mysqli_stmt_execute($stmt_insert_conversation);
        mysqli_stmt_close($stmt_insert_conversation);
    }

    //Send a curl request to the Node.js server (server.js)
    $ch = curl_init('http://127.0.0.1:3000/broadcast');

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $socketData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($socketData)
    ]);
    
    // Execute curl request
    curl_exec($ch);

    $stmt_insert_msg = mysqli_prepare($con, "insert into messages (sid, rid, msg, isRead) values (?, ?, ?, 0)");
    mysqli_stmt_bind_param($stmt_insert_msg, "sss", $sid, $qid, $msg);
    mysqli_stmt_execute($stmt_insert_msg);
    mysqli_stmt_close($stmt_insert_msg);
    
    curl_close($ch);
    echo json_encode([
        'status' => 'ok',
        'message' => 'sent'
    ]);
}