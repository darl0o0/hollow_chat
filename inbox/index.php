<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/inbox.css">
    <script type="module" src="/src/js/init/inbox.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main inbox">
        <?php echo $panel ?>
        <div class="preview">
            <span class="header-title">Inbox</span>
            <div class="section chat">
                <div class="title-sep-wrapper">
                    <span class="title">Messages</span>
                    <div class="sep"></div>
                </div>
                <div class="list"></div>
            </div>
        </div>
    </div>
</body>
</html>