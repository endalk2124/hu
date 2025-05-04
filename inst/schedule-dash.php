<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $course_id = $_POST['course_id'];

    $stmt = $pdo->prepare("INSERT INTO realtime_discussion_rooms (start_time, end_time, course_id, instructor_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$start_time, $end_time, $course_id, $_SESSION['user_id']]);
    header("Location: discussion_room.php");
    exit();
}

$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Discussion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Schedule Real-Time Discussion</h2>
        <form method="POST">
            <div class="mb-3">
                <label>Start Time</label>
                <input type="datetime-local" name="start_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>End Time</label>
                <input type="datetime-local" name="end_time" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Course</label>
                <select name="course_id" class="form-control" required>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>"><?= $course['course_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Schedule</button>
        </form>
    </div>
</body>
</html>