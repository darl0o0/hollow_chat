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

    try{
        const getUsers = await HTTP.send('/src/api/users/', 'GET');
        users = getUsers.response;
    }
    catch(error){
        console.error(error);
    }
});

elPanel.querySelector('.search').addEventListener('input', async (e) => {
    const elWrapSearch = elPanel.querySelector('.wrap-search');
    const elSearchDropdown = elWrapSearch.querySelector('.search-dropdown');
    const query = e.target.value.trim();

    if(query == ''){
        if(elSearchDropdown) elSearchDropdown.remove();
        return;
    }

    try{
        const searchUsers = await Fetch.searchObjectFromList(query, users, 'username');
        let bufferSearchUsers = '';

        if(searchUsers){
            searchUsers.forEach(user => {
                bufferSearchUsers += HTML.userCard(
                    'link',
                    user.id,
                    user.username,
                    user.img,
                    false,
                    {text: user.bio}
                );
            });
        }
        else{
            bufferSearchUsers = '<center>No users found.</center>';
        }

        if(elSearchDropdown){
            elSearchDropdown.querySelector('.list').innerHTML = bufferSearchUsers;
        }
        else{
            elWrapSearch.insertAdjacentHTML('beforeend', `
                <div class="search-dropdown">
                    <div class="list">
                        ${bufferSearchUsers}
                    </div>
                </div>
            `);
        }
    }
    catch(error){
        console.error(error);
    }
});

elPanel.querySelector('.search').addEventListener('blur', () => {
    const elWrapSearch = document.querySelector('.wrap-search');
    const elSearchDropdown = elWrapSearch.querySelector('.search-dropdown');

    setTimeout(() => {
        if(elSearchDropdown) elSearchDropdown.classList.add('display-none');
    }, 200);
});

document.addEventListener('click', (e) => {
    const elWrapSearch = elPanel.querySelector('.wrap-search');
    if(elWrapSearch){
        const elSearchDropdown = elWrapSearch.querySelector('.search-dropdown');
        const isSearchClicked = e.target.closest('.search');
    
        if(elSearchDropdown){
            if(!isSearchClicked) elSearchDropdown.classList.add('display-none');
            else elSearchDropdown.classList.remove('display-none');
        }
    }

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