<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
$list_of_users = "";
$query1 = "select * from users";
$num_of_users = mysqli_num_rows(mysqli_query($con, $query1));
$query = "select * from users where not (id = '$id') order by created_at desc";

// List the users
for($i = 0; $i < $num_of_users-1 ; $i++){

    $result = mysqli_fetch_all(mysqli_query($con, $query));
    $bio = (strlen($result[$i][4])>0)? $result[$i][4] : 'No Bio.';
    $isOnline = $result[$i][6] ? 'visible' : 'hidden';
    
    $list_of_users .= "
        <a class='user-card u-".$result[$i][0]."' href='profile?u=".$result[$i][0]."'>
            <div class='pfp'>
                <img class='pfp-img' src='/src/assets/pfps/".$result[$i][5]."'>
                <div class='online-status' style='visibility: $isOnline'></div>
            </div>
            <div class='label-wrapper'>
                <div class='username-label'>".$result[$i][1]."</div>
                <span class='sub-label'>$bio</span>
            </div>
        </a>
    ";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/home.css">
    <link rel="stylesheet" href="/src/css/global.css">
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main">
        <?php echo $panel ?>
        <div class="preview">
            <span class="header-title">Home</span>
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