<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$user_data = check_login($con);
$id = $user_data['id'];

try{
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        guard('GET');

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
                    'id' => $message['id'],
                    'sid' => $message['sid'],
                    'text' => $message['text'],
                    'seen' => filter_var((int)$message['seen'], FILTER_VALIDATE_BOOLEAN),
                    'react' => [
                        'emoji' => $message['react'],
                        'reactor' => $message['reactor']
                    ],
                    'only_emoji' => $message['only_emoji']
                ];
            }
        }
        elseif($qid != ''){
            $data = null;
        }
        else{
            $data[] = [
                'sid' => $message['sid'],
                'text' => $message['text'],
                'seen' => $message['seen'],
                'react' => $message['react'],
                'reactor' => $message['reactor']
            ];
        }

        respondOk($data);
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
        guard('POST');
        
        $input = json_decode(file_get_contents('php://input'), true);

        // Curl request data
        $sid = $input['sid'];
        $msg = $input['msg'];
        $is_group = $input['isGroup'];
        $group_id = $input['groupId'];

        $rid = filter_var($is_group, FILTER_VALIDATE_BOOL) ? $group_id : $input['qid'];

        $dec_sid = base_convert($sid, 16, 10);
        $dec_rid = base_convert($rid, 16, 10);
        $common_id = base_convert(bcadd($dec_sid, $dec_rid), 10, 16);

        $timestamp = time();
        $time_id = dechex($timestamp);

        $mid = $common_id.$time_id.bin2hex(random_bytes(1));

        $socketData = json_encode([
            'id' => $mid,
            'sid' => $sid,
            'rid' => json_decode($input['qid'], true) ?? $input['qid'],
            'msg' => $msg,
            'isGroup' => filter_var($is_group, FILTER_VALIDATE_BOOL),
            'groupId' => $group_id
        ]);

        $curl_node_token = getEnvValue('CURL_NODE_TOKEN');

        //Send a curl request to the Node.js server (server.js)
        $ch = curl_init('http://127.0.0.1:3000/broadcast');

        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $socketData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($socketData),
            'X-Internal-Token: ' .$curl_node_token
        ]);
        curl_exec($ch);

        // Insert conversation
        $stmt_msg_count = mysqli_prepare($con, "select count(*) from messages where (sid = ? and rid = ?) or (sid = ? and rid = ?)");
        mysqli_stmt_bind_param($stmt_msg_count, "ssss", $sid, $rid, $rid, $sid);
        mysqli_stmt_execute($stmt_msg_count);
        $result_msg_count = mysqli_stmt_get_result($stmt_msg_count);

        $row_count = mysqli_fetch_row($result_msg_count);
        $num_of_msg = $row_count[0];
        mysqli_stmt_close($stmt_msg_count);

        if($num_of_msg == 0){
            $stmt_insert_conversation = mysqli_prepare($con, "insert into conversations (user1, user2, is_group) values (?, ?, 0)");
            mysqli_stmt_bind_param($stmt_insert_conversation, "ss", $sid, $rid);
            mysqli_stmt_execute($stmt_insert_conversation);
            mysqli_stmt_close($stmt_insert_conversation);
        }

        // Insert message
        $stmt_insert_msg = mysqli_prepare($con, "insert into messages (id, sid, rid, msg, seen, created_at) values (?, ?, ?, ?, 0, ?)");
        mysqli_stmt_bind_param($stmt_insert_msg, "ssssi", $mid, $sid, $rid, $msg, $timestamp);
        mysqli_stmt_execute($stmt_insert_msg);
        mysqli_stmt_close($stmt_insert_msg);
        
        respondOk('sent');
    }
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}