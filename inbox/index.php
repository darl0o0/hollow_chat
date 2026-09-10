<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";

$list_of_chats = "";
$list_of_friends = "";
$list_of_requests = "";

$sub_label_style = array(['#a8a8a8', '#d5d6c3'], ['400', 'bolder']);
$is_unread_msg = '';
$id = $user_data['id'];

//List of conversations
$user2_chat = "";
$chats_query = "select * from conversations where (user1 = '$id' or user2 = '$id')";
$chat_row = mysqli_fetch_all(mysqli_query($con, $chats_query));
$num_of_chats = mysqli_num_rows(mysqli_query($con, $chats_query));

if($num_of_chats > 0){

    // Listing conversations
    for($i = 0; $i < $num_of_chats ; $i++){

        // Define the other user's id
        $user2_chat = ($chat_row[$i][0] == $id)? $chat_row[$i][1] : $chat_row[$i][0];
        $query = "select * from users where id = '$user2_chat'";
        $chat = mysqli_fetch_all(mysqli_query($con, $query));
        
        // Select the last message for the current conversation
        $query_last_msg = "select * from messages where (sid = '$id' and rid = '$user2_chat') or (sid = '$user2_chat' and rid = '$id') order by created_at desc limit 1";

        // Make last message "You: msg" or "msg"
        $last_msg = (mysqli_fetch_all(mysqli_query($con, $query_last_msg))[0][0] == $id)? "You: ".mysqli_fetch_all(mysqli_query($con, $query_last_msg))[0][2] : mysqli_fetch_all(mysqli_query($con, $query_last_msg))[0][2];

        $sub_label_color = '';
        $sub_label_weight = '';
        if(mysqli_fetch_all(mysqli_query($con, $query_last_msg))[0][1] == $id && mysqli_fetch_all(mysqli_query($con, $query_last_msg))[0][3] == '0'){

            // Make the last message bold if its not read
            $sub_label_color = $sub_label_style[0][1];
            $sub_label_weight = $sub_label_style[1][1];
            $is_unread_msg = 'visible';
        }
        else{

            // Make the last message light if its read
            $sub_label_color = $sub_label_style[0][0];
            $sub_label_weight = $sub_label_style[1][0];
            $is_unread_msg = 'hidden';
        }

        // If user was deleted
        if(mysqli_num_rows(mysqli_query($con, $query)) > 0){
            $isOnline = $chat[0][6] ? 'visible' : 'hidden';
            $img = $chat[0][5];
            $username = $chat[0][1];
        }
        else{
            $isOnline = 'hidden';
            $img = 'pfp.jpg';
            $username = 'User not found';
        }
        
        //List conversations
        $list_of_chats .= "
        <div class='user-card u-$user2_chat'>
            <a href='/chat?r=$user2_chat' class='profile-link'></a>
                <div class='pfp'>
                    <img class='pfp-img' src='/src/assets/pfps/$img'>
                    <div class='online-status' style='visibility: $isOnline'></div>
                </div>
                <div class='label-wrapper'>
                    <div class='username-label'>$username</div>
                    <span class='sub-label' style='color:$sub_label_color; font-weight: $sub_label_weight'>$last_msg</span>
                </div>
            <div class='btn-wrapper-inbox'>
                <div class='unread-msg' style='visibility: $is_unread_msg'>1</div>
                <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
            </div>
        </div>
        ";
    }
}
else{
    $list_of_chats = "<span class='nothing-here'>Nothing here.</span>";
}

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

//List of requests
$user2_request = "";
$requests_query = "select * from friends where user2 = '$id' and status = 'waiting'";
$request_row = mysqli_fetch_all(mysqli_query($con, $requests_query));


//The number to show next to the section's title
$num_of_requests = mysqli_num_rows(mysqli_query($con, $requests_query));
$requests_num = ($num_of_requests > 0)? (' ('.$num_of_requests.')'): '';

