<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>

    <link rel="stylesheet" href="/src/css/inbox.css">
    <script type="module" src="/src/js/init/inbox.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main inbox">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>
        <div class="preview">
            <div class="search-wrapper">
                <label for="search" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </label>
                <input type="text" name="q" class="input search" placeholder="Search conversation" autocomplete="off">
            </div>
            <div class="section chat">
                <div class="title-sep-wrapper">
                    <span class="title">Messages</span>
                    <div class="sep"></div>
                    <button class="grp-btn model-show" link-model="grp-model">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>
                <div class="list"></div>
            </div>
        </div>
    </div>
    <div class="model grp-model">
        <div class="background"></div>
        <div class="container">
            <h1>
                <i class="fa-solid fa-user-group"></i>
                <span>Create group chat with:</span>
            </h1>
            <div class="grp-members"></div>
            <div class="alert" style="display: none">
                <div class="cooldown-circle"></div>
                <span class="alert-text"></span>
            </div>
            <div class="search-wrapper">
                <label for="search" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </label>
                <input type="text" name="q" class="input search" placeholder="Search conversation" autocomplete="off">
            </div>
            <div class="title-sep-wrapper">
                <span class="title">Suggestions</span>
                <div class="sep"></div>
            </div>
            <div class="list"></div>
            <button class="btn primary-btn create-grp-btn">Create group chat (Beta)</button>
        </div>
    </div>
</body>
</html>