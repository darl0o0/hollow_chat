// Initialization
import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";
import { socket, userDataReady} from "./main.js";

// User objects
let me = null;
let target = null;
let group = null;

// IDs
let id = null;
let qid = null;
let groupId = null;

// Lists of objects
let otherUsers = [];
let otherUserIds = [];
let groupPresence = [];

// Booleans
let isGroup = false;
let isTyping = false;

// Elements
const elField = document.querySelector('.field');
const elInnerField = document.querySelector('.inner-field');

// Pormises
let resolveTargetData;
const targetDataReady = new Promise((resolve) => {
    resolveTargetData = resolve;
});

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

document.addEventListener('DOMContentLoaded', async function(){
    qid = document.querySelector('meta[name="qid"]')?.content;
    countUnread = document.querySelector('meta[name="unread-count"]')?.content;

    try{
        me = await userDataReady;
        id = me.id;

        markChatAsRead(qid);
    }
    catch(error){
        console.error(error);
    }

    const field = document.querySelector('.field');

    if(!id) return;

    const header = document.querySelectorAll('.profile-header');
    const username = document.querySelectorAll('.profile-header .username-label');
    const pfpContainer = document.querySelectorAll('.pfp-container');

    try{
        const apiUser = await HTTP.sendGet('/src/api/users/search.php', {q: qid});
        target = apiUser.response;

        if(target.profile && !target.isYou){
            document.querySelector('.inner-field').style.display = 'flex';

            const header = (device) => `
                <a class="profile-header ${device}-header" href='/profile?q=${qid}'>
                    <div class='pfp pfp-container'>
                        <img class='pfp-img' src='/src/assets/pfps/${target.profile.img}'>
                        <div class='online-status' style='visibility: ${target.profile.online ? 'visible' : 'hidden'}'></div>
                    </div>
                    <div class="label-wrapper">
                        <div class='username-label'>${target.profile.username}</div>
                        <span class="sub-label">View profile</span>
                    </div>
                </a>
            `;

            document.querySelector('.preview').insertAdjacentHTML('afterbegin', header('desktop'));
            document.querySelector('.main.chat').insertAdjacentHTML('afterbegin', header('mobile'));

            resolveTargetData();
        }
        else{
            const apiCheckGrp = await HTTP.sendGet('/src/api/conversations/check-grp.php', {q: qid});
            group = apiCheckGrp.response.group;
            isGroup = apiCheckGrp.response.isGroup;

            if(isGroup){
                const membersData = await HTTP.fetchUsersBatch(apiCheckGrp.response.members);
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

                const header = (device) => `
                    <a class="profile-header ${device}-header"'>
                        <div class='pfp pfp-container'>
                            ${groupImg}
                            <div class='online-status' style='visibility: ${countGroupPresence > 0 ? 'visibile' : 'hidden'}'></div>
                        </div>
                        <div class="label-wrapper">
                            <div class='username-label'>${groupName}</div>
                            <span class="sub-label">View profile</span>
                        </div>
                    </a>
                `;

                document.querySelector('.preview').insertAdjacentHTML('afterbegin', header('desktop'));
                document.querySelector('.main.chat').insertAdjacentHTML('afterbegin', header('mobile'));

                resolveTargetData();
            }
            else{
                throw new Error('User not found');
            }
        }
    }
    catch(error){
        console.error(error);
        
        document.querySelector('.inner-field').style.display = 'none';

        document.querySelector('.not-found').textContent = "User not found.";
        header.forEach((Header)=>Header.href = "/");
        username.forEach((Username)=>Username.textContent = "User not found");
        
        pfpContainer.forEach(pfp => {
            pfp.innerHTML = `
                <img class='pfp-img' src='/src/assets/pfps/pfp.jpg'>
            `;
        });
    }

    // Load chat when the page is loaded
    const apiMessages = await HTTP.sendGet('/src/api/messages/', {qid, isGroup});
    const messages = apiMessages.response;

    if(Array.isArray(messages)){
        let bufferMessages = '';

        for(let i = 0; i < messages.length; i++){
            const message = messages[i];
            const nextMsg = messages[i + 1];

            const isNextFromDifferentUser = !nextMsg || nextMsg.sid != message.sid;
            const addGap = isGroup ? (isNextFromDifferentUser ? 'section-gap' : '') : '';
            const showPfp = isNextFromDifferentUser ? 'visibile' : 'hidden';


            if(message.sid == id){
                const isRead = Boolean(Number(message.isRead)) ? '#e9e4c8' : 'none';

                bufferMessages += `
                    <div class='msg-wrap s-wrapper'>
                        <div class='isRead unread' style='background: ${isRead};'></div>
                        <div class='s m'>${message.msg}</div>
                    </div>
                `;
            }
            else{
                bufferMessages += `
                    <div class='msg-wrap r-wrapper ${addGap}'>
                        <a href='/profile?q=${message.sid}' class='pfp-link' style='visibility: ${showPfp}'>
                            <img src='/src/assets/pfps/${message.user.img}' class='msg-pfp'>
                        </a>
                        <div class='r m'>${message.msg}</div>
                    </div>
                `;
            }
        }
        document.querySelector('.inner-field').innerHTML = bufferMessages;
        field.scrollTop = field.scrollHeight;
    }
    else if(!apiMessages.response){
        document.querySelector('.inner-field').innerHTML = `
            <center>Start chatting now.</center>
        `;
    }
});

