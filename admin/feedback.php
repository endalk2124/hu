<?php
session_start();
require '../db.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../adminlogin.php");
    exit();
}

// Fetch feedback
$stmt = $pdo->query("SELECT feedback.*, users.username 
                    FROM feedback 
                    JOIN users ON feedback.user_id = users.user_id 
                    ORDER BY date DESC");
$feedbacks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feedback Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../navbar.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Feedback Analysis</h2>

        <!-- Feedback Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Reply</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <tr>
                                <td><?= $feedback['feedback_id'] ?></td>
                                <td><?= htmlspecialchars($feedback['username']) ?></td>
                                <td><?= htmlspecialchars($feedback['comment']) ?></td>
                                <td><?= $feedback['date'] ?></td>
                                <td><?= $feedback['reply'] ?: 'No reply yet' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>