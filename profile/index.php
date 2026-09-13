<?php

//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
$mainDisplay = 'flex';
$not_found = '';
$isMyProfile = '';

if(!isset($_GET['u'])){
    $mainDisplay = 'none';
    $not_found = 'User not found';
}
else{

    $user2 = $_GET['u'];
    $query = "select * from users where id = '$user2'";
    
    if(mysqli_num_rows(mysqli_query($con, $query)) == 0){
        $mainDisplay = 'none';
        $display1 = 'none';
        $not_found = 'User not found';
    }
    else{
        $result = mysqli_fetch_all(mysqli_query($con, $query));
    
        // Online status, bio and account privacy
        $isOnlineBackground = $result[0][6]? '#e9e4c8' : 'none';
        $isOnlineBorder = $result[0][6]? '2px solid #e9e4c8' : '2px ridge #464646';
        $isOnlineLabel = $result[0][6]? 'Online' : 'Offline';
        $bio = (strlen($result[0][4])>0)? $result[0][4] : 'No Bio.';
        $privacy = ($result[0][7] == '0')? 'Public' : 'Private';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/profile.css">
    <script type="module" src="/src/js/init/profile.js"></script>
    <meta name="user2" content="<?php echo $user2 ?>">
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main profile">
        <?php echo $panel ?>
        <div class="preview">
            <h1 class="not-found"><?php echo $not_found ?></h1>
            <div style="display: <?php echo $mainDisplay ?>" class="profile-wrapper">
                <div class="profile-card">
                    <img src="/src/assets/pfps/<?php echo $result[0][5] ?>" alt="">
                    <span class="username-label"><?php echo $result[0][1] ?></span>
                    <div class="online-status">
                        <div class="is-online" style="background: <?php echo $isOnlineBackground ?>; border: <?php echo $isOnlineBorder ?>"></div>
                        <span class="is-online-label"><?php echo $isOnlineLabel ?></span>
                    </div>
                </div>
                <div class="profile-details">
                    <div class="details-section">
                        <span class="title">Member since</span>
                        <span class="text"><?php echo $result[0][8] ?></span>
                    </div>
                    <div class="details-section">
                        <span class="title">Bio</span>
                        <span class="text"><?php echo $bio ?></span>
                    </div>
                    <div class="details-section">
                        <span class="title">Account privacy</span>
                        <span class="text"><?php echo $privacy ?></span>
                    </div>
                </div>
            </div>
                <div style="display: <?php echo $mainDisplay ?>" class="profile-actions">
                    <?php
                    if($_GET['u'] == $id){
                        echo '
                            <button class="btn primary-btn share-profile">
                            <i class="fa-solid fa-share"></i>
                            Share profile
                            </button>
                            <form action="/logout.php" method="post" class="logout-form">
                                <input type="hidden" name="id" value="'.$id.'">
                                <button type="submit" class="btn secondary-btn"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></button>
                            </form>
                        ';
                        $isMyProfile = 'rgba(125, 125, 125, 0.15)';
                    }
                    else{
                        echo '
                            <a href="/chat?r='.$user2.'" class="btn primary-btn"><i class="fa fa-comment" aria-hidden="true"></i>Message</a>
                            <form
                            method="post" 
                            class="friend-action">
                                <input type="hidden" name="user2" value="'. $user2.'">
                                <input type="hidden" name="action">
                                <button type="submit" class="btn secondary-btn profile-action-btn"></button>
                            </form>
                        ';
                        $isMyProfile = 'none';
                    }
                    ?>
                </div>
        </div>
    </div>
    <script>
        // Listen for real-time presence changes broadcasted by server.js
        socket.on('user_status_change', (data) => {
            const statusDot = document.querySelector(".is-online");
            const statusLabel = document.querySelector(".is-online-label");
            if(data.userId == "<?php echo $user2; ?>"){
                statusDot.style.background = data.isOnline ? "#e9e4c8" : "none";
                statusDot.style.border = data.isOnline ? "2px solid #e9e4c8" : "2px ridge #464646";
                statusLabel.innerHTML = data.isOnline ? "Online" : "Offline";
            }
        });

        // Copy profile link
        function copyProfileLink(){
            const profileUrl = window.location.href;
            navigator.clipboard.writeText(profileUrl).then(() => {
                const btn = document.querySelector('.share-profile');
                btn.innerHTML = 'Profile link copied!';
                setTimeout(() => {
                    btn.innerHTML = '<i class="fa-solid fa-share"></i>Share profile';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy', err);
            });
        }

        const shareProfileBtn = document.querySelector('.share-profile');
        if(shareProfileBtn)
        shareProfileBtn.addEventListener('click', ()=>{
            copyProfileLink();
        });
    </script>
    <style>
        .profile-action{
            background-color: <?php echo $isMyProfile ?>;
        }
    </style>
</body>
</html>