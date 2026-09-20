<?php
//Initialization
if(session_status() === PHP_SESSION_NONE){
    include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

$user_data = check_login($con);
$id = $user_data['id'];

//The number of incoming requests

$stmt_incoming_requests_count = mysqli_prepare($con, "select count(*) from friends where user2 = ? and status = 'waiting'");
mysqli_stmt_bind_param($stmt_incoming_requests_count, "s", $id);
mysqli_stmt_execute($stmt_incoming_requests_count);
$result_incoming_requests_count = mysqli_stmt_get_result($stmt_incoming_requests_count);

$row_incoming_count = mysqli_fetch_row($result_incoming_requests_count);
$num_of_requests = $row_incoming_count[0];
mysqli_stmt_close($stmt_incoming_requests_count);

// Number of messages that are not read yet
$stmt_unread_count = mysqli_prepare($con, "select count(*) from messages where rid = ? and isRead = '0'");
mysqli_stmt_bind_param($stmt_unread_count, "s", $id);
mysqli_stmt_execute($stmt_unread_count);
$result_unread_count = mysqli_stmt_get_result($stmt_unread_count);

$row_count = mysqli_fetch_row($result_unread_count);
$num_of_unread_messages = $row_count[0];
mysqli_stmt_close($stmt_unread_count);