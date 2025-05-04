<?php
session_start();

// Redirect if not logged in or not an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}

require 'db.php'; // Include the database connection

$error_message = '';
$success_message = '';

// Fetch departments for dropdown
$stmt = $pdo->query("SELECT * FROM departments");
$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    try {
        $course_name = trim($_POST['course_name']);
        $course_code = strtoupper(trim($_POST['course_code']));
        $selected_departments = isset($_POST['departments']) ? $_POST['departments'] : [];
        $action_after_creation = isset($_POST['action_after_creation']) ? $_POST['action_after_creation'] : 'stay';

        // Validate inputs
        if (empty($course_name) || empty($course_code) || empty($selected_departments)) {
            throw new Exception("All fields are required.");
        }

        // Check for duplicate course code
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
        $stmt->execute([$course_code]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("A course with this code already exists.");
        }

        // Insert course into the database
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_code) VALUES (?, ?)");
        $stmt->execute([$course_name, $course_code]);
        $course_id = $pdo->lastInsertId();

        // Link selected departments to the course
        if (!empty($selected_departments)) {
            $stmt = $pdo->prepare("INSERT INTO course_departments (course_id, department_id) VALUES (?, ?)");
            foreach ($selected_departments as $department_id) {
                $stmt->execute([$course_id, $department_id]);
            }
        }

        // Automatically link the instructor to the course
        $stmt = $pdo->prepare("INSERT INTO instructor_courses (instructor_id, course_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $course_id]);

        // Determine the next action based on user selection
        if ($action_after_creation === 'dashboard') {
            header("Location: my_courses.php?success=1"); // Redirect to My Courses Page
            exit();
        } else {
            $success_message = "Course created successfully! You can create another course below.";
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Course - Instructor</title>
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
        <h5 class="text-center mt-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h5>
        <ul class="list-unstyled">
            <li><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i><span class="nav-text">Dashboard</span></a></li>
            <li><a href="inst_course.php" class="nav-link active"><i class="fas fa-book me-2"></i><span class="nav-text">My Courses</span></a></li>
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
            <h4>Create New Course</h4>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light"><i class="fas fa-bell"></i></button>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
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

        <!-- Page Content -->
        <div class="container mt-5">
            <h2 class="mb-4">Create New Course</h2>

            <!-- Display Success/Error Messages -->
            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <!-- Create Course Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">Course Details</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="course_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" class="form-control" name="course_code" required>
                            <small class="text-muted">Use uppercase letters and numbers (e.g., CS101).</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Departments</label>
                            <select class="form-select" name="departments[]" multiple required>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl (or Command) to select multiple departments.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">After Creating the Course</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="action_after_creation" id="stay" value="stay" checked>
                                <label class="form-check-label" for="stay">Stay on this page</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="action_after_creation" id="dashboard" value="dashboard">
                                <label class="form-check-label" for="dashboard">Go to My Courses</label>
                            </div>
                        </div>
                        <button type="submit" name="create_course" class="btn btn-primary">Create Course</button>
                    </form>
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