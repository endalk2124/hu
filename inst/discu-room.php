<?php
session_start();
require 'db.php';

$room_id = $_GET['id'];
$messages = $pdo->prepare("SELECT m.*, u.username FROM discussion_messages m JOIN users u ON m.user_id = u.user_id WHERE m.room_id = ?");
$messages->execute([$room_id]);
$messages = $messages->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Discussion Room</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Real-Time Discussion</h2>
        <ul>
            <?php foreach ($messages as $message): ?>
                <li>
                    <strong><?= htmlspecialchars($message['username']) ?>:</strong> <?= htmlspecialchars($message['content']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <form method="POST" action="send_message.php">
            <input type="hidden" name="room_id" value="<?= $room_id ?>">
            <div class="mb-3">
                <label>Message</label>
                <textarea name="content" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>
</body>
</html>