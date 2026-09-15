<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
$not_found = '';
$link = '/';
if(!isset($_GET['r'])){
    $rec_id = '';
    $username = 'User not found';
    $img = 'pfp.jpg';
    $isOnline = 'hidden';
    $not_found = 'User not found';
}
else{
    $rec_id = $_GET['r'];
    $query = "select * from users where id = '$rec_id'";

    $query_unread_count = "select * from messages where sid = '$rec_id' and rid = '$id' and isRead = '0'";
    $unread_count = mysqli_num_rows(mysqli_query($con, $query_unread_count));

    $query_chat_exists = "select * from conversations where (user1 = '$id' and user2 = '$rec_id') or (user1 = '$rec_id' and user2 = '$id')";
    
    if(mysqli_num_rows(mysqli_query($con, $query)) == 0){
        if(mysqli_num_rows(mysqli_query($con, $query_chat_exists)) == 0){
            $rec_id = '';
            $not_found = 'User not found';
        }
        $username = 'User not found';
        $img = 'pfp.jpg';
        $isOnline = 'hidden';
        $user2 = $_GET['r'];
    }
    else{
        $user2 = $_GET['r'];
        $rec = mysqli_fetch_all(mysqli_query($con, $query));
        $username = $rec[0][1];
        $img = $rec[0][5];
        $isOnline = $rec[0][6] ? 'visible' : 'hidden';
        $link = '/profile?u='.$_GET['r'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/chat.css">
    <meta name="id" content="<?php echo $id ?>">
    <meta name="rid" content="<?php echo $rec_id ?>">
    <meta name="unread-count" content="<?php echo $unread_count ?>">
    <script type="module" src="/src/js/init/chat.js"></script>
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main chat">
        <?php echo $panel ?>
        <a class="profile-header mobile-header" href="<?php echo $link ?>">
            <div class='pfp'>
                <img class='pfp-img' src='/src/assets/pfps/<?php echo $img ?>'>
                <div class='online-status' style="visibility: <?php echo $isOnline; ?>"></div>
            </div>
            <div class="label-wrapper">
                <div class='username-label'><?php echo $username ?></div>
                <span class="sub-label">View profile</span>
            </div>
        </a>
        <div class="preview">
            <a class="profile-header desktop-header" href="<?php echo $link ?>">
                <div class='pfp'>
                    <img class='pfp-img' src='/src/assets/pfps/<?php echo $img ?>'>
                    <div class='online-status' style="visibility: <?php echo $isOnline; ?>"></div>
                </div>
                <div class="label-wrapper">
                    <div class='username-label'><?php echo $username ?></div>
                    <span class="sub-label">View profile</span>
                </div>
            </a>
            <div class="field">
                <center><h1 class="not-found"><?php echo $not_found ?></h1></center>
                <div class="inner-field"></div>
                <div class="m r typing">
                    <img src="/src/assets/typing0.gif">
                </div>
            </div>
            <form class="send-form" method="post">
                <input type="text" name="msg" class="msg-input" placeholder="Type a message..." autocomplete="off" autofocus>
                <input type="hidden" name="sid" value="<?php echo $user_data['id'] ?>">
                <input type="hidden" name="rid" value="<?php echo $user2 ?>">
                <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
</body>
</html>