// When the other user is typing
socket.on('typing_back', async (data) => {

    await targetDataReady;

    if(!isGroup){

        if(data.typedBy == qid && data.typedTo == id){

            if(data.isTyping){
                elField.innerHTML += `
                    <div class='r-wrapper typing-wrapper'>
                        <a href='/profile?q=${target.profile.id}' class='pfp-link'>
                            <img src='/src/assets/pfps/${target.profile.img}' class='msg-pfp'>
                        </a>
                        <div class="m r typing">
                            <img src="/src/assets/typing0.gif">
                        </div>
                    </div>
                `;
                elField.scrollTop = elField.scrollHeight;
            }
            else{
                document.querySelector('.typing-wrapper').remove();
            }
        }
    }
    else{

        if(otherUserIds.includes(data.typedBy) && data.typedTo == groupId){
            const typer = Fetch.selectUserById(data.typedBy, otherUsers);
            const wrapper = document.querySelector('.typing-wrapper');

            if(data.isTyping){

                if(!wrapper){
                    elField.innerHTML += `
                        <div class='r-wrapper typing-wrapper'>
                            <img src='/src/assets/pfps/${typer.img}' class='msg-pfp img-${typer.id}'>
                            <div class="m r typing">
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

// Send message
document.querySelector('.send-form').addEventListener('submit', async function(e){
    e.preventDefault();
    const msgText = document.querySelector('.msg-input').value;
    if(msgText){
        
        //HTTP Request
        await HTTP.sendPost('/src/api/messages/', newMessageData(msgText));

        // Make the input empty && scroll to the bottom of the field && isTyping = 0
        document.querySelector('.msg-input').value = null;
        document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
        isTyping = false;
        
        socket.emit('typing', typingData(false));
    }
});

// Receive messages instantly without polling
socket.on('new_message', (data) => {
    let isRelevantMsg = null;
    if(!isGroup){
        isRelevantMsg = (data.sid == id && data.qid == qid) || (data.sid == qid && data.qid == id);
    }
    else{
        isRelevantMsg = (data.sid == id && data.groupId == groupId) || (otherUserIds.includes(data.sid) && data.groupId == groupId);
    }

    if(!isRelevantMsg) return;
    const msg = data.msg;
    
    // Set 's m' class for sender, 'r m' for receiver
    if(data.sid === id){
        elInnerField.insertAdjacentHTML('beforeend' ,`
            <div class='msg-wrap s-wrapper'>
                <div class='isRead unread'></div>
                <div class='s m'>${msg}</div>
            </div>
        `);
    }
    else{
        let sender;
        if(!isGroup){
            sender = target.profile;
        }
        else{
            sender = Fetch.selectUserById(data.sid, otherUsers);
        }
        const pfps = document.querySelectorAll(`.msg-wrap.m-${sender.id} .msg-pfp`);
        if(pfps.length > 0){
            pfps[pfps.length - 1].style.visibility = 'hidden'
        }
        elInnerField.insertAdjacentHTML('beforeend',`
            <div class='msg-wrap r-wrapper m-${sender.id}'>
                <img src='/src/assets/pfps/${sender.img}' class='msg-pfp'>
                <div class='r m'>${msg}</div>
            </div>
        `);
        markChatAsRead(qid);
    }
    elField.scrollTop = elField.scrollHeight;
});

// When the other user read's the current user's message
socket.on('message_marked_read', (data) => {
    if(data.readBy === qid){
        document.querySelectorAll('.isRead.unread').forEach(el => {
            el.classList.remove('unread');
            el.classList.add('read');
        });
    }
});

// When typing
const elInput = document.querySelector('.send-form input');

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