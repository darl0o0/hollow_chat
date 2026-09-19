import { UI } from "../ui-helpers.js";
import { socket, userDataReady} from "./main.js";

let id = null;
let me = null;

document.addEventListener('DOMContentLoaded', async function(){
    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }
    
    // List users
    let buffer = "";
    try{
        const users = await UI.sendGet('/src/api/users/');
    
        users.response.forEach(user => {
            const bio = user.bio ? user.bio : "No Bio."
            const isOnline = Boolean(Number(user.isOnline)) ? 'visible' : "hidden"
    
            buffer += `
                <a class='user-card u-${user.id}' href='profile?q=${user.id}'>
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
    }
    catch(error){
        console.error(error);
    }
    document.querySelector('.users-list').innerHTML = buffer;
});

// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {
    const onlineStatus = document.querySelector(`.u-${data.userId} .online-status`);
    
    if(onlineStatus){
        onlineStatus.style.visibility = data.isOnline ? "visible" : "hidden";
    }
});