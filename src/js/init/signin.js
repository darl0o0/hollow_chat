import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";

// Elements
const elFormSignin = document.querySelector('.signin-form');

Form.initForm(elFormSignin, 'POST', '', true, '/');

document.querySelector('.wrap-title-link a').addEventListener('mousedown', (e) => {
    e.preventDefault();

    const passwordInput = elFormSignin.querySelector('.password');
    if (passwordInput) passwordInput.value = '';
});

const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
document.body.setAttribute('theme', isDarkMode ? 'dark' : 'light');