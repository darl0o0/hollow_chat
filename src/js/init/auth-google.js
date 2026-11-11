import { UI, HTTP, Fetch, Form, Check, HTML } from "../helpers.js";

function loadGoogleScript(){
    return new Promise((resolve, reject) => {
        if(window.google && window.google.accounts){
            resolve(window.google);
            return;
        }

        const scriptExists = document.querySelector('.google-gsi');
        if(scriptExists){
            scriptExists.addEventListener('load', () => resolve(window.google));
            scriptExists.addEventListener('error', () => reject);
            return;
        }

        const script = document.createElement('script');
        script.className = 'google-gsi';
        script.src = 'https://accounts.google.com/gsi/client';
        script.async = true;
        script.defer = true;
        script.onload = () => resolve(window.google);
        script.onerror = () => reject;
        document.head.appendChild(script);
    });
}

const elBtnGoogle = document.querySelector('.btn.google');
elBtnGoogle.addEventListener('click', async () => {
    try{
        const google = await loadGoogleScript();

        const client = google.accounts.oauth2.initTokenClient({
            client_id: '381767228183-jfb6opbgrrc40gj2kcejj203974e7etv.apps.googleusercontent.com',
            scope: 'email profile',
            callback: async (response) => {
                if(response.access_token){
                    await handleGoogleAuth(response.access_token);
                }
            }
        });
        client.requestAccessToken();
    }
    catch(error){
        console.error(error);
    }
});

async function handleGoogleAuth(accessToken){
    try{
        const res = await HTTP.send('/src/api/auth/google/', 'POST', { token: accessToken });
        
        if(!res.ok){
            const err = new Error(res.response);
            err.isFormFailure = true;

            throw err;
        }

        UI.alert(true, 'Redirecting...', 2, document.querySelector('form').parentElement);
        setTimeout(() => {
            window.location.replace('/');
        }, 2000);
    }
    catch(error){
        console.error(error);
    }
}