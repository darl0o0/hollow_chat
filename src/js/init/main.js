import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";
export const socket = io('https://adjustment-beverage-medium-diameter.trycloudflare.com');
export let me = null;

let resolveUserData;
let rejectUserData;

export const userDataReady = new Promise((resolve, reject) => {
    resolveUserData = resolve;
    rejectUserData = reject;
});

let id = null;
let activeChatUserId = null;

let requestsCount = 0;
let unreadCount = 0;
let notificationsCount = 0;

function updateNotUIState(){
    notificationsCount = Math.max(0, unreadCount) + Math.max(0, requestsCount);
    document.title = notificationsCount > 0 ? `(${notificationsCount}) Hollow Chat` : "Hollow Chat";
    const inboxBadge = document.querySelector('.inbox-action .num-of');

    if(inboxBadge){
        if(unreadCount > 0){
            inboxBadge.style.display = 'flex';
            inboxBadge.textContent = unreadCount > 99 ? '+99' : unreadCount;
        }
        else{
            inboxBadge.style.display = 'none';
            inboxBadge.textContent = '0';
        }
    }
}

document.addEventListener('DOMContentLoaded', async function(){

    // Calling user's own data
    try{
        const meRes = await HTTP.sendGet('/src/api/me/');
        me = meRes.response;
        resolveUserData(me);

    }
    catch(error){
        console.error(error);

        rejectUserData(error);
    }
    id = me.id;

    document.querySelectorAll('.profile-action').forEach((profileAction) => {
        profileAction.querySelector('img').src = `/src/assets/pfps/${me.img}`;
        profileAction.href = `/profile/?q=${me.id}`
    });
    document.querySelector('.profile-action .username-label').textContent = me.username;

    activeChatUserId = document.querySelector('meta[name="qid"]')?.content || null;
    
    requestsCount = parseInt(document.querySelector('meta[name="num-of-requests"]').content, 10) || 0;
    unreadCount = parseInt(document.querySelector('meta[name="num-of-unread"]').content, 10) || 0;

    const res = await HTTP.sendGet('/src/api/friends/');
    const incomingRequestsCount = res?.response?.requests?.incoming?.length;
    
    // Universal notifications list
    UI.globalElements(incomingRequestsCount);
    UI.updateNotCount(requestsCount);
    updateNotUIState();
    
    if(res.response.requests.incoming != null){
        let buffer = "";

        res.response.requests.incoming.forEach(user => {
            const bio = user.bio ? user.bio : "No Bio.";
    
            buffer += `
                <div class='user-card u-${user.id}'>
                    <a href='/profile/?q=${user.id}' class='profile-link'></a>
                    <div class='pfp'>
                        <img class='pfp-img' src='/src/assets/pfps/${user.img}'>
                    </div>
                    <div class='label-wrapper'>
                        <div class='username-label'>${user.username}</div>
                        <span class='sub-label'>${bio}</span>
                    </div>
                    <div class='btn-wrapper-requests'>
                        <form method='post' action='/profile/friend_action.php' class='accept-form' data-id='${user.id}'>
                            <button class='btn-requests'><i class='fa fa-check' aria-hidden='true'></i></button>
                        </form>
                        <form method='post' action='/profile/friend_action.php' class='decline-form' data-id='${user.id}'>
                            <button class='btn-requests'><i class='fa fa-times' aria-hidden='true'></i></button>
                        </form>
                    </div>
                </div>
            `;
        });
        document.querySelector('.requests-model .list').innerHTML = buffer;
    }
    else{
        document.querySelector('.requests-model .list').innerHTML = "<center>Nothing here.</center>";
    }

    document.querySelectorAll('.num-of').forEach(numOf => {
        const val = parseInt(numOf.textContent, 10) || 0;
        if(val == 0){
            numOf.style.display = 'none';
        }
        else if(val > 99){
            numOf.textContent = "+99";
        }
    });
});

document.addEventListener('submit', async (e) => {
    const acceptForm = e.target.closest('.accept-form');
    const declineForm = e.target.closest('.decline-form');

    if(acceptForm || declineForm){
        e.preventDefault();
        const form = acceptForm || declineForm;
        const targetUserId = form.dataset.id;
        const action = acceptForm ? 2 : 1;

        await HTTP.sendPost('/src/api/relationship/', {user2: targetUserId, action});

        const card = document.querySelector(`.requests-model .u-${targetUserId}`);
        card.remove();

        requestsCount = Math.max(0, requestsCount - 1);
        UI.updateNotCount(requestsCount);
        updateNotUIState();

        const listContainer = document.querySelector('.requests-model .list');
        if(listContainer.children.length == 0){
            listContainer.innerHTML = "<center>Nothing here.</center>";
        }
    }
});

socket.on('new_message', (data)=>{
    const isChatOpen = (typeof activeChatUserId !== null) && (activeChatUserId == data.sid);
    
    if(data.sid != id && !isChatOpen){
        unreadCount++;
        updateNotUIState();
    }
});

socket.on('message_marked_read', (data) => {

    if(data.readBy == id){
        const countToDeduct = parseInt(data.count, 10) || 0;
        unreadCount = Math.max(0, unreadCount - countToDeduct);
        updateNotUIState();
    }
});