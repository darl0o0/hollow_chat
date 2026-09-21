<?php

// Connect to database
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "hollow_chat";

try{
    $con = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
}
catch(mysqli_sql_exception $e){
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    die();
}