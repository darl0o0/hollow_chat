<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line){
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if($_SERVER['REQUEST_METHOD'] == "POST"){
    try{
        guard('POST');
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'];

        if(!$token){
            respondError('Google token is missing', 400);
        }

        $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
        $googleResponse = curl_exec($ch);

        $googleUser = json_decode($googleResponse, true);

        if(empty($googleUser['email'])){
            respondError('Failed to authenticate with Google.', 400);
        }

        $email = $googleUser['email'];
        $username = $googleUser['name'] ?? explode('@', $email)[0];
        $googleAvatarUrl = $googleUser['picture'];

        $stmt_check_user = mysqli_prepare($con, 'select id from users where email = ?');
        mysqli_stmt_bind_param($stmt_check_user, "s", $email);
        mysqli_stmt_execute($stmt_check_user);
        $result_user = mysqli_stmt_get_result($stmt_check_user);

        $row_user = mysqli_fetch_assoc($result_user);
        mysqli_stmt_close($stmt_check_user);

        if($row_user){

            //Login
            $stmt_set_online = mysqli_prepare($con, "update users set online = 1 where id = ?");
            mysqli_stmt_bind_param($stmt_set_online, "s", $row_user['id']);
            mysqli_stmt_execute($stmt_set_online);
            mysqli_stmt_close($stmt_set_online);

            include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
            $_SESSION['id'] = $row_user['id'];
            
            respondOk('Redirecting...');
        }
        else{
            $img = null;

            if($googleAvatarUrl){
                $imageContent = @file_get_contents($googleAvatarUrl);
                if($imageContent !== false){
                    $tempFilePath = tempnam(sys_get_temp_dir() , 'google_pfp_');
                    file_put_contents($tempFilePath, $imageContent);

                    $mockFile = [
                        'name' => 'google_avatar.jpg',
                        'type' => 'image/jpeg',
                        'tmp_name' => $tempFilePath,
                        'error' => 0,
                        'size' => filesize($tempFilePath)
                    ];

                    $img = processAndSaveProfilePic($mockFile);

                    if(file_exists($tempFilePath)){
                        unlink($tempFilePath);
                    }
                }
            }

            $id = bin2hex(random_bytes(8));
            $date = date('F Y');
            
            //Create the account and login
            $stmt_register = mysqli_prepare($con, "insert into users (id, username, email, is_google_linked, img, online, private, date) values (?, ?, ?, 1, ?, 1, 0, ?)");
            mysqli_stmt_bind_param($stmt_register, "sssss", $id, $username, $email, $img, $date);
            mysqli_stmt_execute($stmt_register);
            mysqli_stmt_close($stmt_register);

            include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
            
            $_SESSION['id'] = $id;
            respondOk('Redirecting...');
        }
        
        // Credentials

    }
    catch(Throwable $e){
        writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

        respondError("Internal Server Error.", 500);
    }
}