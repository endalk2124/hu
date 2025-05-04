<?php
session_start();
require 'db.php';

$forum_id = $_GET['id'];
$posts = $pdo->prepare("SELECT p.*, u.username FROM forum_posts p JOIN users u ON p.user_id = u.user_id WHERE p.forum_id = ?");
$posts->execute([$forum_id]);
$posts = $posts->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Forum Posts</h2>
        <ul>
            <?php foreach ($posts as $post): ?>
                <li>
                    <strong><?= htmlspecialchars($post['username']) ?>:</strong> <?= htmlspecialchars($post['content']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>