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
$requests_query = "select * from friends where user2 = '$id' and status = 'waiting'";
$num_of_requests = mysqli_num_rows(mysqli_query($con, $requests_query));

// Number of messages that are not read yet
$query_unread_messages = "select * from messages where rid = '$id' and isRead = '0'";
$num_of_unread_messages = mysqli_num_rows(mysqli_query($con, $query_unread_messages));
?>