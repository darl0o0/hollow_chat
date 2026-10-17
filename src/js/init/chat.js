// Initialization
import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";
import { socket, userDataReady} from "./main.js";
import { chatContentLoaded } from "./inbox.js";

const getParams = new URLSearchParams(window.location.search);

// User objects
let me = null;
let target = null;
let group = null;

// IDs
let id = null;
const qid = getParams.get('q') ? (getParams.get('q').length > 0 ? getParams.get('q') : null) : null;
let groupId = null;

// Lists of objects
let otherUsers = [];
let otherUserIds = [];
let groupPresence = [];

// Booleans
let isGroup = false;
let isTyping = false;

// Elements
let elField = null;
let elInnerField = null;
let elAllMsg = null;
let elInput = null;

// HTML
let htmlHeader = null;

// Pormises
let resolveTargetData;
const targetDataReady = new Promise((resolve) => {
    resolveTargetData = resolve;
});

// Counters
let countUnread = 0;
let countGroupPresence = 0;

// Helper functions
function markChatAsRead(activeUserId){
    socket.emit('mark_as_read', {
        senderId: activeUserId,
        receiverId: id,
        unreadCount: countUnread
    });
}

function typingData(isTyping){
    const dataGroupId = isGroup ? groupId : null;
    const dataTypedTo = isGroup ? otherUserIds : qid;

    return {
        typedBy: id,
        typedTo: dataTypedTo,
        isTyping: isTyping,
        isGroup: isGroup,
        groupId: dataGroupId
    }
}

function newMessageData(msg){
    const dataGroupId = isGroup ? groupId : null;
    const to = isGroup ? JSON.stringify(otherUserIds) : qid;

    return {
        sid: id,
        qid: to,
        msg: msg,
        isGroup: isGroup,
        groupId: dataGroupId
    }
}

function appendMessage(msg, isYou, options = {}){
    let isNextFromDifferentUser;

    elField = document.querySelector('.field');            
    elInnerField = document.querySelector('.inner-field'); 
    elAllMsg = elInnerField.querySelectorAll('.msg-wrap');
    
    const elLastMsg = elAllMsg[elAllMsg.length - 1];

    if(elAllMsg.length > 0){
        isNextFromDifferentUser = !(elLastMsg.classList.contains('me-wrap') == isYou);
    }
    const addGap = isNextFromDifferentUser ? 'section-gap' : '';
    
    let showPfp = 'hidden';
    const elAllMsgFromYou = elInnerField.querySelectorAll('.msg-wrap.you-wrap');

    if(!isYou && elAllMsgFromYou.length > 0){
        const elLastMsgFromYou = elAllMsgFromYou[elAllMsgFromYou.length - 1];

        if(isNextFromDifferentUser) elLastMsgFromYou.querySelector('.pfp-link').classList.remove('hidden');
    }

    if(!isYou && options.isLive){
        const elLastMsgFromYou = elAllMsgFromYou[elAllMsgFromYou.length - 1];
        elLastMsgFromYou.querySelector('.pfp-link').classList.add('hidden');
        showPfp = ''
    }

    if(isYou){
        const isRead = options.isRead ? 'seen' : '';

        elInnerField.insertAdjacentHTML('beforeend', `
            <div class='msg-wrap me-wrap ${addGap}'>
                <div class='seen-dot ${isRead}'></div>
                <div class='msg me'>${msg}</div>
            </div>
        `);
    }
    else{
        const sender = options.sender;

        elInnerField.insertAdjacentHTML('beforeend', `
            <div class='msg-wrap you-wrap ${addGap}'>
                <a href='/profile/?q=${sender.id}' class='pfp-link ${showPfp}'>
                    <img src='/src/assets/pfps/${sender.img}' class='msg-pfp'>
                </a>
                <div class='msg you'>${msg}</div>
            </div>
        `);
    }
}

