import { UI } from "../ui-helpers.js";
import { socket, userDataReady} from "./main.js";

let id = null;
let me = null;

let users;

let bufferUsers = "";

const ElUsersList = document.querySelector('.users-list');

document.addEventListener('DOMContentLoaded', async function(){
    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }
    
    // List users
    try{
        const apiUsers = await UI.sendGet('/src/api/users/');
        users = apiUsers.response;
    
        users.forEach(user => {
            const bio = user.bio ? user.bio : "No Bio."
            const isOnline = Boolean(Number(user.isOnline)) ? 'visible' : "hidden"
    
            bufferUsers += `
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

    ElUsersList.innerHTML = bufferUsers;
});

document.querySelector('.search').addEventListener('input', async (e) => {
    const query = e.target.value.trim();

    if(query == ''){
        ElUsersList.innerHTML = bufferUsers;
        return;
    }

    try{
        const searchUsers = await UI.searchObjectFromList(query, users, 'username');
        let bufferSearchUsers = "";
        
        if(searchUsers){
            searchUsers.forEach(user => {
                
                const bio = user.bio ? user.bio : "No Bio."
                const isOnline = Boolean(Number(user.isOnline)) ? 'visible' : "hidden"
        
                bufferSearchUsers += `
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
        else{
            bufferSearchUsers = '<center>No users found.</center>'
        }

        ElUsersList.innerHTML = bufferSearchUsers;
    }
    catch(error){
        console.log(error);
    }
});
// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {
    const ElOnlineStatus = document.querySelector(`.u-${data.userId} .online-status`);
    
    if(ElOnlineStatus){
        ElOnlineStatus.style.visibility = data.isOnline ? "visible" : "hidden";
    }
});