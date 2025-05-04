<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user ID and role from session
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch active discussion rooms for the user
$roomsQuery = "
    SELECT r.room_id, r.room_name, r.start_time, r.end_time, c.course_name, c.course_id
    FROM realtime_discussion_rooms r
    LEFT JOIN courses c ON r.course_id = c.course_id
    WHERE r.status = 'active' AND (
        (r.instructor_id = ? AND ? = 'instructor') OR
        (c.course_id IN (
            SELECT e.course_id
            FROM enrollments e
            WHERE e.student_id = ?
        ) AND ? = 'student')
    )
";
$roomsStmt = $pdo->prepare($roomsQuery);
$roomsStmt->execute([$user_id, $role, $user_id, $role]);
$rooms = $roomsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Discussion Rooms - HU Informatics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background-color: #ffffff;
            color: #212529;
            border-right: 1px solid #dee2e6;
        }
        .course-card {
            transition: box-shadow 0.3s ease;
            height: 100%;
        }
        .course-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .message-box {
            height: 300px;
            overflow-y: scroll;
            border: 1px solid #dee2e6;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #dee2e6;
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
                    <span class="navbar-brand fw-bold">HU Informatics - Real-Time Discussion Rooms</span>
                </div>
            </nav>
            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <header>
                    <h2 class="mb-4">Real-Time Discussion Rooms</h2>
                    <p class="text-muted">Join and participate in real-time discussion rooms.</p>
                </header>
                <!-- Rooms List -->
                <?php if (empty($rooms)): ?>
                    <div class="alert alert-info">
                        No active discussion rooms available.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($rooms as $room): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card course-card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <span class="badge bg-primary"><?= htmlspecialchars(date('F j, Y', strtotime($room['start_time']))) ?></span>
                                                <h5 class="card-title fw-bold mt-2"><?= htmlspecialchars($room['room_name']) ?></h5>
                                                <p class="text-muted small"><?= htmlspecialchars($room['course_name']) ?></p>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <?php if ($role === 'instructor'): ?>
                                                        <li><a class="dropdown-item" href="edit_room.php?id=<?= htmlspecialchars($room['room_id']) ?>">Edit Room</a></li>
                                                        <li><a class="dropdown-item text-danger" href="?action=delete&id=<?= htmlspecialchars($room['room_id']) ?>" onclick="return confirm('Are you sure you want to delete this room?')">Delete Room</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <p class="card-text small text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= htmlspecialchars(date('F j, Y, g:i a', strtotime($room['start_time']))) ?> - <?= htmlspecialchars(date('F j, Y, g:i a', strtotime($room['end_time']))) ?>
                                        </p>
                                    </div>
                                    <div class="card-footer">
                                        <a href="join_room.php?id=<?= htmlspecialchars($room['room_id']) ?>" 
                                           class="btn btn-primary w-100">Join Room</a>
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
</body>
</html>