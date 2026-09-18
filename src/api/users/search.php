<?php

//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$data = [
    'isYou' => null,
    'profile' => null
];


if(isset($_GET['q'])){
    $qid = $_GET['q'];
    $query = "select * from users where id = '$qid'";
    $result = mysqli_fetch_assoc(mysqli_query($con, $query));
    
    if(mysqli_num_rows(mysqli_query($con, $query)) > 0){
        $data = [
            'isYou' => ($qid == $id) ? true : false,
            'profile' => [
                'id' => $result['id'],
                'username' => $result['username'],
                'img' => $result['img'],
                'online' => filter_var($result['online'], FILTER_VALIDATE_BOOLEAN),
                'bio' => $result['bio'],
                'isPrivate' => filter_var($result['private'], FILTER_VALIDATE_BOOLEAN),
                'date' => $result['date']
            ]
        ];
    }
}

echo json_encode(['status' => 'ok', 'response' => $data]);
die();