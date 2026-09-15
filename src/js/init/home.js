import { UI } from "../ui-helpers.js";
import { socket } from "./main.js";

document.addEventListener('DOMContentLoaded', async function(){

    // List users
    const res = await UI.sendGet('/src/api/users/');

    let buffer = "";
    res.response.forEach(user => {
        const bio = user.bio ? user.bio : "No Bio."
        const isOnline = Boolean(Number(user.isOnline)) ? 'visible' : "hidden"

        buffer += `
            <a class='user-card u-${user.id}' href='profile?u=${user.id}'>
                <div class='pfp'>
                    <img class='pfp-img' src='/src/assets/pfps/${user.img}'>
                    <div class='online-status' style='visibility: ${isOnline}'></div>
                </div>
                <div class='label-wrapper'>
                    <div class='username-label'>${user.username}</div>
                    <span class='sub-label'>${bio}</span>
                </div>
            </a>
        `;
    });
    document.querySelector('.users-list').innerHTML = buffer;
});

// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {
    const onlineStatus = document.querySelector(`.u-${data.userId} .online-status`);
    
    if(onlineStatus){
        onlineStatus.style.visibility = data.isOnline ? "visible" : "hidden";
    }
});