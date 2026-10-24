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

    <meta name="unread-count" content="<?php echo $unread_count ?>">

    <link rel="stylesheet" href="/src/css/chat.css">
    <link rel="stylesheet" href="/src/css/inbox.css">
    <script type="module" src="/src/js/init/inbox.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main inbox grid-template-profile">
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
                        <button class="grp-btn modal-show" link-modal="group-modal">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </div>
                    <div class="list"></div>
                </div>
            </div>
            <div class="chat-preview"></div>
        </div>
    </div>
    <div class="model group-modal">
        <div class="background"></div>
        <div class="container">
            <h1>
                <i class="fa-solid fa-user-group"></i>
                <span>Create group chat with:</span>
            </h1>
            <div class="selected-members"></div>
            <div class="alert-placeholder"></div>
            <div class="wrap-search">
                <label for="search" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </label>
                <input type="text" name="q" class="input search" placeholder="Search conversation" autocomplete="off">
            </div>
            <div class="wrap-title">
                <span class="title">Suggestions</span>
                <div class="sep"></div>
            </div>
            <div class="list"></div>
            <button class="btn primary-btn submit btn-disabled">Create group chat (Beta)</button>
        </div>
    </div>
</body>
</html>