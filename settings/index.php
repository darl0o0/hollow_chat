<?php
include("../register/index.php");

$isError = "none";
$error = "";

if(!isset($_SESSION['id'])){
    header("Location: ./signin");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hollow Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/res/assets/logo/hollow_icon_light.png" type="image/x-icon">
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/res/css/settings.css">
</head>
<body>
    <div class="main settings">
        <?php echo $panel ?>
        <div class="preview">
            <span class="header-title">Settings</span>
            <div class="account section">
                <div class="title-sep-wrapper">
                    <span class="section-title">Account settings</span>
                    <div class="section-sep"></div>
                </div>
                <div class="error" style="display: <?php echo $isError; ?>"><?php echo "$error"; ?></div>
                <form action="update.php" method="post">
                    <input type="username" class="input username" name="username" placeholder="Username" pattern="[A-Za-z0-9_]+" maxlength="15" value="<?php echo $user_data['username'];?>">
                    <input type="email" class="input email" name="email" placeholder="Email" value="<?php echo $user_data['email'];?>">
                    <div class="btn-wrapper">
                        <button type="submit" class="btn submit">Apply</button>
                        <button type="button" class="btn change">Change password</button>
                    </div>
                </form>
                <form action="delete.php" method="post">
                    <button type="submit" class="btn delete">Delete account</button>
                </form>
            </div>
            <div class="appearance section">
                <div class="title-sep-wrapper">
                    <span class="section-title">Appearance settings</span>
                    <div class="section-sep"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="change-section">
        <div class="background"></div>
        <div class="form-container">
            <div class="logo">
                <img src="/res/assets/logo/light_logo.png">
            </div>
            <h1>Change your password:</h1>
            <form action="change_pass.php" method="post" autocomplete="off">
                <div class="password-wrapper">
                    <input type="password" name="old_pass" placeholder="Old password" class="input">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="new_pass1" placeholder="New password" class="input">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="new_pass2" placeholder="Confirm new password" class="input">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <button class="btn apply-pass" type="submit">Apply</button>
                <button class="btn cancel-pass" type="button">Cancel</button>
            </form>
        </div>
    </div>
    <script>

        // To toggle between show and hide password
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
        document.querySelector(".change").onclick = function (){
            document.querySelector(".change-section").style.visibility = "visible";
            document.querySelector(".main").style.filter = "blur(3px)";
        }
        function hideModel(){
            document.querySelector(".change-section").style.visibility = "hidden";
            document.querySelector(".main").style.filter = "none";
        }
        document.querySelector(".background").onclick = hideModel;
        document.querySelector(".cancel-pass").onclick = hideModel;
        document.addEventListener('keydown', (e) =>{
            if(e.keyCode === 27){
                hideModel();
            }
        });
        document.querySelector(".submit").addEventListener('click', (e)=>{
            if(eval(document.querySelector(".username").value.length*document.querySelector(".email").value.length) == 0){
                e.preventDefault();
                document.querySelector('.error').style.display = "block";
                document.querySelector('.error').innerHTML = "All inputs are required.";
            }
            else{
                if(document.querySelector(".username").value == "<?php echo $user_data['username'];?>" && document.querySelector(".email").value == "<?php echo $user_data['email'];?>"){
                    e.preventDefault();
                    document.querySelector('.error').style.display = "block";
                    document.querySelector('.error').innerHTML = "Cannot use the current credentials.";
                }
            }
        })
    </script>
</body>
</html>