import { UI, HTTP, Fetch, Form, Check, HTML, defaultProfilePic, performanceTimer } from "../helpers.js";
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

let elFormDelete = null;
let elFormReset = null;

const elBtnGoogle = document.querySelector('.btn.google');

const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

document.addEventListener('DOMContentLoaded', async function(){

    const start = performanceTimer.start();

    try{
        me = await userDataReady;

        elUpdateUsername.value = me.username;
        elUpdateEmail.value = me.email;
        elImg.src = `/src/assets/pfps/${me.img ?? defaultProfilePic}`;
        elUpdateBio.value = me.bio;

        elUpdateUsername.setAttribute('data-initial', me.username);
        elUpdateEmail.setAttribute('data-initial', me.email);
        elUpdateBio.setAttribute('data-initial', me.bio);

        if(me.theme){
            document.querySelector(`.group-theme .option[value="${me.theme}"]`).classList.add('active');
        }
        // else{
        //     const defaultTheme = isDarkMode ? 'dark' : 'light';
        //     document.querySelector(`.group-theme .option[value="${defaultTheme}"]`).classList.add('active');
        // }

        if(me.is_google_linked){
            elBtnGoogle.classList.add('btn-disabled');
            elBtnGoogle.querySelector('span').textContent = 'Linked with Google';
        }
    }
    catch(error){
        console.error(error);
    }

    performanceTimer.stop(start);
});

Form.initForm(elFormUpdate, 'POST', '/src/api/me/', true, false, { attach_data: { action: 'cred' } });

document.querySelector('.img-input').addEventListener('click', () => elUpdatePfp.click());

elUpdatePfp.addEventListener('change', (e) => {
    elImg.src = URL.createObjectURL(e.target.files[0]);
    Form.isFormEnabled(elFormUpdate, true);
});

document.querySelector('.modal-show.delete').addEventListener('click', () => {
    HTML.injectModal(`
            <div class="logo">
                <span class="asset" style="--asset-url: url('/src/assets/logo/logo-text.svg')"></span>
            </div>
            <div class="br"></div>
            <h1>Delete your account:</h1>
            <div class="alert-placeholder"></div>
            <form method="post" autocomplete="off" class="delete-form"
            novalidate>
                <input 
                type="email" 
                class="email input required" 
                name="email" 
                placeholder="Email" 
                autocomplete="off">
                <div class="wrap-password">
                    <input 
                    type="password" 
                    name="password" 
                    placeholder="Password" 
                    class="password input required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="checkbox">
                    <input type="checkbox" class="input agree required" name="agree" required>
                    <span>I unserstand that my account will be permanently deleted</span>
                </div>
                <div class="wrap-btn">
                    <button class="btn delete submit" type="button"><i class="fa-solid fa-trash"></i>Delete Account</button>
                    <button class="btn secondary-btn modal-hide" type="button">Cancel</button>
                </div>
            </form>
    `, {class: 'modal-delete modal-form'});

    elFormDelete = document.querySelector('.delete-form');
    Form.initForm(elFormDelete, 'DELETE', '/src/api/me/', false, '/', { attach_data: { action: 'delete' } });
});

document.querySelector('.modal-show.change').addEventListener('click', () => {
    HTML.injectModal(`
            <div class="logo">
                <span class="asset" style="--asset-url: url('/src/assets/logo/logo-text.svg')"></span>
            </div>
            <div class="br"></div>
            <h1>Reset your password:</h1>
            <div class="alert-placeholder"></div>
            <form method="post" autocomplete="off" class="reset-form">
                <div class="wrap-password">
                    <input type="password" name="old_pass" placeholder="Old password" class="input password required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="wrap-password">
                    <input type="password" name="new_pass" placeholder="New password" class="input password required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="wrap-password">
                    <input type="password" name="new_pass1" placeholder="Confirm new password" class="input password required">
                    <button type="button" class="s-h"><i class="fa fa-eye" aria-hidden="true"></i></button>
                </div>
                <div class="wrap-btn">
                    <button class="btn primary-btn submit" type="button">Apply</button>
                    <button class="btn secondary-btn modal-hide" type="button">Cancel</button>
                </div>
            </form>
    `, {class: 'modal-reset modal-form'});

    elFormReset = document.querySelector('.reset-form');
    Form.initForm(elFormReset, 'PUT', '/src/api/me/reset-password/', false);
});

document.querySelectorAll('.group-theme .option').forEach(op => {
    op.addEventListener('click', async () => {
        document.body.setAttribute('theme', op.value);
        await HTTP.send('/src/api/me/', 'PATCH', { action: 'theme', theme: op.value });
    });
});