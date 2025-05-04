const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const mysql = require('mysql2');

const app = express();
const server = http.createServer(app);
const io = new Server(server);

// MySQL Connection
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root', // Replace with your database username
    password: '', // Replace with your database password
    database: 'ccs'
});

db.connect(err => {
    if (err) throw err;
    console.log('Connected to MySQL');
});

// Serve static files
app.use(express.static('public'));

// Handle Socket.IO connections
io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);

    // Join a room
    socket.on('joinRoom', ({ roomId, userId }) => {
        socket.join(roomId);
        console.log(`User ${userId} joined room ${roomId}`);

        // Log participation in the database
        const query = 'INSERT INTO room_participants (room_id, user_id) VALUES (?, ?)';
        db.execute(query, [roomId, userId], (err) => {
            if (err) console.error(err);
        });

        // Notify others in the room
        io.to(roomId).emit('userJoined', { userId });
    });

    // Send a message
    socket.on('sendMessage', ({ roomId, userId, message }) => {
        console.log(`Message from ${userId}: ${message}`);
        const query = 'INSERT INTO messages (room_id, user_id, content) VALUES (?, ?, ?)';
        db.execute(query, [roomId, userId, message], (err) => {
            if (err) console.error(err);
        });

        // Broadcast the message to the room
        io.to(roomId).emit('receiveMessage', { userId, message });
    });

    // Disconnect
    socket.on('disconnect', () => {
        console.log('A user disconnected:', socket.id);
    });
});

server.listen(3000, () => {
    console.log('Server running on http://localhost:3000');
});