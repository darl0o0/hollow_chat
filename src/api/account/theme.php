<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

guard('PUT');

$input = json_decode(file_get_contents('php://input'), true);

$user_data = check_login($con);
$id = $user_data['id'];

$theme = $input['theme'];

// Update theme
$stmt_update = mysqli_prepare($con, "update users set theme = ? where id = ?");
mysqli_stmt_bind_param($stmt_update, "ss", $theme, $id);
mysqli_stmt_execute($stmt_update);
mysqli_stmt_close($stmt_update);

ok(true, ($theme.", ". $id));