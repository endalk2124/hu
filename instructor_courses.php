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

// Handle POST requests for creating a new course
$successMessage = '';
$newCourse = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $courseTitle = trim($_POST['courseTitle']);
    $courseCode = trim($_POST['courseCode']);
    $courseDescription = trim($_POST['courseDescription']);

    if (!empty($courseTitle) && !empty($courseCode)) {
        try {
            // Insert the new course into the courses table
            $insertCourseQuery = "
                INSERT INTO courses (course_name, course_code, description, status, created_at)
                VALUES (?, ?, ?, 'active', NOW())
            ";
            $stmt = $pdo->prepare($insertCourseQuery);
            $stmt->execute([$courseTitle, $courseCode, $courseDescription]);

            // Get the newly created course ID
            $newCourseId = $pdo->lastInsertId();

            // Link the course to the instructor in the instructor_courses table
            $insertInstructorCourseQuery = "
                INSERT INTO instructor_courses (instructor_id, course_id)
                VALUES (?, ?)
            ";
            $stmt = $pdo->prepare($insertInstructorCourseQuery);
            $stmt->execute([$user_id, $newCourseId]);

            // Fetch the newly created course details
            $newCourseQuery = "
                SELECT c.course_id AS id, c.course_name AS title, c.course_code AS code, 
                       CONCAT('Term ', YEAR(c.created_at)) AS term, COUNT(e.student_id) AS students, 
                       c.description, 'TBD' AS nextClass, 'TBD' AS location, 0 AS progress, 'active' AS status
                FROM courses c
                LEFT JOIN enrollments e ON c.course_id = e.course_id
                INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
                WHERE c.course_id = ?
                GROUP BY c.course_id
            ";
            $stmt = $pdo->prepare($newCourseQuery);
            $stmt->execute([$newCourseId]);
            $newCourse = $stmt->fetch();

            // Set success message
            $successMessage = "Course '$courseTitle' has been successfully created!";
        } catch (PDOException $e) {
            echo "<script>alert('Error adding course: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}

// Handle course management actions (Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $course_id = isset($_GET['id']) ? intval($_GET['id']) : null;

    if ($action === 'delete' && $course_id) {
        try {
            // Delete the course from the courses table
            $deleteCourseQuery = "DELETE FROM courses WHERE course_id = ?";
            $stmt = $pdo->prepare($deleteCourseQuery);
            $stmt->execute([$course_id]);

            // Remove the course from the instructor_courses table
            $deleteInstructorCourseQuery = "DELETE FROM instructor_courses WHERE course_id = ?";
            $stmt = $pdo->prepare($deleteInstructorCourseQuery);
            $stmt->execute([$course_id]);

            // Redirect to refresh the page
            header("Location: instructor_courses.php");
            exit;
        } catch (PDOException $e) {
            echo "<script>alert('Error deleting course: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    }
}

// Fetch active courses for the instructor
$activeCoursesQuery = "
    SELECT c.course_id AS id, c.course_name AS title, c.course_code AS code, 
           CONCAT('Term ', YEAR(c.created_at)) AS term, COUNT(e.student_id) AS students, 
           c.description, 'TBD' AS nextClass, 'TBD' AS location, 0 AS progress, 'active' AS status
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE c.status = 'active' AND ic.instructor_id = ?
    GROUP BY c.course_id
    ORDER BY c.created_at DESC
";
$activeCoursesStmt = $pdo->prepare($activeCoursesQuery);
$activeCoursesStmt->execute([$user_id]);
$activeCourses = $activeCoursesStmt->fetchAll();

// Fetch past courses for the instructor
$pastCoursesQuery = "
    SELECT c.course_id AS id, c.course_name AS title, c.course_code AS code, 
           CONCAT('Term ', YEAR(c.created_at)) AS term, COUNT(e.student_id) AS students, 
           c.description, 'completed' AS status
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE c.status = 'completed' AND ic.instructor_id = ?
    GROUP BY c.course_id
    ORDER BY c.created_at DESC
";
$pastCoursesStmt = $pdo->prepare($pastCoursesQuery);
$pastCoursesStmt->execute([$user_id]);
$pastCourses = $pastCoursesStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Courses - HU Informatics</title>
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
        .progress-bar {
            height: 6px;
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
                    <h2 class="mb-4">My Courses</h2>
                    <p class="text-muted">Manage your teaching courses and materials.</p>
                </header>

                <!-- Success Message -->
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($successMessage) ?>
                    </div>
                <?php endif; ?>

                <!-- Button to Open Add Course Modal -->
                <div class="d-flex justify-content-end mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="fa-solid fa-plus me-2"></i>Create New Course
                    </button>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="courseTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#active" data-bs-toggle="tab">Active (<?= count($activeCourses) + ($newCourse ? 1 : 0) ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#past" data-bs-toggle="tab">Past (<?= count($pastCourses) ?>)</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="courseTabsContent">
                    <!-- Active Courses Tab -->
                    <div class="tab-pane fade show active" id="active">
                        <?php if (empty($activeCourses) && !$newCourse): ?>
                            <div class="alert alert-info">
                                You don't have any active courses yet. Create your first course using the button above.
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php if ($newCourse): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card course-card h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <span class="badge bg-primary"><?= htmlspecialchars($newCourse['term']) ?></span>
                                                        <h5 class="card-title fw-bold mt-2"><?= htmlspecialchars($newCourse['title']) ?></h5>
                                                        <p class="text-muted small"><?= htmlspecialchars($newCourse['code']) ?></p>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="edit_course.php?id=<?= htmlspecialchars($newCourse['id']) ?>">Edit Course</a></li>
                                                            <li><a class="dropdown-item text-danger" href="?action=delete&id=<?= htmlspecialchars($newCourse['id']) ?>" onclick="return confirm('Are you sure you want to delete this course?')">Delete Course</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="card-text small text-muted"><?= htmlspecialchars($newCourse['description']) ?></p>
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <i class="fa-solid fa-users me-2"></i>
                                                    <span><?= htmlspecialchars($newCourse['students']) ?> Students</span>
                                                </div>
                                                <div class="progress mb-2">
                                                    <div class="progress-bar bg-primary" role="progressbar" 
                                                         style="width: <?= htmlspecialchars($newCourse['progress']) ?>%" 
                                                         aria-valuenow="<?= htmlspecialchars($newCourse['progress']) ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-clock me-2"></i>Next: <?= htmlspecialchars($newCourse['nextClass']) ?>
                                                </small>
                                            </div>
                                            <div class="card-footer">
                                                <a href="manage_course.php?id=<?= htmlspecialchars($newCourse['id']) ?>" 
                                                   class="btn btn-primary w-100">Manage Course</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php foreach ($activeCourses as $course): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card course-card h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <span class="badge bg-primary"><?= htmlspecialchars($course['term']) ?></span>
                                                        <h5 class="card-title fw-bold mt-2"><?= htmlspecialchars($course['title']) ?></h5>
                                                        <p class="text-muted small"><?= htmlspecialchars($course['code']) ?></p>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="edit_course.php?id=<?= htmlspecialchars($course['id']) ?>">Edit Course</a></li>
                                                            <li><a class="dropdown-item text-danger" href="?action=delete&id=<?= htmlspecialchars($course['id']) ?>" onclick="return confirm('Are you sure you want to delete this course?')">Delete Course</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="card-text small text-muted"><?= htmlspecialchars($course['description']) ?></p>
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <i class="fa-solid fa-users me-2"></i>
                                                    <span><?= htmlspecialchars($course['students']) ?> Students</span>
                                                </div>
                                                <div class="progress mb-2">
                                                    <div class="progress-bar bg-primary" role="progressbar" 
                                                         style="width: <?= htmlspecialchars($course['progress']) ?>%" 
                                                         aria-valuenow="<?= htmlspecialchars($course['progress']) ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fa-solid fa-clock me-2"></i>Next: <?= htmlspecialchars($course['nextClass']) ?>
                                                </small>
                                            </div>
                                            <div class="card-footer">
                                                <a href="manage_course.php?id=<?= htmlspecialchars($course['id']) ?>" 
                                                   class="btn btn-primary w-100">Manage Course</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Past Courses Tab -->
                    <div class="tab-pane fade" id="past">
                        <?php if (empty($pastCourses)): ?>
                            <div class="alert alert-info">
                                You don't have any past courses yet.
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($pastCourses as $course): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card course-card h-100 shadow-sm opacity-75 hover:shadow-lg transition-shadow">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <span class="badge bg-success"><?= htmlspecialchars($course['term']) ?></span>
                                                        <h5 class="card-title fw-bold mt-2"><?= htmlspecialchars($course['title']) ?></h5>
                                                        <p class="text-muted small"><?= htmlspecialchars($course['code']) ?></p>
                                                    </div>
                                                    <div class="dropdown">
                                                        <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li><a class="dropdown-item" href="edit_course.php?id=<?= htmlspecialchars($course['id']) ?>">Edit Course</a></li>
                                                            <li><a class="dropdown-item text-danger" href="?action=delete&id=<?= htmlspecialchars($course['id']) ?>" onclick="return confirm('Are you sure you want to delete this course?')">Delete Course</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <p class="card-text small text-muted"><?= htmlspecialchars($course['description']) ?></p>
                                                <div class="d-flex align-items-center text-muted small mb-2">
                                                    <i class="fa-solid fa-users me-2"></i>
                                                    <span><?= htmlspecialchars($course['students']) ?> Students</span>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <a href="manage_course.php?id=<?= htmlspecialchars($course['id']) ?>" 
                                                   class="btn btn-outline-secondary w-100">View Archive</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCourseModalLabel">Create New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="add_course" value="1">

                        <!-- Course Title -->
                        <div class="mb-3">
                            <label for="courseTitle" class="form-label">Course Title</label>
                            <input type="text" class="form-control" id="courseTitle" name="courseTitle" placeholder="Enter course title" required>
                        </div>

                        <!-- Course Code -->
                        <div class="mb-3">
                            <label for="courseCode" class="form-label">Course Code</label>
                            <input type="text" class="form-control" id="courseCode" name="courseCode" placeholder="Enter course code" required>
                        </div>

                        <!-- Course Description -->
                        <div class="mb-3">
                            <label for="courseDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="courseDescription" name="courseDescription" rows="3" placeholder="Enter course description"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Course</button>
                    </div>
                </form>
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

        // Activate tab based on URL hash
        if (window.location.hash) {
            const tabTrigger = document.querySelector(`[href="${window.location.hash}"]`);
            if (tabTrigger) {
                const tab = new bootstrap.Tab(tabTrigger);
                tab.show();
            }
        }

        // Update active state in sidebar when tab changes
        const tabLinks = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabLinks.forEach(link => {
            link.addEventListener('shown.bs.tab', function (event) {
                const target = event.target.getAttribute('href');
                history.replaceState(null, '', target);
            });
        });
    </script>
</body>
</html>