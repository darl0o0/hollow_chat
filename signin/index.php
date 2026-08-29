<?php
//Initialization
session_start();
include("../connection.php");
include("../functions.php");
$script = '';

if(!isset($_SESSION['id'])){
    if($_SERVER['REQUEST_METHOD'] == "POST"){

        // Credentials
        $email = $_POST['email'];
        $password = $_POST['password'];
        
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
                    header("Location: /");
                    die;
                }
                else{
                    $script = 'error("Wrong password.")';
                }
            }
            else{
                $script = 'error("The provided credentials do not exist.")';
            }
        }
        else{
            $script = 'error("Oops! Something went wrong.")';
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
    <title>Sign in</title>
    <link rel="shortcut icon" href="/res/assets/logo/hollow_icon_light.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/res/css/signin.css">
</head>
<body>
    <div class="signin-container">
        <div class="logo">
            <img src="/res/assets/logo/light_logo.png">
        </div>
        <div class="br"></div>
        <h1>Sign in</h1>
        <p>Or create an account <a href="/signup">here</a></p>
        <div class="error" style="display: none">
            <div class="cooldown-circle"></div>
            <span class="error-text"></span>
        </div>
        <form method="post" class="signin-form">
            <input type="email" class="input email" name="email" placeholder="Email" autocomplete="off">
            <div class="password-wrapper">
                <input type="password" class="input password" name="password" placeholder="Password">
                <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
            </div>
            <button class="btn submit" type="submit">Sign in <i class="fa-solid fa-right-to-bracket"></i></button>
        </form>
        <button class="btn google"><img src="/res/assets/google.png"></img>Sign in with Google</button>
    </div>
    <script>
        //The error function
        function error(textError){
            document.querySelector('.error').style.display = "flex";
            document.querySelector('.error-text').innerHTML = textError;
            setTimeout(() => {
                document.querySelector('.error').style.display = "none";
                document.querySelector('.error-text').innerHTML = '';
            }, 5000);
        }

        //Check if the email is valid
        function isValidEmail(email) {
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email.trim());
        }

        //Checking the user's provided credentials in the front-end
        document.querySelector('.signin-form').addEventListener('submit', (e)=>{
                const email = document.querySelector('.email').value;
                const pass = document.querySelector('.password').value;
                if(!email || !pass){
                    e.preventDefault();
                    error("All inputs are required.");
                }
                else if(!isValidEmail(email)){
                    e.preventDefault();
                    error("Please enter a valid email.");
                }
        })

        // Show/hide password
        var isVisisble = false;
        document.querySelector(".s-h").onclick = function (){
            if(isVisisble){
                document.querySelector(".password").type = 'password';
                document.querySelector(".s-h").innerHTML = "<i class='fa fa-eye' aria-hidden='true'></i>"
                isVisisble = false;
            }
            else{
                document.querySelector(".password").type = 'text';
                document.querySelector(".s-h").innerHTML = "<i class='fa fa-eye-slash' aria-hidden='true' style='margin-right: -1px'></i>"
                isVisisble = true;
            }
        }
    </script>
    <script><?php echo $script ?></script>
</body>
</html>