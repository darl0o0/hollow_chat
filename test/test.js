import { UI, HTTP, Fetch, Form, Check } from "../src/js/helpers.js";

document.addEventListener('DOMContentLoaded', async () => {
    const res = await HTTP.sendPost('/src/api/batch-fetch/', {q: '53e16788d052bd1f'})
    console.log(res.response);
});