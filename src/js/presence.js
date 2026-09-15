import { socket } from "./init/main.js";

document.addEventListener('DOMContentLoaded', () => {
    const id = document.querySelector('meta[name="id"]').content;
    if(id){
        socket.emit('register_user', id);
    }
});