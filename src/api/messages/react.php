<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$user_data = check_login($con);
$id = $user_data['id'];

try{
    if($_SERVER['REQUEST_METHOD'] == 'PUT'){
        guard('PUT');
        
        $input = json_decode(file_get_contents('php://input'), true);

        $mid = $input['mid'];
        $emoji = $input['emoji'];
        $reactor = $input['by'];

        // Insert message
        $stmt_react = mysqli_prepare($con, "update messages set react = ?, reactor = ? where id = ?");
        mysqli_stmt_bind_param($stmt_react, "sss", $emoji, $reactor, $mid);
        mysqli_stmt_execute($stmt_react);
        mysqli_stmt_close($stmt_react);
        
        respondOk('reacted');
    }
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}