// Initialization
import { UI, HTTP, Fetch, Form, Check, performanceTimer, HTML } from "../helpers.js";
import { socket, userDataReady} from "./main.js";
import { loadChat, chatLoaded } from "./chat.js"

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
let canCreateGroup = false;

// Elements
const elListInbox = document.querySelector('.inbox .list');
const elListSuggestions = document.querySelector('.group-modal .list');
const elSelectedMembers = document.querySelector('.group-modal .selected-members');

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

let resolveChatReadyToLoad;
export const chatReadyToLoad = new Promise((resolve) => {
    resolveChatReadyToLoad = resolve;
});

// Helper functions
function isViewportMobile(){
    if(isMobile){
        if(qid){
            elDmContainer.classList.add('display-none');
            elChatPreview.classList.remove('display-none');
        }
        else{
            document.querySelector('.main.inbox').classList.remove('grid-template-profile');
            elChatPreview.classList.add('display-none');
            elDmContainer.classList.remove('display-none');
        }
    }
    else{
        elDmContainer.classList.remove('display-none');
        elChatPreview.classList.remove('display-none');
    }
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
                const timeA = a.lastMsg?.time ? new Date(a.lastMsg.time).getTime() : Date.now();
                const timeB = b.lastMsg?.time ? new Date(b.lastMsg.time).getTime() : Date.now();
                return timeB - timeA;
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
                let isOnline;

                if(!conversation.username && !conversation.isGroup){
                    username = 'User not found.';
                    img = 'pfp.jpg';
                    isOnline = false;
                }
                else if(!conversation.isGroup){
                    username = conversation.username;
                    img = conversation.img;
                    isOnline = conversation.isOnline;
                    relevantUsers.push(conversation);
                }
                else{
                    const group = conversation;
                    relevantGroups.push(group.id);
                    const membersData = await HTTP.fetchUsersBatch(group.members);
                    
                    const otherUsers = membersData.filter(u => {
                        return u.username != me.username;
                    });
                    otherUsers.forEach(u => {
                        if(!Fetch.selectUserById(u.id, relevantUsers)){
                            u = { isGroup: false, ...u };
                            relevantUsers.push(u);
                        }
                    });

                    if(!group.name){
                        username = otherUsers.map(m => {
                            return m.username;
                        }).join(', ');
                    }

                    if(!groupPresence.has(group.id)){
                        const onlineMembers = otherUsers.filter(u => u.isOnline).map(u => u.id);
                        if(onlineMembers.length > 0){
                            groupPresence.set(group.id, onlineMembers);
                        }
                    }
                    isOnline = groupPresence.has(group.id);

                    if(!group.img){
                        img = [otherUsers[0].img, otherUsers[1].img];
                    }
                }

                let lastMsg;
                if(conversation.lastMsg){
                    lastMsg = conversation.lastMsg.sid == id ?
                    `You: ${conversation.lastMsg.msg}` :
                    (!conversation.isGroup ?
                    conversation.lastMsg.msg :
                    `${Fetch.selectUserById(conversation.lastMsg.sid, relevantUsers).username}: ${conversation.lastMsg.msg}`);
                }
                else{
                    lastMsg = 'Start chatting now...';
                }
    
                bufferInbox += HTML.userCard(
                    'chat-card',
                    conversation.id,
                    username,
                    img,
                    isOnline,
                    {
                        isLastMsg: true,
                        text: lastMsg,
                        isRead: (conversation.lastMsg?.sid == id) || (conversation.lastMsg?.isRead)
                    },
                    {hasBtn: true, btnType: 'more'}
                );
                
                if(conversation.username && !conversation.isGroup){
                    suggestions.push({
                        'id': conversation.id,
                        'username': conversation.username,
                        'img': conversation.img,
                        'bio': conversation.bio
                    });

                    bufferSuggestions += HTML.userCard(
                        '',
                        conversation.id,
                        username,
                        img,
                        false,
                        {text: 'Add to group chat'},
                        {hasPlus: true}
                    );
                }
            }
        }
        else{
            bufferInbox = "<center>Nothing here.</center>";
        }
    
        elListInbox.innerHTML = bufferInbox;
        elListSuggestions.innerHTML = bufferSuggestions;

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
                document.querySelector(`.u-${qid}`)?.classList.add('highlight');
                elChatPreview.insertAdjacentHTML('beforeend', htmlChatPageContent);
            }

            resolveChatReadyToLoad();
            loadChat(target.isGroup);
            try{
                await chatLoaded;
                const elField = document.querySelector('.field');
                elField.scrollTop = elField.scrollHeight;
            }
            catch(error){
                console.error("Chat hasn't loaded successfully.");
            }
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
    await targetDataReady;
});

