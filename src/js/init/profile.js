import { UI } from "../ui-helpers.js";
import { socket } from "./main.js";

let id = null;
let user2 = null;

// A function that defines the state between two users
async function defineState(){
    user2 = document.querySelector('meta[name="user2"]')?.content;
    const profileActionBtn = document.querySelector('.profile-action-btn');
    const actionInput = document.querySelector('[name="action"]');
    if(!user2 || !profileActionBtn || !actionInput) return;

    const res = await UI.sendGet('/src/api/relationship/', {user2});
    const relationship = res?.response?.relationship;
    switch(relationship){
        case false:
            actionInput.value = 0;
            profileActionBtn.innerHTML = '<i class="fa fa-user-plus" aria-hidden="true"></i>Add Friend';
        break;
        case 'sent':
            actionInput.value = 1;
            profileActionBtn.innerHTML = '<i class="fa fa-user-times" aria-hidden="true"></i>Cancel Request';
        break;
        case 'pending':
            actionInput.value = 2;
            profileActionBtn.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i> Accept Request';
        break;
        case true:
            actionInput.value = 1;
            profileActionBtn.innerHTML = '<i class="fa fa-user-times" aria-hidden="true"></i>Remove Friend';
        break;
    }
}

document.addEventListener('DOMContentLoaded', async function(){
    id = document.querySelector('meta[name="id"]')?.content;
    user2 = document.querySelector('meta[name="user2"]')?.content;
    
    const friendActionForm = document.querySelector('.friend-action');
    const logoutForm = document.querySelector('.logout-form');
    const shareProfileBtn = document.querySelector('.share-profile');

    // Define state between users
    if(friendActionForm){
        defineState();

        friendActionForm.addEventListener('submit', async function(e){
            e.preventDefault();
            const formData = new FormData(e.target);

            await UI.sendPost('/src/api/relationship/', formData);
            defineState();
        });
    }

    // Logout
    if(logoutForm){
        logoutForm.addEventListener('submit', async function(e){
            e.preventDefault();
            const formData = new FormData(e.target);

            const res = await UI.sendPost('/src/api/account/logout.php', formData);
            if(res.status == 'ok'){
                window.location.replace('/signin');
            }
        })
    }

    // Copy profile link
    if(shareProfileBtn){
        shareProfileBtn.addEventListener('click', ()=>{
            const profileUrl = window.location.href;
            navigator.clipboard.writeText(profileUrl).then(() => {
                const btn = document.querySelector('.share-profile');
                btn.innerHTML = 'Profile link copied!';
                setTimeout(() => {
                    btn.innerHTML = '<i class="fa-solid fa-share"></i>Share profile';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy', err);
            });
        });
    }
});

// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {
    if(!user2 || data.userId != user2) return;
    const statusDot = document.querySelector(".is-online");
    const statusLabel = document.querySelector(".is-online-label");
    if(data.userId == user2){
        statusDot.style.background = data.isOnline ? "#e9e4c8" : "none";
        statusDot.style.border = data.isOnline ? "2px solid #e9e4c8" : "2px ridge #464646";
        statusLabel.innerHTML = data.isOnline ? "Online" : "Offline";
    }
});