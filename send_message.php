<?php
session_start();
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user']['user_id'];

    if (!$roomId || !$content) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    // Insert message into the database
    $stmt = $pdo->prepare("INSERT INTO messages (room_id, user_id, content) VALUES (:room_id, :user_id, :content)");
    $stmt->execute(['room_id' => $roomId, 'user_id' => $userId, 'content' => $content]);

    echo json_encode(['success' => true]);
}
?>