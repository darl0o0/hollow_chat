<?php
//Initialization
session_start();
include("../connection.php");
include("../functions.php");
$user_data = check_login($con);

//Listening to post request
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $query = "select * from users where username = '$new_username'";
    $query1 = "select * from users where email = '$new_email'";
    if(mysqli_num_rows(mysqli_query($con, $query))*mysqli_num_rows(mysqli_query($con, $query1)) == 0){
        $id = $user_data['id'];
        $update_query = "update users set username = '$new_username', email = '$new_email' where id = '$id'";
        mysqli_query($con, $update_query);
        $_SESSION['user'] = $new_username;
        header("Location: ./");
        die;
    }
    else{
        echo "The provided credentials already exist.";
    }
}