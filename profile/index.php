<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>

    <link rel="stylesheet" href="/src/css/profile.css">
    <script type="module" src="/src/js/init/profile.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main profile">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>
        <div class="preview"></div>
    </div>
</body>
</html>