import { UI, HTTP, Fetch, Form, Check } from "../helpers.js";

// Elements
const elFormSignin = document.querySelector('.signin-form');

Form.initForm(elFormSignin, 'POST', '', true, '/');