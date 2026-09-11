const UI = {

    //The error function
    alert: function(type, alertText, duration, isForModel){
        var alert;
        var alertDuration;
        if(isForModel){
            alert = document.querySelector(`.model-alert`);
        }
        else{
            alert = document.querySelector(`.alert`);
        }
        if(duration == null){
            alertDuration = 5.1;
        }
        else{
            alertDuration = duration;
        }
        alert.style.display = "flex";
        alert.querySelector('.alert-text').innerHTML = alertText;
        switch(type){
            case true:
                alert.style.background = "#182a12";
                alert.style.color = "#73e584";
                alert.style.border = "1px solid #255c23";
                alert.querySelector('.cooldown-circle').style.background = "conic-gradient(#76e76c var(--angle), transparent var(--angle))";
            break;
            case false:
                alert.style.background = "#2a1215";
                alert.style.color = "#e57373";
                alert.style.border = "1px solid #5c2329";
                alert.querySelector('.cooldown-circle').style.background = "conic-gradient(#e7766c var(--angle), transparent var(--angle))";
            break;
        }
        alert.querySelector('.cooldown-circle').style.animationDuration = `${alertDuration}s`;
        setTimeout(() => {
            alert.style.display = "none";
            alert.querySelector('.alert-text').innerHTML = '';
        }, (alertDuration*1000 - 100));
    },
    
    // Strict post request
    sendPost: async function(url, data = {}) {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        };
    
        let body;
    
        if(data instanceof FormData){
            // Leave body as FormData; browser automatically sets multipart/form-data boundary header
            body = data;
        }else{
            // Handle plain JS objects
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(data);
        }
    
        const response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: body
        });
    
        return await response.text();
    },
    
    // Strict get request
    sendGet: async function(url, data = {}) {
        const queryString = new URLSearchParams(data).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
    
        const response = await fetch(fullUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
    
        return await response.json();
    },

    //Check if the email is valid
    isValidEmail: function(email){
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email.trim());
    },
    
    // Hide the on-screen models
    hideModel: function(){
        document.querySelectorAll(".model").forEach(model => {
            model.style.visibility = "hidden";
        })
        document.querySelector(".main").style.filter = "none";
    }
};

document.addEventListener('DOMContentLoaded', () => {

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

    document.querySelector('.notifications-action').addEventListener('click', () => {
        document.querySelector(".requests-model").style.visibility = "visible";
        document.querySelector(".main").style.filter = "blur(3px)";
    })

    document.querySelectorAll(".background").forEach(hideEvent => {
        hideEvent.onclick = UI.hideModel;
    });
    document.addEventListener('keydown', (e) =>{
        if(e.keyCode === 27){
            UI.hideModel();
        }
    });
});