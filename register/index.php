<?php
session_start();
include("c:/xampp/htdocs/connection.php");
include("c:/xampp/htdocs/functions.php");
$user_data = check_login($con);
$id = $user_data['id'];
$panel = '
        <a class="logo" href="/">
            <img src="/res/assets/logo/light_logo.png">
        </a>
        <div class="panel">
            <a href="/" class="panel-action"><i class="fa-solid fa-house"></i><span>Home</span></a>

            <a href="/inbox" class="panel-action"><i class="fa-solid fa-inbox"></i><span>Inbox</span></a>

            <a href="/profile?u='.$id.'" class="goto-profile-2 panel-action"><img src="/res/assets/pfps/'.$user_data['img'].'"></a>

            <a href="/settings" class="panel-action"><i class="fa-solid fa-gear"></i><span>Settings</span></a>

            <form action="/logout.php" method="post">
                <input type="hidden" name="id" value="'.$id.'">
                <button type="submit" class="panel-action"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></button>
            </form>

            <a href="/profile?u='.$id.'" class="goto-profile panel-action"><img src="/res/assets/pfps/'.$user_data['img'].'"><span>'.$user_data['username'].'</span></a>
        </div>
';
?>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
    const socket = io('http://localhost:3000');
    const currentUserId = "<?php echo $id; ?>";
    socket.emit('register_user', currentUserId);
</script>