window.addEventListener('resize', () => {
    isMobile = window.innerWidth <= 550;
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        isViewportMobile();
    }, 50);
});

document.addEventListener('click', (e) => {
    const elUserCard = e.target.closest('.dms-container .user-card');
    const elGroupUserCard = e.target.closest('.group-modal .list .user-card');
    const elSelectedMemberTag = e.target.closest('.group-modal .member-tag');
    let selectedUserId = null;
    let selectedUser = null;
    
    switch(true){
        case Boolean(elUserCard):
            const userId = elUserCard.getAttribute('user-id');
            const url = new URL(window.location.href);
            url.searchParams.set("q", userId);
            window.location.replace(url);
        break;
        case Boolean(elGroupUserCard):
            selectedUserId = elGroupUserCard.getAttribute('user-id');
            selectedUser = Fetch.selectUserById(selectedUserId, conversations);

            if(selectedMembers.length < 4){
                document.querySelector(`.group-modal .list .u-${selectedUserId}`).remove();
        
                suggestions = suggestions.filter(user => {
                    return user.id != selectedUserId;
                });

                selectedMembers.push(selectedUser);
            }
        break;
        case Boolean(elSelectedMemberTag):
            selectedUserId = elSelectedMemberTag.getAttribute('user-id');
            selectedUser = Fetch.selectUserById(selectedUserId, conversations);

            document.querySelector(`.group-modal .member-tag.u-${selectedUserId}`).remove();
            
            selectedMembers = selectedMembers.filter(user => {
                return user.id != selectedUserId;
            });

            suggestions.unshift(selectedUser);
        break;
    }

    // Update selected members and remaining suggestions
    if(elGroupUserCard || elSelectedMemberTag){
        if(selectedMembers.length > 1){
            canCreateGroup = true;
            document.querySelector('.group-modal .submit').classList.remove('btn-disabled');
        }
        else{
            canCreateGroup = false;
            document.querySelector('.group-modal .submit').classList.add('btn-disabled');
        }

        bufferSelectedMembers = "";
        selectedMembers.forEach(t => {
            bufferSelectedMembers += `
                <div class="member-tag u-${t.id}" user-id="${t.id}">
                    <img src="/src/assets/pfps/${t.img}">
                    <span>${t.username}</span>
                    <i class="fa-solid fa-x"></i>
                </div>
            `;
        });
        elSelectedMembers.innerHTML = bufferSelectedMembers;

        bufferSuggestions = "";
        suggestions.forEach(u => {
            bufferSuggestions += `
                <div class='user-card u-${u.id}' user-id="${u.id}">
                    <div class='select-indicator'>
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    <div class='wrap-pfp'>
                        <img src='/src/assets/pfps/${u.img}'>
                    </div>
                    <div class='wrap-label'>
                        <div class='username-label'>${u.username}</div>
                        <span class='sub-label'>Add to group chat</span>
                    </div>
                </div>
            `;
        });
        elListSuggestions.innerHTML = bufferSuggestions;
    }

    if(suggestions.length == 0){
        elListSuggestions.innerHTML = '<center>No conversations remaining.</center>';
    }
});

