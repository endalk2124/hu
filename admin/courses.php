<?php
session_start();
require '../db.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

// Handle Course Addition
if (isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name']);
    $course_code = trim($_POST['course_code']);
    $selected_departments = $_POST['department_ids']; // Array of selected department IDs

    // Check if course already exists
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_code = ?");
    $stmt->execute([$course_code]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Course with this code already exists!";
    } else {
        // Insert course into courses table
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_code) VALUES (?, ?)");
        $stmt->execute([$course_name, $course_code]);
        $course_id = $pdo->lastInsertId(); // Get the ID of the newly created course

        // Insert course-department relationships into course_departments table
        $insert_stmt = $pdo->prepare("INSERT INTO course_departments (course_id, department_id) VALUES (?, ?)");
        foreach ($selected_departments as $department_id) {
            $insert_stmt->execute([$course_id, $department_id]);
        }

        $_SESSION['message'] = "Course added successfully!";
    }
    header("Location: courses.php");
    exit();
}

// Fetch all courses with associated departments
$stmt = $pdo->query("
    SELECT c.course_id, c.course_name, c.course_code, GROUP_CONCAT(d.department_name SEPARATOR ', ') AS department_names
    FROM courses c
    LEFT JOIN course_departments cd ON c.course_id = cd.course_id
    LEFT JOIN departments d ON cd.department_id = d.department_id
    GROUP BY c.course_id
");
$courses = $stmt->fetchAll();

// Fetch all departments for dropdown
$stmt = $pdo->query("SELECT * FROM departments");
$departments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Sidebar styling */
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            transition: margin-left 0.3s ease;
            overflow-y: auto;
        }
        .sidebar.collapsed {
            margin-left: -250px;
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

        /* Main Content Styling */
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .content.collapsed {
            margin-left: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Admin Dashboard</span>
        </div>
    </nav>

    <!-- Toggle Button for Sidebar -->
    <button class="btn btn-dark position-fixed" style="top: 10px; left: 10px; z-index: 1000;" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h4 class="text-center mt-3">Admin Panel</h4>
        <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="../approve_user.php"><i class="fas fa-user-check"></i> Approve Users</a>
        <a href="departments.php"><i class="fas fa-building"></i> Departments</a>
        <a href="../courses.php"><i class="fas fa-book"></i> Courses</a>
        <a href="../feedback.php"><i class="fas fa-comments"></i> Feedback</a>
        <a href="../logs.php"><i class="fas fa-history"></i> System Logs</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <div class="container mt-5">
            <h2 class="mb-4">Manage Courses</h2>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Add Course Form -->
            <div class="card mb-4">
                <div class="card-header">Add New Course</div>
                <div class="card-body">
                    <form method="POST" id="addCourseForm">
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" class="form-control" name="course_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" class="form-control" name="course_code" id="courseCode" required>
                            <small class="form-text text-muted" id="courseCodeValidationMessage"></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Departments</label>
                            <select class="form-select" name="department_ids[]" multiple required>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Hold Ctrl (or Command on Mac) to select multiple departments.</small>
                        </div>
                        <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                    </form>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search courses...">
            </div>

            <!-- Courses List -->
            <div class="card">
                <div class="card-header">All Courses</div>
                <div class="card-body">
                    <table class="table table-bordered" id="coursesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Departments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td><?= htmlspecialchars($course['course_id']) ?></td>
                                    <td><?= htmlspecialchars($course['course_name']) ?></td>
                                    <td><?= htmlspecialchars($course['course_code']) ?></td>
                                    <td><?= htmlspecialchars($course['department_names']) ?></td>
                                    <td>
                                        <a href="edit_course.php?course_id=<?= $course['course_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete_course.php?course_id=<?= $course['course_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this course?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('collapsed');
        }

        // Real-Time Validation for Course Code Uniqueness
        document.getElementById('courseCode').addEventListener('blur', function () {
            const courseCode = this.value.trim();
            const validationMessage = document.getElementById('courseCodeValidationMessage');
            if (courseCode) {
                fetch('check_course_code.php?course_code=' + encodeURIComponent(courseCode))
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            validationMessage.textContent = 'This course code already exists!';
                            validationMessage.style.color = 'red';
                        } else {
                            validationMessage.textContent = 'Course code is available.';
                            validationMessage.style.color = 'green';
                        }
                    })
                    .catch(error => console.error('Error checking course code:', error));
            } else {
                validationMessage.textContent = '';
            }
        });

        // Search and Filter Functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#coursesTable tbody tr');

            rows.forEach(row => {
                const courseName = row.cells[1].textContent.toLowerCase();
                const courseCode = row.cells[2].textContent.toLowerCase();
                if (courseName.includes(searchTerm) || courseCode.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>