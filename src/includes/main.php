<?php
//Initialization
if(session_status() === PHP_SESSION_NONE){
    include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

$user_data = check_login($con);
$id = $user_data['id'];

// Number of messages that are not seen yet
$stmt_unread_count = mysqli_prepare($con, "select count(*) from messages where rid = ? and seen = '0'");
mysqli_stmt_bind_param($stmt_unread_count, "s", $id);
mysqli_stmt_execute($stmt_unread_count);
$result_unread_count = mysqli_stmt_get_result($stmt_unread_count);

$row_count = mysqli_fetch_row($result_unread_count);
$num_of_unread_messages = $row_count[0];
mysqli_stmt_close($stmt_unread_count);