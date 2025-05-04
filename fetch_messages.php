<?php
session_start();
require 'db.php'; // Database connection

$roomId = filter_input(INPUT_GET, 'room_id', FILTER_VALIDATE_INT);

if (!$roomId) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

// Fetch messages for the room
$stmt = $pdo->prepare("
    SELECT m.content, m.sent_at, u.username
    FROM messages m
    JOIN users u ON m.user_id = u.user_id
    WHERE m.room_id = :room_id
    ORDER BY m.sent_at ASC
");
$stmt->execute(['room_id' => $roomId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'messages' => $messages]);
?>