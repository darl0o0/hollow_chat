<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
$qid = $_GET['q'] ?? '';

$query_unread_count = "select * from messages where sid = '$qid' and rid = '$id' and isRead = '0'";
$unread_count = mysqli_num_rows(mysqli_query($con, $query_unread_count));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/head.php"; ?>
    <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main-head.php"; ?>
    
    <meta name="qid" content="<?php echo $qid ?>">
    <meta name="unread-count" content="<?php echo $unread_count ?>">
    
    <link rel="stylesheet" href="/src/css/chat.css">
    <script type="module" src="/src/js/init/chat.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main chat">
        <?php include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/panel.php"; ?>

        <a class="profile-header mobile-header">
            <div class='pfp'>
                <img class='pfp-img'>
                <div class='online-status'></div>
            </div>
            <div class="label-wrapper">
                <div class='username-label'></div>
                <span class="sub-label">View profile</span>
            </div>
        </a>
        <div class="preview">
            <a class="profile-header desktop-header">
                <div class='pfp'>
                    <img class='pfp-img'>
                    <div class='online-status'></div>
                </div>
                <div class="label-wrapper">
                    <div class='username-label'></div>
                    <span class="sub-label">View profile</span>
                </div>
            </a>
            <div class="field">
                <center><h1 class="not-found"></h1></center>
                <div class="inner-field"></div>
                <div class="m r typing">
                    <img src="/src/assets/typing0.gif">
                </div>
            </div>
            <form class="send-form" method="post">
                <input type="text" name="msg" class="msg-input" placeholder="Type a message..." autocomplete="off" autofocus>
                <input type="hidden" name="sid">
                <input type="hidden" name="rid">
                <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</body>
</html>