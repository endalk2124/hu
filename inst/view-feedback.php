<?php
session_start();
require 'db.php';

$feedbacks = $pdo->query("SELECT f.*, u.username FROM feedback f JOIN users u ON f.user_id = u.user_id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Feedback</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Feedback</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback): ?>
                    <tr>
                        <td><?= htmlspecialchars($feedback['username']) ?></td>
                        <td><?= htmlspecialchars($feedback['content']) ?></td>
                        <td>
                            <a href="reply_feedback.php?id=<?= $feedback['feedback_id'] ?>" class="btn btn-sm btn-primary">Reply</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>