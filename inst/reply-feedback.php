<?php
session_start();
require 'db.php';

$feedback_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE feedback_id = ?");
$stmt->execute([$feedback_id]);
$feedback = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply = $_POST['reply'];
    $stmt = $pdo->prepare("UPDATE feedback SET reply = ? WHERE feedback_id = ?");
    $stmt->execute([$reply, $feedback_id]);
    header("Location: view_feedback.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reply to Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Reply to Feedback</h2>
        <p><strong>Feedback:</strong> <?= htmlspecialchars($feedback['content']) ?></p>
        <form method="POST">
            <div class="mb-3">
                <label>Reply</label>
                <textarea name="reply" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Reply</button>
        </form>
    </div>
</body>
</html>