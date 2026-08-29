<?php
//Initialization
session_start();
include("../connection.php");
include("../functions.php");
$isError = "none";
$error = "";

if(!isset($_SESSION['id'])){

    //Listening to post request
    if($_SERVER['REQUEST_METHOD'] == "POST"){

        //Uploading a profile picture
        $raw_img = $_FILES['img'];
        $file_name = $raw_img['name'];
        $tmp_name = $raw_img['tmp_name'];
        $file_dir = "../res/assets/pfps/".$file_name;
        move_uploaded_file($tmp_name, $file_dir);

        //Database insert parameters
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $bio = $_POST['bio'];
        $private = $_POST['privacy'];
        $img = $raw_img['name'];
        $date = date('F Y');

        //Checking credentials
        if(!empty($username) && !empty($email) && !empty($password)){
            $query1 = "select * from users where username = '$username'";
            $query2 = "select * from users where email = '$email'";
            if(mysqli_num_rows(mysqli_query($con, $query1)) == 0 && mysqli_num_rows(mysqli_query($con, $query2)) == 0){
                if(empty($img)){
                    $img = "pfp.jpg";
                }
                $id = bin2hex(random_bytes(8));
                $query = "insert into users (id, username, email, password, bio, img, online, private, date) values ('$id', '$username', '$email', '$password', '$bio', '$img', 1, '$private', '$date')";
                mysqli_query($con, $query);
                $_SESSION['id'] = $id;
                header("Location: /");
                die;
            }
            else{
                $isError = "flex";
                $error = "The provided credentials already exist";
            }
        }
    }
}
else{
    header("Location: /");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign up</title>
    <link rel="shortcut icon" href="/res/assets/logo/hollow_icon_light.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/res/css/global.css">
    <link rel="stylesheet" href="/res/css/signup.css">
</head>
<body>
    <div class="signup-container">
        <div class="logo">
            <img src="/res/assets/logo/light_logo.png">
        </div>
        <div class="br"></div>
        <h1>Sign up</h1>
        <p>Or sign in to your account <a href="/signin">here</a></p>
        <div class="error" style="display: <?php echo $isError ?>">
            <div class="cooldown-circle"></div>
            <span class="error-text"><?php echo $error ?></span>
        </div>
        <form class="signup-form" method="post" enctype="multipart/form-data">
            <div class="form-step step-1 active-step">
                <input type="username" class="input username" name="username" placeholder="Username" pattern="[A-Za-z0-9_]+" maxlength="15">
                <input type="email" class="input email" name="email" placeholder="Email">
                <div class="passwords-container">
                    <div class="password-wrapper">
                        <input type="password" class="input password pass-1" name="password" placeholder="Password">
                        <button type="button" class="s-h s-h-1"><i class="fa fa-eye" aria-hidden="true"></i></button>
                    </div>
                    <div class="password-wrapper">
                        <input type="password" class="input password pass-2" name="password" placeholder="Confirm password">
                        <button type="button" class="s-h s-h-2"><i class="fa fa-eye" aria-hidden="true"></i></button>
                    </div>
                </div>
                <button type="button" class="btn confirm next" onclick="gotoStep(2)">Next<i class="fa-solid fa-arrow-right"></i></button>
                <button class="btn google" type="button">
                    <img src="/res/assets/google.png"></img>
                    <span>Sign in with Google</span>
                </button>
            </div>
            <div class="form-step step-2">
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
                    <div class="input img-input">Choose a profile picture<br>(Optional)<br><i class="fa-solid fa-image"></i></div>
                    <input type="file" name="img" class="pfp" accept="image/jpg, image/jpeg, image/png">
                </div>
                <button type="submit" class="btn confirm submit">Finish<i class="fa-solid fa-right-to-bracket"></i></button>
                <button class="btn back" type="button" onclick="gotoStep(1)">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Go back</span>
                </button>
            </div>
        </form>
    </div>
    <script>
        var currentStep = 1;
        function error(textError){
            document.querySelector('.error').style.display = "flex";
            document.querySelector('.error-text').innerHTML = textError;
            setTimeout(() => {
                document.querySelector('.error').style.display = "none";
                document.querySelector('.error-text').innerHTML = '';
            }, 5000);
        }
        document.querySelector('.signup-form').addEventListener('submit', (e)=>{
            if(currentStep != 2){
                e.preventDefault();
                gotoStep(2);
            }
        });
        //To click on the drag and drop field instead of the file input
        document.querySelector(".img-input").addEventListener("click", (e)=>{
            if(document.querySelector('.img-input .fa-x') && document.querySelector('.img-input .fa-x').contains(e.target)){
                document.querySelector(".img-input").innerHTML = "Choose a profile picture<br>(Optional)<br><i class='fa-solid fa-image'></i>";
                document.querySelector(".pfp").value = "";
            }
            else{
                document.querySelector(".pfp").click();
            }
        });
        document.querySelector(".pfp").onchange = function (){
            document.querySelector(".img-input").innerHTML = "<i class='fa-solid fa-x'></i><img src='"+URL.createObjectURL(document.querySelector(".pfp").files[0])+"' style='object-fit: cover;width: 100px;height: 100px;'>";
        }
        //To toggle between show and hide password
        var isVisisble1 = false;
        document.querySelector(".s-h-1").onclick = function (){
            if(isVisisble1){
                document.querySelector(".pass-1").type = 'password';
                document.querySelector(".s-h-1").innerHTML = "<i class='fa fa-eye' aria-hidden='true'></i>"
                isVisisble1 = false;
            }
            else{
                document.querySelector(".pass-1").type = 'text';
                document.querySelector(".s-h-1").innerHTML = "<i class='fa fa-eye-slash' aria-hidden='true' style='margin-right: -1px'></i>"
                isVisisble1 = true;
            }
        }
        var isVisisble2 = false;
        document.querySelector(".s-h-2").onclick = function (){
            if(isVisisble2){
                document.querySelector(".pass-2").type = 'password';
                document.querySelector(".s-h-2").innerHTML = "<i class='fa fa-eye' aria-hidden='true'></i>"
                isVisisble2 = false;
            }
            else{
                document.querySelector(".pass-2").type = 'text';
                document.querySelector(".s-h-2").innerHTML = "<i class='fa fa-eye-slash' aria-hidden='true' style='margin-right: -1px'></i>"
                isVisisble2 = true;
            }
        }
        function isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email.trim());
        }
        function gotoStep(step){
            if(step == 2){
                const username = document.querySelector('.username').value;
                const email = document.querySelector('.email').value;
                const pass1 = document.querySelector('.pass-1').value;
                const pass2 = document.querySelector('.pass-2').value;
                if(username && email && pass1 && pass2){
                    if(!document.querySelector('.username').value.includes(" ")){
                        if(isValidEmail(email)){
                            if(pass1.length >= 8){
                                if(pass1 == pass2){
                                    document.querySelectorAll('.form-step').forEach(formStep => {
                                        formStep.style.display = 'none';
                                    });
                                    document.querySelector(`.step-${step}`).style.display = 'inline';
                                    currentStep++;
                                }
                                else{
                                    error("Passwords are not identical.");
                                }
                            }
                            else{
                                error("Password must contain at least 8 characters.");
                            }
                        }
                        else{
                            error("Please enter a valid email.");
                        }
                    }
                    else{
                        error("Username cannot contain space.");
                    }
                }
                else{
                    error("All inputs are required.");
                }
            }
            else if(step == 1){
                document.querySelectorAll('.form-step').forEach(formStep => {
                    formStep.style.display = 'none';
                });
                document.querySelector(`.step-${step}`).style.display = 'inline';
                currentStep--;
            }
        }
    </script>
</body>
</html>