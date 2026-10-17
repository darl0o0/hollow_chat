// Initialization
import { UI, HTTP, Fetch, Form, Check, performanceTimer } from "../helpers.js";
import { socket, userDataReady} from "./main.js";
import { loadChat } from "./chat.js"

const timerStart = performanceTimer.start();
const getParams = new URLSearchParams(window.location.search);
let resizeTimer;

// User objects
let me = null;
let id = null;
const qid = getParams.get('q') ? (getParams.get('q').length > 0 ? getParams.get('q') : null) : null;

// HTML Buffers
let bufferInbox = '';
let bufferSuggestions = '';
let bufferSelectedMembers = '';
let bufferSearchInbox = '';

// Lists of objects
let conversations = [];
let suggestions = [];
let selectedMembers = [];

let relevantGroups = [];
let relevantUsers = [];

let groupPresence = new Map();

// Boolean
let isMobile = window.innerWidth <= 550;

// Elements
const elInboxList = document.querySelector('.inbox .list');
const elSuggestionsList = document.querySelector('.grp-model .list');
const elSelectedMembers = document.querySelector('.grp-members');

const elDmContainer = document.querySelector('.dms-container');
const elChatPreview = document.querySelector('.chat-preview');

// HTML
const htmlChatPageContent = `
    <div class="field">
        <center><h1 class="not-found"></h1></center>
        <div class="inner-field"></div>
    </div>
    <form class="send-form" method="post">
        <input type="text" class="msg-input" placeholder="Type a message..." autocomplete="off" autofocus>
        <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
`;

// Promises
let resolveTargetData;
const targetDataReady = new Promise((resolve) => {
    resolveTargetData = resolve;
});

let resolveChatPage;
export const chatContentLoaded = new Promise((resolve) => {
    resolveChatPage = resolve;
});

// Helper functions
function isViewportMobile(){
    if(isMobile){
        if(qid){
            elDmContainer.classList.add('display-none');
            elChatPreview.classList.remove('display-none');
        }
        else{
            elDmContainer.classList.remove('display-none');
            elChatPreview.classList.add('display-none');
        }
    }
    else{
        console.log('desk')
        elDmContainer.classList.remove('display-none');
    }   elChatPreview.classList.remove('display-none');
}

isViewportMobile();

