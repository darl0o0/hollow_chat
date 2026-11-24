let debounceTimer;
const formState = new WeakMap();
export const defaultProfilePic = '03cde714caceaa40df348cc8a5ff14ba.jpg';

export const UI = {

    //The error function
    alert: function(type, alertText, duration = 5.1, container = document){

        const placeholder = container.querySelector('.alert-placeholder');
        placeholder.insertAdjacentHTML('afterend', `
            <div class="form-alert ${type ? 'ok' : 'error'}">
                <div class="cooldown-circle" style='animation-duration: ${duration}s'></div>
                <span class="alert-text">${HTML.escapeHTML(alertText)}</span>
            </div>
        `); 

        const alert = container.querySelector('.form-alert');

        setTimeout(() => {
            alert.remove();
        }, (duration*1000 - 100));
    },
    
    // Hide the on-screen modals
    hideModals: function(){
        document.querySelectorAll('.modal').forEach(modal => {
            modal.remove();
        });
        document.querySelector('.main').classList.remove('filter-blur');
    }
}

export const Convert = {
    timeAgo: function(timestamp){

        if(timestamp < 60) return 'Just now';

        const minutesAgo = Math.floor(timestamp / 60);
        if(minutesAgo < 60) return `${minutesAgo}m ago`;

        const hoursAgo = Math.floor(minutesAgo / 60);
        if(hoursAgo < 24) return `${hoursAgo}h ago`;

        const daysAgo = Math.floor(hoursAgo / 24);
        if(daysAgo < 30) return `${daysAgo}d ago`;

        const monthsAgo = Math.floor(daysAgo / 30);
        if(monthsAgo < 12) return `${monthsAgo}mo ago`;

        const yearsAgo = Math.floor(monthsAgo / 12);
        return `${yearsAgo}y ago`;
    }
}

