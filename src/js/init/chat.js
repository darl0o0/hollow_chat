// Initialization
import { UI, HTTP, Fetch, Form, Check, HTML, defaultProfilePic } from "../helpers.js";
import { socket, userDataReady} from "./main.js";

const getParams = new URLSearchParams(window.location.search);

// User objects
let me = null;
let target = null;

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

const elChatPreview = document.querySelector('.chat-preview');

// HTML
let htmlHeader = null;
const htmlChatPageContent = `
    <div class="wrap-field-input">
        <div class="field">
            <center><h1 class="not-found"></h1></center>
            <div class="inner-field"></div>
        </div>
        <form class="send-form" method="post">
            <input type="text" class="msg-input" placeholder="Type a message..." autocomplete="off">
            <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
`;

// Pormises
let resolveTargetData;
const targetDataReady = new Promise((resolve) => {
    resolveTargetData = resolve;
});

let resolveChatIsLoaded;
export const chatLoaded = new Promise((resolve) => {
    resolveChatIsLoaded = resolve;
});

let resolveChatReadyToLoad;
const chatReadyToLoad = new Promise((resolve) => {
    resolveChatReadyToLoad = resolve;
});

// Counters
let countGroupPresence = 0;

// Helper functions
function readChat(cid){
    socket.emit('seen:ping', { by: id, to: cid });
}

function dataTyping(isTyping){
    const dataGroupId = isGroup ? groupId : null;
    const to = isGroup ? otherUserIds : qid;

    return {
        by: id,
        to,
        isTyping,
        isGroup,
        groupId: dataGroupId
    }
}

function dataMessage(msg){
    const dataGroupId = isGroup ? groupId : null;
    const to = isGroup ? JSON.stringify(otherUserIds) : qid;

    return {
        sid: id,
        qid: to,
        msg,
        isGroup,
        groupId: dataGroupId
    }
}

function updateHeaders(desktopHeader, mobileHeader){
    document.querySelectorAll('.profile-header').forEach(h => h?.remove());

    document.querySelector('.chat-preview').insertAdjacentHTML('afterbegin', desktopHeader);
    document.querySelector('.logo').insertAdjacentHTML('afterend', mobileHeader);
}

// Load chat
async function loadChat(target){
    let bufferMessages = '';

    const elField = document.querySelector('.field');            
    const elInnerField = document.querySelector('.inner-field');
    const elInput = document.querySelector('.send-form input');

    elInput.focus();
    isGroup = target.isGroup;

    const getMessages = await HTTP.send('/src/api/messages/', 'GET', {
        qid: target.id,
        isGroup: target.isGroup
    }); // Should be query
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
        bufferMessages = '<center>Start chatting now.</center>';
    }
    elInnerField.innerHTML = bufferMessages;
    scroll();
}

// Load header
async function loadHeader(target){
    try{
        if(!target){
            throw new Error('User not found');
        }
        if(!target.isGroup){
            const user = target;

            htmlHeader = (device) => HTML.header(device, user.username, user.img, user.isOnline, 'View profile', `/profile/?q=${user.id}`);

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
                groupName = otherUsers
                .slice(0, 2)
                .map(m => m.username)
                .join(', ');

                if(otherUsers.length > 2) groupName += '...';
            }

            let groupImg;
            if(!group.img) groupImg = [otherUsers[0].img, otherUsers[1].img];

            otherUsers.forEach(u => {
                countGroupPresence += u.isOnline ? 1 : 0;
                if(u.isOnline) groupPresence.push(u.id);
            });

            htmlHeader = (device) => HTML.header(device, groupName, groupImg, countGroupPresence > 0, 'Manage chat');

            resolveTargetData();
        }
    }
    catch(error){
        console.error(error);

        htmlHeader = (device) => HTML.header(device, 'User not found.', defaultProfilePic, false, 'Go to home', '/');
        document.querySelector('.chat-preview').innerHTML = '<h1 class="not-found flex-center">User not found.</h1>';
    }
    
    updateHeaders(htmlHeader('desktop'), htmlHeader('mobile'));
}

