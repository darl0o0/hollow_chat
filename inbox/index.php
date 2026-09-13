<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/src/css/inbox.css">
    <script type="module" src="/src/js/init/inbox.js"></script>
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
                <div class="list"></div>
            </div>
        </div>
    </div>
    <script>
        const id = "<?php echo $id ?>";
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

            const targetId = (data.sid == id)? data.rid : data.sid;
            const subLabel = document.querySelector(`.u-${targetId} .sub-label`);
            const isUnreadMsg = document.querySelector(`.u-${targetId} .unread-msg`);

            const conversationCard = document.querySelector(`.u-${targetId}`);
            const listContainer = document.querySelector('.list');
            listContainer.prepend(conversationCard);
            
            if(subLabel){

                if(data.sid == id){
                    subLabelValue[0] = `You: ${data.message}`;
                    subLabel.innerHTML = subLabelValue[0];

                    isUnreadMsg.style.visibility = 'hidden';
                    subLabel.style.color = '#a8a8a8';
                    subLabel.style.fontWeight = '400';
                }
                else{
                    subLabelValue[0] = data.message;
                    subLabel.innerHTML = subLabelValue[0];

                    isUnreadMsg.style.visibility = 'visible';
                    subLabel.style.color = '#d5d6c3';
                    subLabel.style.fontWeight = 'bolder';
                }
            }
        });

        // Class the last message as read as needed
        socket.on('message_marked_read', (data) => {
            const subLabel = document.querySelector(`.u-${data.readTo} .sub-label`);
            const isUnreadMsg = document.querySelector(`.u-${data.readTo} .unread-msg`);

            if(data.readBy == '<?php echo $id ?>'){
                isUnreadMsg.style.visibility = 'hidden';
                subLabel.style.color = '#a8a8a8';
                subLabel.style.fontWeight = '400';
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
        });
    </script>
</body>
</html>