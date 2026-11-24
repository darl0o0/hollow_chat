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
    $method = $_SERVER['REQUEST_METHOD'];
    if(isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])){
        $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    }
    
    switch($method){
        case 'GET':
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
        break;
        case 'PATCH':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $_POST['action'] ?? $input['action'] ?? null;

            switch($action){
                case 'cred':
                    guard('POST');

                    $username = $_POST['username'];
                    $email = $_POST['email'];
                    $bio = $_POST['bio'];

                    $img = $user_data['img'];
                    if(isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK){
                        $img = processAndSaveProfilePic($_FILES['img']);
                    }

                    if(empty($username) || empty($email)){
                        respondError('All inputs are required.', 400);
                    }

                    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                        respondError('Please enter a valid email.', 400);
                    }

                    $stmt_user = mysqli_prepare($con, "select * from users where (username = ? or email = ?) and id != ?");
                    mysqli_stmt_bind_param($stmt_user, "sss", $username, $email, $id);
                    mysqli_stmt_execute($stmt_user);
                    $result_user = mysqli_stmt_get_result($stmt_user);

                    if(mysqli_num_rows($result_user) > 0){
                        respondError('Username or email is already taken.', 400);
                    }

                    $isUnchanged = $username == $user_data['username'] &&
                                $email == $user_data['email'] &&
                                $bio == $user_data['bio'] &&
                                empty($img);

                    if($isUnchanged){
                        respondError('No changes were made.', 400);
                    }

                    // Update the credentials
                    $stmt_update = mysqli_prepare($con, "update users set username = ?, email = ?, img = ?, bio = ? where id = ?");
                    mysqli_stmt_bind_param($stmt_update, "sssss", $username, $email, $img, $bio, $id);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);

                    respondOk('Profile updated.');
                break;
                case 'theme':
                    guard('PATCH');

                    $theme = $input['theme'];

                    // Update theme
                    $stmt_update = mysqli_prepare($con, "update users set theme = ? where id = ?");
                    mysqli_stmt_bind_param($stmt_update, "ss", $theme, $id);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);

                    respondOk('Theme updated');
                break;
                default:
                    respondError('Invalid action.', 400);
                break;
            }
        break;
        case 'DELETE':
            guard('DELETE');

            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? null;

            switch($action){
                case 'logout':
                    $stmt_set_offline = mysqli_prepare($con, "update users set online = 0 where id = ?");
                    mysqli_stmt_bind_param($stmt_set_offline, "s", $id);
                    mysqli_stmt_execute($stmt_set_offline);
                    mysqli_stmt_close($stmt_set_offline);

                    // Destroy cookies
                    $_SESSION = array();

                    if(ini_get("session.use_cookies")){
                        $params = session_get_cookie_params();
                        setcookie(
                            session_name(),
                            '',
                            time() - 42000,
                            $params['path'],
                            $params['domain'],
                            $params['secure'],
                            $params['httponly']
                        );
                    }
                    respondOk('Logged out');
                break;
                case 'delete':
                    
                    // Credentials
                    $email = $input['email'];
                    $password = $input['password'];

                    if(empty($email) || empty($password)){
                        respondError('All inputs are required.', 400);
                    }

                    if($email !== $user_data['email'] || !password_verify($password, $user_data['password'])){
                        respondError('Invalid email or password.', 400);
                    }

                    //Delete user
                    $stmt_delete_user = mysqli_prepare($con, "delete from users where id = ?");
                    mysqli_stmt_bind_param($stmt_delete_user, "s", $id);
                    mysqli_stmt_execute($stmt_delete_user);
                    mysqli_stmt_close($stmt_delete_user);

                    // Delete friends
                    $stmt_delete_friend = mysqli_prepare($con, "delete from friends where user1 = ? or user2 = ?");
                    mysqli_stmt_bind_param($stmt_delete_friend, "ss", $id, $id);
                    mysqli_stmt_execute($stmt_delete_friend);
                    mysqli_stmt_close($stmt_delete_friend);

                    // Destroy cookies
                    $_SESSION = array();

                    if(ini_get("session.use_cookies")){
                        $params = session_get_cookie_params();
                        setcookie(
                            session_name(),
                            '',
                            time() - 42000,
                            $params['path'],
                            $params['domain'],
                            $params['secure'],
                            $params['httponly']
                        );
                    }
                    session_destroy();
                    respondOk('Account Deleted.');
                break;
                default:
                    respondError('Invalid action.', 400);
                break;
            }
        break;
    }
}
catch(Throwable $e){
    writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

    respondError("Internal Server Error.", 500);
}