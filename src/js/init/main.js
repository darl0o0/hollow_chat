import { UI, HTTP, Fetch, Form, Check, HTML, defaultProfilePic } from "../helpers.js";
export const socket = io('http://localhost:3000');
// export const socket = io('https://send-address-vocal-sensors.trycloudflare.com');
export let me = null;
const getParams = new URLSearchParams(window.location.search);

// IDs
let id = null;
const qid = getParams.get('q') ? (getParams.get('q').length > 0 ? getParams.get('q') : null) : null;

// Elements
const elPanel = document.querySelector('.panel');

// Counters
let countRequests = 0;
let countUnseen = 0;
let countNotifications = 0;

// Timers
let debounceTimer;

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

    const inboxBadge = document.querySelector('.inbox-action .count');
    const NotificationsBadge = document.querySelector('.notifications-action .count');
    const elNotificationsCountLabel = document.querySelector('.modal-requests .not-count');
    
    inboxBadge.textContent = countUnseen;
    NotificationsBadge.textContent = countRequests;

    const notificationsCountLabel = (countRequests > 0) ? `(${countRequests})` : '';
    elNotificationsCountLabel.textContent = notificationsCountLabel;

    document.querySelectorAll('.count').forEach(numOf => {
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

const page = document.querySelector('meta[name="page"]')?.content;
if(page) document.querySelector(`.panel .${page}-action`).classList.add('highlight');

// document.body.setAttribute('theme', 'light');
// const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
// document.body.setAttribute('theme', isDarkMode ? 'dark' : 'light');

document.addEventListener('DOMContentLoaded', async function(){

    // Fetch user's data
    try{
        const getMe = await HTTP.send('/src/api/me/', 'GET');
        me = getMe.response;
        id = me.id;

        if(me.theme === 'default'){
            const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.body.setAttribute('theme', isDarkMode ? 'dark' : 'light');
        }
        else{
            document.body.setAttribute('theme', me.theme);
        }

        resolveUserData(me);

    }
    catch(error){
        console.error(error);

        rejectUserData(error);
    }

    // My profile button
    document.querySelectorAll('.profile-action').forEach(profileAction => {
        profileAction.querySelector('img').src = `/src/assets/pfps/${me.img ?? defaultProfilePic}`;
        profileAction.href = `/profile/?q=${me.id}`
    });
    document.querySelector('.profile-action .username-label').textContent = me.username;

    // Metadata
    countUnseen = Number(document.querySelector('meta[name="count-unread"]').content);

    const getFriends = await HTTP.send('/src/api/friends/', 'GET');
    const requests = getFriends.response.requests;
    countRequests = requests.incoming?.length ?? 0;
    
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

elPanel.querySelector('.search').addEventListener('input', async (e) => {
    const elWrapSearch = elPanel.querySelector('.wrap-search');
    const elSearchDropdown = elWrapSearch.querySelector('.search-dropdown');
    const query = e.target.value.trim();

    try{
        let searchUsers;
        clearTimeout(debounceTimer);

        debounceTimer = setTimeout(async () => {

            if(query == ''){
                if(elSearchDropdown) elSearchDropdown.remove();
                return;
            }
            
            searchUsers = (await HTTP.send('/src/api/users/', 'GET', { q: query })).response;
            let bufferSearchUsers = '';
    
            if(searchUsers){
                searchUsers.forEach(user => {
                    bufferSearchUsers += HTML.userCard(
                        'link',
                        user.id,
                        user.username,
                        user.img,
                        false,
                        {text: user.bio}
                    );
                });
            }
            else{
                bufferSearchUsers = '<center>No users found.</center>';
            }
    
            if(elSearchDropdown){
                elSearchDropdown.querySelector('.list').innerHTML = bufferSearchUsers;
            }
            else{
                elWrapSearch.insertAdjacentHTML('beforeend', `
                    <div class="search-dropdown">
                        <div class="list">
                            ${bufferSearchUsers}
                        </div>
                    </div>
                `);
            }
        }, 300);

    }
    catch(error){
        console.error(error);
    }
});

elPanel.querySelector('.search').addEventListener('blur', () => {
    const elWrapSearch = document.querySelector('.wrap-search');
    const elSearchDropdown = elWrapSearch.querySelector('.search-dropdown');

    setTimeout(() => {
        if(elSearchDropdown) elSearchDropdown.classList.add('display-none');
    }, 200);
});

elPanel.querySelector('.search').addEventListener('click', (e) => {
    const elWrapSearch = elPanel.querySelector('.wrap-search');
    if(elWrapSearch){
        const elSearchDropdown = elWrapSearch.querySelector('.search-dropdown');
        const isSearchClicked = e.target.closest('.search');
    
        if(elSearchDropdown){
            if(!isSearchClicked) elSearchDropdown.classList.add('display-none');
            else elSearchDropdown.classList.remove('display-none');
        }
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

        await HTTP.send('/src/api/relationship/', 'POST', {qid: targetId, action});

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
        if(countUnseen > 0) countUnseen -= data.count;
        updateBadges();
    }
});