// Load chat
export async function loadChat(argIsGroup){
    elField = document.querySelector('.field');            
    elInnerField = document.querySelector('.inner-field'); 
    elAllMsg = elInnerField.querySelectorAll('.msg-wrap');
    elInput = document.querySelector('.send-form input');
    isGroup = argIsGroup;

    const getMessages = await HTTP.sendGet('/src/api/messages/', {
        qid: qid,
        isGroup: argIsGroup
    });
    const messages = getMessages.response;

    if(Array.isArray(messages)){

        for(let i = 0; i < messages.length; i++){
            const message = messages[i];
            
            if(message.sid == id){
                const isRead = Boolean(Number(message.isRead));

                appendMessage(message.msg, true, {
                    isRead: isRead
                });
            }
            else{
                appendMessage(message.msg, false, {
                    sender: message.user,
                    isLive: false
                });
            }
        }

        const elAllMsgFromYou = elInnerField.querySelectorAll('.msg-wrap.you-wrap');
        const elLastMsgFromYou = elAllMsgFromYou[elAllMsgFromYou.length - 1];

        elLastMsgFromYou.querySelector('.pfp-link').classList.remove('hidden');

        // elInnerField.innerHTML = bufferMessages;
    }
    else if(!getMessages.response){
        return `
            <center>Start chatting now.</center>
        `;
    }
    elField.scrollTop = elField.scrollHeight;
}

document.addEventListener('DOMContentLoaded', async function(){
    countUnread = document.querySelector('meta[name="unread-count"]')?.content;

    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }

    try{
        if(!id) return;

        await chatContentLoaded;

        if(qid){
            target = await HTTP.fetchUsersBatch(qid);
            if(target){
                markChatAsRead(qid);
            }
        }

        try{
            if(!target){
                htmlHeader = (device) => `
                    <a class="profile-header ${device}-header" href='/'>
                        <div class='pfp pfp-container'>
                            <img class='pfp-img' src='/src/assets/pfps/pfp.jpg'>
                        </div>
                        <div class="label-wrapper">
                            <div class='username-label'>User not found.</div>
                            <span class="sub-label">Go to home</span>
                        </div>
                    </a>
                `;

                throw new Error('User not found.');
            }
            if(!target.isGroup){
                const user = target;
                document.querySelector('.inner-field').style.display = 'flex';

                htmlHeader = (device) => `
                    <a class="profile-header ${device}-header" href='/profile/?q=${qid}'>
                        <div class='pfp pfp-container'>
                            <img class='pfp-img' src='/src/assets/pfps/${user.img}'>
                            <div class='online-status' style='visibility: ${user.online ? 'visible' : 'hidden'}'></div>
                        </div>
                        <div class="label-wrapper">
                            <div class='username-label'>${user.username}</div>
                            <span class="sub-label">View profile</span>
                        </div>
                    </a>
                `;

                resolveTargetData();
            }
            else{
                const group = target;

                const membersData = await HTTP.fetchUsersBatch(group.members);
                otherUsers = membersData.filter(user => {
                    return user.id != me.id;
                });
                
                otherUsers.forEach(u => {
                    otherUserIds.push(u.id);
                });

                groupId = group.id;
                
                let groupName = '';
                if(!group.name){
                    groupName = otherUsers.map(m => {
                        return m.username;
                    }).join(', ');
                }

                let memberPfp1;
                let memberPfp2;
                let groupImg = '';
                if(!group.img){
                    
                    memberPfp1 = otherUsers[0].img;
                    memberPfp2 = otherUsers[1].img;

                    groupImg = `
                        <img class='sub-pfp' src='/src/assets/pfps/${memberPfp1}'>
                        <img class='sub-pfp' src='/src/assets/pfps/${memberPfp2}'>
                    `;
                }
                otherUsers.forEach(u => {
                    countGroupPresence += u.online ? 1 : 0;
                    if(u.online) groupPresence.push(u.id);
                });

                htmlHeader = (device) => `
                    <a class="profile-header ${device}-header"'>
                        <div class='pfp pfp-container'>
                            ${groupImg}
                            <div class='online-status' style='visibility: ${countGroupPresence > 0 ? 'visible' : 'hidden'}'></div>
                        </div>
                        <div class="label-wrapper">
                            <div class='username-label'>${groupName}</div>
                            <span class="sub-label">View profile</span>
                        </div>
                    </a>
                `;

                resolveTargetData();
            }
        }
        catch(error){
            console.error(error);

            document.querySelector('.chat-preview').innerHTML = '<h1 class="not-found flex-center">User not found.</h1>';
        }
        
        document.querySelector('.chat-preview').insertAdjacentHTML('afterbegin', htmlHeader('desktop'));
        document.querySelector('.logo').insertAdjacentHTML('afterend', htmlHeader('mobile'));

        // Send message
        document.querySelector('.send-form').addEventListener('submit', async function(e){
            e.preventDefault();
            const msgText = document.querySelector('.msg-input').value;
            if(msgText){
                await HTTP.sendPost('/src/api/messages/', newMessageData(msgText));

                document.querySelector('.msg-input').value = null;
                isTyping = false;
                socket.emit('typing', typingData(false));
            }
        });

        // When typing
        elInput.addEventListener('input', () => {
            const hasText = elInput.value.trim().length > 0;

            if(hasText && !isTyping){
                isTyping = true;
                socket.emit('typing', typingData(true));
            }

            else if(!hasText && isTyping){
                isTyping = false;
                socket.emit('typing', typingData(false));
            }
        });
    }
    catch(error){
        console.error('An error occured while trying to load chat');
    }
});

