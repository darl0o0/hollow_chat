import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";
import { userDataReady} from "./main.js";

// User objects
let me = null;

// Elements
const elFormUpdate = document.querySelector('.update-form');

const elUpdateUsername = elFormUpdate.querySelector('.username');
const elUpdateEmail = elFormUpdate.querySelector('.email');
const elUpdatePfp = elFormUpdate.querySelector('input[name="img"]');
const elImg = elFormUpdate.querySelector('img');
const elUpdateBio = elFormUpdate.querySelector('.bio');

const elFormDelete = document.querySelector('.delete-form');

const elFormReset = document.querySelector('.reset-form');


document.addEventListener('DOMContentLoaded', async function(){

    try{
        me = await userDataReady;

        elUpdateUsername.value = me.username;
        elUpdateEmail.value = me.email;
        elImg.src = `/src/assets/pfps/${me.img}`;
        elUpdateBio.value = me.bio;

        elUpdateUsername.setAttribute('data-initial', me.username);
        elUpdateEmail.setAttribute('data-initial', me.email);
        elUpdateBio.setAttribute('data-initial', me.bio);
    }
    catch(error){
        console.error(error);
    }
    
});

Form.initForm(elFormUpdate, '/src/api/account/update.php', true);
Form.initForm(elFormDelete, '/src/api/account/delete.php', false, '/');
Form.initForm(elFormReset, '/src/api/account/reset-pass.php', false);

document.querySelector('.img-input').addEventListener('click', () => elUpdatePfp.click());

elUpdatePfp.addEventListener('change', (e) => {
    elImg.src = URL.createObjectURL(e.target.files[0]);
    Form.isFormEnabled(elFormUpdate)
});