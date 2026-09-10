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
    <script>
        // Listen for real-time presence changes broadcasted by server.js
        socket.on('user_status_change', (data) => {
            
            // data = { userId: "...", isOnline: 1 or 0 }
            document.querySelectorAll(".online-status").forEach(onlineStatus => {
                if(data.userId == "<?php echo $rec_id; ?>"){
                    onlineStatus.style.visibility = data.isOnline ? "visible" : "hidden";
                }
            });
        });

        // Receive messages instantly without polling
        const field = document.querySelector('.field');
        socket.on('new_message', (data) => {
            const chatField = document.querySelector('.inner-field');
            const msgDiv = document.createElement('div');
            
            // Set 's m' class for sender, 'r m' for receiver
            if(data.sid === currentUserId){
                msgDiv.className = 's m';
                const msgWrap = document.createElement('div');
                msgWrap.className = 'msg-wrap';
                chatField.appendChild(msgWrap);
                const msgIsRead = document.createElement('div');
                msgIsRead.classList.add('isRead');
                msgIsRead.classList.add('unread');
                msgWrap.appendChild(msgIsRead);
                msgWrap.appendChild(msgDiv);
            }
            else{
                msgDiv.className = 'r m';
                chatField.appendChild(msgDiv);
                markChatAsRead("<?php echo $rec_id; ?>");
            }
            msgDiv.textContent = data.message;
            
            field.scrollTop = field.scrollHeight;
        });

        // Mark the last message as read when the page is loaded
        function markChatAsRead(activeUserId){
            socket.emit('mark_as_read', {
                senderId: activeUserId,
                receiverId: currentUserId
            });
        }

        // When the other user read's the current user's message
        socket.on('message_marked_read', (data) => {
            const activePartnerId = "<?php echo $rec_id; ?>"
            if(data.readBy === activePartnerId){
                document.querySelectorAll('.isRead.unread').forEach(el => {
                    el.classList.remove('unread');
                    el.classList.add('read');
                });
            }
        });

        // When typing
        let isTyping = false;
        const inputEl = document.querySelector('.send-form input');
        inputEl.addEventListener('input', () => {
            const hasText = inputEl.value.trim().length > 0;
            if(hasText && !isTyping) {
                isTyping = true;
                socket.emit('typing', {
                    typedBy: "<?php echo $id; ?>",
                    typedTo: "<?php echo $rec_id; ?>",
                    isTyping: 1
                });
            } 
            else if(!hasText && isTyping) {
                isTyping = false;
                socket.emit('typing', {
                    typedBy: "<?php echo $id; ?>",
                    typedTo: "<?php echo $rec_id; ?>",
                    isTyping: 0
                });
            }
        });

        // When the other user is typing
        socket.on('typing_back', (data) => {
            if(data.typedBy == "<?php echo $rec_id; ?>" && data.typedTo == currentUserId){
                console.log('fghfgdhfgh')
                if(data.isTyping){
                    document.querySelector('.typing').style.display = 'inline';
                    document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
                }
                else{
                    document.querySelector('.typing').style.display = 'none';
                }
            }
        })

        // Send message
        document.querySelector('.send-form').addEventListener('submit', async function(e){
            e.preventDefault();
            if(document.querySelector('.msg-input').value){
                const formData = new FormData(e.target);
                
                //HTTP Request
                await UI.sendPost('send.php', formData);

                // Make the input empty && scroll to the bottom of the field && isTyping = 0
                document.querySelector('.msg-input').value = null;
                document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
                isTyping = false;
                socket.emit('typing', {
                    typedBy: "<?php echo $id; ?>",
                    typedTo: "<?php echo $rec_id; ?>",
                    isTyping: 0
                });
            }
        })

        window.onload = async function(){

            // Load chat when the page is loaded
            const htmlResponse = await UI.sendPost('get_msg.php', {rid: "<?php echo $rec_id ?>"});
            document.querySelector('.inner-field').innerHTML = htmlResponse;
            field.scrollTop = field.scrollHeight;

            markChatAsRead("<?php echo $rec_id; ?>")
        }
    </script>
</body>
</html>