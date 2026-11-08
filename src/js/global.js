import { UI, HTTP, Fetch, Form, Check, performanceTimer, HTML } from "./helpers.js";

// Show notifications modal
document.addEventListener('click', (e) => {
    const modalBtn = e.target.closest('.modal-show');
    if(modalBtn){
        const modalName = modalBtn.getAttribute('link-modal');

        const modal = document.querySelector(`.${modalName}`);
        const main = document.querySelector('.main');

        modal.classList.remove('hidden');
        main.classList.add('filter-blur');
    }

    const hideModalBtn = e.target.closest('.modal-hide');
    if(hideModalBtn){
        UI.hidemodal();
    }
});

// Hide notifications modal
document.addEventListener('keydown', (e) =>{
    if(e.key === 'Escape'){
        UI.hidemodal();
    }
});

document.addEventListener('click', (e) => {
    if(e.target.matches('.background')){
        UI.hidemodal();
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

// Custom select input
document.querySelectorAll('.option').forEach(op => {
    op.addEventListener('click', (e) => {
        const option = e.target;

        option.parentElement.querySelector('.option.active')?.classList.remove('active');
        option.classList.add('active');
    });
});