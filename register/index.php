<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
$id = $user_data['id'];
$panel = '
    <a class="logo" href="/">
        <img src="/src/assets/logo/light_logo.png">
    </a>
    <div class="panel">
        <a href="/" class="panel-action home-action"><i class="fa-solid fa-house"></i><span>Home</span></a>

        <a href="/inbox" class="panel-action inbox-action"><i class="fa-solid fa-inbox"></i><span>Inbox</span></a>

        <a href="/profile?u='.$id.'" class="panel-action goto-profile-mobile profile-action"><img src="/src/assets/pfps/'.$user_data['img'].'"></a>

        <a href="/settings" class="panel-action settings-action"><i class="fa-solid fa-gear"></i><span>Settings</span></a>

        <form action="/logout.php" method="post" class="logout-form">
            <input type="hidden" name="id" value="'.$id.'">
            <button type="submit" class="panel-action logout"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></button>
        </form>

        <a href="/profile?u='.$id.'" class="panel-action goto-profile-desktop profile-action">
            <div class="pfp">
                <img src="/src/assets/pfps/'.$user_data['img'].'">
            </div>
            <div class="label-wrapper">
                <div class"username-label">'.$user_data['username'].'</div>
                <span class="sub-label">My profile</span>
            </div>
        </a>
    </div>
';
?>
<meta name="csrf-token" content="<?php echo getCsrfToken(); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/src/assets/logo/hollow_icon_light.png" type="image/x-icon">
<script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script src="/src/js/ui-helpers.js"></script>
<script>
    const socket = io('http://localhost:3000');
    const currentUserId = "<?php echo $id; ?>";
    socket.emit('register_user', currentUserId);
</script>