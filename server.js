const express = require('express');
const http = require('http');
const { Server } = require('socket.io');

// Initialize Express app
const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: '*', // Allow all origins (replace with your frontend URL in production)
        methods: ['GET', 'POST']
    }
});

// Serve static files (optional)
app.use(express.static('public'));

// Handle Socket.IO connections
io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);

    // Join a room
    socket.on('join_room', ({ roomId }) => {
        socket.join(roomId);
        console.log(`User joined room: ${roomId}`);
    });

    // Listen for incoming messages
    socket.on('message', (data) => {
        console.log('Message received:', data);

        // Broadcast the message to all users in the room
        io.to(data.roomId).emit('message', data);
    });

    // Handle disconnection
    socket.on('disconnect', () => {
        console.log('A user disconnected:', socket.id);
    });
});

// Start the server
const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});