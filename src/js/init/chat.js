// Initialization
import { UI, HTTP, Fetch, Form, Check, HTML } from "../helpers.js";
import { socket, userDataReady} from "./main.js";
import { chatReadyToLoad } from "./inbox.js";

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

let resolveChatIsLoaded;
export const chatLoaded = new Promise((resolve) => {
    resolveChatIsLoaded = resolve;
});

// Counters
let countUnread = 0;
let countGroupPresence = 0;

// Helper functions
function readChat(activeUserId){
    socket.emit('mark_as_read', {
        senderId: activeUserId,
        receiverId: id,
        unreadCount: countUnread
    });
}

function dataTyping(isTyping){
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

function dataMessage(msg){
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

// Load chat
export async function loadChat(argIsGroup){
    let bufferMessages = '';

    elField = document.querySelector('.field');            
    elInnerField = document.querySelector('.inner-field');
    elInput = document.querySelector('.send-form input');
    isGroup = argIsGroup;

    const getMessages = await HTTP.sendGet('/src/api/messages/', {
        qid: qid,
        isGroup: argIsGroup
    });
    const messages = getMessages.response;

    if(Array.isArray(messages) && messages){

        for(let i = 0; i < messages.length; i++){
            const message = messages[i];

            const nextMsg = messages[i + 1];
            const isNextFromDifferentUser = !nextMsg || message.sid != nextMsg.sid;

            const previousMsg = messages[i - 1];
            const isPreviousFromDifferentUser = !previousMsg || message.sid != previousMsg.sid;
            
            bufferMessages += HTML.message(
                message,
                message.sid == id,
                {isPreviousFromDifferentUser, isNextFromDifferentUser},
                message.user,
                message.isRead
            );
        }
    }
    else{
        bufferMessages = `
            <center>Start chatting now.</center>
        `;
    }
    elInnerField.innerHTML = bufferMessages;
    elField.scrollTop = elField.scrollHeight;
}

document.addEventListener('DOMContentLoaded', async function(){
    countUnread = Number(document.querySelector('meta[name="unread-count"]')?.content);

    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }

    try{
        if(!id) return;

        await chatReadyToLoad;

        if(qid){
            target = await HTTP.fetchUsersBatch(qid);
            if(target){
                readChat(qid);
            }
        }

        try{
            if(!target){
                htmlHeader = (device) => `
                    <a class="profile-header ${device}-header" href='/'>
                        <div class='wrap-pfp'>
                            <img src='/src/assets/pfps/pfp.jpg'>
                        </div>
                        <div class="wrap-label">
                            <div class='username-label'>User not found.</div>
                            <span class="sub-label">Go to home</span>
                        </div>
                    </a>
                `;

                throw new Error('User not found.');
            }
            if(!target.isGroup){
                const user = target;

                htmlHeader = (device) => `
                    <a class="profile-header ${device}-header" href='/profile/?q=${qid}'>
                        <div class='wrap-pfp ${user.isOnline ? 'online' : 'offline'}'>
                            <img src='/src/assets/pfps/${user.img}'>
                            <div class='online-status'></div>
                        </div>
                        <div class="wrap-label">
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
                        <img src='/src/assets/pfps/${memberPfp1}'>
                        <img src='/src/assets/pfps/${memberPfp2}'>
                    `;
                }
                otherUsers.forEach(u => {
                    countGroupPresence += u.online ? 1 : 0;
                    if(u.online) groupPresence.push(u.id);
                });

                htmlHeader = (device) => `
                    <a class="profile-header ${device}-header">
                        <div class='wrap-pfp ${countGroupPresence > 0 ? 'online' : 'offline'}'>
                            ${groupImg}
                            <div class='online-status'></div>
                        </div>
                        <div class="wrap-label">
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
            if(msgText.trim().length > 0){
                await HTTP.sendPost('/src/api/messages/', dataMessage(msgText));

                document.querySelector('.msg-input').value = null;
                isTyping = false;
                socket.emit('typing', dataTyping(false));
            }
        });

        // When typing
        elInput.addEventListener('input', () => {
            const hasText = elInput.value.trim().length > 0;

            if(hasText && !isTyping){
                isTyping = true;
                socket.emit('typing', dataTyping(true));
            }

            else if(!hasText && isTyping){
                isTyping = false;
                socket.emit('typing', dataTyping(false));
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName) || document.activeElement.isContentEditable) return;

            if(e.ctrlKey || e.metaKey || e.altKey || (e.key.length > 1 && e.key !== 'Backspace')) return;

            elInput.focus();
        });
        
        resolveChatIsLoaded();
    }
    catch(error){
        console.error('An error occured while trying to load chat: ', error);
    }
});

// Users presense
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
    
    document.querySelectorAll('.profile-header .wrap-pfp').forEach(pfp => {

        if(data.userId == qid){
            if(data.isOnline) pfp.classList.replace('offline', 'online');
            else if(!data.isOnline) pfp.classList.replace('online', 'offline');
        }

        if(isGroup){
            if(countGroupPresence > 0) pfp.classList.replace('offline', 'online');
            else if(countGroupPresence == 0) pfp.classList.replace('online', 'offline');
        }
    });
});

// New message
socket.on('new_message', (msg) => {
    elInnerField = document.querySelector('.inner-field');
    let isRelevantMsg = null;
    if(!isGroup){
        isRelevantMsg = (msg.sid == id && msg.rid == qid) || (msg.sid == qid && msg.rid == id);
    }
    else{
        isRelevantMsg = (msg.sid == id && msg.groupId == groupId) || (otherUserIds.includes(msg.sid) && msg.groupId == groupId);
    }

    if(!isRelevantMsg) return;

    const elAllMsg = document.querySelectorAll('.inner-field .wrap-msg');
    const elLastMsg = elAllMsg[elAllMsg.length - 1];

    let isPreviousFromDifferentUser = true;
    if(elLastMsg){
        const isLastMsgFromYou = elLastMsg.classList.contains('you-wrap');
        if(isLastMsgFromYou && msg.sid != id){
            elLastMsg.querySelector('.pfp-link').classList.replace('visible', 'hidden');
        }
        elLastMsg.classList.remove('section-gap');
    
        isPreviousFromDifferentUser = (msg.sid == id) != (elLastMsg.classList.contains('me-wrap'));
        if(!isPreviousFromDifferentUser) elLastMsg.classList.remove('last-msg');
    }
    
    elInnerField.innerHTML += HTML.message(
        msg,
        msg.sid == id,
        {isPreviousFromDifferentUser: isPreviousFromDifferentUser, isNextFromDifferentUser: true},
        !isGroup ? target : Fetch.selectUserById(msg.sid, otherUsers),
        false,
        {isLive: true}
    );

    if(msg.sid != id) readChat(qid);
    elField.scrollTop = elField.scrollHeight;
});

// Seen message
socket.on('message_marked_read', (data) => {
    if(data.readBy === qid){
        elInnerField.querySelectorAll('.me-wrap').forEach(el => {
            el.classList.add('seen');
        });
    }
});

// Typing
socket.on('typing_back', async (data) => {

    await targetDataReady;

    if(!isGroup){

        if(data.typedBy == qid && data.typedTo == id){

            if(data.isTyping){
                elField.innerHTML += `
                    <div class='you-wrap wrap-typing'>
                        <div class='pfp-wrap'>
                            <a href='/profile/?q=${target.id}' class='pfp-link'>
                                <img src='/src/assets/pfps/${target.img}' class='msg-pfp'>
                            </a>
                        </div>
                        <div class="msg you typing">
                            <img src="/src/assets/typing0.gif">
                        </div>
                    </div>
                `;
                elField.scrollTop = elField.scrollHeight;
            }
            else{
                document.querySelector('.wrap-typing').remove();
            }
        }
    }
    else{

        if(otherUserIds.includes(data.typedBy) && data.typedTo == groupId){
            const typer = Fetch.selectUserById(data.typedBy, otherUsers);
            const wrapTyping = document.querySelector('.wrap-typing');

            if(data.isTyping){

                if(!wrapTyping){
                    elField.innerHTML += `
                        <div class='you-wrap wrap-typing'>
                            <div class='pfp-wrap'>
                                <img src='/src/assets/pfps/${typer.img}' class='msg-pfp img-${typer.id}'>
                            </div>
                            <div class="msg you typing">
                                <img src="/src/assets/typing0.gif">
                            </div>
                        </div>
                    `;
                }
                else{
                    if(!wrapTyping.querySelector(`.img-${typer.id}`)){
                        wrapTyping.querySelector('.pfp-wrap').insertAdjacentHTML('afterbegin', `
                            <img src='/src/assets/pfps/${typer.img}' class='msg-pfp img-${typer.id}'>
                        `);
                    }
                }
                elField.scrollTop = elField.scrollHeight;
            }
            else{

                if(wrapTyping){
                    const typerImg = wrapTyping.querySelector(`.img-${typer.id}`);
                    if(typerImg) typerImg.remove();

                    if(wrapTyping.querySelectorAll('.msg-pfp').length == 0){
                        wrapTyping.remove();
                    }
                }
            }
        }
    }
});