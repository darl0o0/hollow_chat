<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

guard('GET');

$user_data = check_login($con);
$id = $user_data['id'];
$data = [];

$stmt_users = mysqli_prepare($con, "select * from users where not (id = ?) order by created_at desc");
mysqli_stmt_bind_param($stmt_users, "s", $id);
mysqli_stmt_execute($stmt_users);
$result_users = mysqli_stmt_get_result($stmt_users);

$users = mysqli_fetch_all($result_users, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_users);

foreach($users as $user){
    $data[] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'bio' => $user['bio'],
        'img' => $user['img'],
        'isOnline' => $user['online']
    ];
}

ok(true, $data);