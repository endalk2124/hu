<?php
// Start session to ensure user authentication

// Default user data in case session data is missing (fallback)
$user = [
    'id' => $_SESSION['user']['id'] ?? null,
    'name' => $_SESSION['user']['name'] ?? 'Student',
    'email' => $_SESSION['user']['email'] ?? 'student@example.com',
    'role' => $_SESSION['user']['role'] ?? 'student'
];

// Get room ID from query string
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : null;

if (!$room_id) {
    die("Invalid room ID.");
}

// Database connection
$host = 'localhost';
$dbname = 'ccs';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch details of the discussion room
$stmt = $pdo->prepare("
    SELECT r.room_name, r.start_time, r.end_time, c.course_name
    FROM realtime_discussion_rooms r
    JOIN courses c ON r.course_id = c.course_id
    WHERE r.room_id = ?
");
$stmt->execute([$room_id]);
$roomDetails = $stmt->fetch();

if (!$roomDetails) {
    die("Room not found or invalid room ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Discussion Room</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-box {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 10px;
            background-color: #fff;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1><?= htmlspecialchars($roomDetails['room_name']) ?></h1>
        <p><strong>Course:</strong> <?= htmlspecialchars($roomDetails['course_name']) ?></p>
        <p><strong>Start Time:</strong> <?= htmlspecialchars($roomDetails['start_time']) ?></p>
        <p><strong>End Time:</strong> <?= htmlspecialchars($roomDetails['end_time']) ?></p>
        <p>Welcome, <?= htmlspecialchars($user['name']) ?>!</p>

        <!-- Chat Box -->
        <div class="chat-box" id="chatBox"></div>

        <!-- Message Input -->
        <form id="chatForm" class="mt-3">
            <div class="input-group">
                <input type="text" class="form-control" id="messageInput" placeholder="Type your message..." required>
                <button type="submit" class="btn btn-primary">Send</button>
            </div>
        </form>
    </div>

    <!-- Socket.IO Client -->
    <script src="https://cdn.socket.io/4.6.0/socket.io.min.js"></script>
    <script>
        const roomId = <?= $room_id ?>;
        const userId = <?= $user['id'] ?>;

        // Connect to the Socket.IO server
        const socket = io('http://localhost:3000'); // Replace with your Node.js server URL

        // Join the room
        socket.emit('joinRoom', { roomId, userId });

        // Receive messages
        socket.on('receiveMessage', ({ userId, message }) => {
            const chatBox = document.getElementById('chatBox');
            const messageElement = document.createElement('div');
            messageElement.innerHTML = `<strong>User ${userId}: </strong>${message}`;
            chatBox.appendChild(messageElement);
            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to bottom
        });

        // Send messages
        document.getElementById('chatForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const message = document.getElementById('messageInput').value.trim();
            if (message) {
                socket.emit('sendMessage', { roomId, userId, message });
                document.getElementById('messageInput').value = '';
            }
        });
    </script>
</body>
</html>