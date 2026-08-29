<?php
include("../register/index.php");
if(isset($_SESSION['id'])){
    if(isset($_GET['r'])){
        $rec_id = $_GET['r'];
        $query = "select * from users where id = '$rec_id'";
        $rec = mysqli_fetch_all(mysqli_query($con, $query));
        $isOnline = $rec[0][6] ? 'visible' : 'hidden';
        $rec_len = strlen($rec[0][0]);
    }
}
else{
    header("Location: ../signin");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Chatroom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/res/assets/logo/hollow_icon_light.png" type="image/x-icon">
    <script src="https://kit.fontawesome.com/b4672b084e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/res/css/chat.css">
    <script>
        // Listen for real-time presence changes broadcasted by server.js
        socket.on('user_status_change', (data) => {
            // data = { userId: "...", isOnline: 1 or 0 }
            const statusDot = document.querySelector(".online-status");
            if(data.userId == "<?php echo $rec_id; ?>"){
                statusDot.style.visibility = data.isOnline ? "visible" : "hidden";
            }
        });
        // Receive messages instantly without polling
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
    </script>
</head>
<body>
    <div class="main chat">
        <?php echo $panel ?>
        <a class="profile-header a2" href="/profile?u=<?php echo $_GET['r'] ?>">
            <div class='pfp'>
                <img class='pfp-img' src='/res/assets/pfps/<?php echo $rec[0][5] ?>'>
                <div class='online-status' style="visibility: <?php echo $isOnline; ?>"></div>
            </div>
            <div class="label-wrapper">
                <div class='username-label'><?php echo $rec[0][1] ?></div>
                <span class="sub-label">View profile</span>
            </div>
        </a>
        <div class="preview">
            <a class="profile-header a1" href="/profile?u=<?php echo $_GET['r'] ?>">
                <div class='pfp'>
                    <img class='pfp-img' src='/res/assets/pfps/<?php echo $rec[0][5] ?>'>
                    <div class='online-status' style="visibility: <?php echo $isOnline; ?>"></div>
                </div>
                <div class="label-wrapper">
                    <div class='username-label'><?php echo $rec[0][1] ?></div>
                    <span class="sub-label">View profile</span>
                </div>
            </a>
            <div class="field">
                <div class="inner-field">
                    </div>
                    <div class="m r typing"><img src="/res/assets/typing0.gif" alt=""></div>
            </div>
            <form class="send-form" method="post">
                <input type="text" name="msg" class="msg-input" placeholder="Type a message..." autocomplete="off">
                <input type="hidden" name="sid" value="<?php echo $user_data['id'] ?>">
                <input type="hidden" name="rid" value="<?php echo $_GET['r'] ?>">
                <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>
    <script>
        async function chat(){
            var res = await fetch('get_msg.php', {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "rid=<?php echo $rec_id ?>"
            });
            var data = await res.text();
            document.querySelector('.inner-field').innerHTML = data;
            field.scrollTop = field.scrollHeight;
        }
        function markChatAsRead(activeUserId){
            socket.emit('mark_as_read', {
                senderId: activeUserId,
                receiverId: currentUserId
            });
        }

        socket.on('message_marked_read', (data) => {
            const activePartnerId = "<?php echo $rec_id; ?>"
            if(data.readBy === activePartnerId){
                document.querySelectorAll('.isRead.unread').forEach(el => {
                    el.classList.remove('unread');
                    el.classList.add('read');
                });
            }
        });
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
        socket.on('typing_back', (data) => {
            if(data.typedBy == "<?php echo $rec_id; ?>" && data.typedTo == currentUserId){
                if(data.isTyping){
                    document.querySelector('.typing').style.display = 'inline';
                    document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
                }
                else{
                    document.querySelector('.typing').style.display = 'none';
                }
            }
        })
        var field = document.querySelector('.field');
        document.querySelector('.send-form').addEventListener('submit', (e)=>{
            if(document.querySelector('.msg-input').value.length != 0){
                var url = "send.php";
                var request = new XMLHttpRequest();
                request.open('POST', url, true);
                request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
                request.send(new FormData(e.target));
                document.querySelector('.msg-input').value = null;
                document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
            }
            e.preventDefault();
        })
        window.onload = function(){
            chat().then(() => {
                markChatAsRead("<?php echo $rec_id; ?>")
            })
        }
    </script>
</body>
</html>