<?php
// Start session to ensure user authentication
session_start();

// Check if the user is authenticated and is a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Default user data in case session data is missing (fallback)
$user = [
    'id' => $_SESSION['user']['id'] ?? null,
    'name' => $_SESSION['user']['name'] ?? 'Student',
    'email' => $_SESSION['user']['email'] ?? 'student@example.com',
    'role' => $_SESSION['user']['role'] ?? 'student'
];

// Database connection
$host = 'localhost';
$dbname = 'ccs';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch available discussion rooms for the student's enrolled courses
$stmt = $pdo->prepare("
    SELECT r.room_id, r.room_name, r.schedule_date, r.schedule_time, r.max_participants, c.course_name
    FROM realtime_discussion_rooms r
    JOIN enrollments e ON r.course_id = e.course_id
    JOIN courses c ON r.course_id = c.course_id
    WHERE e.student_id = ? AND r.schedule_date <= CURDATE() AND r.schedule_time <= CURTIME()
");
$stmt->execute([$user['id']]);
$availableRooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Rooms</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .room-card {
            margin-bottom: 20px;
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
                    <h2 class="mb-4">Available Discussion Rooms</h2>
                    <p class="text-muted">Join and participate in real-time discussion rooms for your courses.</p>
                </header>

                <!-- Available Rooms -->
                <?php if (count($availableRooms) > 0): ?>
                    <div class="row">
                        <?php foreach ($availableRooms as $room): ?>
                            <div class="col-md-6">
                                <div class="card room-card">
                                    <div class="card-header">
                                        <?= htmlspecialchars($room['room_name']) ?>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Course:</strong> <?= htmlspecialchars($room['course_name']) ?></p>
                                        <p><strong>Date:</strong> <?= htmlspecialchars($room['schedule_date']) ?></p>
                                        <p><strong>Time:</strong> <?= htmlspecialchars($room['schedule_time']) ?></p>
                                        <p><strong>Max Participants:</strong> <?= htmlspecialchars($room['max_participants']) ?></p>
                                        <a href="realtime_discussion.php?room_id=<?= $room['room_id'] ?>" class="btn btn-success">
                                            <i class="fa-solid fa-arrow-right me-2"></i>Join Room
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-comments fa-3x text-muted mb-3"></i>
                        <h4>No available discussion rooms</h4>
                        <p>There are no active discussion rooms for your courses at this time.</p>
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