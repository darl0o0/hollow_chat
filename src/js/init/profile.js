import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";
import { socket, userDataReady} from "./main.js";

// User objects
let me = null;

// IDs
let id = null;
let user2 = null;

// Elements
const elPreview = document.querySelector('.preview');

// Helper functions
async function defineState(){

    if(!user2) return;

    const res = await HTTP.sendGet('/src/api/relationship/', {user2});
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

    user2 = document.querySelector('meta[name="qid"]')?.content;

    try{
        const user = await HTTP.sendGet('/src/api/users/search.php', {q: user2});
        
        if(user.response.profile){

            const profileWrapper = (forceOnline) => {
                const styleOnlineDot = forceOnline ? 
                `background: #e9e4c8; border: 2px solid #e9e4c8` : 
                (`background: ${user.response.profile.online ? '#e9e4c8' : 'none'}; border: ${user.response.profile.online ? '2px solid #e9e4c8' : '2px ridge #464646'}`);

                const onlineLabel = forceOnline ? 
                'Online' : user.response.profile.online ? 'Online' : 'Offline';
                
                return `<div class="profile-wrapper">
                    <div class="profile-card">
                        <img src='/src/assets/pfps/${user.response.profile.img}'>
                        <span class="username-label">${user.response.profile.username}</span>
                        <div class="online-status">
                            <div class="is-online" style='${styleOnlineDot}'></div>
                            <span class="is-online-label">${onlineLabel}</span>
                        </div>
                    </div>
                    <div class="profile-details">
                        <div class="details-section date">
                            <span class="title">Member since</span>
                            <span class="text">${user.response.profile.date}</span>
                        </div>
                        <div class="details-section bio">
                            <span class="title">Bio</span>
                            <span class="text">${user.response.profile.bio}</span>
                        </div>
                        <div class="details-section privacy">
                            <span class="title">Account privacy</span>
                            <span class="text">${user.response.profile.isPrivate ? 'Private' : 'Public'}</span>
                        </div>
                    </div>
                </div>`
            };

            let profileActions;
    
            if(user.response.isYou){
                document.querySelectorAll('.profile-action').forEach(el => {
                    el.style.background = 'rgba(125, 125, 125, 0.15)';
                });
                elPreview.insertAdjacentHTML('afterbegin', profileWrapper(true));
    
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
                elPreview.insertAdjacentHTML('afterbegin', profileWrapper(false));
                const stat = await defineState();

                profileActions = `
                    <div class="profile-actions">
                        <a href="/inbox/?q=${user2}" class="btn primary-btn"><i class="fa fa-comment" aria-hidden="true"></i>Message</a>
                        <form
                        method="post" 
                        class="friend-action">
                            <input type="hidden" name="user2" value="${user2}">
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

        await HTTP.sendPost('/src/api/relationship/', formData);
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
socket.on('user_status_change', (data) => {

    if(!user2 || data.userId != user2) return;

    const statusDot = document.querySelector(".is-online");
    const statusLabel = document.querySelector(".is-online-label");

    if(data.userId == user2){

        if(data.isOnline){
            statusDot.style = `background: #e9e4c8; border: 2px solid #e9e4c8`;
            statusLabel.textContent = 'Online';
        }
        else{
            statusDot.style = `background: none; border: 2px ridge #464646`;
            statusLabel.textContent = 'Offline';
        }
    }
});