<?php

//Initialization
session_start();
include $_SERVER['DOCUMENT_ROOT'] . "/src/config/connection.php";
include $_SERVER['DOCUMENT_ROOT'] . "/src/includes/functions.php";
requireStrictGet();

$user_data = check_login($con);
header('Content-Type: application/json; charset=utf-8');

$id = $user_data['id'];
$list_of_friends = "";

//List of friends
$user2_friend = "";
$friends_query = "select * from friends where (user1 = '$id' or user2 = '$id') and status = 'done'";
$friend_row = mysqli_fetch_all(mysqli_query($con, $friends_query));

//The number to show next to the section's title
$num_of_friends = mysqli_num_rows(mysqli_query($con, $friends_query));
$friends_num = ($num_of_friends > 0)? (' ('.$num_of_friends.')'): '';

if($num_of_friends > 0){

    // Listing friends
    for($i = 0; $i < $num_of_friends ; $i++){

        // Define the other user's id
        $user2_friend = ($friend_row[$i][0] == $id)? $friend_row[$i][1] : $friend_row[$i][0];
        $query = "select * from users where id = '$user2_friend'";
        $friend = mysqli_fetch_all(mysqli_query($con, $query));
        
        // Bio and online status
        $bio = (strlen($friend[0][4])>0)? $friend[0][4] : 'No Bio.';
        $isOnline = $friend[0][6] ? 'visible' : 'hidden';

        //List friends
        $list_of_friends .= "
        <div class='user-card u-$user2_friend'>
            <a href='/profile?u=$user2_friend' class='profile-link'></a>
                <div class='pfp'>
                    <img class='pfp-img' src='/src/assets/pfps/".$friend[0][5]."'>
                </div>
                <div class='label-wrapper'>
                    <div class='username-label'>".$friend[0][1]."</div>
                    <span class='sub-label'>$bio</span>
                </div>
            <div class='btn-wrapper-inbox'>
                <a href='/chat?r=$user2_friend' class='btn-inbox'><i class='fa fa-comment' aria-hidden='true'></i></a>
                <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
            </div>
        </div>
        ";
    }
}
else{
    $list_of_friends = "<span class='nothing-here'>Nothing here.</span>";
}