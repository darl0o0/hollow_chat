import { UI } from "../ui-helpers.js";
import { socket, userDataReady} from "./main.js";

let me = null;
let id = null;
let user2 = null;

// A function that defines the state between two users
async function defineState(){
    user2 = document.querySelector('meta[name="qid"]')?.content;
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
    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }
    user2 = document.querySelector('meta[name="qid"]')?.content;

    const img = document.querySelector('.profile-card img');
    const username = document.querySelector('.profile-card .username-label');
    const onlineDot = document.querySelector('.profile-card .is-online');
    const onlineLabel = document.querySelector('.profile-card .is-online-label');

    const date = document.querySelector('.details-section.date .text');
    const bio = document.querySelector('.details-section.bio .text');
    const privacy = document.querySelector('.details-section.privacy .text');

    try{
        const user = await UI.sendGet('/src/api/users/search.php', {q: user2});
        
        if(user.response.profile){
            document.querySelector('.profile-wrapper').style.display = 'flex';

            img.src = `/src/assets/pfps/${user.response.profile.img}`;
            username.textContent = user.response.profile.username;
            onlineDot.style.background = user.response.profile.online ? '#e9e4c8' : 'none';
            onlineDot.style.border = user.response.profile.online ? '2px solid #e9e4c8' : '2px ridge #464646';
            onlineLabel.textContent = user.response.profile.online ? 'Online' : 'Offline';
    
            date.textContent = user.response.profile.date;
            bio.textContent = user.response.profile.bio;
            privacy.textContent = user.response.profile.isPrivate ? 'Private' : 'Public';
    
            if(user.response.isYou){
                onlineDot.style.background = '#e9e4c8';
                onlineDot.style.border = '2px solid #e9e4c8';
                onlineLabel.textContent = 'Online';

                document.querySelector('.profile-action').style.background = 'rgba(125, 125, 125, 0.15)';
    
                document.querySelector('.profile-actions').innerHTML = `
                    <button class="btn primary-btn share-profile">
                    <i class="fa-solid fa-share"></i>
                    Share profile
                    </button>
                    <form action="/logout.php" method="post" class="logout-form">
                        <button type="submit" class="btn secondary-btn"><i class="fa-solid fa-right-from-bracket"></i><span>Logout</span></button>
                    </form>
                `;
            }
            else{
                document.querySelector('.profile-actions').innerHTML = `
                    <a href="/chat?q=${user2}" class="btn primary-btn"><i class="fa fa-comment" aria-hidden="true"></i>Message</a>
                    <form
                    method="post" 
                    class="friend-action">
                        <input type="hidden" name="user2" value="${user2}">
                        <input type="hidden" name="action">
                        <button type="submit" class="btn secondary-btn profile-action-btn"></button>
                    </form>
                `;
            }
        }
        else{
            document.querySelector('.profile-wrapper').style.display = 'none';
            document.querySelector('.not-found').textContent = "User not found.";
        }
    }
    catch(error){
        console.error(error);
    }
    
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

            const res = await UI.sendPost('/src/api/account/logout.php');
            if(res.status == 'ok'){
                window.location.replace('/signin');
            }
        });
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