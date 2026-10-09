<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

if(!isset($_SESSION['id'])){

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        checkHeadersCSRF();
        
        // Credentials
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if(empty($email) || empty($password)){
            echo json_encode([
                'status' => 'error',
                'message' => 'All inputs are required.'
            ]);
            return;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo json_encode([
                'status' => 'error',
                'message' => 'Please enter a valid email.'
            ]);
            return;
        }

        $stmt_user = mysqli_prepare($con, "select id, password from users where email = ?");
        mysqli_stmt_bind_param($stmt_user, "s", $email);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);

        $row_user = mysqli_fetch_assoc($result_user);
        mysqli_stmt_close($stmt_user);

        if(!$row_user || !password_verify($password, $row_user['password'])){
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ]);
            return;
        }

        //Login
        $stmt_set_online = mysqli_prepare($con, "update users set online = 1 where email = ?");
        mysqli_stmt_bind_param($stmt_set_online, "s", $email);
        mysqli_stmt_execute($stmt_set_online);
        mysqli_stmt_close($stmt_set_online);

        include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
        $_SESSION['id'] = $row_user['id'];
        
        echo json_encode([
            'status' => 'ok',
            'message' => 'Redirecting...'
        ]);
        die();
    }
}
else{
    header("Location: /");
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    
    <link rel="stylesheet" href="/src/css/signin.css">
    <script type="module" src="/src/js/init/signin.js" defer></script>
    <title>Sign in</title>
</head>
<body>
    <div class="register-container signin-container">
        <div class="register-header">
            <div class="register-logo">
                <img src="/src/assets/logo/light_logo.png">
            </div>
            <div class="brv"></div>
            <div class="title-or-wrapper">
                <h1>Sign in</h1>
                <p>Or create an account <a href="/signup">here</a></p>
            </div>
        </div>
        <div class="alert-placeholder"></div>
        <form method="post" class="signin-form" novalidate autocomplete="off">
            <input type="email" class="input email required" name="email" placeholder="Email" autocomplete="off">
            <div class="password-wrapper">
                <input type="password" class="input password required" name="password" placeholder="Password">
                <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
            </div>
            <div class="btn-wrapper">
                <button class="btn submit primary-btn" type="button">Sign in <i class="fa-solid fa-right-to-bracket"></i></button>
                <button class="btn google  secondary-btn" type="button"><img src="/src/assets/google.png"></img>Sign in with Google</button>
            </div>
        </form>
    </div>
</body>
</html>