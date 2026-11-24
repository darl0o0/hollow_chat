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

        //Database primary parameters
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password1 = trim($_POST['password-1']);
        $password2 = trim($_POST['password-2']);
        $hashed_password = password_hash($password1, PASSWORD_DEFAULT);

        //Database secondary parameters
        $bio = trim($_POST['bio']);
        $private = $_POST['privacy'];
        $img = processAndSaveProfilePic($_FILES['img']);

        if(empty($username) || empty($email) || empty($password1) || empty($password2)){
            respondError('All inputs are required.', 400);
        }

        if(str_contains($username, " ")){
            respondError('Username cannot contain space.', 400);
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            respondError('Please enter a valid email.', 400);
        }

        if(strlen($password1) < 8){
            respondError('Please provide an 8 characters long password.', 400);
        }

        if($password1 !== $password2){
            respondError('Passwords do not match.', 400);
        }

        $stmt_user = mysqli_prepare($con, "select * from users where username = ? or email = ?");
        mysqli_stmt_bind_param($stmt_user, "ss", $username, $email);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);

        $row_user = mysqli_fetch_all($result_user, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_user);

        if(count($row_user) > 0){
            respondError('Cannot use the provided credentials.', 400);
        }
        
        $id = bin2hex(random_bytes(8));
        $date = date('F Y');
        
        //Create the account and login
        $stmt_register = mysqli_prepare($con, "insert into users (id, username, email, password, bio, img, online, private, date) values (?, ?, ?, ?, ?, ?, 1, ?, ?)");
        mysqli_stmt_bind_param($stmt_register, "ssssssis", $id, $username, $email, $hashed_password, $bio, $img, $private, $date);
        mysqli_stmt_execute($stmt_register);
        mysqli_stmt_close($stmt_register);

        include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
        
        $_SESSION['id'] = $id;
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
    
    <link rel="stylesheet" href="/src/css/signup.css">
    <script type="module" src="/src/js/init/signup.js" defer></script>
    <title>Sign up</title>
</head>
<body>
    <div class="register-container signup-container">
        <div class="register-header">
            <div class="register-logo">
                <span class="asset" style="--asset-url: url('/src/assets/logo/logo-text.svg')"></span>
            </div>
            <div class="brv"></div>
            <div class="wrap-title-link">
                <h1>Sign up</h1>
                <p>Or sign in to your account <a href="/signin/">here</a></p>
            </div>
        </div>
        <div class="alert-placeholder"></div>
        <form class="signup-form" method="post" enctype="multipart/form-data" novalidate autocomplete="off">
            <div class="form-step step-1 active-form-step">
                <div class="wrap-username-email">
                    <input type="username" class="input username required" name="username" placeholder="Username" pattern="[A-Za-z0-9_]+" maxlength="15" readonly>
                    <input type="email" class="input email required" name="email" placeholder="Email" readonly>
                </div>
                <div class="wrap-password">
                    <input type="password" class="input password pass-1 required match" name="password-1" placeholder="Password" readonly>
                    <button type="button" class="s-h s-h-1"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="wrap-password">
                    <input type="password" class="input password pass-2 required match" name="password-2" placeholder="Confirm password" readonly>
                    <button type="button" class="s-h s-h-2"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="wrap-btn">
                    <button type="button" class="btn next primary-btn">Next<i class="fa-solid fa-arrow-right"></i></button>
                    <button class="btn google secondary-btn" type="button">
                        <img src="/src/assets/google.png"></img>
                        <span>Sign in with Google</span>
                    </button>
                </div>
            </div>
            <div class="form-step step-2">
                <div class="wrap-bio-pfp">
                    <div>
                        <textarea class="input bio" name="bio" maxlength="150" placeholder="Write a short bio...&#10;(Optional)"></textarea>
                        <div class="privacy">
                            <label>Account privacy:</label>
                            <select name="privacy" class="input privacy-select">
                                <option value="0">Public</option>
                                <option value="1">Private</option>
                            </select>
                        </div>
                    </div>
                    <div class="input upload-field">
                        <p>
                            Choose a profile picture (Optional)<br>
                        </p>
                        <i class="fa-solid fa-image"></i>
                    </div>
                    <input type="file" name="img" class="pfp" accept="image/jpg, image/jpeg, image/png">
                </div>
                <div class="wrap-btn">
                    <button type="button" class="btn primary-btn submit">Finish<i class="fa-solid fa-right-to-bracket"></i></button>
                    <button class="btn secondary-btn back" type="button">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Go back</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>