<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}
require 'db.php';

$resource_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM resources WHERE resource_id = ?");
$stmt->execute([$resource_id]);
$resource = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("UPDATE resources SET title = ?, description = ?, course_id = ? WHERE resource_id = ?");
    $stmt->execute([$title, $description, $course_id, $resource_id]);
    header("Location: view_resources.php");
    exit();
}

$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Resource</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Resource</h2>
        <form method="POST">
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($resource['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" required><?= htmlspecialchars($resource['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label>Course</label>
                <select name="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>" <?= $course['course_id'] == $resource['course_id'] ? 'selected' : '' ?>>
                            <?= $course['course_name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>