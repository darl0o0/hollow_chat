let debounceTimer;
const formState = new WeakMap();
const defaultProfilePic = '03cde714caceaa40df348cc8a5ff14ba.jpg';

export const UI = {

    //The error function
    alert: function(type, alertText, duration = 5.1, container = document){

        const placeholder = container.querySelector('.alert-placeholder');
        placeholder.insertAdjacentHTML('afterend', `
            <div class="alert ${type ? 'ok' : 'error'}">
                <div class="cooldown-circle" style='animation-duration: ${duration}s'></div>
                <span class="alert-text">${HTML.escapeHTML(alertText)}</span>
            </div>
        `); 

        const alert = container.querySelector('.alert');

        setTimeout(() => {
            alert.remove();
        }, (duration*1000 - 100));
    },

    // Global elements
    globalElements: function(){
        if(document.querySelector('.modal-requests')) return;
        let countNot = Number(document.querySelector('.notifications-action .num-of').textContent);
        countNot = (!isNaN(countNot) && countNot > 0) ? `(${countNot})` : '';

        const htmlNotificationsModal = `
            <div class="modal modal-requests hidden">
                <div class="background"></div>
                <div class="container">
                    <h1><i class="fa-solid fa-bell"></i>Notifications</h1>
                    <div class="wrap-title">
                        <span class="title">Friend requests <span class="not-count">${countNot}</span></span>
                        <div class="sep"></div>
                    </div>
                    <div class="list"></div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', htmlNotificationsModal);
    },
    
    // Hide the on-screen modals
    hidemodal: function(){
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.add('hidden');
        });
        document.querySelector('.main').classList.remove('filter-blur');
    }
}

export const HTTP = {

    // Return multiple users data
    fetchUsersBatch: async function(userIds){
        if(!userIds || userIds.length == 0) return [];

        const search = await this.sendPost('/src/api/batch-fetch/', {
            q: userIds
        });

        if(search.status == 'ok'){
            return search.response;
        }

        return [];
    },
    
    // Strict post request
    sendPost: async function(url, data = {}) {
        const csrfToken = Fetch.getCookie('csrf_token');

        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        };

        let body;
    
        if(data instanceof FormData){
            body = data;
        }
        else{
            body = JSON.stringify(data);
            headers['Content-Type'] = 'application/json';
        }
    
        const response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: body,
            credentials: 'include'
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
            },
            credentials: 'include'
        });
    
        try{
            return await response.json();
        }
        catch(error){
            console.error("Server returned non-JSON response:", response);
            throw new Error("Server returned HTML instead of JSON (likely session expired or PHP error).");
        }
    }
}

export const Fetch = {

    // Retrieve cookie value by name
    getCookie: function(name){
        const match = document.cookie.match(new RegExp('(^|;\\s*)' + name + '=([^;]+)'));
        return match ? match[2] : '';
    },

    // Select a user via ID
    selectUserById: function(argId, list){
        const user = list.find(u => u.id == argId);
        if(!user) return null;

        return {
            id: user.id,
            username: user.username,
            img: user.img,
            bio: user.bio ? user.bio : "No Bio."
        };
    },

    // Search for a specific object in a list
    searchObjectFromList: function(query, list, key){
        return new Promise((resolve) => {
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                const filteredList = list.filter(object => {
                    const keyMatch = object[key] && object[key].toLowerCase().includes(query);
                    return keyMatch;
                });

                resolve(filteredList.length > 0 ? filteredList : null);
            }, 300);
        });
    },

    getObjectSize: function(obj){
        const str = JSON.stringify(obj);
        return new Blob([str]).size;
    }
}

export const Form = {

    // Enable/Disable certain form
    isFormEnabled: function (form, isEnabled){
        formState.set(form, isEnabled);

        const submitBtn = form.querySelectorAll('button.submit, button.next');
        submitBtn.forEach(btn => {
            btn.classList.toggle('btn-disabled', !isEnabled);
        });

        return isEnabled;
    },

    // Check form state
    getFormState: function(form){
        return formState.has(form) ? formState.get(form) : true;
    },

    // Convert formData into JSON object
    formToJson: function(formData){
        const obj = {};

        formData.forEach((value, key) => {
            if(Object.prototype.hasOwnProperty.call(obj, key)){
                if(!Array.isArray(obj[key])){
                    obj[key] = [obj[key]];
                }
                obj[key].push(value);
            }
            else{
                obj[key] = value;
            }
        });

        return obj;
    },

    // Initialize a form
    initForm: function(form, action, updatePasswordManager, redirect){
        this.isFormEnabled(form, false);

        const isDistinct = form.classList.contains('distinct');
        
        const elInputs = form.querySelectorAll('.input');
        const elRequiredInputs = form.querySelectorAll('.required');
        const elDistinctInputs = form.querySelectorAll('.distinct');

        const elCheckbox = form.querySelector('.required[type="checkbox"]');
        const elSubmitBtn = form.querySelector('.submit');

        elInputs.forEach(input => {
            const eventType = input.type == 'checkbox' ? 'change' : 'input'
            input.addEventListener(eventType, () => {
                const isEmpty = Array.from(elRequiredInputs).some(i => i.value.trim().length == 0);

                let isSame = false;
                if(isDistinct){
                    isSame = Array.from(elDistinctInputs).every(i => i.value?.trim() === i.dataset.initial);
                }

                const isChecked = elCheckbox ? elCheckbox.checked : true;

                const isEnabled = !isEmpty && !isSame && isChecked;
                this.isFormEnabled(form, isEnabled);
            });
        
            input.addEventListener('keydown', (e) => {
                if(e.key === 'Enter'){
                    e.preventDefault();
                    const elNextBtn = form.querySelector('.next');

                    if(elNextBtn && elNextBtn.offsetParent !== null){
                        elNextBtn.click();
                    }
                    else{
                        this.submitForm(e, form, action, updatePasswordManager, redirect);
                    }
                }
            });
        });

        elSubmitBtn.addEventListener('click', (e) => {
            this.submitForm(e, form, action, updatePasswordManager, redirect);
        });
    },

    // Submit a form
    submitForm: async function(e, form, action, updatePasswordManager, redirect){
        e.preventDefault();

        if(!this.getFormState(form)) return;

        const isDistinct = form.classList.contains('distinct');
        const elFormParent = form.parentElement;
        try{
            const elPrimaryInputs = form.querySelectorAll('.required');
            if(Array.from(elPrimaryInputs).some(i => i.value.trim().length == 0)){
                throw new Error("All inputs are required.");
            }

            const elUsername = form.querySelector('.username');
            const username = elUsername?.value.trim();
            if(elUsername && username.includes(" ")){
                throw new Error("Username cannot contain space.");
            }

            const elEmail = form.querySelector('.email');
            const email = elEmail?.value.trim();
            if(elEmail && !Check.isValidEmail(email)){
                throw new Error("Please enter a valid email.");
            }

            const elPasswords = form.querySelectorAll('.password');
            if(elPasswords.length > 0){
                const hasShortPassword = Array.from(elPasswords).some(i => i.value.trim().length < 8);
                if(hasShortPassword){
                    throw new Error("Please provide an 8 characters long password.");
                }

                const elMatchPasswords = form.querySelectorAll('.password.match');
                if(elMatchPasswords.length >= 2){
                    const values = Array.from(elMatchPasswords).map(i => i.value.trim());
                    const firstValue = values[0];
                    const allMatch = values.every(val => val === firstValue);
                    if(!allMatch){
                        throw new Error("Passwords do not match.");
                    }
                }
            }

            const formData = new FormData(form);
            const hasFileInput = Boolean(form.querySelector('input[type="file"]'));

            const data = hasFileInput ? formData : this.formToJson(formData);
            const res = await HTTP.sendPost(action, data);
        
            if(res.status == 'error'){
                throw new Error(res.response);
            }
            else if(res.status == 'ok'){
                UI.alert(true, res.response, 2, elFormParent);
                this.isFormEnabled(form, false);

                if(isDistinct){
                    const elDistinctInputs = form.querySelectorAll('.distinct');
                    elDistinctInputs.forEach(input => {
                        input.setAttribute('data-initial', input.value.trim());
                    });
                }
                
                if(updatePasswordManager && window.PasswordCredential){
                    const elPassword = form.querySelector('.password');
                    const password = elPassword.value.trim();
                    const cred = new PasswordCredential({
                        id: email,
                        password: password
                    });
                    navigator.credentials.store(cred);
                }
                if(redirect){
                    setTimeout(() => {
                        window.location.replace(redirect);
                    }, 2000);
                }
            }
        }
        catch(error){
            this.isFormEnabled(form, false);

            if(error instanceof TypeError || error instanceof SyntaxError){
                console.error(error);
                UI.alert(false, "An unexpected error occurred. Please try again.", undefined, elFormParent);
            }
            else{
                UI.alert(false, error.message, undefined, elFormParent);
            }
            
            setTimeout(() => {
                this.isFormEnabled(form, true);
            }, 5000);
        }
    }
}

export const Check = {

    //Check if the email is valid
    isValidEmail: function(email){
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email.trim());
    }
}

export const performanceTimer = {
    start(){
        return performance.now();
    },
    stop(startTime, label = 'Execution'){
        const duration = (performance.now() - startTime).toFixed(2);
        console.log(`[Performance] ${label}: ${duration}ms`);
        return duration;
    }
}

export const HTML = {
    escapeHTML: function(str){
        if(!str) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    },

    userCard: function(type, id, username, img, isOnline, subLabel = {}, options = {}){
        let bufferPfps;
        if(Array.isArray(img) && img.length > 1){
            bufferPfps = `
                <img src='/src/assets/pfps/${this.escapeHTML(img[0] ?? defaultProfilePic)}'>
                <img src='/src/assets/pfps/${this.escapeHTML(img[1] ?? defaultProfilePic)}'>
            `;
        }
        else{
            bufferPfps = `
                <img src='/src/assets/pfps/${this.escapeHTML(img ?? defaultProfilePic)}'>
            `;
        }

        const online = isOnline ? 'online' : 'offline';

        subLabel.text = subLabel.text ? subLabel.text : 'No bio.';

        let isLink = false;
        if(type == 'link'){
            isLink = true;
        }

        let bufferBtn = '';
        if(options.hasBtn){
            switch(options.btnType){
                case 'more':
                    bufferBtn = `
                        <div class='wrap-btn-inbox'>
                            <div class='unread-msg'></div>
                            <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
                        </div>
                    `;
                break;
                case 'dilemma':
                    bufferBtn = `
                        <div class='wrap-btn-requests'>
                            <form method='post' class='accept-form' data-id='${this.escapeHTML(id)}'>
                                <button class='btn-requests'><i class='fa fa-check' aria-hidden='true'></i></button>
                            </form>
                            <form method='post' class='decline-form' data-id='${this.escapeHTML(id)}'>
                                <button class='btn-requests'><i class='fa fa-times' aria-hidden='true'></i></button>
                            </form>
                        </div>
                    `;
                break;
            }
        }

        return `
            <${isLink ? 'a' : 'div'}
            class='user-card ${type} ${subLabel.isLastMsg ? (subLabel.isRead ? 'seen' : 'unseen') : ''} u-${this.escapeHTML(id)}'
            ${subLabel.isLastMsg ? ` data-last-msg='${this.escapeHTML(subLabel.text)}'` : ''} 
            user-id='${this.escapeHTML(id)}'
            ${isLink ? ` href='profile/?q=${this.escapeHTML(id)}'` : ''} 
            tabindex='0'>
                ${options.hasPlus ? `
                    <div class='select-indicator'>
                        <i class="fa-solid fa-plus"></i>
                    </div>` : ''}
                <div class='wrap-pfp ${online}'>
                    ${bufferPfps}
                    <div class='online-status'></div>
                </div>
                <div class='wrap-label'>
                    <div class='username-label'>${this.escapeHTML(username)}</div>
                    <span class='sub-label'>${this.escapeHTML(subLabel.text)}</span>
                </div>
                ${bufferBtn}
            </${isLink ? 'a' : 'div'}>
        `;
    },

    message: function(msg, isMe, boundMsg = {}, sender, isSeen, options = {}){
        const addGap = boundMsg.isNextFromDifferentUser ? ' section-gap' : '';

        if(isMe){
            return `
                <div class='wrap-msg wrap-me mid-${this.escapeHTML(msg.id)}${isSeen ? ' seen' : ''}${addGap}${boundMsg.isPreviousFromDifferentUser ? ' first-msg' : ''}${boundMsg.isNextFromDifferentUser ? ' last-msg' : ''}'>
                    <div class='others-wrap'>
                        <div class='seen-dot'></div>
                    </div>
                    <div class='msg me'>${this.escapeHTML(msg.msg)}</div>
                </div>
            `;
        }
        else{
            const showPfp = (boundMsg.isNextFromDifferentUser || options.isLive) ? 'visible' : 'hidden';

            return `
                <div class='wrap-msg wrap-you mid-${this.escapeHTML(msg.id)}${addGap}${boundMsg.isPreviousFromDifferentUser ? ' first-msg' : ''}${boundMsg.isNextFromDifferentUser ? ' last-msg' : ''}'>
                    <div class='wrap-pfp'>
                        <a href='/profile/?q=${this.escapeHTML(msg.sid)}' class='pfp-link ${showPfp}'>
                            <img src='/src/assets/pfps/${this.escapeHTML(sender.img ?? defaultProfilePic)}' class='msg-pfp'>
                        </a>
                    </div>
                    <div class='msg you'>${this.escapeHTML(msg.msg)}</div>
                </div>
            `;
        }
    }
}