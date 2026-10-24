import { UI, HTTP, Fetch, Form, Check, HTML } from "../helpers.js";
import { socket, userDataReady} from "./main.js";

// User objects
let me = null;

// IDs
let id = null;

// Lists of objects
let users;

// Buffers
let bufferUsers = "";

// Elements
const elListUsers = document.querySelector('.users-list');

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
        const apiUsers = await HTTP.sendGet('/src/api/users/');
        users = apiUsers.response;
    
        users.forEach(user => {
            bufferUsers += HTML.userCard(
                'link',
                user.id,
                user.username,
                user.img,
                user.isOnline,
                {text: user.bio}
            );
        });
    }
    catch(error){
        console.error(error);
    }

    elListUsers.innerHTML = bufferUsers;
});

document.querySelector('.search').addEventListener('input', async (e) => {
    const query = e.target.value.trim();

    if(query == ''){
        elListUsers.innerHTML = bufferUsers;
        return;
    }

    try{
        const searchUsers = await Fetch.searchObjectFromList(query, users, 'username');
        let bufferSearchUsers = "";
        
        if(!searchUsers){
            bufferSearchUsers = '<center>No users found.</center>';
            return;
        }

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

        elListUsers.innerHTML = bufferSearchUsers;
    }
    catch(error){
        console.error(error);
    }
});

// Users presence
socket.on('user_status_change', (data) => {
    const pfp = document.querySelector(`.u-${data.userId} .wrap-pfp`);
    
    if(data.isOnline){
        pfp.classList.replace('offline', 'online');
    }
    else{
        pfp.classList.replace('online', 'offline');
    }
});