// When the other user is typing
socket.on('typing_back', async (data) => {

    await targetDataReady;

    if(!isGroup){

        if(data.typedBy == qid && data.typedTo == id){

            if(data.isTyping){
                elField.innerHTML += `
                    <div class='you-wrap typing-wrap'>
                        <a href='/profile/?q=${target.id}' class='pfp-link'>
                            <img src='/src/assets/pfps/${target.img}' class='msg-pfp'>
                        </a>
                        <div class="msg you typing">
                            <img src="/src/assets/typing0.gif">
                        </div>
                    </div>
                `;
                elField.scrollTop = elField.scrollHeight;
            }
            else{
                document.querySelector('.typing-wrap').remove();
            }
        }
    }
    else{

        if(otherUserIds.includes(data.typedBy) && data.typedTo == groupId){
            const typer = Fetch.selectUserById(data.typedBy, otherUsers);
            const wrapper = document.querySelector('.typing-wrap');

            if(data.isTyping){

                if(!wrapper){
                    elField.innerHTML += `
                        <div class='you-wrap typing-wrap'>
                            <img src='/src/assets/pfps/${typer.img}' class='msg-pfp img-${typer.id}'>
                            <div class="msg you typing">
                                <img src="/src/assets/typing0.gif">
                            </div>
                        </div>
                    `;
                }
                else{
                    if(!wrapper.querySelector(`.img-${typer.id}`)){
                        wrapper.insertAdjacentHTML('afterbegin', `
                            <img src='/src/assets/pfps/${typer.img}' class='msg-pfp img-${typer.id}'>
                        `);
                    }
                }
                elField.scrollTop = elField.scrollHeight;
            }
            else{

                if(wrapper){
                    const typerImg = wrapper.querySelector(`.img-${typer.id}`);
                    if(typerImg) typerImg.remove();

                    if(wrapper.querySelectorAll('.msg-pfp').length == 0){
                        wrapper.remove();
                    }
                }
            }
        }
    }
});

// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {

    if(isGroup && otherUserIds.includes(data.userId)){
        if(data.isOnline && !groupPresence.includes(data.userId)){
            groupPresence.push(data.userId);
            countGroupPresence++;
        }
        else if(!data.isOnline && groupPresence.includes(data.userId)){
            groupPresence = groupPresence.filter(id => id != data.userId);
            countGroupPresence--;
        }
    }
    
    document.querySelectorAll(".online-status").forEach(onlineStatus => {

        if(data.userId == qid){
            onlineStatus.style.visibility = data.isOnline ? "visible" : "hidden";
        }
        onlineStatus.style.visibility = countGroupPresence > 0 ? "visible" : "hidden";
    });
});

// Receive messages instantly without polling
socket.on('new_message', (data) => {
    let isRelevantMsg = null;
    if(!isGroup){
        isRelevantMsg = (data.sid == id && data.rid == qid) || (data.sid == qid && data.rid == id);
    }
    else{
        isRelevantMsg = (data.sid == id && data.groupId == groupId) || (otherUserIds.includes(data.sid) && data.groupId == groupId);
    }

    if(!isRelevantMsg) return;
    
    const msg = data.msg;
    
    // Set 's m' class for sender, 'r m' for receiver
    if(data.sid === id){
        appendMessage(msg, true, {
            isRead: false,
            isLive: true
        });
    }
    else{
        let sender = !isGroup ? target : Fetch.selectUserById(data.sid, otherUsers);

        appendMessage(msg, false, {
            sender: sender,
            showPfp: true,
            isLive: true
        });

        markChatAsRead(qid);
    }
    elField.scrollTop = elField.scrollHeight;
});

// When the other user read's the current user's message
socket.on('message_marked_read', (data) => {
    if(data.readBy === qid){
        document.querySelectorAll('.seen-dot').forEach(el => {
            el.classList.add('seen');
        });
    }
});