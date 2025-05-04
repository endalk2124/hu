<?php
session_start();

// Redirect if not logged in or not an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}

require 'db.php'; // Include the database connection

try {
    // Fetch instructor details
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, i.first_name, i.last_name 
        FROM users u 
        JOIN instructors i ON u.user_id = i.user_id 
        WHERE u.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$instructor) {
        die("Instructor not found.");
    }

    // Fetch courses managed by the instructor
    $stmt = $pdo->prepare("
        SELECT c.course_id, c.course_name, c.course_code, GROUP_CONCAT(d.department_name SEPARATOR ', ') AS departments
        FROM courses c
        LEFT JOIN course_departments cd ON c.course_id = cd.course_id
        LEFT JOIN departments d ON cd.department_id = d.department_id
        WHERE c.course_id IN (
            SELECT ic.course_id 
            FROM instructor_courses ic 
            WHERE ic.instructor_id = ?
        )
        GROUP BY c.course_id, c.course_name, c.course_code
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Instructor Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            transition: all 0.3s ease;
            overflow-y: auto;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 15px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
            padding: 20px;
        }
        /* Header Styling */
        .header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 0;
        }
        .header h4 {
            margin: 0;
        }
        /* Card Styling */
        .course-card {
            cursor: pointer;
            transition: transform 0.3s ease;
            background-color: #ffffff;
            color: #343a40;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .course-card:hover {
            transform: scale(1.02);
        }
        /* Mobile Adaptation */
        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
            }
            .main-content {
                margin-left: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Left Navigation Menu -->
    <div class="sidebar" id="sidebar">
        <h5 class="text-center mt-3">Welcome, <?= htmlspecialchars($instructor['username']) ?>!</h5>
        <ul class="list-unstyled">
            <li><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i><span class="nav-text">Dashboard</span></a></li>
            <li><a href="my_courses.php" class="nav-link active"><i class="fas fa-book me-2"></i><span class="nav-text">My Courses</span></a></li>
            <li><a href="upload_resources.php" class="nav-link"><i class="fas fa-upload me-2"></i><span class="nav-text">Upload Resources</span></a></li>
            <li><a href="discussions.php" class="nav-link"><i class="fas fa-comments me-2"></i><span class="nav-text">Discussions</span></a></li>
            <li><a href="students.php" class="nav-link"><i class="fas fa-users me-2"></i><span class="nav-text">Students</span></a></li>
            <li><a href="assessments.php" class="nav-link"><i class="fas fa-pencil-alt me-2"></i><span class="nav-text">Assessments</span></a></li>
            <li><a href="settings.php" class="nav-link"><i class="fas fa-cog me-2"></i><span class="nav-text">Settings</span></a></li>
            <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt me-2"></i><span class="nav-text">Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header d-flex justify-content-between align-items-center">
            <button class="btn btn-light" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h4>My Courses</h4>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light"><i class="fas fa-bell"></i></button>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($instructor['first_name']) ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Course Management -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="card-title">My Courses</h5>
                        <a href="inst_create_course.php" class="btn btn-light">Create New Course</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($courses)): ?>
                            <div class="row">
                                <?php foreach ($courses as $course): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card course-card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($course['course_name']) ?></h5>
                                                <p class="card-text"><strong>Code:</strong> <?= htmlspecialchars($course['course_code']) ?></p>
                                                <p class="card-text"><strong>Departments:</strong> <?= htmlspecialchars($course['departments']) ?></p>
                                                <div class="progress mt-3">
                                                    <div class="progress-bar" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">Syllabus 70% Complete</div>
                                                </div>
                                                <div class="mt-3">
                                                    <a href="course_details.php?course_id=<?= $course['course_id'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                                    <a href="upload_resources.php?course_id=<?= $course['course_id'] ?>" class="btn btn-success btn-sm">Upload Resources</a>
                                                    <a href="create_forum.php?course_id=<?= $course['course_id'] ?>" class="btn btn-info btn-sm">Create Forum</a>
                                                    <a href="schedule_discussion.php?course_id=<?= $course['course_id'] ?>" class="btn btn-warning btn-sm">Schedule Session</a>
                                                    <a href="delete_course.php?course_id=<?= $course['course_id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No courses assigned to you yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Sidebar Toggle -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');

            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
                sidebar.style.width = '250px';
                mainContent.style.marginLeft = '250px';
            } else {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('collapsed');
                sidebar.style.width = '60px';
                mainContent.style.marginLeft = '60px';
            }
        }
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>