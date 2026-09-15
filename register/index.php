<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
$user_data = check_login($con);
$id = $user_data['id'];

$list_of_requests = "";
$user2_request = "";
$requests_query = "select * from friends where user2 = '$id' and status = 'waiting'";
$request_row = mysqli_fetch_all(mysqli_query($con, $requests_query));

//The number of incoming requests
$num_of_requests = mysqli_num_rows(mysqli_query($con, $requests_query));

// Number of messages that are not read yet
$query_unread_messages = "select * from messages where rid = '$id' and isRead = '0'";
$num_of_unread_messages = mysqli_num_rows(mysqli_query($con, $query_unread_messages));

$panel = '
    <a class="logo" href="/">
        <img src="/src/assets/logo/light_logo.png">
    </a>
    <div class="panel">
        <a href="/" class="panel-action home-action"><i class="fa-solid fa-house"></i><span>Home</span></a>

        <a href="/inbox" class="panel-action inbox-action">
        <div class="icon-wrapper">        
            <i class="fa-solid fa-inbox"></i>
            <div class="num-of">'.$num_of_unread_messages.'</div>
        </div>
        <span>Inbox</span>
        </a>

        <a href="/profile?u='.$id.'" class="panel-action goto-profile-mobile profile-action"><img src="/src/assets/pfps/'.$user_data['img'].'"></a>

        <button class="panel-action notifications-action">
            <div class="icon-wrapper">        
                <i class="fa-solid fa-bell"></i>
                <div class="num-of">'.$num_of_requests.'</div>
            </div>
            <span>Notifications</span>
        </button>
        
        <a href="/settings" class="panel-action settings-action"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
        
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

// //List of requests
// if($num_of_requests > 0){
    
//     // Listing requests
//     for($i = 0; $i < $num_of_requests ; $i++){
        
//         // Define the other user's id
//         $user2_request = $request_row[$i][0];
//         $query = "select * from users where id = '$user2_request'";
//         $request = mysqli_fetch_all(mysqli_query($con, $query));

//         // Bio
//         $bio = (strlen($request[0][4])>0)? $request[0][4] : 'No Bio.';

//         //List requests
//         $list_of_requests .= "
//         ";
//     }
// }
// else{
//     $list_of_requests = "<span class='nothing-here'>Nothing here.</span>";
// }
?>
<meta name="csrf-token" content="<?php echo getCsrfToken(); ?>">
<meta name="id" content="<?php echo $id; ?>">
<meta name="num-of-unread" content="<?php echo $num_of_unread_messages; ?>">
<meta name="num-of-requests" content="<?php echo $num_of_requests; ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="/src/assets/logo/hollow_icon_light.png" type="image/x-icon">
<script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script type="module" src="/src/js/init/main.js"></script>
<script type="module" src="/src/js/presence.js"></script>