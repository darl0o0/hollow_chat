const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const mysql = require('mysql2/promise');

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

io.on('connection', (socket) => {

  // 1. MODIFIED: User joins a room named after their userId
  socket.on('register_user', async (userId) => {
      if (!userId) return;

      socket.userId = userId;
      socket.join(userId); // <--- Join room for multi-tab support

      await db.query('UPDATE users SET online = 1 WHERE id = ?', [userId]);

      // Broadcast to everyone on home page
      io.emit('user_status_change', { userId: userId, isOnline: 1 });
  });

  // 2. MODIFIED: Typing indicator sent directly to the recipient's room
  socket.on('typing', (data) => {
    const { typedBy, typedTo, isTyping } = data;
    
    // Sends to all tabs/devices of typedTo
    io.to(typedTo).emit('typing_back', {
      typedBy: typedBy,
      typedTo: typedTo,
      isTyping: isTyping
    });
  });

  // 3. MODIFIED: Read receipts sent directly to the sender's room
  socket.on('mark_as_read', async (data) => {
    const { senderId, receiverId } = data;

    const query = 'update messages set isRead = 1 where sid = ? and rid = ? and isRead = 0';
    await db.query(query, [senderId, receiverId]);

    // Sends to all tabs/devices of the partner
    io.to(senderId).emit('message_marked_read', {
      readBy: receiverId
    });
    // Sends to all tabs/devices of the current user
    io.to(receiverId).emit('message_marked_read', {
      readBy: receiverId,
      readTo: senderId
    });
  });

  // 4. MODIFIED: Offline status updated only when ALL tabs of this user close
  socket.on('disconnect', async () => {
      const userId = socket.userId;

      if (userId) {
          // Check if user still has other tabs open in their room
          const remainingSockets = await io.in(userId).fetchSockets();

          // Only set offline if no other sockets remain in this room
          if (remainingSockets.length === 0) {
              await db.query('UPDATE users SET online = 0 WHERE id = ?', [userId]);
              io.emit('user_status_change', { userId: userId, isOnline: 0 });
          }
      }
  });
});

// 5. MODIFIED: Emit directly to room IDs instead of checking userSockets object
app.post('/broadcast', (req, res) => {
  const { sid, rid, message, isRead } = req.body;

  // Send message to all tabs/browsers of receiver
  io.to(rid).emit('new_message', { sid, message });

  // Send back to all tabs/browsers of sender
  io.to(sid).emit('new_message', { sid, rid, message, isRead });

  res.json({ status: 'success' });
});

server.listen(3000, () => console.log('Socket broadcaster running on port 3000'));