document.querySelector('.inbox .search').addEventListener('input', async (e) => {
    bufferSearchInbox = '';
    const query = e.target.value.trim();
    if(query == ''){
        elListInbox.innerHTML = bufferInbox;
        return;
    }

    const searchInbox = await Fetch.searchObjectFromList(query, conversations, 'username');
    if(!searchInbox){
        elListInbox.innerHTML = '<center>No conversations found.</center>';
        return;
    }

    try{
        const sortedSearchInbox = searchInbox.sort((a, b) => {
            const timeA = a.lastMsg?.time ? new Date(a.lastMsg.time).getTime() : Date.now();
            const timeB = b.lastMsg?.time ? new Date(b.lastMsg.time).getTime() : Date.now();
            return timeB - timeA;
        });

        sortedSearchInbox.forEach(conversation => {
            bufferSearchInbox += HTML.userCard(
                '',
                conversation.id,
                conversation.username,
                conversation.img,
                conversation.isOnline,
                {text: 'Open chat'},
                {hasBtn: true, btnType: 'more'}
            );
        });

        elListInbox.innerHTML = bufferSearchInbox;
    }
    catch(error){
        console.error(error);
    }
});

document.querySelector('.group-modal .search').addEventListener('input', async (e) => {
    const query = e.target.value.trim();
    if(query == ''){
        if(suggestions.length == 0){
            elListSuggestions.innerHTML = '<center>No conversations remaining.</center>';
            return;
        }

        elListSuggestions.innerHTML = bufferSuggestions;
        return;
    }

    const searchSuggestions = await Fetch.searchObjectFromList(query, suggestions, 'username');

    if(!searchSuggestions){
        elListSuggestions.innerHTML = '<center>No conversations found.</center>';
        return;
    }

    try{
        let bufferSearchSuggestions = '';

        searchSuggestions.forEach(conversation => {
            bufferSearchSuggestions += HTML.userCard(
                '',
                conversation.id,
                conversation.username,
                conversation.img,
                false,
                {text: 'Add to group chat'},
                {hasPlus: true}
            );
        });
        elListSuggestions.innerHTML = bufferSearchSuggestions;
    }
    catch(error){
        console.error(error);
    }
});

document.querySelector('.group-modal .submit').addEventListener('click', async function(){
    if(canCreateGroup){
        if(selectedMembers.length == 0){
            UI.alert(false, "Please select at least one member.");
            return;
        }
    
        try{
            const createGrp = await HTTP.sendPost('/src/api/conversations/create-grp.php', {
                group_name: null,
                members: selectedMembers.map(m => m.id)
            });
            if(createGrp.status == 'ok'){
                window.location.replace(`/inbox/?q=${createGrp.response.groupId}`);
            }
        }
        catch(error){
            console.error(error);
        }
    }
});

// Users presense
socket.on('presence', (data) => {
    const pfp = document.querySelector(`.u-${data.userId} .wrap-pfp`);
    
    if(data.isOnline) pfp?.classList.replace('offline', 'online');
    else if(!data.isOnline) pfp?.classList.replace('online', 'offline');

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

            const groupPfp = document.querySelector(`.u-${groupId} .wrap-pfp`);
            if(onlineMembers.length > 0){
                groupPfp.classList.replace('offline', 'online');
            }
            else if(onlineMembers.length == 0){
                groupPfp.classList.replace('online', 'offline');
            }
        }
    });
});

