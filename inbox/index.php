<?php
//Initialization
include $_SERVER['DOCUMENT_ROOT'] . "/register/index.php";
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
                </div>
            </div>
        </div>
    </div>
    <?php echo $requests_model ?>
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

        window.onload = async function(){
            const res = await UI.sendGet('/src/api/conversations');
            const sortedConversations = res.response.sort((a, b) => {
                return new Date(b.lastMsg.time) - new Date(a.lastMsg.time)
            })
            sortedConversations.forEach(conversation => {
                const isOnline = eval(conversation.isOnline) ? 'visible' : 'hidden';
                var username;

                var lastMsg;
                var badgeVsibility;
                var lastMsgColor;
                var lastMsgWeight;
                
                if(conversation.username != null){
                    username = conversation.username;
                }
                else{
                    username = "User not found"
                }

                if(conversation.lastMsg.sid != id){
                    lastMsg = conversation.lastMsg.msg;
    
                    if(!eval(conversation.lastMsg.isRead)){
                        badgeVsibility = 'visiblie';
                        lastMsgColor = '#d5d6c3';
                        lastMsgWeight = 'bolder';
                    }
                    else{
                        badgeVsibility = 'hidden';
                        lastMsgColor = '#a8a8a8';
                        lastMsgWeight = '400';
                    }
                }
                else{
                    lastMsg = `You: ${conversation.lastMsg.msg}`;
                    badgeVsibility = 'hidden';
                }

                document.querySelector('.list').innerHTML += `
                    <div class='user-card u-${conversation.id}'>
                        <a href='/chat?r=${conversation.id}' class='profile-link'></a>
                            <div class='pfp'>
                                <img class='pfp-img' src='/src/assets/pfps/${conversation.img}'>
                                <div class='online-status' style='visibility: ${isOnline}'></div>
                            </div>
                            <div class='label-wrapper'>
                                <div class='username-label'>${username}</div>
                                <span class='sub-label' style='color:${lastMsgColor}; font-weight: ${lastMsgWeight}'>${lastMsg}</span>
                            </div>
                        <div class='btn-wrapper-inbox'>
                            <div class='unread-msg' style='visibility: ${badgeVsibility}'></div>
                            <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
                        </div>
                    </div>
                `;
            });
        }
    </script>
</body>
</html>