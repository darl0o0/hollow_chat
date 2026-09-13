import { UI } from "../ui-helpers.js";

document.addEventListener('DOMContentLoaded', async function(){

    // List users
    const res = await UI.sendGet('/src/api/users/');
    var buffer = "";
    res.response.forEach(user => {
        const bio = user.bio ? user.bio : "No Bio."
        const isOnline = eval(user.isOnline) ? 'visible' : "hidden"

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