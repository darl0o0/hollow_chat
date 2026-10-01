<?php

//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
requireStrictPost();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$data = [];

$clean_ids = [];
$raw_ids = json_decode($_POST['ids'], true);
foreach($raw_ids as $raw_id){
    $clean_ids[] = (string)$raw_id;
}
$clean_ids = array_slice($clean_ids, 0, 20);

$placeholders = implode(',', array_fill(0, count($clean_ids), '?'));
$types = str_repeat('s', count($clean_ids));

$query = "select id, username, img, online, bio, private, date from users where id in ($placeholders)";

$stmt_users = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt_users, $types, ...$clean_ids);
mysqli_stmt_execute($stmt_users);
$result_users = mysqli_stmt_get_result($stmt_users);

$row_users = mysqli_fetch_all($result_users, MYSQLI_ASSOC);
$data = $row_users;

echo json_encode([
    'status' => 'ok',
    'response' => $data
]);
mysqli_stmt_close($stmt_users);
die();