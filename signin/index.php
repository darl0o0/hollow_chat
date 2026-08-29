<?php

//Initialization
session_start();
include("../connection.php");
include("../functions.php");
$isError = "none";
$error = "";
if(!isset($_SESSION['id'])){
    if($_SERVER['REQUEST_METHOD'] == "POST"){

        //If there's no session data && the server received a post reqeust:
        $email = $_POST['email'];
        $password = $_POST['password'];
        if(!empty($email) && !empty($password)){

            //If both email and password inputs are non-empty:
            $query = "select * from users where email = '$email'";
            $result = mysqli_query($con, $query);
            if($result){
                if($result && mysqli_num_rows($result)>0){

                    //If an account with the provided email exists:
                    $user_data = mysqli_fetch_assoc($result);
                    if($user_data['password'] === $password){

                        //If the password for the provided email is correct: update the user's online status to online, create a session array (id) with the value of an automatically generated id and redirect the client to the home page.
                        $query1 = "update users set online = 1 where email = '$email'";
                        mysqli_query($con, $query1);
                        $_SESSION['id'] = $user_data['id'];
                        header("Location: /");
                        die;
                    }
                    else{
                        $isError = "block";
                        $error = "Wrong password";
                    }
                }
                else{
                    $isError = "block";
                    $error = "The provided credentials do not exist";
                }
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
        <div class="error" style="display: <?php echo $isError ?>"><?php echo $error ?></div>
        <form method="post">
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
        document.querySelector('.submit').addEventListener('click', (event)=>{
            // If the inputs are empty
            if(eval(document.querySelector('.email').value.length*document.querySelector('.password').value.length) == 0){
                event.preventDefault();
                document.querySelector('.error').style.display = "block";
                document.querySelector('.error').innerHTML = "All inputs are required";
            }
        })
        //To toggle between show and hide password
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
</body>
</html>