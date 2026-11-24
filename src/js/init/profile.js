import { UI, HTTP, Fetch, Form, Check, HTML, defaultProfilePic } from "../helpers.js";
import { socket, userDataReady} from "./main.js";

const getParams = new URLSearchParams(window.location.search);

// User objects
let me = null;

// IDs
let id = null;
const qid = getParams.get('q') ? (getParams.get('q').length > 0 ? getParams.get('q') : null) : null;

// Elements
const elPreview = document.querySelector('.preview');

let stat;

// Helper functions
function defineState(relationship){
    switch(relationship){
        case false:
            return {
                friendAction: 0,
                btnActionContent: '<i class="fa fa-user-plus" aria-hidden="true"></i>Add Friend'
            }
            
        case 'outgoing':
            return {
                friendAction: 1,
                btnActionContent: '<i class="fa fa-user-times" aria-hidden="true"></i>Cancel Request'
            }
            
        case 'incoming':
            return {
                friendAction: 2,
                btnActionContent: '<i class="fa fa-check" aria-hidden="true"></i> Accept Request'
            }

        case true:
            return {
                friendAction: 1,
                btnActionContent: '<i class="fa fa-user-times" aria-hidden="true"></i>Remove Friend'
            }
    }
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
        const user = await HTTP.fetchUsersBatch(qid);
        
        if(user){
            const wrapProfile = (forceOnline) => {
                const onlineClass = (forceOnline || user.isOnline) ? 'online' : 'offline';

                const onlineLabel = (forceOnline || user.isOnline) ? 'Online' : 'Offline';
                
                return `<div class="wrap-profile">
                    <div class="profile-card">
                        <img src='/src/assets/pfps/${HTML.escapeHTML(user.img ?? defaultProfilePic)}'>
                        <span class="username-label">${HTML.escapeHTML(user.username)}</span>
                        <div class="online-status">
                            <div class="is-online ${onlineClass}"></div>
                            <span class="is-online-label">${onlineLabel}</span>
                        </div>
                    </div>
                    <div class="profile-details">
                        <div class="details-section date">
                            <span class="title">Member since</span>
                            <span class="text">${HTML.escapeHTML(user.date)}</span>
                        </div>
                        <div class="details-section bio">
                            <span class="title">Bio</span>
                            <span class="text">${HTML.escapeHTML(user.bio)}</span>
                        </div>
                        <div class="details-section privacy">
                            <span class="title">Account privacy</span>
                            <span class="text">${user.isPrivate ? 'Private' : 'Public'}</span>
                        </div>
                    </div>
                </div>`
            };

            let profileActions;
    
            if(user.id == id){
                document.querySelectorAll('.profile-action').forEach(el => {
                    el.classList.add('highlight');
                });
                elPreview.insertAdjacentHTML('afterbegin', wrapProfile(true));
    
                profileActions = `
                    <div class="wrap-btn profile-actions">
                        <button class="btn primary-btn share-profile">
                        <i class="fa-solid fa-share"></i>
                        Share profile
                        </button>
                        <form action="/logout.php" method="post" class="logout-form">
                            <button type="submit" class="btn secondary-btn"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></button>
                        </form>
                    </div>
                `;
            }
            else{
                elPreview.insertAdjacentHTML('afterbegin', wrapProfile(false));

                const getFriends = await HTTP.send('/src/api/friends/', 'GET', {q: qid, action: 'query'}); // Should be query
                const relationship = getFriends.response;

                stat = defineState(relationship);

                profileActions = `
                    <div class="wrap-btn profile-actions">
                        <a href="/inbox/?q=${HTML.escapeHTML(qid)}" class="btn primary-btn"><i class="fa fa-comment" aria-hidden="true"></i>Message</a>
                        <button type="submit" class="btn secondary-btn friend-action">${stat.btnActionContent}</button>
                    </div>
                `;
            }

            elPreview.insertAdjacentHTML('beforeend', profileActions);
        }
        else{
            throw new Error('User not found.');
        }
    }
    catch(error){
        console.error(error);
        if(error.message == 'User not found.'){
            elPreview.insertAdjacentHTML('afterbegin', '<h1>User not found.<h1>');
        }
    }
});

elPreview.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const elLogoutForm = e.target.closest('.logout-form');
    if(elLogoutForm){
        const logout = await HTTP.send('/src/api/me/', 'DELETE', { action: 'logout' });
        if(logout.ok){
            window.location.replace('/signin');
        }
    }
});

elPreview.addEventListener('click', async (e) => {
    
    const elBtnFriendAction = e.target.closest('.friend-action');
    if(elBtnFriendAction){
        const postFriends = await HTTP.send('/src/api/friends/', 'POST', { q: qid, action: stat.friendAction });

        if(postFriends.ok){
            const newRelationship = postFriends.response;
            stat = defineState(newRelationship);
            
            elBtnFriendAction.innerHTML = stat.btnActionContent;
        }
    }

    // Copy profile link
    const elShareProfile = e.target.closest('.share-profile');
    if(elShareProfile){
            const profileUrl = window.location.href;

            try{
                navigator.clipboard.writeText(profileUrl);
                elShareProfile.innerHTML = 'Profile link copied!';

                setTimeout(() => {
                    elShareProfile.innerHTML = '<i class="fa-solid fa-share"></i>Share profile';
                }, 2000);
            }
            catch(error){
                console.error('Failed to copy', error);
            }
    }
});

// Listen for users' presence
socket.on('presence', (data) => {

    if(!qid || data.userId != qid) return;

    const statusDot = document.querySelector('.is-online');
    const statusLabel = document.querySelector('.is-online-label');

    if(data.userId == qid){

        if(data.isOnline){
            statusDot.classList.replace('offline', 'online');
            statusLabel.textContent = 'Online';
        }
        else{
            statusDot.classList.replace('online', 'offline');
            statusLabel.textContent = 'Offline';
        }
    }
});