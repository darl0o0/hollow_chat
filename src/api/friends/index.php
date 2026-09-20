<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
// requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');
$id = $user_data['id'];

$data = [
    'friends' => [],
    'requests' => [
        'incoming' => [],
        'outgoing' => []
    ]
];

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
    // $data['friends'][] = "
    // <div class='user-card u-$user2_friend'>
    //     <a href='/profile?u=$user2_friend' class='profile-link'></a>
    //         <div class='pfp'>
    //             <img class='pfp-img' src='/src/assets/pfps/".$friend[0][5]."'>
    //         </div>
    //         <div class='label-wrapper'>
    //             <div class='username-label'>".$friend[0][1]."</div>
    //             <span class='sub-label'>$bio</span>
    //         </div>
    //     <div class='btn-wrapper-inbox'>
    //         <a href='/chat?r=$user2_friend' class='btn-inbox'><i class='fa fa-comment' aria-hidden='true'></i></a>
    //         <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
    //     </div>
    // </div>
    // ";
}
$data['friends'] = (count($data['friends']) > 0) ? $data['friends'] : null;

$data['requests']['incoming'] = (count($data['requests']['incoming']) > 0) ? $data['requests']['incoming'] : null;

$data['requests']['outgoing'] = (count($data['requests']['outgoing']) > 0) ? $data['requests']['outgoing'] : null;

echo json_encode(['status' => 'ok', 'response' => $data]);
die();