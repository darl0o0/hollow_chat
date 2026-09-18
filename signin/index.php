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
        $query = "select * from users where email = '$email'";
        $result = mysqli_query($con, $query);
        $user_data = mysqli_fetch_assoc($result);

        if(empty($email) || empty($password)){
            echo json_encode(['status' => 'error', 'message' => 'All inputs are required.']);
            return;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email.']);
            return;
        }

        if(!$result){
            echo json_encode(['status' => 'error', 'message' => 'Oops! Something went wrong.']);
            return;
        }

        if(mysqli_num_rows($result) == 0){
            echo json_encode(['status' => 'error', 'message' => 'The provided credentials do not exist.']);
            return;
        }

        if($user_data['password'] !== $password){
            echo json_encode(['status' => 'error', 'message' => 'Wrong password.']);
            return;
        }

        //Log in
        $query1 = "update users set online = 1 where email = '$email'";
        mysqli_query($con, $query1);

        include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
        $_SESSION['id'] = $user_data['id'];
        
        echo json_encode(['status' => 'ok', 'message' => 'Redirecting...']);
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
    <style>
    </style>
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
        <div class="alert" style="display: none">
            <div class="cooldown-circle"></div>
            <span class="alert-text"></span>
        </div>
        <form method="post" class="signin-form" novalidate>
            <input type="email" class="input email" name="email" placeholder="Email" autocomplete="off">
            <div class="password-wrapper">
                <input type="password" class="input password" name="password" placeholder="Password">
                <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
            </div>
            <div class="btn-wrapper">
                <button class="btn submit primary-btn" type="submit">Sign in <i class="fa-solid fa-right-to-bracket"></i></button>
                <button class="btn google  secondary-btn"><img src="/src/assets/google.png"></img>Sign in with Google</button>
            </div>
        </form>
    </div>
</body>
</html>