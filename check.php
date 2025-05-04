<?php
include 'db.php';
?>
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

            // Redirect to avoid duplicate submissions on refresh
            header("Location: instructor_courses.php");
            exit;
        } catch (PDOException $e) {
            echo "<script>alert('Error adding course: " . htmlspecialchars($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('Please fill all required fields.');</script>";
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

                <!-- Button to Open Add Course Modal -->
                <div class="d-flex justify-content-end mb-4">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="fa-solid fa-plus me-2"></i>Create New Course
                    </button>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="courseTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#active" data-bs-toggle="tab">Active (<?= count($activeCourses) ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#past" data-bs-toggle="tab">Past (<?= count($pastCourses) ?>)</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="courseTabsContent">
                    <!-- Active Courses Tab -->
                    <div class="tab-pane fade show active" id="active">
                        <?php if (empty($activeCourses)): ?>
                            <div class="alert alert-info">
                                You don't have any active courses yet. Create your first course using the button above.
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
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
                                                            <li><a class="dropdown-item" href="#">Edit Course</a></li>
                                                            <li><a class="dropdown-item" href="#">Manage Materials</a></li>
                                                            <li><a class="dropdown-item" href="#">View Students</a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item" href="#">Archive Course</a></li>
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
                                                            <li><a class="dropdown-item" href="#">View Course Archive</a></li>
                                                            <li><a class="dropdown-item" href="#">Export Course Data</a></li>
                                                            <li><a class="dropdown-item" href="#">Clone for New Term</a></li>
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
                <div class="modal-body">
                    <form method="POST">
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

                        <!-- Submit Button -->
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-plus me-2"></i>Create Course
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



<?php
// Start the session to access session variables


// Simulate user data (replace with actual database fetch)
$user = [
    'name' => $_SESSION['user']['name'] ?? 'Instructor',
    'email' => $_SESSION['user']['email'] ?? 'instructor@example.com',
    'role' => $_SESSION['user_role'] ?? 'instructor'
];

// Database connection
$host = 'localhost';
$dbname = 'ccs';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch active courses from the database
$activeCoursesQuery = "
    SELECT c.course_id AS id, c.course_name AS title, c.course_code AS code, 
           CONCAT('Fall 2023') AS term, COUNT(e.student_id) AS students, 
           c.description, 'TBD' AS nextClass, 'TBD' AS location, 0 AS progress, 'active' AS status
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    WHERE c.status = 'active'
    GROUP BY c.course_id
";
$activeCoursesStmt = $pdo->query($activeCoursesQuery);
$activeCourses = $activeCoursesStmt->fetchAll();

// Fetch past courses from the database
$pastCoursesQuery = "
    SELECT c.course_id AS id, c.course_name AS title, c.course_code AS code, 
           CONCAT('Spring 2023') AS term, COUNT(e.student_id) AS students, 
           c.description, 'completed' AS status
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    WHERE c.status = 'completed'
    GROUP BY c.course_id
";
$pastCoursesStmt = $pdo->query($pastCoursesQuery);
$pastCourses = $pastCoursesStmt->fetchAll();

// Handle form submission for adding a new course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $courseTitle = filter_input(INPUT_POST, 'courseTitle', FILTER_SANITIZE_STRING);
    $courseCode = filter_input(INPUT_POST, 'courseCode', FILTER_SANITIZE_STRING);
    $courseTerm = filter_input(INPUT_POST, 'courseTerm', FILTER_SANITIZE_STRING);
    $courseDescription = filter_input(INPUT_POST, 'courseDescription', FILTER_SANITIZE_STRING);

    try {
        $insertCourseQuery = "
            INSERT INTO courses (course_name, course_code, description, status)
            VALUES (:course_name, :course_code, :description, 'active')
        ";
        $stmt = $pdo->prepare($insertCourseQuery);
        $stmt->execute([
            ':course_name' => $courseTitle,
            ':course_code' => $courseCode,
            ':description' => $courseDescription
        ]);

        // Redirect to avoid duplicate submissions on refresh
        header("Location: instructor_courses.php");
        exit;
    } catch (PDOException $e) {
        error_log("Error adding course: " . $e->getMessage());
        echo "An error occurred while adding the course. Please try again later.";
    }
}
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
        :root {
            --sidebar-bg: #f8f9fa;
            --sidebar-text: #212529;
            --sidebar-active: #e9ecef;
            --sidebar-hover: #e9ecef;
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #fd7e14;
            --danger-color: #dc3545;
        }
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s;
            border-right: 1px solid #dee2e6;
        }
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                z-index: 1000;
                height: 100vh;
            }
            .sidebar.active {
                margin-left: 0;
            }
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
        .nav-tabs .nav-link.active {
            font-weight: 500;
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
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-light me-2 d-lg-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand fw-bold">HU Informatics - Instructor</span>
                </div>
            </nav>
            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <header>
                    <h2 class="text-2xl font-bold">My Courses</h2>
                    <p class="text-muted">Manage your teaching courses and materials</p>
                </header>
                <!-- Search and Filter -->
                <div class="mb-4 d-flex flex-column flex-sm-row gap-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                        <input type="text" id="searchQuery" class="form-control" placeholder="Search courses...">
                    </div>
                    <button class="btn btn-outline-secondary d-flex align-items-center">
                        <i class="fa-solid fa-filter me-2"></i> Filter
                    </button>
                </div>
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="courseTabs">
                    <li class="nav-item">
                        <a class="nav-link active" href="#active" data-bs-toggle="tab">Active (<?php echo count($activeCourses); ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#past" data-bs-toggle="tab">Past (<?php echo count($pastCourses); ?>)</a>
                    </li>
                </ul>
                <!-- Tab Content -->
                <div class="tab-content" id="courseTabsContent">
                    <!-- Active Courses Tab -->
                    <div class="tab-pane fade show active" id="active">
                        <div class="row g-4">
                            <?php foreach ($activeCourses as $course): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card course-card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span class="badge bg-primary"><?php echo htmlspecialchars($course['term']); ?></span>
                                                    <h5 class="card-title fw-bold mt-2"><?php echo htmlspecialchars($course['title']); ?></h5>
                                                    <p class="text-muted small"><?php echo htmlspecialchars($course['code']); ?></p>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#">Edit Course</a></li>
                                                        <li><a class="dropdown-item" href="#">Manage Materials</a></li>
                                                        <li><a class="dropdown-item" href="#">View Students</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item" href="#">Archive Course</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <p class="card-text small text-muted"><?php echo htmlspecialchars($course['description']); ?></p>
                                            <div class="d-flex align-items-center text-muted small mb-2">
                                                <i class="fa-solid fa-users me-2"></i>
                                                <span><?php echo htmlspecialchars($course['students']); ?> Students</span>
                                            </div>
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo htmlspecialchars($course['progress']); ?>%" aria-valuenow="<?php echo htmlspecialchars($course['progress']); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <small class="text-muted"><i class="fa-solid fa-clock me-2"></i>Next: <?php echo htmlspecialchars($course['nextClass']); ?></small>
                                        </div>
                                        <div class="card-footer">
                                            <a href="manage_course.php?id=<?php echo htmlspecialchars($course['id']); ?>" class="btn btn-primary w-100">Manage Course</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <!-- Past Courses Tab -->
                    <div class="tab-pane fade" id="past">
                        <div class="row g-4">
                            <?php foreach ($pastCourses as $course): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card course-card h-100 shadow-sm opacity-75 hover:shadow-lg transition-shadow">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span class="badge bg-success"><?php echo htmlspecialchars($course['term']); ?></span>
                                                    <h5 class="card-title fw-bold mt-2"><?php echo htmlspecialchars($course['title']); ?></h5>
                                                    <p class="text-muted small"><?php echo htmlspecialchars($course['code']); ?></p>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#">View Course Archive</a></li>
                                                        <li><a class="dropdown-item" href="#">Export Course Data</a></li>
                                                        <li><a class="dropdown-item" href="#">Clone for New Term</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <p class="card-text small text-muted"><?php echo htmlspecialchars($course['description']); ?></p>
                                            <div class="d-flex align-items-center text-muted small mb-2">
                                                <i class="fa-solid fa-users me-2"></i>
                                                <span><?php echo htmlspecialchars($course['students']); ?> Students</span>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <a href="manage_course.php?id=<?php echo htmlspecialchars($course['id']); ?>" class="btn btn-outline-secondary w-100">View Archive</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="courseTitle" class="form-label">Course Title</label>
                            <input type="text" class="form-control" id="courseTitle" name="courseTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="courseCode" class="form-label">Course Code</label>
                            <input type="text" class="form-control" id="courseCode" name="courseCode" required>
                        </div>
                        <div class="mb-3">
                            <label for="courseTerm" class="form-label">Term</label>
                            <input type="text" class="form-control" id="courseTerm" name="courseTerm" required>
                        </div>
                        <div class="mb-3">
                            <label for="courseDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="courseDescription" name="courseDescription" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="add_course" class="btn btn-primary">Create Course</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Button to Open Add Course Modal -->
    <div class="position-fixed bottom-0 end-0 p-4">
        <button class="btn btn-primary rounded-circle shadow" data-bs-toggle="modal" data-bs-target="#addCourseModal">
            <i class="fa-solid fa-plus"></i>
        </button>
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