<?php
include("../register/index.php");
if(isset($_GET['u'])){
    $friend_action = '';
    $action;
    $user1 = $user_data['id'];
    $user2 = $_GET['u'];

    $profile_id = $_GET['u'];
    $query = "select * from users where id = '$profile_id'";
    $result = mysqli_fetch_all(mysqli_query($con, $query));

    $isOnline = $result[0][6]? '#e9e4c8' : 'none';
    $isOnlineLabel = $result[0][6]? 'Online' : 'Offline';
    $bio = (strlen($result[0][4])>0)? $result[0][4] : 'No Bio.';
    $privacy = ($result[0][7] == '0')? 'Public' : 'Private';

    $query1 = "select * from friends where (user1 = '$user1' and user2 = '$user2') or (user1 = '$user2' and user2 = '$user1')";
    $num = mysqli_num_rows(mysqli_query($con, $query1));

    if($num == 0){
        $action = 0;
        $friend_action = '<i class="fa fa-user-plus" aria-hidden="true"></i>Add Friend';
    }
    else{
        if(mysqli_fetch_all(mysqli_query($con, $query1))[0][2] == 'waiting'){
            if(mysqli_fetch_all(mysqli_query($con, $query1))[0][1] == $id){
                $action = 2;
                $friend_action = '<i class="fa fa-check" aria-hidden="true"></i> Accept Request';
            }
            else{
                $action = 1;
                $friend_action = '<i class="fa fa-user-times" aria-hidden="true"></i>Cancel Request';
            }
        }
        elseif(mysqli_fetch_all(mysqli_query($con, $query1))[0][2] == 'done'){
            $action = 1;
            $friend_action = '<i class="fa fa-user-times" aria-hidden="true"></i>Remove Friend';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hollow Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/res/assets/logo/hollow_icon_light.png" type="image/x-icon">
    <link rel="stylesheet" href="/style.css">
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/res/css/profile.css">
    <script>
        socket.on('user_status_change', (data) => {
            const statusDot = document.querySelector(".is-online");
            const statusLabel = document.querySelector(".is-online-label");
            if(data.userId == "<?php echo $profile_id; ?>"){
                statusDot.style.background = data.isOnline ? "#e9e4c8" : "none";
                statusLabel.innerHTML = data.isOnline ? "Online" : "Offline";
            }
        });
    </script>
<style>
</style>
</head>
<body>
    <div class="main profile">
        <?php echo $panel ?>
        <div class="preview">
            <div class="profile-wrapper">
                <div class="profile-card">
                    <img src="/res/assets/pfps/<?php echo $result[0][5] ?>" alt="">
                    <span class="username-label"><?php echo $result[0][1] ?></span>
                    <div class="online-status">
                        <div class="is-online" style="background: <?php echo $isOnline ?>"></div>
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
            <div class="profile-actions">
                <a href="/chat?r=<?php echo $result[0][0] ?>" class="goto-chat profile-action-btn"><i class="fa fa-comment" aria-hidden="true"></i>Message</a>
                <form action="friend_action.php" method="post">
                    <input type="hidden" name="user2_id" value="<?php echo $result[0][0] ?>">
                    <input type="hidden" name="from" value="pr">
                    <input type="hidden" name="action" value="<?php echo $action ?>">
                    <button type="submit" class="friend-action profile-action-btn"><?php echo $friend_action ?></button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>