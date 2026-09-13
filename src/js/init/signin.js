import { UI } from "../ui-helpers.js";

document.addEventListener('DOMContentLoaded', async function(){
    //Checking the user's provided credentials in the front-end
    document.querySelector('.signin-form').addEventListener('submit', async function(e){
        e.preventDefault();
        const email = document.querySelector('.email').value;
        const password = document.querySelector('.password').value;
        if(email && password){
            if(UI.isValidEmail(email)){
                const formData = new FormData(e.target);
                
                //HTTP Request
                const res = await UI.sendPost('', formData);
                if(res.status == 'ok'){
                    UI.alert(true, res.message, 2);
                    setTimeout(() => {
                        window.location.replace('/');
                    }, 2000);
                }
                else if(res.status == 'error'){
                    UI.alert(false, res.message, null);
                }
            }
            else{
                UI.alert(false, "Please enter a valid email.", null);
            }
        }
        else{
            UI.alert(false, "All inputs are required.", null);
        }
    });
});