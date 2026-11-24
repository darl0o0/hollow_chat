<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>


    <meta name="page" content="inbox">
    <link rel="stylesheet" href="/src/css/chat.css">
    <link rel="stylesheet" href="/src/css/inbox.css">
    <script type="module" src="/src/js/init/inbox.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main inbox">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>
        <div class="preview">
            <div class="dms-container">
                <div class="wrap-search">
                    <label for="search" class="search-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </label>
                    <input type="text" name="q" class="input search" placeholder="Search conversation" autocomplete="off">
                </div>
                <div class="section chat">
                    <div class="wrap-title">
                        <span class="title">Messages</span>
                        <div class="sep"></div>
                        <button class="new-message modal-show" link-modal="group-modal">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                    <div class="list"></div>
                </div>
            </div>
            <div class="chat-preview"></div>
        </div>
    </div>
</body>
</html>