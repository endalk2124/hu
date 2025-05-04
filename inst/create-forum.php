<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("INSERT INTO discussion_forums (title, course_id, instructor_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $course_id, $_SESSION['user_id']]);
    header("Location: view_forum.php");
    exit();
}

$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Create Discussion Forum</h2>
        <form method="POST">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Course</label>
                <select name="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>"><?= $course['course_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Forum</button>
        </form>
    </div>
</body>
</html>