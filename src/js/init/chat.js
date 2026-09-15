import { UI } from "../ui-helpers.js";
import { socket } from "./main.js";

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

    id = document.querySelector('meta[name="id"]')?.content;
    rid = document.querySelector('meta[name="rid"]')?.content;
    unreadCount = document.querySelector('meta[name="unread-count"]')?.content;

    const field = document.querySelector('.field');

    if(!id || !rid) return;

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
    
    field.scrollTop = field.scrollHeight;
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