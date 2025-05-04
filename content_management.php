<?php


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

// Fetch resources from the database
$resourcesQuery = "
    SELECT r.resource_id AS id, r.title, r.description, r.file_path, r.upload_date, 
           c.course_name AS course, COUNT(v.view_id) AS views
    FROM resources r
    LEFT JOIN courses c ON r.course_id = c.course_id
    LEFT JOIN resource_views v ON r.resource_id = v.resource_id
    GROUP BY r.resource_id
";
$resourcesStmt = $pdo->query($resourcesQuery);
$sampleResources = $resourcesStmt->fetchAll();

// Fetch assignments from the database
$assignmentsQuery = "
    SELECT a.assignment_id AS id, a.title, c.course_name AS course, a.due_date, a.status, 
           COUNT(s.submission_id) AS submissions
    FROM assignments a
    LEFT JOIN courses c ON a.course_id = c.course_id
    LEFT JOIN submissions s ON a.assignment_id = s.assignment_id
    GROUP BY a.assignment_id
";
$assignmentsStmt = $pdo->query($assignmentsQuery);
$sampleAssignments = $assignmentsStmt->fetchAll();

// Fetch courses from the database
$coursesQuery = "
    SELECT c.course_id AS id, c.course_name AS title, c.course_code AS code, 
           COUNT(e.student_id) AS students, c.start_date, c.end_date, c.status
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    GROUP BY c.course_id
";
$coursesStmt = $pdo->query($coursesQuery);
$sampleCourses = $coursesStmt->fetchAll();

// Handle form submission for adding a new resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $title = filter_input(INPUT_POST, 'resourceTitle', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'resourceDescription', FILTER_SANITIZE_STRING);
    $courseId = filter_input(INPUT_POST, 'resourceCourse', FILTER_VALIDATE_INT);
    $filePath = '/path/to/uploaded/file'; // Replace with actual file upload logic

    try {
        $insertResourceQuery = "
            INSERT INTO resources (title, description, file_path, course_id, upload_date)
            VALUES (:title, :description, :file_path, :course_id, NOW())
        ";
        $stmt = $pdo->prepare($insertResourceQuery);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':file_path' => $filePath,
            ':course_id' => $courseId
        ]);
        header("Location: content_management.php");
        exit;
    } catch (PDOException $e) {
        error_log("Error adding resource: " . $e->getMessage());
        echo "An error occurred while adding the resource. Please try again later.";
    }
}

// Handle form submission for adding a new assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assignment'])) {
    $title = filter_input(INPUT_POST, 'assignmentTitle', FILTER_SANITIZE_STRING);
    $courseId = filter_input(INPUT_POST, 'assignmentCourse', FILTER_VALIDATE_INT);
    $dueDate = filter_input(INPUT_POST, 'dueDate', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'assignmentDescription', FILTER_SANITIZE_STRING);

    try {
        $insertAssignmentQuery = "
            INSERT INTO assignments (title, description, due_date, course_id, status)
            VALUES (:title, :description, :due_date, :course_id, 'draft')
        ";
        $stmt = $pdo->prepare($insertAssignmentQuery);
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':due_date' => $dueDate,
            ':course_id' => $courseId
        ]);
        header("Location: content_management.php");
        exit;
    } catch (PDOException $e) {
        error_log("Error adding assignment: " . $e->getMessage());
        echo "An error occurred while adding the assignment. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management</title>
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
                <h2 class="mb-4">Content Management</h2>
                <p class="text-muted">Manage your educational resources and materials</p>
            </header>

            <!-- Search and Filter -->
            <div class="mb-4 d-flex flex-column flex-sm-row gap-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-search"></i></span>
                    <input type="text" id="searchQuery" class="form-control" placeholder="Search content...">
                </div>
                <button class="btn btn-outline-secondary d-flex align-items-center">
                    <i class="fa-solid fa-filter me-2"></i> Filter
                </button>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4" id="contentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab">Resources</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments" type="button" role="tab">Assignments</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="courses-tab" data-bs-toggle="tab" data-bs-target="#courses" type="button" role="tab">Courses</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="discussions-tab" data-bs-toggle="tab" data-bs-target="#discussions" type="button" role="tab">Discussions</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="contentTabsContent">
                <!-- Resources Tab -->
                <div class="tab-pane fade show active" id="resources" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploaded</th>
                                    <th>Views</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sampleResources as $resource): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                        <td><?php echo htmlspecialchars($resource['course']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo strtoupper(pathinfo($resource['file_path'], PATHINFO_EXTENSION)); ?></span></td>
                                        <td>-- KB</td>
                                        <td><?php echo htmlspecialchars($resource['upload_date']); ?></td>
                                        <td><?php echo htmlspecialchars($resource['views']); ?></td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">View Details</a></li>
                                                    <li><a class="dropdown-item" href="#">Edit</a></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                        <i class="fa-solid fa-plus me-2"></i> Add Resource
                    </button>
                </div>

                <!-- Assignments Tab -->
                <div class="tab-pane fade" id="assignments" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Submissions</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sampleAssignments as $assignment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['course']); ?></td>
                                        <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $assignment['status'] === 'published' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo ucfirst($assignment['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($assignment['submissions']); ?></td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">View Details</a></li>
                                                    <li><a class="dropdown-item" href="#">Edit</a></li>
                                                    <li><a class="dropdown-item" href="#">Publish</a></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                        <i class="fa-solid fa-calendar-days me-2"></i> Add Assignment
                    </button>
                </div>

                <!-- Courses Tab -->
                <div class="tab-pane fade" id="courses" role="tabpanel">
                    <div class="row">
                        <?php foreach ($sampleCourses as $course): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Code:</strong> <?php echo htmlspecialchars($course['code']); ?></p>
                                        <p><strong>Students:</strong> <?php echo htmlspecialchars($course['students']); ?></p>
                                        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($course['start_date']); ?></p>
                                        <p><strong>End Date:</strong> <?php echo htmlspecialchars($course['end_date']); ?></p>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-outline-secondary btn-sm w-100">Resources</button>
                                            <button class="btn btn-outline-secondary btn-sm w-100">Students</button>
                                        </div>
                                        <button class="btn btn-primary btn-sm mt-2 w-100">Manage Course</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Discussions Tab -->
                <div class="tab-pane fade" id="discussions" role="tabpanel">
                    <div class="text-center py-5">
                        <i class="fa-solid fa-comments fa-3x text-muted mb-3"></i>
                        <h4>No discussions yet</h4>
                        <p>Create your first discussion topic for students to engage with.</p>
                        <button class="btn btn-primary">
                            <i class="fa-solid fa-plus me-2"></i> Create Discussion
                        </button>
                    </div>
                </div>
            </div>
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
                    <form method="POST" action="">
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
                                <?php foreach ($sampleCourses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <?php echo htmlspecialchars($course['title']); ?>
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

    <!-- Add Assignment Modal -->
    <div class="modal fade" id="addAssignmentModal" tabindex="-1" aria-labelledby="addAssignmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAssignmentModalLabel">Create New Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="assignmentTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="assignmentTitle" name="assignmentTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="assignmentCourse" class="form-label">Course</label>
                            <select class="form-select" id="assignmentCourse" name="assignmentCourse" required>
                                <option value="">Select course</option>
                                <?php foreach ($sampleCourses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['id']); ?>">
                                        <?php echo htmlspecialchars($course['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" name="dueDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="assignmentDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="assignmentDescription" name="assignmentDescription" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="add_assignment" class="btn btn-primary">Create Assignment</button>
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