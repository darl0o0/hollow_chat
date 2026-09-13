import { UI } from "../ui-helpers.js";

const id = document.querySelector('meta[name="id"]').content;
const rid = document.querySelector('meta[name="rid"]').content;

document.addEventListener('DOMContentLoaded', async function(){

    // Load chat when the page is loaded
    const res = await UI.sendGet('/src/api/messages/', {rid: rid});
    if(Array.isArray(res.response)){
        var buffer = '';
        res.response.forEach(message => {
            if(message.sid == id){
                const isRead = eval(message.isRead) ? '#e9e4c8' : 'none';

                buffer += `
                    <div class='msg-wrap'>
                        <div class='isRead unread' style='background: ${isRead};'></div>
                        <div class='s m'>${message.msg}</div>
                    </div>
                `;
            }
            else if(message.sid == rid){
                buffer += `
                    <div class='r m'>${message.msg}</div>
                `;
            }
        });
        document.querySelector('.inner-field').innerHTML = buffer;
        field.scrollTop = field.scrollHeight;
    }
    else if(res.response == null){
        document.querySelector('.inner-field').innerHTML = `
            <center>Start chatting now.</center>
        `;
    }
});

// Send message
document.querySelector('.send-form').addEventListener('submit', async function(e){
    e.preventDefault();
    if(document.querySelector('.msg-input').value){
        const formData = new FormData(e.target);
        
        //HTTP Request
        await UI.sendPost('/src/api/messages/', formData);

        // Make the input empty && scroll to the bottom of the field && isTyping = 0
        document.querySelector('.msg-input').value = null;
        document.querySelector('.field').scrollTop = document.querySelector('.field').scrollHeight;
        isTyping = false;
    }
});