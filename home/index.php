<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>

    <link rel="stylesheet" href="/src/css/home.css">
    <script type="module" src="/src/js/init/home.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>
        <div class="preview">
            <span class="header-title">Home</span>
            <div class="search-wrapper">
                <label for="search" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </label>
                <input type="text" class="search" id="search" placeholder="Search user...">
            </div>
            <div class="users-list"></div>
        </div>
    </div>
</body>
</html>