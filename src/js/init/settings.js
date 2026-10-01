import { UI } from "../ui-helpers.js";
import { userDataReady} from "./main.js";

let me = null;

document.addEventListener('DOMContentLoaded', async function(){

    const usernameEl = document.querySelector('.username');
    const emailEl = document.querySelector('.email');

    try{
        me = await userDataReady;

        usernameEl.value = me.username;
        emailEl.value = me.email;

        usernameEl.setAttribute('data-initial', me.username);
        emailEl.setAttribute('data-initial', me.email);
    }
    catch(error){
        console.error(error);
    }

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

        try{
            const update = await UI.sendPost('/src/api/account/update.php', formData);
    
            if(update.status == 'ok'){
                UI.alert(true, update.message, 3);
    
                usernameEl.dataset.initial = username;
                emailEl.dataset.initial = email;
            }
            else if(update.status == 'error'){
                UI.alert(false, update.message, null);
                return;
            }
        }
        catch(error){
            console.error(error);
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

        try{
            const deleteRes = await UI.sendPost('/src/api/account/delete.php', formData);
    
            if(deleteRes.status == 'ok'){
                UI.alert(true, deleteRes.message, 4, true);
                setTimeout(() => {
                    window.location.replace('/signin');
                }, 4000);
            }
            else if(deleteRes.status == 'error'){
                UI.alert(false, deleteRes.message, null, true);
                return;
            }
        }
        catch(error){
            console.error(error);
        }
    });
});