// Render chat
async function renderChat(targetId){
    target = await HTTP.fetchUsersBatch(targetId);

    resolveChatReadyToLoad();
    loadHeader(target);
    loadChat(target);
    try{
        await chatLoaded;
        readChat(target.id);
        const elField = document.querySelector('.field');
        scroll();
    }
    catch(error){
        console.error("Chat hasn't loaded successfully.");
    }
}

// Scroll down
function scroll(){
    elField = document.querySelector('.field');
    elField.scrollTop = elField.scrollHeight;
}

export async function inboxPageState(isActiveChat, chatId = null){
    document.querySelectorAll('.user-card.highlight').forEach(c => c.classList.remove('highlight'));
    elChatPreview.querySelector('.wrap-field-input')?.remove();

    if(isActiveChat && chatId){
        updateHeaders(HTML.skeleton('header', 'desktop'), HTML.skeleton('header', 'mobile'));
        elChatPreview.querySelector('.init-inbox')?.remove();
        document.querySelector(`.chat-card.u-${chatId}`)?.classList.add('highlight');

        elChatPreview.classList.remove('flex-center');
        elChatPreview.insertAdjacentHTML('beforeend', htmlChatPageContent);
        
        // Preview chat
        try{
            renderChat(chatId);
        }
        catch(error){
            console.error(error);
        }
    }
    else{
        document.querySelectorAll('.user-card.highlight').forEach(c => c.classList.remove('highlight'));
        elChatPreview.innerHTML = '';
        elChatPreview.classList.add('flex-center');
        elChatPreview.insertAdjacentHTML('afterbegin', `
            <div class="init-inbox">
                <img src="/src/assets/logo/empty_inbox.png">
                <div class="label-wrap">
                    <span class="label">Your direct messages.</span>
                    <span class="sub-label">Choose a chat or start a new one.</span>
                </div>
                <button class="btn primary-btn"><i class="fa-solid fa-comment-medical"></i> Start a new chat</button>
            </div>
        `);
    }
}

if(qid){
    inboxPageState(true, qid);
}
else{
    inboxPageState(false);
}

