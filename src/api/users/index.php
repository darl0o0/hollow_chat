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
$data = [];

$q = $_GET['q'];

try{
    guard('GET');

    if(isset($q)){
        $q_term = "%$q%";

        $stmt_search_users = mysqli_prepare($con, "select * from users where username like ?");
        mysqli_stmt_bind_param($stmt_search_users, "s", $q_term);
        mysqli_stmt_execute($stmt_search_users);
        $result_search_users = mysqli_stmt_get_result($stmt_search_users);
    
        $search_users = mysqli_fetch_all($result_search_users, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_search_users);

        if(count($search_users) > 0){
            foreach($search_users as $user){
                $data[] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'bio' => $user['bio'],
                    'img' => $user['img'],
                    'isOnline' => $user['online']
                ];
            }
        }
        else{
            $data = null;
        }
    }
    else{
        $stmt_users = mysqli_prepare($con, "select * from users where not (id = ?) order by created_at desc");
        mysqli_stmt_bind_param($stmt_users, "s", $id);
        mysqli_stmt_execute($stmt_users);
        $result_users = mysqli_stmt_get_result($stmt_users);
    
        $users = mysqli_fetch_all($result_users, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_users);
    
        foreach($users as $user){
            $data[] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'bio' => $user['bio'],
                'img' => $user['img'],
                'isOnline' => $user['online']
            ];
        }
    }

    respondOk($data);
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}