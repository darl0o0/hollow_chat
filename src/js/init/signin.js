import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";

// Elements
const elFormSignin = document.querySelector('.signin-form');

Form.initForm(elFormSignin, 'POST', '', true, '/');

const isDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
document.body.setAttribute('theme', isDarkMode ? 'dark' : 'light');