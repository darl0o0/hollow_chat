const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const mysql = require('mysql2/promise');
require('dotenv').config();

const db = mysql.createPool({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'hollow_chat'
});

const app = express();
app.use(express.json());

const server = http.createServer(app);
const io = new Server(server, {
    cors: { origin: "*" }
});

const INTERNAL_TOKEN = process.env.INTERNAL_TOKEN;

const activeTypingUsers = new Map();
const disconnectTimers = new Map();

io.on('connection', (socket) => {

    // Go online
    socket.on('register', async (userId) => {
        if (!userId) return;

        socket.userId = userId;
        socket.join(userId); // Join room for multi-tab support

        if(disconnectTimers.has(userId)){
            clearTimeout(disconnectTimers.get(userId));
            disconnectTimers.delete(userId);
        }
        else{
            await db.query('UPDATE users SET online = 1 WHERE id = ?', [userId]);
            io.emit('presence', { userId: userId, isOnline: true });
        }

        activeTypingUsers.forEach((typingData) => {
            const { by, to, isTyping, isGroup, groupId } = typingData;
            
            if(!isGroup && to == userId){
                io.to(to).emit('typing:pong', {
                    by,
                    to,
                    isTyping
                });
            }
            else if(Array.isArray(to) && to.includes(userId)){
                to.forEach(id => {
                    io.to(id).emit('typing:pong', {
                        by,
                        to: groupId,
                        isTyping,
                        isGroup
                    });
                });
            }
        });
    });

    // Typing
    socket.on('typing:ping', (data) => {
        const { by, to, isTyping, isGroup, groupId } = data;

        if(isTyping) activeTypingUsers.set(by, data);
        else activeTypingUsers.delete(by);

        if(!isGroup) io.to(to).emit('typing:pong', { by, to, isTyping });
        else{
            to.forEach(id => {
                io.to(id).emit('typing:pong', { by, to: groupId, isTyping, isGroup });
            });
        }
    });

    // Seen
    socket.on('seen:ping', async (data) => {
        const { by, to } = data;

        const query = 'update messages set isRead = 1 where sid = ? and rid = ? and isRead = 0';
        const [result] = await db.query(query, [to, by]);
        const count = result.affectedRows;

        // Send to partner
        io.to(to).emit('seen:pong', { by, count });

        // Send to current user
        io.to(by).emit('seen:pong', { by, to, count });
    });

    // Go offline (Page Reload / Tab Close)
    socket.on('disconnect', async () => {
        const userId = socket.userId;

        if(!userId) return;

        // Reset typing status on-reload
        if(activeTypingUsers.has(userId)){
            const typingData = activeTypingUsers.get(userId);
            const { to, isGroup, groupId } = typingData;

            if(!isGroup) io.to(to).emit('typing:pong', { by: userId, to, isTyping: false });
            else{
                to.forEach(id => {
                    io.to(id).emit('typing:pong', {
                        by: userId,
                        to: groupId,
                        isTyping: false,
                        isGroup
                    });
                })
            }

            activeTypingUsers.delete(userId);
        }

        if(disconnectTimers.has(userId)) clearTimeout(disconnectTimers.get(userId));

        // If disconnected for 3secs set user offline
        const timer = setTimeout(async () => {
            disconnectTimers.delete(userId);

            const remainingSockets = await io.in(userId).fetchSockets();
            if(remainingSockets.length === 0){
                await db.query('update users set online = 0 where id = ?', [userId]);
                io.emit('presence', { userId: userId, isOnline: false });
            }
        }, 3000);

        disconnectTimers.set(userId, timer);
    });
});

// Handle messages
app.post('/broadcast', (req, res) => {

    const token = req.headers['x-internal-token'];
    if(token !== INTERNAL_TOKEN){
        return res.status(403).json({ status: 'error', message: 'Unauthorized' });
    }

    const { mid, sid, rid, msg, isGroup, groupId } = req.body;

    // Send to partner
    if(!isGroup) io.to(rid).emit('message', { mid, sid, rid, msg });
    else{
        rid.forEach(id => {
            io.to(id).emit('message', { mid, sid, msg, isGroup, groupId});
        });
    }

    // Send to current user
    if(!isGroup) io.to(sid).emit('message', { mid, sid, rid, msg });
    else io.to(sid).emit('message', { mid, sid, groupId, msg });

    res.json({ status: 'success' });
});

server.listen(3000, () => console.log('Listening to sockets on port 3000'));

// Handling server shutdown
process.on('SIGINT', async () => {
    console.log('\nShutting down server...');

    server.close(async () => {
        console.log('Socket server closed.');

        try{
            await db.end();
            console.log('Database connection closed.');

            process.exit(0);
        }
        catch(error){
            console.error('Error during shutdown:', error);
            process.exit(1);
        }
    });

    setTimeout(() => {
        console.error('Failed to close connection, Forcing server to shut down...');
        process.exit(1);
    }, 5000);
});