if($num_of_requests > 0){
    
    // Listing requests
    for($i = 0; $i < $num_of_requests ; $i++){
        
        // Define the other user's id
        $user2_request = $request_row[$i][0];
        $query = "select * from users where id = '$user2_request'";
        $request = mysqli_fetch_all(mysqli_query($con, $query));

        // Bio
        $bio = (strlen($request[$i][4])>0)? $request[$i][4] : 'No Bio.';

        //List requests
        $list_of_requests .= "
        <div class='user-card u-$user2_request'>
            <a href='/profile?u=$user2_request' class='profile-link'></a>
            <div class='pfp'>
                <img class='pfp-img' src='/src/assets/pfps/".$request[0][5]."'>
            </div>
            <div class='label-wrapper'>
                <div class='username-label'>".$request[0][1]."</div>
                <span class='sub-label'>$bio</span>
            </div>
            <div class='btn-wrapper-inbox'>
                <form method='post' action='/profile/friend_action.php'>
                    <input type='hidden' name='user2_id' value='$user2_request'>
                    <input type='hidden' name='from' value='fr'>
                    <button name='action' value='2' class='btn-inbox'><i class='fa fa-check' aria-hidden='true'></i></button>
                </form>
                <form method='post' action='/profile/friend_action.php'>
                    <input type='hidden' name='user2_id' value='$user2_request'>
                    <input type='hidden' name='from' value='fr'>
                    <button name='action' value='1' class='btn-inbox'><i class='fa fa-times' aria-hidden='true'></i></button>
                </form>
            </div>
        </div>
        ";
    }
}
else{
    $list_of_requests = "<span class='nothing-here'>Nothing here.</span>";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/inbox.css">
    <title>Hollow Chat</title>
</head>
<body>
    <div class="main inbox">
        <?php echo $panel ?>
        <div class="preview">
            <span class="header-title">Inbox</span>
            <div class="section chat">
                <div class="title-sep-wrapper">
                    <span class="title">Messages</span>
                    <div class="sep"></div>
                </div>
                <div class="list">
                    <?php echo $list_of_chats ?>
                </div>
            </div>
            <div class="section friends">
                <div class="title-sep-wrapper">
                    <span class="title">Friends<?php echo $friends_num ?></span>
                    <div class="sep"></div>
                </div>
                <div class="list">
                    <?php echo $list_of_friends ?>
                </div>
            </div>
            <div class="section requests">
                <div class="title-sep-wrapper">
                    <span class="title">Requests<?php echo $requests_num ?></span>
                    <div class="sep"></div>
                </div>
                <div class="list">
                    <?php echo $list_of_requests ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Listen for real-time presence changes broadcasted by server.js
        socket.on('user_status_change', (data) => {
            const statusDot = document.querySelector(`.chat .u-${data.userId} .online-status`);
            
            if (statusDot) {
                statusDot.style.visibility = data.isOnline ? "visible" : "hidden";
            }
        });
        
        // When a new message is received class as yours or the other user's && as read or unread
        var subLabelValue = [null, 'Typing...']
        socket.on('new_message', (data) => {
            const targetId = (data.sid == '<?php echo $id ?>')? data.rid : data.sid;
            const subLabel = document.querySelector(`.u-${targetId} .sub-label`);
            const isUnreadMsg = document.querySelector(`.u-${targetId} .unread-msg`);
            if(subLabel){
                if(data.sid == '<?php echo $id ?>'){
                    subLabelValue[0] = `You: ${data.message}`;
                    subLabel.style.color = '<?php echo $sub_label_style[0][0] ?>';
                    subLabel.style.fontWeight = '<?php echo $sub_label_style[1][0] ?>';
                    subLabel.innerHTML = subLabelValue[0];
                    isUnreadMsg.style.visibility = 'hidden';
                }
                else{
                    subLabelValue[0] = data.message;
                    subLabel.style.color = '<?php echo $sub_label_style[0][1] ?>';
                    subLabel.style.fontWeight = '<?php echo $sub_label_style[1][1] ?>';
                    isUnreadMsg.style.visibility = 'visible';
                    subLabel.innerHTML = subLabelValue[0];
                }
            }
        });

        // Class the last message as read as needed
        socket.on('message_marked_read', (data) => {
            const subLabel = document.querySelector(`.u-${data.readTo} .sub-label`);
            const isUnreadMsg = document.querySelector(`.u-${data.readTo} .unread-msg`);
            if(data.readBy == '<?php echo $id ?>'){
                subLabel.style.color = '<?php echo $sub_label_style[0][0] ?>';
                subLabel.style.fontWeight = '<?php echo $sub_label_style[1][0] ?>';
                isUnreadMsg.style.visibility = 'hidden';
            }
        });

        // When the other user is typing
        socket.on('typing_back', (data) => {
            const targetId = (data.typedBy == '<?php echo $id ?>')? data.typedTo : data.typedBy;
            const subLabel = document.querySelector(`.chat .u-${targetId} .sub-label`);
            if(data.typedBy == targetId && data.typedTo == currentUserId){
                if(data.isTyping){
                    if(subLabelValue[0] == null){
                        subLabelValue[0] = subLabel.innerHTML;
                    }
                    subLabel.innerHTML = subLabelValue[1];
                }
                else{
                    subLabel.innerHTML = subLabelValue[0];
                }
            }
        })
    </script>
</body>
</html>