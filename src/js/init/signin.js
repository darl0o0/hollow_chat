import { UI } from "../ui-helpers.js";

//Checking the user's provided credentials in the front-end
document.querySelector('.signin-form').addEventListener('submit', async function(e){
    e.preventDefault();
    const email = document.querySelector('.email')?.value.trim();
    const password = document.querySelector('.password')?.value.trim();

    if(!email || !password){
        UI.alert(false, "All inputs are required.", null);
        return;
    }
    if(!UI.isValidEmail(email)){
        UI.alert(false, "Please enter a valid email.", null);
        return;
    }
    const formData = new FormData(e.target);
    const res = await UI.sendPost('', formData);

    if(res.status == 'error'){
        UI.alert(false, res.message, null);
        return;
    }
    else if(res.status == 'ok'){
        UI.alert(true, res.message, 2);
        document.querySelector('.submit').disabled = true;
        setTimeout(() => {
            window.location.replace('/');
        }, 2000);
    }
});