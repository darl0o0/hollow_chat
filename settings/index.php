<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>
    
    <link rel="stylesheet" href="/src/css/settings.css">
    <script type="module" src="/src/js/init/settings.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main settings">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>
        <div class="preview">
            <div class="account section">
                <div class="title-sep-wrapper">
                    <span class="title">Account settings</span>
                    <div class="sep"></div>
                </div>
                <div class="alert-placeholder"></div>
                <form method="post" class="update-form distinct" novalidate>
                    <div class="pfp-wrapper">
                        <div class="img-input">
                            <img>
                            <i class="fa-solid fa-camera"></i>
                        </div>
                        <input type="file" name="img" accept="image/jpg, image/jpeg, image/png">
                        <div class="inputs-wrapper">
                            <input 
                            type="username" 
                            class="input username required distinct" 
                            name="username" 
                            placeholder="Username" 
                            pattern="[A-Za-z0-9_]+" 
                            maxlength="15">
        
                            <input 
                            type="email" 
                            class="input email required distinct" 
                            name="email" 
                            placeholder="Email">
                        </div>
                    </div>
                    <textarea class="input bio distinct" name="bio" maxlength="150" placeholder="Write a short bio...&#10;(Optional)"></textarea>
                    <div class="btn-wrapper">
                        <button type="button" class="btn primary-btn submit">Apply<i class="fa-solid fa-check"></i></button>
                        <button type="button" class="btn secondary-btn change model-show" link-model="reset-model">Change password</button>
                    </div>
                </form>
                <button class="btn delete model-show" link-model="delete-model">Delete account</button>
            </div>
            <div class="appearance section">
                <div class="title-sep-wrapper">
                    <span class="title">Appearance settings</span>
                    <div class="sep"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="reset-model model">
        <div class="background"></div>
        <div class="form-container">
            <div class="logo">
                <img src="/src/assets/logo/light_logo.png">
            </div>
            <h1>Reset your password:</h1>
            <div class="alert-placeholder"></div>
            <form method="post" autocomplete="off" class="reset-form">
                <div class="password-wrapper">
                    <input type="password" name="old_pass" placeholder="Old password" class="input password required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="new_pass" placeholder="New password" class="input password required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="password-wrapper">
                    <input type="password" name="new_pass1" placeholder="Confirm new password" class="input password required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="btn-wrapper">
                    <button class="btn primary-btn submit" type="button">Apply</button>
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
            <div class="alert-placeholder"></div>
            <form method="post" autocomplete="off" class="delete-form"
            novalidate>
                <input 
                type="email" 
                class="email input required" 
                name="email" 
                placeholder="Email" 
                autocomplete="off">
                <div class="password-wrapper">
                    <input 
                    type="password" 
                    name="password" 
                    placeholder="Password" 
                    class="password input required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="checkbox">
                    <input type="checkbox" class="input agree required" name="agree" required>
                    <label for="agree">I unserstand that my account will be permanently deleted</label>
                </div>
                <div class="btn-wrapper">
                    <button class="btn delete submit" type="button"><i class="fa-solid fa-trash"></i>Delete Account</button>
                    <button class="btn secondary-btn model-hide" type="button">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>