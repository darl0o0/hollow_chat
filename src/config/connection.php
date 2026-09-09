<?php

// Connect to database
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "hollow_chat";
if(!$con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname)){
    die("Failed to connect!");
}
?>