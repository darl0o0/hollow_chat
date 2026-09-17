export const UI = {

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
            body = data;
        }
        else{
            // Convert plain JS objects into FormData so PHP populates $_POST automatically
            body = new FormData();
            for(const key in data){
                if(Object.prototype.hasOwnProperty.call(data, key)){
                    body.append(key, data[key]);
                }
            }
        }
    
        const response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: body
        });
    
        const text = await response.text();

        if(!text || !text.trim()){
            return {};
        }

        return JSON.parse(text);
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

    // Global elements
    globalElements: function(numOfNot){
        if(document.querySelector('.requests-model')) return;
        numOfNot = (numOfNot > 0) ? `(${numOfNot})` : '';
        const modalHTML = `
            <div class="model requests-model">
                <div class="background"></div>
                <div class="container">
                    <h1><i class="fa-solid fa-bell"></i>Notifications</h1>
                    <div class="title-sep-wrapper">
                        <span class="title">Friend requests <span class="not-count">${numOfNot}</span></span>
                        <div class="sep"></div>
                    </div>
                    <div class="list"></div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    },

    // Update notifications count
    updateNotCount: function(count){
        let countLabel = (count > 0) ? `(${count})` : '';
        const countLabelEl = document.querySelector('.requests-model .not-count');
        const notificationsBadge = document.querySelector('.notifications-action .num-of');

        if(notificationsBadge){
            if(count > 0){
                notificationsBadge.style.display = 'flex';
                notificationsBadge.textContent = count > 99 ? '+99' : count;
            }
            else{
                notificationsBadge.style.display = 'none';
                notificationsBadge.textContent = '0';
            }
        }
        countLabelEl.textContent = countLabel;
    },
    
    // Hide the on-screen models
    hideModel: function(){
        document.querySelectorAll(".model").forEach(model => {
            model.style.visibility = "hidden";
        })
        document.querySelector(".main").style.filter = "none";
    }
};