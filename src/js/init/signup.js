import { UI } from "../ui-helpers.js";

let currentStep = 1;

function validateStep1(){
    const username = document.querySelector('.username')?.value.trim();
    const email = document.querySelector('.email')?.value.trim();
    const pass1 = document.querySelector('.pass-1')?.value.trim();
    const pass2 = document.querySelector('.pass-2')?.value.trim();
    if(!username || !email || !pass1 || !pass2){
        UI.alert(false, "All inputs are required.", null);
        return false;
    }
    if(username.includes(" ")){
        UI.alert(false, "Username cannot contain space.", null);
        return false;
    }
    if(!UI.isValidEmail(email)){
        UI.alert(false, "Please enter a valid email.", null);
        return false;
    }
    if(pass1.length < 8){
        UI.alert(false, "Password must contain at least 8 characters.", null);
        return false;
    }
    if(pass1 != pass2){
        UI.alert(false, "Passwords are not identical.", null);
        return false;
    }
    return true;
}

//Navigate between step 1 and step 2 && checking the user's provided credentials in the front-end
function gotoStep(step){
    if(step == 2){
        if(!validateStep1()) return;

        document.querySelectorAll('.form-step').forEach(formStep => {
            formStep.style.display = 'none';
        });
        document.querySelector(`.step-${step}`).style.display = 'inline';

        currentStep++;
    }
    else if(step == 1){
        document.querySelectorAll('.form-step').forEach(formStep => {
            formStep.style.display = 'none';
        });
        document.querySelector(`.step-${step}`).style.display = 'inline';
        
        currentStep--;
    }
}

//Prevent the user from submitting if they're not in the final step of the inscription
document.querySelector('.signup-form').addEventListener('submit', async function(e){
    e.preventDefault();

    if(currentStep == 2){
        const formData = new FormData(e.target);
        const res = await UI.sendPost('', formData);

        if(res.status == 'error'){
            gotoStep(1);
            currentStep--;
            UI.alert(false, res.message, null);
        }
        else if(res.status == 'ok'){
            UI.alert(true, res.message, 2);
            document.querySelector('.submit').disabled = true;
            setTimeout(() => {
                window.location.replace('/');
            }, 2000);
        }
    }
    else{
        gotoStep(2);
    }
});

document.querySelector('.next').addEventListener('click', () => gotoStep(2));

document.querySelector('.back').addEventListener('click', () => gotoStep(1));

//The upload field functionality
document.querySelector(".img-input").addEventListener("click", (e)=>{
    if(document.querySelector('.img-input .fa-x') && document.querySelector('.img-input .fa-x').contains(e.target)){
        document.querySelector(".img-input").innerHTML = "Choose a profile picture<br>(Optional)<br><i class='fa-solid fa-image'></i>";
        document.querySelector(".pfp").value = "";
    }
    else{
        document.querySelector(".pfp").click();
    }
});
document.querySelector(".pfp").onchange = function (){
    document.querySelector(".img-input").innerHTML = "<i class='fa-solid fa-x'></i><img src='"+URL.createObjectURL(document.querySelector(".pfp").files[0])+"'>";
}