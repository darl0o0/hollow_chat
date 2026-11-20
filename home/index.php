<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>

    <meta name="page" content="home">
    <link rel="stylesheet" href="/src/css/home.css">
    <script type="module" src="/src/js/init/home.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>
        <div class="preview">
            <div class="home-list"></div>
        </div>
        <div class="right-panel">
            <span class="asset" style="--asset-url: url('/src/assets/logo/under-maintenance.svg')"></span>
            <h1>Coming soon...</h1>
        </div>
    </div>
</body>
</html>