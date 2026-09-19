import { UI } from "../ui-helpers.js";
import { socket, userDataReady} from "./main.js";

let me = null;
let id = null;

document.addEventListener('DOMContentLoaded', async function(){
    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }

    // List conversations
    let buffer = "";
    try{
        const conversations = await UI.sendGet('/src/api/conversations');
    
        if(conversations.response != null && Array.isArray(conversations.response)){
            const sortedConversations = conversations.response.sort((a, b) => {
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
                        badgeVsibility = 'visibile';
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
                    <div class='user-card u-${conversation.id}' data-last-msg='${lastMsg}'>
                        <a href='/chat?q=${conversation.id}' class='profile-link'></a>
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
    }
    catch(error){
        console.error(error);
    }
    document.querySelector('.list').innerHTML = buffer;
});

// Listen for real-time presence changes broadcasted by server.js
socket.on('user_status_change', (data) => {
    const statusDot = document.querySelector(`.u-${data.userId} .online-status`);
    
    if(statusDot){
        statusDot.style.visibility = data.isOnline ? "visible" : "hidden";
    }
});

// When a new message is received class as yours or the other user's && as read or unread
socket.on('new_message', (data) => {

    const targetId = (data.sid == id)? data.rid : data.sid;
    const listContainer = document.querySelector('.list');
    const conversationCard = document.querySelector(`.u-${targetId}`);
    
    if(conversationCard){
        listContainer.prepend(conversationCard);
    
        const subLabel = document.querySelector(`.u-${targetId} .sub-label`);
        const isUnreadMsg = document.querySelector(`.u-${targetId} .unread-msg`);
    
        const lastMsgText = (data.sid == id) ? `You: ${data.message}` : data.message;
        conversationCard.setAttribute('data-last-msg', lastMsgText);
        
        if(subLabel){
            subLabel.innerHTML = lastMsgText;
            if(data.sid == id){
                isUnreadMsg.style.visibility = 'hidden';
                subLabel.style.color = '#a8a8a8';
                subLabel.style.fontWeight = '400';
            }
            else{
                isUnreadMsg.style.visibility = 'visible';
                subLabel.style.color = '#d5d6c3';
                subLabel.style.fontWeight = 'bolder';
            }
        }
    }
});

// Class the last message as read as needed
socket.on('message_marked_read', (data) => {
    const subLabel = document.querySelector(`.u-${data.readTo} .sub-label`);
    const isUnreadMsg = document.querySelector(`.u-${data.readTo} .unread-msg`);

    if(data.readBy == id){
        isUnreadMsg.style.visibility = 'hidden';
        subLabel.style.color = '#a8a8a8';
        subLabel.style.fontWeight = '400';
    }
});

// When the other user is typing
socket.on('typing_back', (data) => {
    const targetId = (data.typedBy == id)? data.typedTo : data.typedBy;

    const subLabel = document.querySelector(`.u-${targetId} .sub-label`);
    const conversationCard = document.querySelector(`.u-${targetId}`);

    if(data.typedBy == targetId && data.typedTo == id){
        if(data.isTyping){
            subLabel.innerHTML = 'Typing...';
        }
        else{
            subLabel.innerHTML = conversationCard.getAttribute('data-last-msg') || '';
        }
    }
});