<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
requireStrictGet();

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
$friends_query = "select * from friends where (user1 = '$id' or user2 = '$id')";
$row = mysqli_fetch_all(mysqli_query($con, $friends_query));
$num_of_rows = mysqli_num_rows(mysqli_query($con, $friends_query));
    
// Pushing into data
for($i = 0; $i < $num_of_rows ; $i++){

    // Define the other user's id
    $user2 = ($row[$i][0] == $id)? $row[$i][1] : $row[$i][0];
    $query = "select * from users where id = '$user2'";
    $friend = mysqli_fetch_all(mysqli_query($con, $query));

    if($row[$i][2] == 'done'){

        //Push friend
        $data['friends'][] = [
            'id' => $user2,
            'username' => $friend[0][1],
            'img' => $friend[0][5],
            'bio' => $friend[0][4]
        ];
    }
    elseif($row[$i][2] == 'waiting'){
        if($row[$i][1] == $id){

            //Push incoming request
            $data['requests']['incoming'][] = [
                'id' => $user2,
                'username' => $friend[0][1],
                'img' => $friend[0][5],
                'bio' => $friend[0][4]
            ];
        }
        elseif($row[$i][0] == $id){

            //Push outgoing request
            $data['requests']['outgoing'][] = [
                'id' => $user2,
                'username' => $friend[0][1],
                'img' => $friend[0][5],
                'bio' => $friend[0][4]
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