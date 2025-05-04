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

// Get course ID from query string
$course_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$course_id) {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center'>
                <h4>Invalid Course ID</h4>
                <p>The course ID you provided is invalid or missing.</p>
                <a href='instructor_courses.php' class='btn btn-primary'>Go Back to Courses</a>
            </div>
          </div>";
    exit;
}

// Fetch course details and validate ownership
$stmt = $pdo->prepare("
    SELECT c.course_id AS id, c.course_name AS title, c.course_code AS code, 
           c.description, COUNT(e.student_id) AS students
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    INNER JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE c.course_id = ? AND ic.instructor_id = ?
    GROUP BY c.course_id
");
$stmt->execute([$course_id, $user_id]);
$course = $stmt->fetch();

if (!$course) {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger text-center'>
                <h4>Course Not Found</h4>
                <p>The course you are trying to access does not exist or you do not have permission to manage it.</p>
                <a href='instructor_courses.php' class='btn btn-primary'>Go Back to Courses</a>
            </div>
          </div>";
    exit;
}

// Handle POST requests for updating course details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $courseTitle = trim($_POST['courseTitle']);
    $courseCode = trim($_POST['courseCode']);
    $courseDescription = trim($_POST['courseDescription']);

    if (!empty($courseTitle) && !empty($courseCode)) {
        try {
            $updateCourseQuery = "
                UPDATE courses
                SET course_name = ?, course_code = ?, description = ?
                WHERE course_id = ?
            ";
            $stmt = $pdo->prepare($updateCourseQuery);
            $stmt->execute([$courseTitle, $courseCode, $courseDescription, $course_id]);

            // Redirect to refresh the page and show success message
            header("Location: manage_course.php?id=$course_id&success=1");
            exit;
        } catch (PDOException $e) {
            echo "<script>alert('Error updating course: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
    }
}

// Handle course deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    try {
        // Delete the course from the courses table
        $deleteCourseQuery = "DELETE FROM courses WHERE course_id = ?";
        $stmt = $pdo->prepare($deleteCourseQuery);
        $stmt->execute([$course_id]);

        // Remove the course from the instructor_courses table
        $deleteInstructorCourseQuery = "DELETE FROM instructor_courses WHERE course_id = ?";
        $stmt = $pdo->prepare($deleteInstructorCourseQuery);
        $stmt->execute([$course_id]);

        // Redirect to the courses page after deletion
        header("Location: instructor_courses.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Error deleting course: " . htmlspecialchars($e->getMessage()) . "');</script>";
    }
}

// Success message flag
$successMessage = isset($_GET['success']) ? true : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course - <?= htmlspecialchars($course['title']) ?></title>
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

            <!-- Manage Course Content -->
            <div class="container-fluid p-4">
                <header>
                    <h2 class="mb-4"><?= htmlspecialchars($course['title']) ?></h2>
                    <p class="text-muted">Course Code: <?= htmlspecialchars($course['code']) ?></p>
                </header>

                <!-- Success Message -->
                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        Course details updated successfully!
                    </div>
                <?php endif; ?>

                <!-- Edit Course Form -->
                <form method="POST">
                    <input type="hidden" name="update_course" value="1">

                    <!-- Course Title -->
                    <div class="mb-3">
                        <label for="courseTitle" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="courseTitle" name="courseTitle" 
                               value="<?= htmlspecialchars($course['title']) ?>" required>
                    </div>

                    <!-- Course Code -->
                    <div class="mb-3">
                        <label for="courseCode" class="form-label">Course Code</label>
                        <input type="text" class="form-control" id="courseCode" name="courseCode" 
                               value="<?= htmlspecialchars($course['code']) ?>" required>
                    </div>

                    <!-- Course Description -->
                    <div class="mb-3">
                        <label for="courseDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="courseDescription" name="courseDescription" rows="3"><?= htmlspecialchars($course['description']) ?></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save me-2"></i>Save Changes
                    </button>
                </form>

                <hr class="my-4">

                <!-- Delete Course Option -->
                <div class="mt-4">
                    <h6 class="text-danger"><i class="fa-solid fa-trash me-2"></i>Delete Course</h6>
                    <p class="text-muted">
                        Permanently delete this course and all associated data.
                    </p>
                    <a href="?action=delete&id=<?= htmlspecialchars($course_id) ?>" 
                       class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                        Delete Course
                    </a>
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