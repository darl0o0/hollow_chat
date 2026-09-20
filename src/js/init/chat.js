import { UI } from "../ui-helpers.js";
import { socket, userDataReady} from "./main.js";

let me = null;
let id = null;
let rid = null;
let unreadCount = 0;

// Mark the last message as read when the page is loaded
function markChatAsRead(activeUserId){
    socket.emit('mark_as_read', {
        senderId: activeUserId,
        receiverId: id,
        unreadCount: unreadCount
    });
}

document.addEventListener('DOMContentLoaded', async function(){
    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }

    rid = document.querySelector('meta[name="qid"]')?.content;
    unreadCount = document.querySelector('meta[name="unread-count"]')?.content;

    const field = document.querySelector('.field');

    if(!id) return;

    const header = document.querySelectorAll('.profile-header');
    const img = document.querySelectorAll('.pfp-img');
    const username = document.querySelectorAll('.username-label');
    const onlineDot = document.querySelectorAll('.online-status');

    try{
        const user = await UI.sendGet('/src/api/users/search.php', {q: rid});
        
        if(user.response.profile && !user.response.isYou){
            document.querySelector('.inner-field').style.display = 'flex';

            header.forEach((Header)=>Header.href = `/profile?q=${rid}`);
            img.forEach((Img)=>Img.src = `/src/assets/pfps/${user.response.profile.img}`);
            username.forEach((Username)=>Username.textContent = user.response.profile.username);
            onlineDot.forEach((OnlineDot)=>OnlineDot.style.visibility = user.response.profile.online ? 'visible' : 'hidden');

            document.querySelector('input[name="sid"]').value = id;
            document.querySelector('input[name="rid"]').value = rid;
        }
        else{
            throw new Error('User not found');
        }
    }
    catch(error){
        console.error(error);
        
        document.querySelector('.inner-field').style.display = 'none';

        document.querySelector('.not-found').textContent = "User not found.";
        header.forEach((Header)=>Header.href = "/");
        img.forEach((Img)=>Img.src = "/src/assets/pfps/pfp.jpg");
        username.forEach((Username)=>Username.textContent = "User not found");
        onlineDot.forEach((OnlineDot)=>OnlineDot.style.visibility = 'hidden');
    }

    // Load chat when the page is loaded
    const res = await UI.sendGet('/src/api/messages/', {rid: rid});
    if(Array.isArray(res.response)){
        var buffer = '';
        res.response.forEach(message => {
            if(message.sid == id){
                const isRead = Boolean(Number(message.isRead)) ? '#e9e4c8' : 'none';

                buffer += `
                    <div class='msg-wrap'>
                        <div class='isRead unread' style='background: ${isRead};'></div>
                        <div class='s m'>${message.msg}</div>
                    </div>
                `;
            }
            else if(message.sid == rid){
                buffer += `
                    <div class='r m'>${message.msg}</div>
                `;
            }
        });
        document.querySelector('.inner-field').innerHTML = buffer;
        field.scrollTop = field.scrollHeight;
    }
    else if(res.response == null){
        document.querySelector('.inner-field').innerHTML = `
            <center>Start chatting now.</center>
        `;
    }
});

document.addEventListener('DOMContentLoaded', () => {
    markChatAsRead(rid);
});

// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {
    
    // data = { userId: "...", isOnline: 1 or 0 }
    document.querySelectorAll(".online-status").forEach(onlineStatus => {
        if(data.userId == rid){
            onlineStatus.style.visibility = data.isOnline ? "visible" : "hidden";
        }
    });
});

// Send message
document.querySelector('.send-form').addEventListener('submit', async function(e){
    e.preventDefault();
    if(document.querySelector('.msg-input').value){
        const formData = new FormData(e.target);
        
        //HTTP Request
        await UI.sendPost('/src/api/messages/', formData);

        // Make the input empty && scroll to the bottom of the field && isTyping = 0
        document.querySelector('.msg-input').value = null;
        document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
        isTyping = false;
        
        socket.emit('typing', {
            typedBy: id,
            typedTo: rid,
            isTyping: 0
        });
    }
});

// Receive messages instantly without polling
socket.on('new_message', (data) => {
    const isRelevantMsg = (data.sid == id && data.rid == rid) || (data.sid == rid && data.rid == id);
    if(!isRelevantMsg) return;

    const chatField = document.querySelector('.inner-field');
    const msgDiv = document.createElement('div');
    
    // Set 's m' class for sender, 'r m' for receiver
    if(data.sid === id){
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
        markChatAsRead(rid);
    }
    msgDiv.textContent = data.message;
    
    document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
});

// When the other user read's the current user's message
socket.on('message_marked_read', (data) => {
    if(data.readBy === rid){
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
    if(hasText && !isTyping){
        isTyping = true;
        socket.emit('typing', {
            typedBy: id,
            typedTo: rid,
            isTyping: 1
        });
    } 
    else if(!hasText && isTyping){
        isTyping = false;
        socket.emit('typing', {
            typedBy: id,
            typedTo: rid,
            isTyping: 0
        });
    }
});

// When the other user is typing
socket.on('typing_back', (data) => {
    if(data.typedBy == rid && data.typedTo == id){
        if(data.isTyping){
            document.querySelector('.typing').style.display = 'inline';
            document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
        }
        else{
            document.querySelector('.typing').style.display = 'none';
        }
    }
})