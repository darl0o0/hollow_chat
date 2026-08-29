<?php
//Initialization
include("register/index.php");
$list_of_users = "";
if(isset($_SESSION['id'])){

    $session_id = $_SESSION['id'];
    $query1 = "select * from users";
    $num_of_users = mysqli_num_rows(mysqli_query($con, $query1));
    $query = "select * from users where not (id = '$id')";

    // List the users
    for($i = 0; $i < $num_of_users-1 ; $i++){

        $result = mysqli_fetch_all(mysqli_query($con, $query));
        $bio = (strlen($result[$i][4])>0)? $result[$i][4] : 'No Bio.';
        $isOnline = $result[$i][6] ? 'visible' : 'hidden';
        
        $list_of_users .= "
            <a class='user-card u-".$result[$i][0]."' href='profile?u=".$result[$i][0]."'>
                <div class='pfp'>
                    <img class='pfp-img' src='/res/assets/pfps/".$result[$i][5]."'>
                    <div class='online-status' style='visibility: $isOnline'></div>
                </div>
                <div class='label-wrapper'>
                    <div class='username-label'>".$result[$i][1]."</div>
                    <span class='sub-label'>$bio</span>
                </div>
            </a>
        ";
    }
}
else{
    header("Location: /signin");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Hollow Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/res/assets/logo/hollow_icon_light.png" type="image/x-icon">
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/res/css/home.css">
</head>
<body>
    <div class="main">
        <?php echo $panel ?>
        <div class="preview">
            <span class="username-title"><?php echo $user_data['username']?></span>
            <div class="search-wrapper">
                <label for="search" class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </label>
                <input type="text" class="search" id="search" placeholder="Search user...">
            </div>
            <div class="users-list"><?php echo $list_of_users?></div>
        </div>
    </div>
    <script>
        // Listen for real-time presence changes broadcasted by server.js
        socket.on('user_status_change', (data) => {
            const statusDot = document.querySelector(`.u-${data.userId} .online-status`);
            
            if (statusDot) {
                statusDot.style.visibility = data.isOnline ? "visible" : "hidden";
            }
        });
    </script>
</body>
</html>