export const HTTP = {

    // Return multiple users data
    fetchUsersBatch: async function(userIds){
        if(!userIds || userIds.length == 0) return [];

        const search = await this.send('/src/api/batch-fetch/', 'POST', {
            q: userIds
        });

        if(search.ok){
            return search.response;
        }

        return [];
    },

    // Send strict http request
    send: async function(url, method, data = {}, override = false){
        let input;
        let headers = {'X-Requested-With': 'XMLHttpRequest'};
        let body;

        if(override) headers = {...headers, 'X-HTTP-METHOD-OVERRIDE': override}

        if(method === 'GET'){
            const queryString = new URLSearchParams(data).toString();
            input = queryString ? `${url}?${queryString}` : url;

            headers = {
                ...headers
            };
        }
        else{
            input = url;

            headers = {
                ...headers,
                'X-CSRF-TOKEN': Fetch.getCookie('csrf_token')
            };
    
            if(data instanceof FormData){
                body = data;
            }
            else{
                body = JSON.stringify(data);
                headers['Content-Type'] = 'application/json';
            }
        }
        
        try{
            const http = await fetch(input, {
                method,
                headers,
                body,
                credentials: 'include'
            });

            return await http.json();
        }
        catch(error){
            console.error(error);
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
        const currentState = formState.get(form) || {};
        formState.set(form, { ...currentState, isEnabled });

        const submitBtn = form.querySelectorAll('button.submit, button.next');
        submitBtn.forEach(btn => {
            btn.classList.toggle('btn-disabled', !isEnabled);
        });

        return isEnabled;
    },

    // Check form state
    getFormState: function(form){
        const state = formState.get(form);
        if(state && typeof state === 'object'){
            return state.isEnabled !== undefined ? state.isEnabled : true;
        }
        return state !== undefined ? state : true;
    },

    setErrorCooldown: function(form, duration = 5000){
        const currentState = formState.get(form) || {};
        formState.set(form, { ...currentState, errorUntil: Date.now() + duration, isEnabled: false });
        this.isFormEnabled(form, false);
    },

    isInErrorCooldown: function(form){
        const state = formState.get(form);
        if(!state || !state.errorUntil) return false;
        return Date.now() < state.errorUntil;
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
    initForm: function(form, method, action, updatePasswordManager, redirect, options = {}){
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

                const isCooldown = this.isInErrorCooldown(form);

                const isEnabled = !isEmpty && !isSame && isChecked && !isCooldown;
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
                        this.submitForm(e, form, method, action, updatePasswordManager, redirect, options);
                    }
                }
            });
        });

        elSubmitBtn.addEventListener('click', (e) => {
            this.submitForm(e, form, method, action, updatePasswordManager, redirect, options);
        });
    },

    // Submit a form
    submitForm: async function(e, form, method, action, updatePasswordManager, redirect, options = {}){
        e.preventDefault();

        if(!this.getFormState(form)) return;

        const isDistinct = form.classList.contains('distinct');
        const elFormParent = form.parentElement;
        try{
            const elPrimaryInputs = form.querySelectorAll('.required');
            if(Array.from(elPrimaryInputs).some(i => i.value.trim().length == 0)){
                const err = new Error("All inputs are required.");
                err.isFormFailure = true;
                throw err;
                
            }

            const elUsername = form.querySelector('.username');
            const username = elUsername?.value.trim();
            if(elUsername && username.includes(" ")){
                const err = new Error("Username cannot contain space.");
                err.isFormFailure = true;
                throw err;
            }

            const elEmail = form.querySelector('.email');
            const email = elEmail?.value.trim();
            if(elEmail && !Check.isValidEmail(email)){
                const err = new Error("Please enter a valid email.");
                err.isFormFailure = true;
                throw err;
            }

            const elPasswords = form.querySelectorAll('.password');
            if(elPasswords.length > 0){
                const hasShortPassword = Array.from(elPasswords).some(i => i.value.trim().length < 8);
                if(hasShortPassword){
                    const err = new Error("Please provide an 8 characters long password.");
                    err.isFormFailure = true;
                    throw err;
                }

                const elMatchPasswords = form.querySelectorAll('.password.match');
                if(elMatchPasswords.length >= 2){
                    const values = Array.from(elMatchPasswords).map(i => i.value.trim());
                    const firstValue = values[0];
                    const allMatch = values.every(val => val === firstValue);
                    if(!allMatch){
                        const err = new Error("Passwords do not match.");
                        err.isFormFailure = true;
                        throw err;
                    }
                }
            }

            const formData = new FormData(form);

            if(options?.attach_data){
                for(const [key, value] of Object.entries(options.attach_data)){
                    formData.set(key, value);
                }
            }

            const hasFileInput = Boolean(form.querySelector('input[type="file"]'));
            const override = (hasFileInput && method == 'POST') ? 'PATCH' : false;

            const data = hasFileInput ? formData : this.formToJson(formData);
            const res = await HTTP.send(action, method, data, override);
        
            if(!res.ok){
                const err = new Error(res.response);
                if(res.code == 400) err.isFormFailure = true;

                throw err;
            }

            UI.alert(true, res.response, 2, elFormParent);
            this.isFormEnabled(form, false);

            if(isDistinct){
                const elDistinctInputs = form.querySelectorAll('.distinct');
                elDistinctInputs.forEach(input => {
                    input.setAttribute('data-initial', input.value.trim());
                });
            }

            if(redirect){
                setTimeout(() => {
                    window.location.replace(redirect);
                }, 2000);
            }
        }
        catch(error){
            this.setErrorCooldown(form);

            if(!error.isFormFailure){
                UI.alert(false, "An unexpected error occurred. Please try again.", undefined, elFormParent);
                console.error(error);
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
            class='user-card ${type} ${subLabel.isLastMsg ? (subLabel.seen ? 'seen' : 'unseen') : ''} u-${this.escapeHTML(id)}'
            ${subLabel.isLastMsg ? ` data-last-msg="${this.escapeHTML(subLabel.text)}"` : ''} 
            user-id='${this.escapeHTML(id)}'
            ${isLink ? ` href='/profile/?q=${this.escapeHTML(id)}'` : ''} >
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

    skeleton: function(type, device = null){
        switch(type){
            case 'card':
            return `
                <div class='skeleton skeleton-card'>
                    <div class='wrap-pfp'>
                        <div class='pfp'></div>
                    </div>
                    <div class='wrap-label'>
                        <div class='label'></div>
                        <div class='label'></div>
                    </div>
                </div>
            `;

            case 'post':
            return `
                <div class='skeleton skeleton-post'>
                    <div class="header">
                        <div class='wrap-pfp'>
                            <div class='pfp'></div>
                        </div>
                        <div class='wrap-label'>
                            <div class='label'></div>
                            <div class='label'></div>
                        </div>
                    </div>
                    <div class="content">
                        <div class="media"></div>
                    </div>
                </div>
            `;

            case 'header':
            return `
                <div class="skeleton profile-header ${device}-header">
                    <div class='wrap-pfp'>
                        <div class='pfp'></div>
                    </div>
                    <div class="wrap-label">
                        <div class='label'></div>
                        <div class='label'></div>
                    </div>
                </div>
            `;
        }
    },

    message: function(msg, isMe, boundMsg = {}, options = {}){

        const hasReaction = Boolean(msg.react?.emoji && msg.react.reactor);
        const reaction = hasReaction ? msg.react : null;

        const classMid = `mid-${this.escapeHTML(msg.id)}`;
        const classIsMe = isMe ? 'me' : 'you';
        const classSeen = msg.seen ? 'seen' : '';
        const classFirstMsg = boundMsg.isPreviousFromDifferentUser ? 'first-msg' : '';
        const classLastMsg = (boundMsg.isNextFromDifferentUser || boundMsg.isNextFromDifferentUser == null) ? 'last-msg' : '';
        const classHasReaction = hasReaction ? 'has-reaction' : '';
        const classOnlyEmoji = msg.only_emoji ? 'only-emoji' : '';

        const classWrapMsg = ['wrap-msg', classMid, classIsMe, classSeen, classFirstMsg, classLastMsg, classHasReaction, classOnlyEmoji].filter(Boolean).join(' ');

        const htmlWrapPfp = `
            <div class='wrap-pfp hidden'>
                <a href='/profile/?q=${this.escapeHTML(msg.sid)}' class='pfp-link'>
                    <img src='/src/assets/pfps/${this.escapeHTML(msg.sender.img ?? defaultProfilePic)}' class='msg-pfp'>
                </a>
            </div>
        `;

        const htmlReactionTag = `
            <div class='wrap-reaction-tag'>
                <div class='reaction-tag'>
                    <img src='/src/assets/pfps/${options.reactor_img}'>
                    <span>${reaction?.emoji}</span>
                </div>
            </div>
        `;

        return `
        <div class='${classWrapMsg}' mid='${this.escapeHTML(msg.id)}'>
            <div class='wrap-msg-others'>
                <div class='others-wrap'>
                    <div class='msg-actions interact-on-hover hidden'>
                        <button class='msg-action react'>
                            <i class="fa-regular fa-face-grin"></i>
                        </button>
                        <button class='msg-action reply'>
                            <i class="fa-solid fa-reply"></i>
                        </button>
                        <button class='msg-action more'>
                            <i class="fa-solid fa-ellipsis"></i>
                        </button>
                    </div>
                    ${isMe ? '<div class="seen-dot"></div>' : ''}
                </div>
                <div class='msg'>${this.escapeHTML(msg.text)}</div>
                ${!isMe ? htmlWrapPfp : ''}
            </div>
            ${hasReaction ? htmlReactionTag : ''}
        </div>
        `;
    },

    post: function(user, post){
        const time =  Math.floor(Date.now()/1000) - post.time;

        return `
            <div class='post'>
                <a class="header" href="/profile?q=${user.id}">
                    <div class='wrap-pfp'>
                        <img src='/src/assets/pfps/${user.img}'>
                    </div>
                    <div class='wrap-label'>
                        <div class='username-label'>${user.username}</div>
                        <span class='sub-label'>${Convert.timeAgo(time)}</span>
                    </div>
                </a>
                <div class="content">
                    ${post.content.text ? `<p>${post.content.text}</p>` : ''}
                    <div class="wrap-media">
                        ${post.content.img ? 
                            `
                                <img class='media' src='/src/assets/posts/${post.content.img}'>
                            ` 
                        : ''}
                    </div>
                </div>
                <div class="actions">
                    <button class="action like${post.isLikedByMe ? ' liked' : ''}">
                        <i class="${post.isLikedByMe ? 'fa-regular fa-solid' : 'fa-regular'} fa-heart"></i>
                        <span>${post.count.like}</span>
                    </button>
                    <button class="action comment">
                        <i class="fa-regular fa-comment"></i>
                        <span>${post.count.comment}</span>
                    </button>
                    <button class="action repost">
                        <i class="fa-solid fa-retweet"></i>
                        <span>${post.count.repost}</span>
                    </button>
                    <button class="action share">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>${post.count.share}</span>
                    </button>
                </div>
            </div>
        `;
    },

    header: function(device, usernameLabel, img, isOnline, subLabel, href = null){
        
        let isGroup = false;
        let bufferPfps;
        if(Array.isArray(img) && img.length > 1){
            isGroup = true;
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

        return `
            <a class="profile-header ${device}-header" ${href ? `href="${href}"` : ''}>
                <div class='wrap-pfp ${isOnline ? 'online' : 'offline'}'>
                    ${bufferPfps}
                    <div class='online-status'></div>
                </div>
                <div class="wrap-label">
                    <div class='username-label'>${HTML.escapeHTML(usernameLabel)}</div>
                    <span class="sub-label">${subLabel}</span>
                </div>
            </a>
        `;
    },

    injectModal: function(htmlModal, options = {}){
        UI.hideModals();
        const main = document.querySelector('.main');
        main.classList.add('filter-blur');

        main.insertAdjacentHTML('afterend', `
            <div class="modal ${options.class}">
                <div class="background"></div>
                <div class="container">
                    ${htmlModal}
                </div>
            </div>
        `);
    }
}

export const Get = {
    urlKey: function(key){
        const getParams = new URLSearchParams(window.location.search);
        let value = getParams.get(key) ? (getParams.get(key).length > 0 ? getParams.get(key) : null) : null;
        return value;
    }
}

// Skeleton screen method
Element.prototype.skeleton = function(contentType){
    const listHeight = this.clientHeight;

    let elHeight;

    switch(contentType){
        case 'card':
            elHeight = 72;
        break;
        case 'post':
            elHeight = listHeight * .5;
        break;
    }

    const count = Math.floor(listHeight / elHeight);

    for(let i = 0; i < count; i++){
        this.innerHTML += HTML.skeleton(contentType);
    }
}