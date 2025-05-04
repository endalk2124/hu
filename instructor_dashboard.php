<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and has the role of instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

// Fetch user details from the session
$user = [
    'name' => $_SESSION['username'] ?? 'Instructor',
    'email' => $_SESSION['email'] ?? 'instructor@example.com'
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Pagination settings
$itemsPerPage = 6; // Number of items per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Fetch resources from the database with pagination
$resourcesQuery = "
    SELECT r.resource_id AS id, r.title, r.description, r.file_path, r.upload_date, 
           c.course_name AS course, COUNT(v.view_id) AS views, AVG(rating.rating) AS avg_rating
    FROM resources r
    LEFT JOIN courses c ON r.course_id = c.course_id
    LEFT JOIN resource_views v ON r.resource_id = v.resource_id
    LEFT JOIN ratings rating ON r.resource_id = rating.resource_id
    WHERE r.instructor_id = :instructor_id
    GROUP BY r.resource_id
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($resourcesQuery);
$stmt->bindValue(':instructor_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$sampleResources = $stmt->fetchAll();

// Count total resources for pagination
$totalResourcesQuery = "
    SELECT COUNT(*) AS total 
    FROM resources 
    WHERE instructor_id = :instructor_id
";
$totalStmt = $pdo->prepare($totalResourcesQuery);
$totalStmt->bindValue(':instructor_id', $_SESSION['user_id'], PDO::PARAM_INT);
$totalStmt->execute();
$totalResources = $totalStmt->fetchColumn();
$totalPages = ceil($totalResources / $itemsPerPage);

// Handle form submission for adding a new resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $title = filter_input(INPUT_POST, 'resourceTitle', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'resourceDescription', FILTER_SANITIZE_STRING);
    $courseId = filter_input(INPUT_POST, 'resourceCourse', FILTER_VALIDATE_INT);

    // File upload logic
    if (isset($_FILES['resourceFile']) && $_FILES['resourceFile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create uploads directory if it doesn't exist
        }
        $fileName = basename($_FILES['resourceFile']['name']);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['resourceFile']['tmp_name'], $filePath)) {
            // File uploaded successfully
        } else {
            $filePath = null; // File upload failed
        }
    } else {
        $filePath = null; // No file uploaded
    }

    try {
        $insertResourceQuery = "
            INSERT INTO resources (title, description, file_path, course_id, upload_date, instructor_id)
            VALUES (:title, :description, :file_path, :course_id, NOW(), :instructor_id)
        ";
        $stmt = $pdo->prepare($insertResourceQuery);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':file_path' => $filePath,
            ':course_id' => $courseId,
            ':instructor_id' => $_SESSION['user_id'],
        ]);
        header("Location: instructor_dashboard.php");
        exit;
    } catch (PDOException $e) {
        error_log("Error adding resource: " . $e->getMessage());
        echo "An error occurred while adding the resource. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - HU Informatics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }
        /* Responsive Layout */
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }
        }
        /* Feedback Icons */
        .feedback-icons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .feedback-icons button {
            background: none;
            border: none;
            cursor: pointer;
        }
        .feedback-icons i {
            font-size: 1.2rem;
        }
        /* Pagination */
        .pagination {
            justify-content: center;
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
                    <button class="btn btn-light me-2 d-lg-none" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="navbar-brand fw-bold">HU Informatics - Instructor</span>
                </div>
            </nav>
            <!-- Dashboard Content -->
            <header>
                <h2 class="mb-4">Instructor Dashboard</h2>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p>
            </header>
            <!-- Search and Filter -->
            <div class="mb-4 d-flex flex-column flex-sm-row gap-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                    <input type="text" id="searchQuery" class="form-control" placeholder="Search resources...">
                </div>
                <button class="btn btn-outline-secondary d-flex align-items-center">
                    <i class="fa-solid fa-filter me-2"></i> Filter
                </button>
            </div>
            <!-- Resources List -->
            <div class="row g-4">
                <?php if (empty($sampleResources)): ?>
                    <div class="alert alert-info">
                        No resources available. Add a new resource using the button below.
                    </div>
                <?php else: ?>
                    <?php foreach ($sampleResources as $resource): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card event-card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($resource['course']); ?></span>
                                    </div>
                                    <p class="card-text small text-muted"><?php echo htmlspecialchars($resource['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i><?php echo htmlspecialchars($resource['upload_date']); ?>
                                        </small>
                                        <div class="feedback-icons">
                                            <button title="Like this resource">
                                                <i class="fa-solid fa-thumbs-up"></i> <?php echo rand(0, 50); ?>
                                            </button>
                                            <button title="Rate this resource">
                                                <i class="fa-solid fa-star"></i> <?php echo number_format((float)$resource['avg_rating'], 1); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <!-- Add Resource Button -->
            <button class="btn btn-primary mt-4" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                <i class="fa-solid fa-plus me-2"></i> Add Resource
            </button>
        </div>
    </div>
    <!-- Add Resource Modal -->
    <div class="modal fade" id="addResourceModal" tabindex="-1" aria-labelledby="addResourceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addResourceModalLabel">Add New Resource</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="resourceTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="resourceTitle" name="resourceTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="resourceDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="resourceDescription" name="resourceDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="resourceCourse" class="form-label">Course</label>
                            <select class="form-select" id="resourceCourse" name="resourceCourse" required>
                                <option value="">Select course</option>
                                <?php
                                // Fetch courses from the database
                                $coursesQuery = "SELECT course_id, course_name FROM courses WHERE instructor_id = :instructor_id";
                                $coursesStmt = $pdo->prepare($coursesQuery);
                                $coursesStmt->bindValue(':instructor_id', $_SESSION['user_id'], PDO::PARAM_INT);
                                $coursesStmt->execute();
                                $sampleCourses = $coursesStmt->fetchAll();
                                foreach ($sampleCourses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['course_id']); ?>">
                                        <?php echo htmlspecialchars($course['course_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="resourceFile" class="form-label">File</label>
                            <input type="file" class="form-control" id="resourceFile" name="resourceFile">
                        </div>
                        <button type="submit" name="add_resource" class="btn btn-primary">Upload Resource</button>
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