<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hollow Chat</title>
    <link rel="stylesheet" href="/src/css/settings.css">
</head>
<body>
    <div class="main settings">
        <?php echo $panel ?>
        <div class="preview">
            <span class="header-title">Settings</span>
            <div class="account section">
                <div class="title-sep-wrapper">
                    <span class="title">Account settings</span>
                    <div class="sep"></div>
                </div>
                <div class="alert" style="display: none">
                    <div class="cooldown-circle"></div>
                    <span class="alert-text"></span>
                </div>
                <form method="post" class="update-form">
                    <input type="username" class="input username" name="username" placeholder="Username" pattern="[A-Za-z0-9_]+" maxlength="15" value="<?php echo $user_data['username'];?>">
                    <input type="email" class="input email" name="email" placeholder="Email" value="<?php echo $user_data['email'];?>">
                    <div class="btn-wrapper">
                        <button type="submit" class="btn primary-btn submit">Apply<i class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn secondary-btn change">Change password</button>
                    </div>
                </form>
                <button class="btn delete">Delete account</button>
            </div>
            <div class="appearance section">
                <div class="title-sep-wrapper">
                    <span class="title">Appearance settings</span>
                    <div class="sep"></div>
                </div>
            </div>
        </div>
    </div>
    <?php echo $requests_model ?>
    <div class="change-model model">
        <div class="background"></div>
        <div class="form-container">
            <div class="logo">
                <img src="/src/assets/logo/light_logo.png">
            </div>
            <h1>Change your password:</h1>
            <form action="change_pass.php" method="post" autocomplete="off">
                <div class="password-wrapper">
                    <input type="password" name="old_pass" placeholder="Old password" class="input password">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="new_pass1" placeholder="New password" class="input password">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="new_pass2" placeholder="Confirm new password" class="input password">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="btn-wrapper">
                    <button class="btn primary-btn" type="submit">Apply</button>
                    <button class="btn secondary-btn" type="button">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div class="delete-model model">
        <div class="background"></div>
        <div class="form-container">
            <div class="logo">
                <img src="/src/assets/logo/light_logo.png">
            </div>
            <h1>Delete your account:</h1>
            <div class="model-alert" style="display: none">
                <div class="cooldown-circle"></div>
                <span class="alert-text"></span>
            </div>
            <form method="post" autocomplete="off" class="delete-form"
            novalidate>
                <input type="email" class="input delete-email" name="email" placeholder="Email" autocomplete="off">
                <div class="password-wrapper">
                    <input type="password" name="password" placeholder="Password" class="input password delete-password">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="checkbox">
                    <input type="checkbox" class="agree" name="agree" required>
                    <label for="agree">I unserstand that my account will be permanently deleted</label>
                </div>
                <div class="btn-wrapper">
                    <button class="btn delete" type="submit"><i class="fa-solid fa-trash"></i>Delete Account</button>
                    <button class="btn secondary-btn" type="button">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>

        // Show the on-screen models
        document.querySelector(".change").onclick = function (){
            document.querySelector(".change-model").style.visibility = "visible";
            document.querySelector(".main").style.filter = "blur(3px)";
        }
        document.querySelector(".delete").onclick = function (){
            document.querySelector(".delete-model").style.visibility = "visible";
            document.querySelector(".main").style.filter = "blur(3px)";
        }
        document.querySelectorAll(".model .secondary-btn").forEach(hideEvent => {
            hideEvent.onclick = UI.hideModel;
        });

        //Update form
        document.querySelector(".update-form").addEventListener('submit', async function(e){
            e.preventDefault();
            const username = document.querySelector('.username').value;
            const email = document.querySelector('.email').value;
            if(username && email){
                if(username != "<?php echo $user_data['username'];?>" || email != "<?php echo $user_data['email'];?>"){
                    const formData = new FormData(e.target);
                    
                    //HTTP Request
                    const res = await UI.sendPost('update.php', formData);
                    const jsonRes = JSON.parse(res);
                    if(jsonRes.status == 'ok'){
                        UI.alert(true, jsonRes.message, 3);
                    }
                    else if(jsonRes.status == 'error'){
                        UI.alert(false, jsonRes.message, null);
                    }
                }
                else{
                    UI.alert(false, "Cannot use the current credentials.", null);
                }
            }
            else{
                UI.alert(false, "All inputs are required.", null);
            }
        })

        // Delete account form
        document.querySelector('.delete-form').addEventListener('submit', async function(e){
            e.preventDefault();
            const email = document.querySelector('.delete-email').value;
            const password = document.querySelector('.delete-password').value;
            if(email && password){
                const formData = new FormData(e.target);
                
                //HTTP Request
                const res = await UI.sendPost('delete.php', formData);
                const jsonRes = JSON.parse(res);
                if(jsonRes.status == 'ok'){
                    UI.alert(true, jsonRes.message, 4, true);
                    setTimeout(() => {
                        window.location.replace('/signin');
                    }, 4000);
                }
                else if(jsonRes.status == 'error'){
                    UI.alert(false, jsonRes.message, null, true);
                }
            }
            else{
                UI.alert(false, "All inputs are required.", null, true);
            }
        });
    </script>
</body>
</html>