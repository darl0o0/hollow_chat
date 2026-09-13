import { UI } from "../ui-helpers.js";

document.addEventListener('DOMContentLoaded', () => {

    // Show the on-screen models
    document.querySelector(".change").addEventListener('click', () => {            
        document.querySelector(".change-model").style.visibility = "visible";
        document.querySelector(".main").style.filter = "blur(3px)";
    });
    document.querySelector(".delete").addEventListener('click', () => {
        document.querySelector(".delete-model").style.visibility = "visible";
        document.querySelector(".main").style.filter = "blur(3px)";
    })
    document.querySelectorAll(".model .secondary-btn").forEach(hideEvent => {
        hideEvent.addEventListener('click', () => {
            UI.hideModel();
        });
    });
    
    //Update form
    document.querySelector(".update-form").addEventListener('submit', async function(e){
        e.preventDefault();
        const username = document.querySelector('.username').value;
        const email = document.querySelector('.email').value;
        if(username && email){
            if(username != "<?php echo $user_data['username'];?>" || email != "<?php echo $user_data['email'];?>"){
                const formData = new FormData(e.target);
                
                //HTTP Request
                const res = await UI.sendPost('/src/api/account/update.php', formData);
                if(res.status == 'ok'){
                    UI.alert(true, res.message, 3);
                }
                else if(res.status == 'error'){
                    console.log(res.message);
                    UI.alert(false, res.message, null);
                }
            }
            else{
                UI.alert(false, "Cannot use the current credentials.", null);
            }
        }
        else{
            UI.alert(false, "All inputs are required.", null);
        }
    });
    
    // Delete account form
    document.querySelector('.delete-form').addEventListener('submit', async function(e){
        e.preventDefault();
        const email = document.querySelector('.delete-email').value;
        const password = document.querySelector('.delete-password').value;
        if(email && password){
            const formData = new FormData(e.target);
            
            //HTTP Request
            const res = await UI.sendPost('/src/api/account/delete.php', formData);
            if(res.status == 'ok'){
                UI.alert(true, res.message, 4, true);
                setTimeout(() => {
                    window.location.replace('/signin');
                }, 4000);
            }
            else if(res.status == 'error'){
                UI.alert(false, res.message, null, true);
            }
        }
        else{
            UI.alert(false, "All inputs are required.", null, true);
        }
    });
});