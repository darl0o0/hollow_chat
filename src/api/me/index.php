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

try{
    guard('GET');

    $data = [
        'id' => $user_data['id'],
        'username' => $user_data['username'],
        'email' => $user_data['email'],
        'is_google_linked' => filter_var($user_data['is_google_linked'], FILTER_VALIDATE_BOOLEAN),
        'bio' => $user_data['bio'],
        'img' => $user_data['img'],
        'isPrivate' => filter_var($user_data['private'], FILTER_VALIDATE_BOOLEAN),
        'theme' => $user_data['theme']
    ];

    respondOk($data);
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}