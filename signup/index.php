<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";

if(!isset($_SESSION['id'])){

    if($_SERVER['REQUEST_METHOD'] == "POST"){
        checkHeadersCSRF();

        //Database primary parameters
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password1 = trim($_POST['password-1']);
        $password2 = trim($_POST['password-2']);
            
        //Uploading a profile picture
        $raw_img = $_FILES['img'];
        $file_name = $raw_img['name'];
        $tmp_name = $raw_img['tmp_name'];
        $file_dir = "../src/assets/pfps/".$file_name;
        move_uploaded_file($tmp_name, $file_dir);

        //Database secondary parameters
        $bio = trim($_POST['bio']);
        $private = $_POST['privacy'];
        $img = $raw_img['name'];

        $query1 = "select * from users where username = '$username'";
        $query2 = "select * from users where email = '$email'";

        if(empty($username) || empty($email) || empty($password1) || empty($password2)){
            echo json_encode(['status' => 'error', 'message' => 'All inputs are required.']);
            return;
        }

        if(str_contains($username, " ")){
            echo json_encode(['status' => 'error', 'message' => 'Username cannot contain space.']);
            return;
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email.']);
            return;
        }

        if(strlen($password1) < 8){
            echo json_encode(['status' => 'error', 'message' => 'Password must contain at least 8 characters.']);
            return;
        }

        if($password1 !== $password2){
            echo json_encode(['status' => 'error', 'message' => 'Passwords are not identical.']);
            return;
        }

        if(mysqli_num_rows(mysqli_query($con, $query1)) > 0 && mysqli_num_rows(mysqli_query($con, $query2)) > 0){
            echo json_encode(['status' => 'error', 'message' => 'Cannot use the provided credentials.']);
            return;
        }

        if(empty($img)){
            $img = "pfp.jpg";
        }
        $id = bin2hex(random_bytes(8));
        $date = date('F Y');
        
        //Create the account and log the user in
        $query = "insert into users (id, username, email, password, bio, img, online, private, date) values ('$id', '$username', '$email', '$password1', '$bio', '$img', 1, '$private', '$date')";
        mysqli_query($con, $query);

        include $_SERVER['DOCUMENT_ROOT'] . "/src/config/session.php";
        
        $_SESSION['id'] = $id;
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
                <p>Or sign in to your account <a href="/signin">here</a></p>
            </div>
        </div>
        <div class="alert" style="display: none">
            <div class="cooldown-circle"></div>
            <span class="alert-text"></span>
        </div>
        <form class="signup-form" method="post" enctype="multipart/form-data" novalidate>
            <div class="form-step step-1">
                <div class="username-email-wrapper">
                    <input type="username" class="input username" name="username" placeholder="Username" pattern="[A-Za-z0-9_]+" maxlength="15">
                    <input type="email" class="input email" name="email" placeholder="Email">
                </div>
                <div class="password-wrapper">
                    <input type="password" class="input password pass-1" name="password-1" placeholder="Password">
                    <button type="button" class="s-h s-h-1"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" class="input password pass-2" name="password-2" placeholder="Confirm password">
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
            <div class="form-step step-2" style="display: none">
                <div class="bio-pfp-wrapper">
                    <div class="bio-privacy-wrapper">
                        <textarea class="input bio" name="bio" maxlength="150" placeholder="Write a short bio...&#10;(Optional)"></textarea>
                        <div class="privacy">
                            <label>Account's privacy:</label>
                            <select name="privacy" class="input privacy-select">
                                <option value="0">Public</option>
                                <option value="1">Private</option>
                            </select>
                        </div>
                    </div>
                    <div class="input img-input">
                        <p>
                            Choose a profile picture (Optional)<br>
                        </p>
                        <i class="fa-solid fa-image"></i>
                    </div>
                    <input type="file" name="img" class="pfp" accept="image/jpg, image/jpeg, image/png">
                </div>
                <div class="btn-wrapper">
                    <button type="submit" class="btn primary-btn submit">Finish<i class="fa-solid fa-right-to-bracket"></i></button>
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