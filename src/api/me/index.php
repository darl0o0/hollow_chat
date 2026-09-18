<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$data = [
    'id' => $user_data['id'],
    'username' => $user_data['username'],
    'email' => $user_data['email'],
    'bio' => $user_data['bio'],
    'img' => $user_data['img'],
    'isPrivate' => filter_var($user_data['private'], FILTER_VALIDATE_BOOLEAN)
];

echo json_encode(['status' => 'ok', 'response' => $data]);
die();