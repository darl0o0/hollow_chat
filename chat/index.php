<?php
//Initialization
require_once $_SERVER['DOCUMENT_ROOT'] . "/src/includes/main.php";
$qid = $_GET['q'] ?? '';

$stmt_unread_count = mysqli_prepare($con, "select count(*) from messages where sid = ? and rid = ? and isRead = '0'");
mysqli_stmt_bind_param($stmt_unread_count, "ss", $qid, $id);
mysqli_stmt_execute($stmt_unread_count);
$result_unread_count = mysqli_stmt_get_result($stmt_unread_count);

$row_count = mysqli_fetch_row($result_unread_count);
$unread_count = $row_count[0];
mysqli_stmt_close($stmt_unread_count);
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

        <div class="preview">
            <div class="field">
                <center><h1 class="not-found"></h1></center>
                <div class="inner-field"></div>
            </div>
            <form class="send-form" method="post">
                <input type="text" class="msg-input" placeholder="Type a message..." autocomplete="off" autofocus>
                <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</body>
</html>