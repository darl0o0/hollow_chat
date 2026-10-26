import { UI, HTTP, Fetch, Form, Check, HTML } from "../helpers.js";
import { socket, userDataReady} from "./main.js";

const getParams = new URLSearchParams(window.location.search);

// User objects
let me = null;

// IDs
let id = null;
const qid = getParams.get('q') ? (getParams.get('q').length > 0 ? getParams.get('q') : null) : null;

// Elements
const elPreview = document.querySelector('.preview');

// Helper functions
async function defineState(){

    if(!qid) return;

    const res = await HTTP.sendGet('/src/api/relationship/', {qid});
    const relationship = res?.response?.relationship;

    switch(relationship){
        case false:
            return {
                actionInput: 0,
                profileActionContent: '<i class="fa fa-user-plus" aria-hidden="true"></i>Add Friend'
            }
            
        case 'sent':
            return {
                actionInput: 1,
                profileActionContent: '<i class="fa fa-user-times" aria-hidden="true"></i>Cancel Request'
            }
            
        case 'pending':
            return {
                actionInput: 2,
                profileActionContent: '<i class="fa fa-check" aria-hidden="true"></i> Accept Request'
            }

        case true:
            return {
                actionInput: 1,
                profileActionContent: '<i class="fa fa-user-times" aria-hidden="true"></i>Remove Friend'
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
                const onlineClass = (forceOnline || user.online) ? 'online' : 'offline';

                const onlineLabel = (forceOnline || user.online) ? 'Online' : 'Offline';
                
                return `<div class="wrap-profile">
                    <div class="profile-card">
                        <img src='/src/assets/pfps/${HTML.escapeHTML(user.img)}'>
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
                    <div class="profile-actions">
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
                const stat = await defineState();

                profileActions = `
                    <div class="profile-actions">
                        <a href="/inbox/?q=${HTML.escapeHTML(qid)}" class="btn primary-btn"><i class="fa fa-comment" aria-hidden="true"></i>Message</a>
                        <form
                        method="post" 
                        class="friend-action">
                            <input type="hidden" name="qid" value="${HTML.escapeHTML(qid)}">
                            <input type="hidden" name="action" value='${stat.actionInput}'>
                            <button type="submit" class="btn secondary-btn profile-action-btn">${stat.profileActionContent}</button>
                        </form>
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
    const elFriendActionForm = e.target.closest('.friend-action');
    const elLogoutForm = e.target.closest('.logout-form');
    e.preventDefault();

    if(elFriendActionForm){
        const formData = new FormData(e.target);
        const data = Form.formToJson(formData);

        await HTTP.sendPost('/src/api/relationship/', data);
        const stat = await defineState();

        elFriendActionForm.querySelector('[name="action"]').value = stat.actionInput;
        elFriendActionForm.querySelector('.profile-action-btn').innerHTML = stat.profileActionContent
    }

    if(elLogoutForm){
        const apiLogout = await HTTP.sendPost('/src/api/account/logout.php');
        if(apiLogout.status == 'ok'){
            window.location.replace('/signin');
        }
    }
});

elPreview.addEventListener('click', (e) => {
    const elShareProfile = e.target.closest('.share-profile');

    // Copy profile link
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