// New message
socket.on('message', async (data) => {
    const targetId = !data.isGroup ? (data.sid == id ? data.rid : data.sid) : data.groupId;
    const listContainer = document.querySelector('.dms-container .list');
    const elConversationCard = listContainer.querySelector(`.u-${targetId}`);
    
    if(elConversationCard){
        listContainer.prepend(elConversationCard);
        const elSubLabel = elConversationCard.querySelector('.sub-label');

        const lastMsg = data.sid == id ?
        `You: ${data.msg}` :
        (!data.isGroup ?
            data.msg :
            `${Fetch.selectUserById(data.sid, relevantUsers).username}: ${data.msg}`
        );

        elSubLabel.innerHTML = lastMsg;
        elConversationCard.setAttribute('data-last-msg', lastMsg);

        if(data.sid == id) elConversationCard.classList.replace('unseen', 'seen');
        else elConversationCard.classList.replace('seen', 'unseen');
    }
    else{
        const target = await HTTP.fetchUsersBatch(targetId);

        let username;
        let img;
        let isOnline;
        let bufferPfps;

        if(!data.isGroup){
            username = target.username;
            img = target.img;
            isOnline = target.isOnline ? 'online' : 'offline';

            bufferPfps = `
                <img src='/src/assets/pfps/${img}'>
                <div class='online-status'></div>
            `;

            relevantUsers.push(target);
        }
        else{
            const group = target;
            relevantGroups.push(group.id);
            const membersData = await HTTP.fetchUsersBatch(group.members);
            const otherUsers = membersData.filter(user => {
                return user.id != me.id;
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
            isOnline = groupPresence.has(group.id) ? 'online' : 'offline';

            if(!group.img){
                const memberPfp1 = otherUsers[0].img;
                const memberPfp2 = otherUsers[1].img;
                bufferPfps = `
                    <img class='sub-pfp' src='/src/assets/pfps/${memberPfp1}'>
                    <img class='sub-pfp' src='/src/assets/pfps/${memberPfp2}'>
                    <div class='online-status'></div>
                `;
            }
        }
        const lastMsg = data.sid == id ? `You: ${data.msg}` : (!data.isGroup ? data.msg : `${Fetch.selectUserById(data.sid, relevantUsers).username}: ${data.msg}`);
        
        listContainer.insertAdjacentHTML('afterbegin', `
            <div class='user-card chat-card ${data.sid == id ? 'seen' : 'unseen'} u-${targetId}' data-last-msg='${lastMsg}' data-user-id='${targetId}'>
                <div class='wrap-pfp ${isOnline}'>
                    ${bufferPfps}
                </div>
                <div class='wrap-label'>
                    <div class='username-label'>${username}</div>
                    <span class='sub-label'>${lastMsg}</span>
                </div>
                <div class='wrap-btn-inbox'>
                    <div class='unread-msg'></div>
                    <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
                </div>
            </div>
        `);
    }
});

// Seen message
socket.on('seen:pong', async (data) => {
    const elUserCard = document.querySelector(`.u-${data.readTo}`);
    if(data.readBy == id && elUserCard) elUserCard.classList.replace('unseen', 'seen');
});

// Typing
let typers = new Map();
socket.on('typing:pong', async (data) => {
    const { by, to, isTyping, isGroup, groupId } = data;
    
    await targetDataReady;
    let targetId;
    let isRelevant;
    
    if(!isGroup){
        targetId = (by == id)? to : by;
        isRelevant = by == targetId && to == id;
    }
    else{
        targetId = to;
        isRelevant = relevantGroups.includes(to);
    }

    const elConversationCard = document.querySelector(`.u-${targetId}`);

    if(isRelevant && elConversationCard){
        const elSubLabel = elConversationCard.querySelector('.sub-label');

        if(isGroup && !typers.has(to)){
            typers.set(to, []);
        }

        if(isTyping){
            if(isGroup && !typers.get(to).includes(by)){
                typers.get(to).push(by);
            }
            
            if(!isGroup){
                elSubLabel.innerHTML = 'Typing...';
            }
            else{
                elSubLabel.innerHTML = typers.get(to).length > 1 ? 
                `${typers.get(to).length} are typing...` : 
                `${Fetch.selectUserById(typers.get(to), relevantUsers).username} is typing...`;
            }
        }
        else{
            if(isGroup && typers.has(to)){
                const groupTypers = typers.get(to);
                const index = groupTypers.indexOf(by);
                if(index != -1) groupTypers.splice(index, 1);
                if(groupTypers.length == 0) typers.delete(by);
            }
            
            if(!isGroup){
                elSubLabel.innerHTML = elConversationCard.getAttribute('data-last-msg') || '';
            }
            else{
                elSubLabel.innerHTML = typers.get(to).length < 1 ?
                elConversationCard.getAttribute('data-last-msg') || '' :
                `${Fetch.selectUserById(typers.get(to), conversations).username} is typing...`;
            }
        }
    }
});