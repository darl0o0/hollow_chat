<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

ini_set('display_errors', '0');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

if(isset($_SESSION['id'])){
    header("Location: /");
    return;
}

if($_SERVER['REQUEST_METHOD'] == "POST"){
    try{
        guard('POST');
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Credentials
        $email = trim($input['email']);
        $password = trim($input['password']);

        if(empty($email) || empty($password)){
            respondError('All inputs are required.', 400);
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            respondError('Please enter a valid email.', 400);
        }

        $stmt_user = mysqli_prepare($con, "select id, password from users where email = ?");
        mysqli_stmt_bind_param($stmt_user, "s", $email);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);

        $row_user = mysqli_fetch_assoc($result_user);
        mysqli_stmt_close($stmt_user);

        if(!$row_user || !password_verify($password, $row_user['password'])){
            respondError('Invalid email or password.', 400);
        }

        //Login
        $stmt_set_online = mysqli_prepare($con, "update users set online = 1 where email = ?");
        mysqli_stmt_bind_param($stmt_set_online, "s", $email);
        mysqli_stmt_execute($stmt_set_online);
        mysqli_stmt_close($stmt_set_online);

        include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
        $_SESSION['id'] = $row_user['id'];
        
        respondOk('Redirecting...');
    }
    catch(Throwable $e){
        writeLog("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

        respondError("Internal Server Error.", 500);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    
    <link rel="stylesheet" href="/src/css/signin.css">
    <script type="module" src="/src/js/init/signin.js" defer></script>
    <script type="module" src="/src/js/init/auth-google.js" defer></script>
    <title>Sign in</title>
</head>
<body>
    <div class="register-container signin-container">
        <div class="register-header">
            <div class="register-logo">
                <img class="asset" src="/src/assets/logo/light_logo.png">
            </div>
            <div class="brv"></div>
            <div class="wrap-title-link">
                <h1>Sign in</h1>
                <p>Or create an account <a href="/signup/">here</a></p>
            </div>
        </div>
        <div class="alert-placeholder"></div>
        <form method="post" class="signin-form" novalidate autocomplete="off">
            <input type="email" class="input email required" name="email" placeholder="Email" autocomplete="email">
            <div class="wrap-password">
                <input type="password" class="input password required" name="password" placeholder="Password" autocomplete="password">
                <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
            </div>
            <div class="wrap-btn">
                <button class="btn submit primary-btn" type="button">Sign in <i class="fa-solid fa-right-to-bracket"></i></button>
                <button class="btn google secondary-btn" type="button"><img src="/src/assets/google.png"></img>Sign in with Google</button>
            </div>
        </form>
    </div>
</body>
</html>