<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}
require 'db.php';

$resources = $pdo->query("SELECT r.*, c.course_name FROM resources r JOIN courses c ON r.course_id = c.course_id")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Resources</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Resources</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Course</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resources as $resource): ?>
                    <tr>
                        <td><?= htmlspecialchars($resource['title']) ?></td>
                        <td><?= htmlspecialchars($resource['description']) ?></td>
                        <td><?= htmlspecialchars($resource['course_name']) ?></td>
                        <td>
                            <a href="edit_resource.php?id=<?= $resource['resource_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_resource.php?id=<?= $resource['resource_id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>