document.addEventListener('DOMContentLoaded', async function(){
    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }

    // List conversations
    try{
        const getConversations = await HTTP.sendGet('/src/api/conversations/');
    
        if(getConversations.response && Array.isArray(getConversations.response)){

            const srotedConversations = getConversations.response.sort((a, b) => {
                return new Date(b.lastMsg.time) - new Date(a.lastMsg.time)
            });
            
            const conversationIds = srotedConversations.map(c => c.id);

            conversations = await HTTP.fetchUsersBatch(conversationIds);

            conversations = srotedConversations.map(conversation => {
                const target = conversations.find(u => u && u.id == conversation.id);
                let result;

                const match = srotedConversations.find(c => c.id == conversation.id);

                if(target){
                    result = {
                        ...target,
                        lastMsg: match ? match.lastMsg : null
                    }
                }
                else{
                    result = {
                        isGroup: false,
                        id: conversation.id,
                        username: null,
                        lastMsg: match ? match.lastMsg : null
                    }
                }

                return result;
            });

            for(const conversation of conversations){
                let username;
                let img;
                let bio = conversation.bio ? conversation.bio : "No Bio."
                let isOnline = eval(conversation.isOnline) ? 'visible' : 'hidden';

                let bufferPfps;
    
                let lastMsg;
                let badgeVsibility;
                let lastMsgColor;
                let lastMsgWeight;
                if(!conversation.username && !conversation.isGroup){
                    username = 'User not found.';
                    img = 'pfp.jpg';
                    bufferPfps = `
                        <img class='pfp-img' src='/src/assets/pfps/${img}'>
                    `;
                    isOnline = 'hidden';
                }
                else if(!conversation.isGroup){
                    username = conversation.username;
                    img = conversation.img;
                    bufferPfps = `
                        <img class='pfp-img' src='/src/assets/pfps/${img}'>
                        <div class='online-status ${isOnline}'></div>
                    `;

                    relevantUsers.push(conversation);
                }
                else{
                    const group = conversation;
                    relevantGroups.push(group.id);
                    const membersData = await HTTP.fetchUsersBatch(group.members);
                    const otherUsers = membersData.filter(user => {
                        return user.username != me.username;
                    });
                    otherUsers.forEach(user => {
                        if(!Fetch.selectUserById(user.id, relevantUsers)){
                            user = { isGroup: false, ...user };
                            relevantUsers.push(user);
                        }
                    });

                    if(!group.name){
                        username = otherUsers.map(m => {
                            return m.username;
                        }).join(', ');
                    }

                    if(!groupPresence.has(group.id)){
                        const onlineMembers = otherUsers.filter(u => u.online).map(u => u.id);
                        if(onlineMembers.length > 0){
                            groupPresence.set(group.id, onlineMembers);
                        }
                    }

                    if(!group.img){
                        const memberPfp1 = otherUsers[0].img;
                        const memberPfp2 = otherUsers[1].img;
                        bufferPfps = `
                            <img class='sub-pfp' src='/src/assets/pfps/${memberPfp1}'>
                            <img class='sub-pfp' src='/src/assets/pfps/${memberPfp2}'>
                            <div class='online-status' style='visibility: ${groupPresence.has(group.id) ? 'visible' : 'hidden'}'></div>
                        `;
                    }
                }
    
                if(conversation.lastMsg.sid != id){

                    lastMsg = !conversation.isGroup ? 
                    conversation.lastMsg.msg : 
                    `${Fetch.selectUserById(conversation.lastMsg.sid, relevantUsers).username}: ${conversation.lastMsg.msg}`;
    
                    if(!eval(conversation.lastMsg.isRead)){
                        badgeVsibility = 'visible';
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
    
                bufferInbox += `
                    <div class='user-card u-${conversation.id}' data-last-msg='${lastMsg}' data-user-id='${conversation.id}'>
                        <div class='pfp'>
                            ${bufferPfps}
                        </div>
                        <div class='label-wrapper'>
                            <div class='username-label'>${username}</div>
                            <span class='sub-label' style='color:${lastMsgColor}; font-weight: ${lastMsgWeight}'>${lastMsg}</span>
                        </div>
                        <div class='btn-wrapper-inbox'>
                            <div class='unread-msg ${badgeVsibility}'></div>
                            <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
                        </div>
                    </div>
                `;
                
                if(conversation.username != null){
                    suggestions.push({
                        'id': conversation.id,
                        'username': conversation.username,
                        'img': conversation.img,
                        'bio': conversation.bio
                    });
                    bufferSuggestions += `
                        <div class='user-card u-${conversation.id}' user-id="${conversation.id}">
                            <div class='select-indicator'>
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <div class='pfp'>
                                <img class='pfp-img' src='/src/assets/pfps/${conversation.img}'>
                            </div>
                            <div class='label-wrapper'>
                                <div class='username-label'>${username}</div>
                                <span class='sub-label'>${bio}</span>
                            </div>
                        </div>
                    `;
                }
            }
        }
        else{
            bufferInbox = "<center>Nothing here.</center>";
        }
    
        document.querySelector('.inbox .list').innerHTML = bufferInbox;
        document.querySelector('.grp-model .list').innerHTML = bufferSuggestions;

        resolveTargetData();
    }
    catch(error){
        console.error(error);
    }

    // Preview chat
    try{
        if(qid){
            const target = await HTTP.fetchUsersBatch(qid);
            elChatPreview.innerHTML = '';
            elChatPreview.classList.remove('flex-center');
            if(target){
                document.querySelector(`.u-${qid}`).classList.add('highlight');
                elChatPreview.insertAdjacentHTML('beforeend', htmlChatPageContent);
            }

            resolveChatPage();
            loadChat(target.isGroup);
        }
        else{
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
    catch(error){
        console.error(error);
    }

    performanceTimer.stop(timerStart, 'Inbox fully loaded');
    await targetDataReady;
});

window.addEventListener('resize', () => {
    isMobile = window.innerWidth <= 550;
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        isViewportMobile();
    }, 250);
});

document.addEventListener('click', (e) => {
    const elUserCard = e.target.closest('.dms-container .user-card');
    const elGroupUserCard = e.target.closest('.grp-model .list .user-card');
    const elSelectedMemberTag = e.target.closest('.grp-model .member-tag');
    let selectedUserId = null;
    let selectedUser = null;
    
    switch(true){
        case Boolean(elUserCard):;
            const userId = elUserCard.getAttribute('data-user-id');
            const url = new URL(window.location.href);
            url.searchParams.set("q", userId);
            window.location.replace(url);

            elChatPreview.innerHTML = `
            `;
        break;
        case Boolean(elGroupUserCard):
            selectedUserId = elGroupUserCard.getAttribute('user-id');
            selectedUser = Fetch.selectUserById(selectedUserId, conversations);

            if(selectedMembers.length < 4){
                document.querySelector(`.grp-model .list .u-${selectedUserId}`).remove();
        
                suggestions = suggestions.filter(user => {
                    return user.id != selectedUserId;
                });

                selectedMembers.push(selectedUser);
            }
        break;
        case Boolean(elSelectedMemberTag):
            selectedUserId = elSelectedMemberTag.getAttribute('user-id');
            selectedUser = Fetch.selectUserById(selectedUserId, conversations);

            document.querySelector(`.grp-model .member-tag.u-${selectedUserId}`).remove();
            
            selectedMembers = selectedMembers.filter(user => {
                return user.id != selectedUserId;
            });
                
            suggestions.unshift(selectedUser);
        break;
    }

    if(elGroupUserCard || elSelectedMemberTag){

        // Update selected members and remaining suggestions
        bufferSelectedMembers = "";
        selectedMembers.forEach(tag => {
            bufferSelectedMembers += `
                <div class="member-tag u-${tag.id}" user-id="${tag.id}">
                    <img src="/src/assets/pfps/${tag.img}">
                    <span>${tag.username}</span>
                    <i class="fa-solid fa-x"></i>
                </div>
            `;
        });
        elSelectedMembers.innerHTML = bufferSelectedMembers;
    
        bufferSuggestions = "";
        suggestions.forEach(u => {
            const bio = u.bio ? u.bio : "No Bio."
            bufferSuggestions += `
                <div class='user-card u-${u.id}' user-id="${u.id}">
                    <div class='select-indicator'>
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div class='pfp'>
                        <img class='pfp-img' src='/src/assets/pfps/${u.img}'>
                    </div>
                    <div class='label-wrapper'>
                        <div class='username-label'>${u.username}</div>
                        <span class='sub-label'>${bio}</span>
                    </div>
                </div>
            `;
        });
        document.querySelector('.grp-model .list').innerHTML = bufferSuggestions;
    }
});

document.querySelector('.inbox .search').addEventListener('input', async (e) => {
    const query = e.target.value.trim();

    if(query == ''){
        elInboxList.innerHTML = bufferInbox;
        return;
    }

    const searchInbox = await Fetch.searchObjectFromList(query, conversations, 'username');

    if(!searchInbox){
        elInboxList.innerHTML = '<center>No conversations found.</center>';
        return;
    }

    try{
        const sortedSearchInbox = searchInbox.sort((a, b) => {
            return new Date(b.lastMsg.time) - new Date(a.lastMsg.time)
        });

        sortedSearchInbox.forEach(conversation => {
            const isOnline = eval(conversation.isOnline) ? 'visible' : 'hidden';
            let username;
    
            let lastMsg;
            let badgeVsibility;
            let lastMsgColor;
            let lastMsgWeight;
            
            if(conversation.username != null){
                username = conversation.username;
            }
            else{
                username = "User not found"
            }
    
            if(conversation.lastMsg.sid != id){
                lastMsg = conversation.lastMsg.msg;
    
                if(!eval(conversation.lastMsg.isRead)){
                    badgeVsibility = 'visible';
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
    
            bufferSearchInbox += `
                <div class='user-card u-${conversation.id}' data-last-msg='${lastMsg}'>
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

        elInboxList.innerHTML = bufferSearchInbox;
    }
    catch(error){
        console.log(error);
    }
});

document.querySelector('.grp-model .search').addEventListener('input', async (e) => {
    const query = e.target.value.trim();

    if(query == ''){
        elSuggestionsList.innerHTML = bufferSuggestions;
        return;
    }

    const searchSuggestions = await Fetch.searchObjectFromList(query, suggestions, 'username');

    if(!searchSuggestions){
        elSuggestionsList.innerHTML = '<center>No conversations found.</center>';
        return;
    }

    try{
        let bufferSearchSuggestions = '';

        searchSuggestions.forEach(conversation => {
            const bio = conversation.bio ? conversation.bio : "No Bio."

            bufferSearchSuggestions += `
                <div class='user-card u-${conversation.id}' user-id="${conversation.id}">
                    <div class='select-indicator'>
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div class='pfp'>
                        <img class='pfp-img' src='/src/assets/pfps/${conversation.img}'>
                    </div>
                    <div class='label-wrapper'>
                        <div class='username-label'>${conversation.username}</div>
                        <span class='sub-label'>${bio}</span>
                    </div>
                </div>
            `;
        });
        elSuggestionsList.innerHTML = bufferSearchSuggestions;
    }
    catch(error){
        console.log(error);
    }
});

document.querySelector('.create-grp-btn').addEventListener('click', async function(){
    if(selectedMembers.length == 0){
        UI.alert(false, "Please select at least one member.");
        return;
    }

    try{
        const createGrp = await HTTP.sendPost('/src/api/conversations/create-grp.php', {
            group_name: null,
            members: JSON.stringify(selectedMembers)
        });
        if(createGrp.status == 'ok'){
            window.location.replace(`/chat/?q=${createGrp.response.groupId}`);
        }
    }
    catch(error){
        console.error(error);
    }
});

// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {
    const statusDot = document.querySelector(`.u-${data.userId} .online-status`);
    
    if(statusDot){
        statusDot.style.visibility = data.isOnline ? "visible" : "hidden";
    }

    groupPresence.forEach((onlineMembers, groupId) => {
        const groupConversation = conversations.find(c => c.isGroup && c.id == groupId);

        if(groupConversation && groupConversation.members.includes(data.userId)){
            if(data.isOnline){
                if(!onlineMembers.includes(data.userId)){
                    onlineMembers.push(data.userId);
                }
            }
            else{
                const index = onlineMembers.indexOf(data.userId);
                if(index > -1){
                    onlineMembers.splice(index, 1);
                }
            }

            if(onlineMembers.length > 0){
                groupPresence.set(groupId, onlineMembers);
            }
            else{
                groupPresence.set(groupId, []);
            }

            const groupCard = document.querySelector(`.u-${groupId}`);
            if(groupCard){
                const groupStatusDot = groupCard.querySelector('.online-status');
                if(groupStatusDot){
                    groupStatusDot.style.visibility = onlineMembers.length > 0 ? 'visible' : 'hidden';
                }
            }
        }
    });
});

// When a new message is received class as yours or the other user's && as read or unread
socket.on('new_message', (data) => {

    const targetId = !data.isGroup ? (data.sid == id? data.rid : data.sid) : data.groupId;
    const listContainer = document.querySelector('.list');
    const elConversationCard = document.querySelector(`.u-${targetId}`);
    
    if(elConversationCard){
        listContainer.prepend(elConversationCard);
    
        const elSubLabel = document.querySelector(`.u-${targetId} .sub-label`);
        const isUnreadMsg = document.querySelector(`.u-${targetId} .unread-msg`);
    
        const lastMsgText = data.sid == id ? `You: ${data.msg}` : (!data.isGroup ? data.msg : `${Fetch.selectUserById(data.sid, relevantUsers).username}: ${data.msg}`);
        elConversationCard.setAttribute('data-last-msg', lastMsgText);
        
        if(elSubLabel){
            elSubLabel.innerHTML = lastMsgText;
            if(data.sid == id){
                isUnreadMsg.style.visibility = 'hidden';
                elSubLabel.style.color = '#a8a8a8';
                elSubLabel.style.fontWeight = '400';
            }
            else{
                isUnreadMsg.style.visibility = 'visible';
                elSubLabel.style.color = '#d5d6c3';
                elSubLabel.style.fontWeight = 'bolder';
            }
        }
    }
});

// Class the last message as read as needed
socket.on('message_marked_read', async (data) => {
    const elSubLabel = document.querySelector(`.u-${data.readTo} .sub-label`);
    const isUnreadMsg = document.querySelector(`.u-${data.readTo} .unread-msg`);

    if(data.readBy == id){
        isUnreadMsg.style.visibility = 'hidden';
        elSubLabel.style.color = '#a8a8a8';
        elSubLabel.style.fontWeight = '400';
    }
});

// When the other user is typing
let typers = new Map();
socket.on('typing_back', async (data) => {
    
    await targetDataReady;
    let targetId;
    let isRelevant;
    
    if(!data.isGroup){
        targetId = (data.typedBy == id)? data.typedTo : data.typedBy;
        isRelevant = data.typedBy == targetId && data.typedTo == id;
    }
    else{
        targetId = data.typedTo;
        isRelevant = relevantGroups.includes(data.typedTo);
    }

    const elConversationCard = document.querySelector(`.u-${targetId}`);

    if(isRelevant && elConversationCard){
        const elSubLabel = elConversationCard.querySelector('.sub-label');

        if(data.isGroup && !typers.has(data.typedTo)){
            typers.set(data.typedTo, []);
        }

        if(data.isTyping){

            if(data.isGroup && !typers.get(data.typedTo).includes(data.typedBy)){
                typers.get(data.typedTo).push(data.typedBy);
            }
            elSubLabel.innerHTML = 
            !data.isGroup ? 
            'Typing...' : 
            (typers.get(data.typedTo).length > 1 ? 
                `${typers.get(data.typedTo).length} are typing...` : 
                `${Fetch.selectUserById(typers.get(data.typedTo), conversations).username} is typing...`
            );
        }
        else{

            if(data.isGroup && typers.has(data.typedTo)){
                const groupTypers = typers.get(data.typedTo);
                const index = groupTypers.indexOf(data.typedBy);
                if(index != -1) groupTypers.splice(index, 1);
                if(groupTypers.length == 0) typers.delete(data.typedBy);
            }
            
            elSubLabel.innerHTML = 
            !data.isGroup ? 
            (elConversationCard.getAttribute('data-last-msg') || '') : 
            typers.get(data.typedTo).length < 1 ? 
            (elConversationCard.getAttribute('data-last-msg') || '') : 
            `${Fetch.selectUserById(typers.get(data.typedTo), conversations).username} is typing...`;
        }
    }
});