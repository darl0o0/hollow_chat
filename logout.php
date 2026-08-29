<?php
session_start();
include("connection.php");
include("functions.php");
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id = $_POST['id'];
    $query = "update users set online = 0 where id = '$id'";
    mysqli_query($con, $query);
    unset($_SESSION['id']);
    header("Location: ./signin");
}