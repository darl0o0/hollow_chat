import { UI, HTTP, Fetch, Form, Check, HTML } from "../helpers.js";
import { socket, userDataReady} from "./main.js";

// User objects
let me = null;

// IDs
let id = null;

// Lists of objects
let users;

// Elements
const elListUsers = document.querySelector('.home-list');
const elPanel = document.querySelector('.panel');

// Timers
let timerHeart;

const post = {
    id: '435078f543gd56',
    author_id: 'c0a2999dbc990361',
    time: 1793396587,
    content: {
        text: null,
        img: 'bilal.jpg'
    },
    isLikedByMe: false,
    count: {
        like: 100,
        comment: 50,
        repost: 60,
        share: 80
    }
}

elListUsers.skeleton('post');

setTimeout(async () => {
    elListUsers.innerHTML = HTML.post(await HTTP.fetchUsersBatch(post.author_id), post);
}, 400);

document.addEventListener('DOMContentLoaded', async function(){
    try{
        me = await userDataReady;
        id = me.id;
    }
    catch(error){
        console.error(error);
    }
});

document.addEventListener('click', (e) => {
    const elBtnLike = e.target.closest('.action.like');
    if(elBtnLike){
        elBtnLike.classList.toggle('liked');
        elBtnLike.querySelector('i').classList.toggle('fa-solid');
    }
});

document.addEventListener('dblclick', (e) => {
    const elPostContent = e.target.closest('.post .content');
    if(elPostContent){
        const elHeart = elPostContent.querySelector('.heart');
        const elBtnLike = elPostContent.parentElement.querySelector('.actions .like');
        elBtnLike.classList.add('liked');
        elBtnLike.querySelector('i').classList.add('fa-solid');
        if(elHeart){
            clearTimeout(timerHeart);
            elHeart.remove();
        }
        
        timerHeart = setTimeout(() => {
            elPostContent.querySelector('.heart').remove();
        }, 1200);
        elPostContent.insertAdjacentHTML('afterbegin', `
            <div class='heart'>
                <i class="fa-solid fa-heart"></i>
            </div>
        `);
    }
});

// Users presence
socket.on('presence', (data) => {
    const pfp = document.querySelector(`.u-${data.userId} .wrap-pfp`);
    
    if(data.isOnline){
        pfp?.classList.replace('offline', 'online');
    }
    else{
        pfp?.classList.replace('online', 'offline');
    }
});