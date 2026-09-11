<?php
//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
// requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$data = [];

$query1 = "select * from users";
$num_of_users = mysqli_num_rows(mysqli_query($con, $query1));
$query = "select * from users where not (id = '$id') order by created_at desc";

// List the users
for($i = 0; $i < $num_of_users-1 ; $i++){

    $result = mysqli_fetch_all(mysqli_query($con, $query));
    $bio = (strlen($result[$i][4])>0)? $result[$i][4] : 'No Bio.';
    $isOnline = $result[$i][6] ? 'visible' : 'hidden';
    
    $data[] = [
        'id' => $result[$i][0],
        'username' => $result[$i][1],
        'bio' => $result[$i][4],
        'img' => $result[$i][5],
        'isOnline' => $result[$i][6]
    ];
}
echo json_encode(['status' => 'ok', 'response' => $data]);
die();