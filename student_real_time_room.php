<?php
// Start session to ensure user authentication
session_start();

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: student_login.php");
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

// Fetch courses the student is enrolled in
$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_name 
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.student_id = ? AND c.status = 'active'
");
$stmt->execute([$user_id]);
$enrolledCourses = $stmt->fetchAll();

// Fetch scheduled rooms for the student's enrolled courses
$scheduledRooms = [];
if (!empty($enrolledCourses)) {
    // Extract course IDs from the enrolled courses
    $courseIds = array_column($enrolledCourses, 'course_id');

    // Prepare the SQL query with valid course IDs
    $scheduledRoomsQuery = "
        SELECT r.room_id, r.room_name, r.start_time, r.end_time, c.course_name
        FROM realtime_discussion_rooms r
        JOIN courses c ON r.course_id = c.course_id
        WHERE c.course_id IN (" . implode(',', $courseIds) . ")
          AND r.start_time <= NOW() AND r.end_time >= NOW()
        ORDER BY r.start_time ASC
    ";
    $stmt = $pdo->query($scheduledRoomsQuery);
    $scheduledRooms = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Discussion Rooms</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .room-card {
            transition: transform 0.2s;
        }
        .room-card:hover {
            transform: scale(1.02);
        }
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
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
            <?php include 'student_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 main-content bg-light">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
                <div class="container-fluid">
                    <button class="btn btn-light me-2 d-lg-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <span class="navbar-brand fw-bold">HU Informatics - Student</span>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <header>
                    <h2 class="mb-4">Available Real-Time Discussion Rooms</h2>
                    <p class="text-muted">Join active discussion rooms for your enrolled courses.</p>
                </header>

                <!-- No Rooms Available -->
                <?php if (empty($scheduledRooms)): ?>
                    <div class="alert alert-info">No real-time discussion rooms are currently available for your courses.</div>
                <?php else: ?>
                    <!-- List of Scheduled Rooms -->
                    <div class="row">
                        <?php foreach ($scheduledRooms as $room): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card room-card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($room['room_name']) ?></h5>
                                        <p class="card-text">
                                            <strong>Course:</strong> <?= htmlspecialchars($room['course_name']) ?><br>
                                            <strong>Start Time:</strong> <?= htmlspecialchars(date('M d, Y h:i A', strtotime($room['start_time']))) ?><br>
                                            <strong>End Time:</strong> <?= htmlspecialchars(date('M d, Y h:i A', strtotime($room['end_time']))) ?>
                                        </p>
                                        <a href="real_time_discussion.php?room_id=<?= htmlspecialchars($room['room_id']) ?>" class="btn btn-primary">
                                            <i class="fa-solid fa-door-open me-2"></i>Join Room
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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