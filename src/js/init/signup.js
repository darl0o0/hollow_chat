import { UI } from "../ui-helpers.js";

var currentStep = 1;

//Navigate between step 1 and step 2 && checking the user's provided credentials in the front-end
function gotoStep(step){
    if(step == 2){
        const username = document.querySelector('.username').value;
        const email = document.querySelector('.email').value;
        const pass1 = document.querySelector('.pass-1').value;
        const pass2 = document.querySelector('.pass-2').value;
        if(username && email && pass1 && pass2){
            if(!username.includes(" ")){
                if(UI.isValidEmail(email)){
                    if(pass1.length >= 8){
                        if(pass1 == pass2){
                            document.querySelectorAll('.form-step').forEach(formStep => {
                                formStep.style.display = 'none';
                            });
                            document.querySelector(`.step-${step}`).style.display = 'inline';
                            currentStep++;
                        }
                        else{
                            UI.alert(false, "Passwords are not identical.", null);
                        }
                    }
                    else{
                        UI.alert(false, "Password must contain at least 8 characters.", null);
                    }
                }
                else{
                    UI.alert(false, "Please enter a valid email.", null);
                }
            }
            else{
                UI.alert(false, "Username cannot contain space.", null);
            }
        }
        else{
            UI.alert(false, "All inputs are required.", null);
        }
    }
    else if(step == 1){
        document.querySelectorAll('.form-step').forEach(formStep => {
            formStep.style.display = 'none';
        });
        document.querySelector(`.step-${step}`).style.display = 'inline';
        currentStep--;
    }
}

document.addEventListener('DOMContentLoaded', async function(){

    //Prevent the user from submitting if they're not in the final step of the inscription
    document.querySelector('.signup-form').addEventListener('submit', async function(e){
        e.preventDefault();

        if(currentStep == 2){
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
            gotoStep(2);
        }
    });

    document.querySelector('.next').addEventListener('click', () => gotoStep(2));

    document.querySelector('.back').addEventListener('click', () => gotoStep(1));
});