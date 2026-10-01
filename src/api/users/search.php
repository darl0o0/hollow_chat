<?php

//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
// requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$data = [
    'isYou' => null,
    'profile' => null
];

if(isset($_GET['q'])){
    $qid = $_GET['q'];

    $stmt_user = mysqli_prepare($con, "select id, username, img, online, bio, private, date from users where id = ?");
    mysqli_stmt_bind_param($stmt_user, "s", $qid);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);

    $row_user = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);
    
    if($row_user){
        $data = [
            'isYou' => ($qid == $id) ? true : false,
            'profile' => [
                'id' => $row_user['id'],
                'username' => $row_user['username'],
                'img' => $row_user['img'],
                'online' => filter_var($row_user['online'], FILTER_VALIDATE_BOOLEAN),
                'bio' => $row_user['bio'],
                'isPrivate' => filter_var($row_user['private'], FILTER_VALIDATE_BOOLEAN),
                'date' => $row_user['date']
            ]
        ];
    }
}

echo json_encode([
    'status' => 'ok',
    'response' => $data
]);
die();