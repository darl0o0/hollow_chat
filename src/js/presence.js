import { socket, userDataReady} from "./init/main.js";

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
    if(id){
        socket.emit('register_user', id);
    }
});