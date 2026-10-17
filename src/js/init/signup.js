import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";

let currentPreviewUrl = null;

// Elements
const elFormSignup = document.querySelector('.signup-form');

const elUsername = elFormSignup.querySelector('.username');
const elEmail = elFormSignup.querySelector('.email');
const elPass1 = elFormSignup.querySelector('.pass-1');
const elPass2 = elFormSignup.querySelector('.pass-2');

const elUploadField = elFormSignup.querySelector(".upload-field");

// Functions
function validateStep1(){
    const username = elUsername.value.trim();
    const email = elEmail.value.trim();
    const pass1 = elPass1.value.trim();
    const pass2 = elPass2.value.trim();

    if(!username || !email || !pass1 || !pass2){
        throw new Error("All inputs are required.");
    }
    if(username.includes(" ")){
        throw new Error("Username cannot contain space.");
    }
    if(!Check.isValidEmail(email)){
        throw new Error("Please enter a valid email.");
    }
    if(pass1.length < 8){
        throw new Error("Password must be at least 8 characters long.");
    }
    if(pass1 != pass2){
        throw new Error("Passwords do not match.");
    }
}

function gotoStep(step){
    if(step == 2){
        try{
            if(!Form.getFormState(elFormSignup)) return;

            validateStep1();
        }
        catch(error){
            Form.isFormEnabled(elFormSignup, false);
            UI.alert(false, error.response);
            setTimeout(() => {
                Form.isFormEnabled(elFormSignup, true);
            }, 5000);
            return;
        }
    }
    elFormSignup.querySelectorAll('.form-step').forEach(formStep => {
        formStep.classList.remove('active-form-step');
    });
    elFormSignup.querySelector(`.step-${step}`).classList.add('active-form-step');
}

function uploadFile(file){
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    const maxSizeMB = 2;
    const maxSizeBytes = maxSizeMB * 1024 * 1024;
    try{
        if(!allowedTypes.includes(file.type)){
            elFormSignup.querySelector(".pfp").value = '';
            throw new Error('Please upload a valid image.');
        }
        if(file.size > maxSizeBytes){
            elFormSignup.querySelector(".pfp").value = '';
            throw new Error(`File size exceeds ${maxSizeMB}MB limit.`);
        }
        if(currentPreviewUrl){
            URL.revokeObjectURL(currentPreviewUrl);
        }
        currentPreviewUrl = URL.createObjectURL(file);
        elUploadField.innerHTML = `<i class='fa-solid fa-x'></i><img src='${currentPreviewUrl}'>`;
    }
    catch(error){
        Form.isFormEnabled(elFormSignup, false);
        UI.alert(false, error.response);
        setTimeout(() => {
            Form.isFormEnabled(elFormSignup, true);
        }, 5000);
    }
}

Form.initForm(elFormSignup, '', true, '/');

elFormSignup.querySelector('.next').addEventListener('click', () => gotoStep(2));
elFormSignup.querySelector('.back').addEventListener('click', () => gotoStep(1));
elFormSignup.querySelector('.submit').addEventListener('click', (e) => {
    try{
        if(!Form.getFormState(elFormSignup)) return;

        validateStep1();
    }
    catch(error){
        e.stopImmediatePropagation();
        gotoStep(1)
        Form.isFormEnabled(elFormSignup, false);
        UI.alert(false, error.response);
        setTimeout(() => {
            Form.isFormEnabled(elFormSignup, true);
        }, 5000);
        return;
    }
}, true);

//The drag and drop field
elUploadField.addEventListener("click", (e)=>{
    const xIcon = elFormSignup.querySelector('.upload-field .fa-x');

    if(xIcon && (e.target === xIcon || xIcon.contains(e.target))){
        e.stopPropagation();
        
        if(currentPreviewUrl){
            URL.revokeObjectURL(currentPreviewUrl);
            currentPreviewUrl = null;
        }
        elUploadField.innerHTML = "Choose a profile picture<br>(Optional)<br><i class='fa-solid fa-image'></i>";
        elFormSignup.querySelector(".pfp").value = "";
    }
    else{
        elFormSignup.querySelector(".pfp").click();
    }
});
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(uploadEvent => {
    elUploadField.addEventListener(uploadEvent, (e) => {
        e.preventDefault();
        e.stopPropagation();
    });
});
['dragenter', 'dragover'].forEach(onDragEvent => {
    elUploadField.addEventListener(onDragEvent, () => elUploadField.classList.add('active-drag'));
});
['dragleave', 'drop'].forEach(offDragEvent => {
    elUploadField.addEventListener(offDragEvent, () => elUploadField.classList.remove('active-drag'));
});
elUploadField.addEventListener('drop', (e) => {
    const files = e.dataTransfer.files;
    if(files.length > 0){
        elFormSignup.querySelector(".pfp").files = files;
        uploadFile(files[0]);
    }
});
elFormSignup.querySelector(".pfp").addEventListener('change', (e) => {
    if(e.target.files[0]){
        const file = e.target.files[0];
        uploadFile(file);
    }
});