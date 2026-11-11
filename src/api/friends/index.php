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

$data = [
    'friends' => [],
    'requests' => [
        'incoming' => [],
        'outgoing' => []
    ]
];

try{
    guard('GET');

    //Select from friends table
    $stmt_friends = mysqli_prepare($con, "select * from friends where (user1 = ? or user2 = ?)");
    mysqli_stmt_bind_param($stmt_friends, "ss", $id, $id);
    mysqli_stmt_execute($stmt_friends);
    $result_friends = mysqli_stmt_get_result($stmt_friends);

    $friends = mysqli_fetch_all($result_friends, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt_friends);

    // Pushing into data
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

    respondOk($data);
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}