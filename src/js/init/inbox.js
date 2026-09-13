import { UI } from "../ui-helpers.js";

document.addEventListener('DOMContentLoaded', async function(){

    // List conversations
    const res = await UI.sendGet('/src/api/conversations');
    var buffer = "";

    if(res.response != null && Array.isArray(res.response)){
        const sortedConversations = res.response.sort((a, b) => {
            return new Date(b.lastMsg.time) - new Date(a.lastMsg.time)
        });
        sortedConversations.forEach(conversation => {
            const isOnline = eval(conversation.isOnline) ? 'visible' : 'hidden';
            var username;

            var lastMsg;
            var badgeVsibility;
            var lastMsgColor;
            var lastMsgWeight;
            
            if(conversation.username != null){
                username = conversation.username;
            }
            else{
                username = "User not found"
            }

            if(conversation.lastMsg.sid != id){
                lastMsg = conversation.lastMsg.msg;

                if(!eval(conversation.lastMsg.isRead)){
                    badgeVsibility = 'visiblie';
                    lastMsgColor = '#d5d6c3';
                    lastMsgWeight = 'bolder';
                }
                else{
                    badgeVsibility = 'hidden';
                    lastMsgColor = '#a8a8a8';
                    lastMsgWeight = '400';
                }
            }
            else{
                lastMsg = `You: ${conversation.lastMsg.msg}`;
                badgeVsibility = 'hidden';
            }

            buffer += `
                <div class='user-card u-${conversation.id}'>
                    <a href='/chat?r=${conversation.id}' class='profile-link'></a>
                        <div class='pfp'>
                            <img class='pfp-img' src='/src/assets/pfps/${conversation.img}'>
                            <div class='online-status' style='visibility: ${isOnline}'></div>
                        </div>
                        <div class='label-wrapper'>
                            <div class='username-label'>${username}</div>
                            <span class='sub-label' style='color:${lastMsgColor}; font-weight: ${lastMsgWeight}'>${lastMsg}</span>
                        </div>
                    <div class='btn-wrapper-inbox'>
                        <div class='unread-msg' style='visibility: ${badgeVsibility}'></div>
                        <button class='btn-inbox'><i class='fa fa-ellipsis-h' aria-hidden='true'></i></button>
                    </div>
                </div>
            `;
        });
    }
    else{
        buffer = "<center>Nothing here.</center>";
    }
    document.querySelector('.list').innerHTML = buffer;
});