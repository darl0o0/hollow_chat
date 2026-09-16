<?php
//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
if(!isset($_SESSION['id'])){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        
        checkHeadersCSRF();
        // Credentials
        $email = $_POST['email'];
        $password = $_POST['password'];
        if(!empty($email) && !empty($password)){
            
            $query = "select * from users where email = '$email'";
            $result = mysqli_query($con, $query);
    
            if($result){
                if(mysqli_num_rows($result)>0){
                    
                    //If an account with the provided email exists:
                    $user_data = mysqli_fetch_assoc($result);
                    if($user_data['password'] === $password){
    
                        //Log the user in
                        $query1 = "update users set online = 1 where email = '$email'";
                        mysqli_query($con, $query1);
                        $_SESSION['id'] = $user_data['id'];
                        echo json_encode(['status' => 'ok', 'message' => 'Redirecting...']);
                    }
                    else{
                        echo json_encode(['status' => 'error', 'message' => 'Wrong password.']);
                    }
                }
                else{
                    echo json_encode(['status' => 'error', 'message' => 'The provided credentials do not exist.']);
                }
            }
            else{
                echo json_encode(['status' => 'error', 'message' => 'Oops! Something went wrong.']);
            }
        }
        else{
            echo json_encode(['status' => 'error', 'message' => 'All inputs are required.']);
        }
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