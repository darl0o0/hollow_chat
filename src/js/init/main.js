import { UI, HTTP, Fetch, Form, Check, HTML } from "../helpers.js";
export const socket = io('https://payments-essence-notices-directory.trycloudflare.com');
export let me = null;
const getParams = new URLSearchParams(window.location.search);

// IDs
let id = null;
const qid = getParams.get('q') ? (getParams.get('q').length > 0 ? getParams.get('q') : null) : null;

// Counters
let countRequests = 0;
let countUnseen = 0;
let countNotifications = 0;

// Pormises
let resolveUserData;
let rejectUserData;
export const userDataReady = new Promise((resolve, reject) => {
    resolveUserData = resolve;
    rejectUserData = reject;
});

// Helper functions
function updateBadges(){
    countNotifications = countUnseen + countRequests;
    document.title = countNotifications > 0 ? `(${countNotifications}) Hollow Chat` : "Hollow Chat";

    const inboxBadge = document.querySelector('.inbox-action .num-of');
    const NotificationsBadge = document.querySelector('.notifications-action .num-of');
    const elNotificationsCountLabel = document.querySelector('.modal-requests .not-count');
    
    inboxBadge.textContent = countUnseen;
    NotificationsBadge.textContent = countRequests;

    const notificationsCountLabel = (countRequests > 0) ? `(${countRequests})` : '';
    elNotificationsCountLabel.textContent = notificationsCountLabel;

    document.querySelectorAll('.num-of').forEach(numOf => {
        const val = Number(numOf.textContent);
        if(val == 0){
            numOf.classList.add('display-none');
        }
        else{
            numOf.classList.remove('display-none');
            if(val > 99){
                numOf.textContent = "+99";
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', async function(){

    // Fetch user's data
    try{
        const getMe = await HTTP.sendGet('/src/api/me/');
        me = getMe.response;
        id = me.id;
        resolveUserData(me);

    }
    catch(error){
        console.error(error);

        rejectUserData(error);
    }

    // My profile button
    document.querySelectorAll('.profile-action').forEach(profileAction => {
        profileAction.querySelector('img').src = `/src/assets/pfps/${me.img}`;
        profileAction.href = `/profile/?q=${me.id}`
    });
    document.querySelector('.profile-action .username-label').textContent = me.username;

    // Metadata
    countUnseen = Number(document.querySelector('meta[name="num-of-unread"]').content);

    const getFriends = await HTTP.sendGet('/src/api/friends/');
    const requests = getFriends.response.requests;
    countRequests = requests.incoming?.length;
    
    // Universal notifications list
    UI.globalElements();
    updateBadges();
    
    if(Array.isArray(requests.incoming)){
        let bufferRequests = "";
        requests.incoming.forEach(u => {
            bufferRequests += HTML.userCard(
                'link',
                u.id,
                u.username,
                u.img,
                false,
                {text: 'View profile'},
                {hasBtn: true, btnType: 'dilemma'}
            );
        });
        document.querySelector('.modal-requests .list').innerHTML = bufferRequests;
    }
    else{
        document.querySelector('.modal-requests .list').innerHTML = "<center>Nothing here.</center>";
    }
});

document.addEventListener('submit', async (e) => {
    const elFormAccept = e.target.closest('.accept-form');
    const elFormDecline = e.target.closest('.decline-form');

    if(elFormAccept || elFormDecline){
        e.preventDefault();
        const elActiveForm = elFormAccept || elFormDecline;
        const targetId = elActiveForm.dataset.id;
        const action = elFormAccept ? 2 : 1;

        await HTTP.sendPost('/src/api/relationship/', {user2: targetId, action});

        const elCard = document.querySelector(`.modal-requests .u-${targetId}`);
        elCard.remove();

        countRequests--;
        updateBadges();

        const elListRequests = document.querySelector('.modal-requests .list');
        if(elListRequests.children.length == 0){
            elListRequests.innerHTML = "<center>Nothing here.</center>";
        }
    }
});

// New message
socket.on('message', (data) => {
    const isChatOpen = Boolean(qid) && (qid == data.sid);

    if(data.sid != id && !isChatOpen){
        countUnseen++;
        updateBadges();
    }
});

// Seen message
socket.on('seen:pong', (data) => {
    if(data.by == id){
        countUnseen -= data.count;
        updateBadges();
    }
});