<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
$qid = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>

    <link rel="stylesheet" href="/src/css/profile.css">
    <script type="module" src="/src/js/init/profile.js"></script>
    <meta name="user2" content="<?php echo $qid ?>">
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main profile">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>
        <div class="preview">
            <h1 class="not-found"></h1>
            <div class="profile-wrapper">
                <div class="profile-card">
                    <img>
                    <span class="username-label"></span>
                    <div class="online-status">
                        <div class="is-online"></div>
                        <span class="is-online-label"></span>
                    </div>
                </div>
                <div class="profile-details">
                    <div class="details-section date">
                        <span class="title">Member since</span>
                        <span class="text"></span>
                    </div>
                    <div class="details-section bio">
                        <span class="title">Bio</span>
                        <span class="text"></span>
                    </div>
                    <div class="details-section privacy">
                        <span class="title">Account privacy</span>
                        <span class="text"></span>
                    </div>
                </div>
            </div>
            <div class="profile-actions"></div>
        </div>
    </div>
</body>
</html>