<?php
session_start();
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
    $userId = $_SESSION['user']['user_id'];

    // Check if the room exists
    $stmt = $pdo->prepare("SELECT * FROM realtime_discussion_rooms WHERE room_id = :room_id");
    $stmt->execute(['room_id' => $roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }

    // Add user to the room (optional: track participants in a separate table)
    echo json_encode(['success' => true, 'room' => $room]);
}
?>