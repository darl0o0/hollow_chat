import { UI } from "../ui-helpers.js";

const profileActionBtn = document.querySelector('.profile-action-btn');
const friendActionForm = document.querySelector('.friend-action');

// A function that defines the state between two users
async function defineState(){
    const user2 = document.querySelector('meta[name="user2"]').content;

    const res = await UI.sendGet('/src/api/relationship/', {user2: user2});
    const actionInput = document.querySelector('[name="action"]');
    switch(res.response.relationship){
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

// Alter the relationship state and update the friend action button
if(friendActionForm){
    friendActionForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const formData = new FormData(e.target);
        await UI.sendPost('/src/api/relationship/', formData);
        defineState();
    });
}

document.addEventListener('DOMContentLoaded', async function(){

    // Define state between users
    if(friendActionForm)
    defineState();

    // Logout
    if(document.querySelector('.logout-form')){
        document.querySelector('.logout-form').addEventListener('submit', async function(e){
            e.preventDefault();
            const formData = new FormData(e.target);
    
            //HTTP Request
            const res = await UI.sendPost('/src/api/account/logout.php', formData);
            if(res.status == 'ok'){
                window.location.replace('/signin');
            }
        })
    }
});