import { UI } from "./helpers.js";

// Show notifications modal
document.addEventListener('click', (e) => {
    if(e.target.matches('.background')){
        UI.hideModals();
    }

    const hideModalsBtn = e.target.closest('.modal-hide');
    if(hideModalsBtn){
        UI.hideModals();
    }

    const elBtnEye = e.target.closest('.s-h');
    if(elBtnEye){
        const input = elBtnEye.parentElement.querySelector('input');
        const icon = elBtnEye.querySelector('i');

        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        icon.classList.toggle('fa-eye', !isPassword);
        icon.classList.toggle('fa-eye-slash', isPassword);
    }
});

// Hide notifications modal
document.addEventListener('keydown', (e) =>{
    if(e.key === 'Escape'){
        UI.hideModals();
    }
});

// Custom select input
document.querySelectorAll('.option').forEach(op => {
    op.addEventListener('click', (e) => {
        const option = e.target;

        option.parentElement.querySelector('.option.active')?.classList.remove('active');
        option.classList.add('active');
    });
});