let debounceTimer;
const formState = new WeakMap();

export const UI = {

    //The error function
    alert: function(type, alertText, duration = 5.1, container = document){

        const placeholder = container.querySelector('.alert-placeholder');
        placeholder.insertAdjacentHTML('afterend', `
            <div class="alert ${type ? 'ok' : 'error'}">
                <div class="cooldown-circle" style='animation-duration: ${duration}s'></div>
                <span class="alert-text">${alertText}</span>
            </div>
        `); 

        const alert = container.querySelector('.alert');

        setTimeout(() => {
            alert.remove();
        }, (duration*1000 - 100));
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
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        };
    
        let body;
    
        if(data instanceof FormData){
            body = data;
        }
        else{
            // Convert plain JS objects into FormData so PHP populates $_POST automatically
            body = JSON.stringify(data);
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

            const data = this.formToJson(new FormData(form));
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
            UI.alert(false, error.message, undefined, elFormParent);
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