<?php
// Start session to ensure user authentication
session_start();

// Redirect if not logged in or not an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Include the database connection
require 'db.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Fetch available courses for the instructor from the instructor_courses table
$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_name
    FROM courses c
    JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = ? AND c.status = 'active'
");
$stmt->execute([$user_id]);
$courses = $stmt->fetchAll();

// Debugging: Log session data and query results
error_log("Session user ID: " . $user_id);
error_log("Fetched courses: " . print_r($courses, true));

// Handle POST requests for scheduling discussion rooms
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_room'])) {
    $room_name = trim($_POST['room_name']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $course_id = intval($_POST['course_id']);
    $max_participants = intval($_POST['max_participants']);

    if (!empty($room_name) && !empty($start_time) && !empty($end_time)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO realtime_discussion_rooms (room_name, start_time, end_time, course_id, instructor_id, max_participants)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$room_name, $start_time, $end_time, $course_id, $user_id, $max_participants]);

            // Get the last inserted room ID
            $room_id = $pdo->lastInsertId();

            // Success message with link to the room
            $successMessage = "Discussion room scheduled successfully! 
                                <a href='real_time_discussion.php?room_id=" . htmlspecialchars($room_id) . "' class='btn btn-success mt-2'>Go to Room</a>";
        } catch (PDOException $e) {
            $errorMessage = "Error scheduling room: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $errorMessage = "Please fill all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Discussion Room</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            margin-top: 50px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar bg-white text-dark p-3">
            <?php include 'instructor_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 main-content bg-light">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
                <div class="container-fluid">
                    <button class="btn btn-light me-2 d-lg-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <span class="navbar-brand fw-bold">HU Informatics - Instructor</span>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <header>
                    <h2 class="mb-4">Schedule New Discussion Room</h2>
                    <p class="text-muted">Create and schedule a new real-time discussion room for your courses.</p>
                </header>

                <!-- Success or Error Messages -->
                <?php if ($successMessage): ?>
                    <div class="alert alert-success"><?= $successMessage ?></div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-danger"><?= $errorMessage ?></div>
                <?php endif; ?>

                <!-- Schedule Room Form -->
                <div class="form-container">
                    <form method="POST">
                        <input type="hidden" name="schedule_room" value="1">

                        <!-- Room Name -->
                        <div class="mb-3">
                            <label for="room_name" class="form-label">Room Name</label>
                            <input type="text" class="form-control" id="room_name" name="room_name" placeholder="Enter room name" required>
                        </div>

                        <!-- Start Time -->
                        <div class="mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="datetime-local" class="form-control" id="start_time" name="start_time" required>
                        </div>

                        <!-- End Time -->
                        <div class="mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="datetime-local" class="form-control" id="end_time" name="end_time" required>
                        </div>

                        <!-- Course Selection -->
                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php if (!empty($courses)): ?>
                                    <?php foreach ($courses as $course): ?>
                                        <option value="<?= htmlspecialchars($course['course_id']) ?>">
                                            <?= htmlspecialchars($course['course_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>No active courses available</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Maximum Participants -->
                        <div class="mb-3">
                            <label for="max_participants" class="form-label">Maximum Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants" value="10" min="1" required>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-calendar-days me-2"></i>Schedule Room
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>