document.addEventListener('DOMContentLoaded', async function(){

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
        
        // Send message
        document.querySelector('.send-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const msgText = document.querySelector('.msg-input').value;
            if(msgText.trim().length > 0){
                await HTTP.send('/src/api/messages/', 'POST', dataMessage(msgText));

                document.querySelector('.msg-input').value = null;
                isTyping = false;
                socket.emit('typing:ping', dataTyping(false));
            }
        });

        // When typing
        const elInput = document.querySelector('.send-form input');
        elInput.addEventListener('input', () => {
            const hasText = elInput.value.trim().length > 0;

            if(hasText && !isTyping){
                isTyping = true;
                socket.emit('typing:ping', dataTyping(true));
            }

            else if(!hasText && isTyping){
                isTyping = false;
                socket.emit('typing:ping', dataTyping(false));
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

document.addEventListener('click', (e) => {
    const elReactBtn = e.target.closest('.msg-react');
    const isReactMenuClicked = e.target.closest('.wrap-react-menu');

    if(!elReactBtn && !isReactMenuClicked){
        document.querySelectorAll('.wrap-react-menu').forEach(el => el.remove());

        document.querySelectorAll('.msg-actions').forEach(el => {
            el.classList.replace('visible', 'hidden');
            el.classList.add('interact-on-hover');
        });
        return;
    }

    if(!elReactBtn) return;

    const elMsgActions = elReactBtn.parentElement;
    const elReactMenu = elMsgActions.querySelector('.wrap-react-menu');

    if(elReactMenu){
        elReactMenu.remove();
        elMsgActions.classList.replace('visible', 'hidden');
        elMsgActions.classList.add('interact-on-hover');
    }
    else{
        document.querySelectorAll('.wrap-react-menu').forEach(el => el.remove());
        document.querySelectorAll('.msg-actions').forEach(el => {
            el.classList.replace('visible', 'hidden');
            el.classList.add('interact-on-hover');
        });

        elMsgActions.insertAdjacentHTML('afterbegin', `
            <div class='wrap-react-menu'>
                <div class='react-menu'>
                    <button class='heart'>❤️</button>
                    <button class='laugh'>😂</button>
                    <button class='cute'>🥺</button>
                </div>
            </div>
        `);
        elMsgActions.classList.replace('hidden', 'visible');
        elMsgActions.classList.remove('interact-on-hover');
    }
});

// Users presense
socket.on('presence', (data) => {

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
socket.on('message', (msg) => {
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
        const isLastMsgFromYou = elLastMsg.classList.contains('wrap-you');
        if(isLastMsgFromYou && msg.sid != id){
            elLastMsg.querySelector('.pfp-link').classList.replace('visible', 'hidden');
        }
        elLastMsg.classList.remove('section-gap');
    
        isPreviousFromDifferentUser = (msg.sid == id) != (elLastMsg.classList.contains('wrap-me'));
        if(!isPreviousFromDifferentUser) elLastMsg.classList.remove('last-msg');
    }
    
    elInnerField.innerHTML += HTML.message(
        msg,
        msg.sid == id,
        {isPreviousFromDifferentUser, isNextFromDifferentUser: true},
        !isGroup ? target : Fetch.selectUserById(msg.sid, otherUsers),
        false,
        {isLive: true}
    );

    if(msg.sid != id) readChat(qid);
    scroll();
});

// Seen message
socket.on('seen:pong', (data) => {
    if(data.by === qid){
        elInnerField.querySelectorAll('.wrap-me').forEach(el => {
            el.classList.add('seen');
        });
    }
});

// Typing
socket.on('typing:pong', async (data) => {
    const { by, to, isTyping } = data;
    await targetDataReady;

    elField = document.querySelector('.field');

    if(!isGroup){

        if(by == qid && to == id){

            if(isTyping){
                elField.innerHTML += `
                    <div class='wrap-you wrap-typing'>
                        <div class='wrap-pfp'>
                            <a href='/profile/?q=${HTML.escapeHTML(target.id)}' class='pfp-link'>
                                <img src='/src/assets/pfps/${HTML.escapeHTML(target.img)}' class='msg-pfp'>
                            </a>
                        </div>
                        <div class="msg you typing">
                            <img src="/src/assets/typing0.gif">
                        </div>
                    </div>
                `;
                scroll();
            }
            else{
                document.querySelector('.wrap-typing').remove();
            }
        }
    }
    else{
        if(otherUserIds.includes(by) && to == groupId){
            const typer = Fetch.selectUserById(by, otherUsers);
            const wrapTyping = document.querySelector('.wrap-typing');

            if(isTyping){

                if(!wrapTyping){
                    elField.innerHTML += `
                        <div class='wrap-you wrap-typing'>
                            <div class='wrap-pfp'>
                                <img src='/src/assets/pfps/${HTML.escapeHTML(typer.img)}' class='msg-pfp img-${HTML.escapeHTML(typer.id)}'>
                            </div>
                            <div class="msg you typing">
                                <img src="/src/assets/typing0.gif">
                            </div>
                        </div>
                    `;
                }
                else{
                    if(!wrapTyping.querySelector(`.img-${typer.id}`)){
                        wrapTyping.querySelector('.wrap-pfp').insertAdjacentHTML('afterbegin', `
                            <img src='/src/assets/pfps/${HTML.escapeHTML(typer.img)}' class='msg-pfp img-${HTML.escapeHTML(typer.id)}'>
                        `);
                    }
                }
                scroll();
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