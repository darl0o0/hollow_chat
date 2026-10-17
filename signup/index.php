<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

if(!isset($_SESSION['id'])){

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        checkHeadersCSRF();
        $input = json_decode(file_get_contents('php://input'), true);

        //Database primary parameters
        $username = trim($input['username']);
        $email = trim($input['email']);
        $password1 = trim($input['password-1']);
        $password2 = trim($input['password-2']);
        $hashed_password = password_hash($password1, PASSWORD_DEFAULT);
            
        //Uploading a profile picture
        $raw_img = $_FILES['img'];
        $file_name = $raw_img['name'];
        $tmp_name = $raw_img['tmp_name'];
        $file_dir = "../src/assets/pfps/".$file_name;
        move_uploaded_file($tmp_name, $file_dir);

        //Database secondary parameters
        $bio = trim($input['bio']);
        $private = $input['privacy'];
        $img = $raw_img['name'];

        if(empty($username) || empty($email) || empty($password1) || empty($password2)){
            ok(false, 'All inputs are required.');
        }

        if(str_contains($username, " ")){
            ok(false, 'Username cannot contain space.');
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            ok(false, 'Please enter a valid email.');
        }

        if(strlen($password1) < 8){
            ok(false, 'Please provide an 8 characters long password.');
        }

        if($password1 !== $password2){
            ok(false, 'Passwords do not match.');
        }

        $stmt_user = mysqli_prepare($con, "select * from users where username = ? or email = ?");
        mysqli_stmt_bind_param($stmt_user, "ss", $username, $email);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);

        $row_user = mysqli_fetch_all($result_user, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_user);

        if(count($row_user) > 0){
            ok(false, 'Cannot use the provided credentials.');
        }

        if(empty($img)){
            $img = "pfp.jpg";
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
        ok(true, 'Redirecting...');
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
    
    <link rel="stylesheet" href="/src/css/signup.css">
    <script type="module" src="/src/js/init/signup.js" defer></script>
    <title>Sign up</title>
</head>
<body>
    <div class="register-container signup-container">
        <div class="register-header">
            <div class="register-logo">
                <img src="/src/assets/logo/light_logo.png">
            </div>
            <div class="brv"></div>
            <div class="title-or-wrapper">
                <h1>Sign up</h1>
                <p>Or sign in to your account <a href="/signin/">here</a></p>
            </div>
        </div>
        <div class="alert-placeholder"></div>
        <form class="signup-form" method="post" enctype="multipart/form-data" novalidate>
            <div class="form-step step-1 active-form-step">
                <div class="username-email-wrapper">
                    <input type="username" class="input username required" name="username" placeholder="Username" pattern="[A-Za-z0-9_]+" maxlength="15">
                    <input type="email" class="input email required" name="email" placeholder="Email">
                </div>
                <div class="password-wrapper">
                    <input type="password" class="input password pass-1 required match" name="password-1" placeholder="Password">
                    <button type="button" class="s-h s-h-1"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" class="input password pass-2 required match" name="password-2" placeholder="Confirm password">
                    <button type="button" class="s-h s-h-2"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="btn-wrapper">
                    <button type="button" class="btn next primary-btn">Next<i class="fa-solid fa-arrow-right"></i></button>
                    <button class="btn google secondary-btn" type="button">
                        <img src="/src/assets/google.png"></img>
                        <span>Sign in with Google</span>
                    </button>
                </div>
            </div>
            <div class="form-step step-2">
                <div class="bio-pfp-wrapper">
                    <div class="bio-privacy-wrapper">
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
                <div class="btn-wrapper">
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