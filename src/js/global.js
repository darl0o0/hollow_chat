import { UI } from "./helpers.js";

// Show notifications model
document.addEventListener('click', (e) => {
    const modelBtn = e.target.closest('.modal-show');
    if(modelBtn){
        const modelName = modelBtn.getAttribute('link-modal');

        const model = document.querySelector(`.${modelName}`);
        const main = document.querySelector(".main");

        if(model) model.style.visibility = "visible";
        if(main) main.style.filter = "blur(3px)";
    }

    const hideModalBtn = e.target.closest('.model-hide');
    if(hideModalBtn){
        UI.hideModel();
    }
});

// Hide notifications model
document.addEventListener('keydown', (e) =>{
    if(e.keyCode === 27 || e.key === 'Escape'){
        UI.hideModel();
    }
});

document.addEventListener('click', (e) => {
    if(e.target.matches('.background')){
        UI.hideModel();
    }
});

// Show/hide password
document.querySelectorAll('.s-h').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
    });
});