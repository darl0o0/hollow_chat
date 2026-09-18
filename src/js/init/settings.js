import { UI } from "../ui-helpers.js";

document.addEventListener('DOMContentLoaded', async function(){

    const usernameEl = document.querySelector('.username');
    const emailEl = document.querySelector('.email');

    try{
        const me = await UI.sendGet('/src/api/me/');

        usernameEl.value = me.response.username;
        emailEl.value = me.response.email;

        usernameEl.setAttribute('data-initial', me.response.username);
        emailEl.setAttribute('data-initial', me.response.email);
    }
    catch(error){
        console.error(error);
    }

    // Show the on-screen models
    const changeBtn = document.querySelector(".change");
    const deleteBtn = document.querySelector(".delete");
    const main = document.querySelector(".main");

    changeBtn.addEventListener('click', () => { 
        const model = document.querySelector(".change-model");
        if(model) model.style.visibility = "visible";
        if(main) main.style.filter = "blur(3px)";
    });

    deleteBtn.addEventListener('click', () => { 
        const model = document.querySelector(".delete-model");
        if(model) model.style.visibility = "visible";
        if(main) main.style.filter = "blur(3px)";
    });

    // Hide the on-screen models
    document.querySelectorAll(".model .secondary-btn").forEach(hideEvent => {
        hideEvent.addEventListener('click', () => {
            UI.hideModel();
        });
    });
    
    //Update form
    document.querySelector(".update-form").addEventListener('submit', async function(e){
        e.preventDefault();

        const username = usernameEl?.value.trim();
        const email = emailEl?.value.trim();

        if(!username || !email){
            UI.alert(false, "All inputs are required.", null);
            return;
        }

        const initUsername = usernameEl.dataset.initial || '';
        const initEmail = emailEl.dataset.initial || '';

        if(username == initUsername && email == initEmail){
            UI.alert(false, "Cannot use the current credentials.", null);
            return;
        }

        const formData = new FormData(e.target);

        const res = await UI.sendPost('/src/api/account/update.php', formData);

        if(res.status == 'ok'){
            UI.alert(true, res.message, 3);

            usernameEl.dataset.initial = username;
            emailEl.dataset.initial = email;
        }
        else if(res.status == 'error'){
            UI.alert(false, res.message, null);
            return;
        }
    });
    
    // Delete account form
    document.querySelector('.delete-form').addEventListener('submit', async function(e){
        e.preventDefault();
        const email = document.querySelector('.delete-email').value;
        const password = document.querySelector('.delete-password').value;
        if(!email || !password){
            UI.alert(false, "All inputs are required.", null, true);
            return;
        }
        const formData = new FormData(e.target);

        const res = await UI.sendPost('/src/api/account/delete.php', formData);

        if(res.status == 'ok'){
            UI.alert(true, res.message, 4, true);
            setTimeout(() => {
                window.location.replace('/signin');
            }, 4000);
        }
        else if(res.status == 'error'){
            UI.alert(false, res.message, null, true);
            return;
        }
    });
});