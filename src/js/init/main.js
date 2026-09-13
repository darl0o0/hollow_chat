import { UI } from "../ui-helpers.js";

document.addEventListener('DOMContentLoaded', () => {

    // Universal notifications list
    UI.globalElements();

    // Show/hide password
    document.querySelectorAll('.s-h').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            const icon = btn.querySelector('i');

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            icon.classList.toggle('fa-eye', !isPassword);
            icon.classList.toggle('fa-eye-slash', isPassword);
        });
    });

    // Notifications model
    if(document.querySelector('.notifications-action')){
        document.querySelector('.notifications-action').addEventListener('click', () => {
            document.querySelector(".requests-model").style.visibility = "visible";
            document.querySelector(".main").style.filter = "blur(3px)";
        });
    }

    if(document.querySelector(".background")){
        document.addEventListener('keydown', (e) =>{
            if(e.keyCode === 27){
                UI.hideModel();
            }
        });
    }

    document.querySelectorAll(".background").forEach(hideEvent => {
        hideEvent.onclick = UI.hideModel;
    });
});

document.addEventListener('DOMContentLoaded', async function(){
    const res = await UI.sendGet('/src/api/friends/');
    var buffer = "";
    res.response.requests.incoming.forEach(user => {
        const bio = user.bio ? user.bio : "No Bio."

        buffer += `
            <div class='user-card u-${user.id}'>
                <a href='/profile?u=${user.id}' class='profile-link'></a>
                <div class='pfp'>
                    <img class='pfp-img' src='/src/assets/pfps/${user.img}'>
                </div>
                <div class='label-wrapper'>
                    <div class='username-label'>${user.username}</div>
                    <span class='sub-label'>${bio}</span>
                </div>
                <div class='btn-wrapper-requests'>
                    <form method='post' action='/profile/friend_action.php'>
                        <input type='hidden' name='user2_id' value='$user2_request'>
                        <input type='hidden' name='from' value='fr'>
                        <button name='action' value='2' class='btn-requests'><i class='fa fa-check' aria-hidden='true'></i></button>
                    </form>
                    <form method='post' action='/profile/friend_action.php'>
                        <input type='hidden' name='user2_id' value='$user2_request'>
                        <input type='hidden' name='from' value='fr'>
                        <button name='action' value='1' class='btn-requests'><i class='fa fa-times' aria-hidden='true'></i></button>
                    </form>
                </div>
            </div>
        `;
    });
    document.querySelector('.requests-model .list').innerHTML = buffer;
});