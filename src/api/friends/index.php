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

$data;

try{
    $method = $_SERVER['REQUEST_METHOD'];
    if(isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])){
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    }

    switch($method){
        case 'GET':
            guard('GET');

            //Select from friends table
            $stmt_friends = mysqli_prepare($con, "select * from friends where (user1 = ? or user2 = ?)");
            mysqli_stmt_bind_param($stmt_friends, "ss", $id, $id);
            mysqli_stmt_execute($stmt_friends);
            $result_friends = mysqli_stmt_get_result($stmt_friends);

            $friends = mysqli_fetch_all($result_friends, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_friends);
            
            $action = $_GET['action'] ??  null;

            switch($action){
                case 'query':
                    $qid = $_GET['q'];

                    $stmt_user = mysqli_prepare($con, "select * from friends where 
                    (user1 = ? and user2 = ?) or 
                    (user1 = ? and user2 = ?)");
                    mysqli_stmt_bind_param($stmt_user, "ssss", $id, $qid, $qid, $id);
                    mysqli_stmt_execute($stmt_user);
                    $result_user = mysqli_stmt_get_result($stmt_user);

                    $row_user = mysqli_fetch_assoc($result_user);
                    mysqli_stmt_close($stmt_user);
                    
                    if(!$row_user) $data = false;
                    else{
                        if($row_user['status'] == 'waiting'){
                            if($row_user['user2'] == $id) $data = 'incoming';
                            else $data = 'outgoing';
                        }
                        elseif($row_user['status'] == 'done') $data = true;
                    }

                    respondOk($data);
                break;
                case 'count':
                    $count_friends = 0;
                    $count_outgoing = 0;
                    $count_incoming = 0;

                    foreach($friends as $friend){
                        if($friend['status'] == 'done') $count_friends++;
                        elseif($friend['status'] == 'waiting'){
                            if($friend['user2'] == $id) $count_incoming++;
                            elseif($friend['user1'] == $id) $count_outgoing++;
                        }
                    }

                    $data = [
                        'count_friends' => $count_friends,
                        'count_outgoing' => $count_outgoing,
                        'count_incoming' => $count_incoming
                    ];
                break;
                case 'all':
                    $data = [
                        'friends' => [],
                        'requests' => [
                            'incoming' => [],
                            'outgoing' => []
                        ]
                    ];
                    foreach($friends as $row_friend){

                        // Define the other user's id
                        $friend_id = ($row_friend['user1'] == $id)? $row_friend['user2'] : $row_friend['user1'];

                        $stmt_user = mysqli_prepare($con, "select * from users where id = ?");
                        mysqli_stmt_bind_param($stmt_user, "s", $friend_id);
                        mysqli_stmt_execute($stmt_user);
                        $result_friends = mysqli_stmt_get_result($stmt_user);

                        $friend = mysqli_fetch_assoc($result_friends);
                        mysqli_stmt_close($stmt_user);

                        if($row_friend['status'] == 'done'){

                            //Push friend
                            $data['friends'][] = [
                                'id' => $friend_id,
                                'username' => $friend['username'],
                                'img' => $friend['img'],
                                'bio' => $friend['bio']
                            ];
                        }
                        elseif($row_friend['status'] == 'waiting'){
                            if($row_friend['user2'] == $id){

                                //Push incoming request
                                $data['requests']['incoming'][] = [
                                    'id' => $friend_id,
                                    'username' => $friend['username'],
                                    'img' => $friend['img'],
                                    'bio' => $friend['bio']
                                ];
                            }
                            elseif($row_friend['user1'] == $id){

                                //Push outgoing request
                                $data['requests']['outgoing'][] = [
                                    'id' => $friend_id,
                                    'username' => $friend['username'],
                                    'img' => $friend['img'],
                                    'bio' => $friend['bio']
                                ];
                            }
                        }
                    }
                    $data['friends'] = (count($data['friends']) > 0) ? $data['friends'] : null;

                    $data['requests']['incoming'] = (count($data['requests']['incoming']) > 0) ? $data['requests']['incoming'] : null;

                    $data['requests']['outgoing'] = (count($data['requests']['outgoing']) > 0) ? $data['requests']['outgoing'] : null;
                break;
                default:
                    respondError('Invalid action.', 400);
                break;
            }

            respondOk($data);
        break;
        case 'POST':
            guard('POST');

            $input = json_decode(file_get_contents('php://input'), true);

            $qid = $input['q'];

            // 0 = send friend request
            // 1 = cancel or decline request
            // 2 = accept request
            switch($input['action']){
                case 0:
                    $stmt_send = mysqli_prepare($con, "insert into friends (user1, user2, status) values (?, ?, 'waiting')");
                    mysqli_stmt_bind_param($stmt_send, "ss", $id, $qid);
                    mysqli_stmt_execute($stmt_send);
                    mysqli_stmt_close($stmt_send);
                    $data = 'outgoing';
                break;
                case 1:
                    $stmt_remove = mysqli_prepare($con, "delete from friends where (user1 = ? and user2 = ?) or (user1 = ? and user2 = ?)");
                    mysqli_stmt_bind_param($stmt_remove, "ssss", $id, $qid, $qid, $id);
                    mysqli_stmt_execute($stmt_remove);
                    mysqli_stmt_close($stmt_remove);
                    $data = false;
                break;
                case 2:
                    $stmt_add = mysqli_prepare($con, "update friends set status = 'done' where (user1 = ? and user2 = ?) or (user1 = ? and user2 = ?)");
                    mysqli_stmt_bind_param($stmt_add, "ssss", $id, $qid, $qid, $id);
                    mysqli_stmt_execute($stmt_add);
                    mysqli_stmt_close($stmt_add);
                    $data = true;
                break;
                default:
                    respondError('Invalid action.', 400);
                break;
            }
            
            respondOk($data);
        break;
    }
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}