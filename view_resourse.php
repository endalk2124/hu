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

// Fetch instructor details
try {
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, i.first_name, i.last_name 
        FROM users u 
        JOIN instructors i ON u.user_id = i.user_id 
        WHERE u.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Fetch courses assigned to the instructor
$stmt = $pdo->prepare("
    SELECT c.course_id, c.course_name, c.course_code 
    FROM courses c 
    JOIN instructor_courses ic ON c.course_id = ic.course_id 
    WHERE ic.instructor_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch resources uploaded by the instructor
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_course = isset($_GET['course']) ? intval($_GET['course']) : 0;

$query = "
    SELECT r.resource_id, r.title, r.description, r.file_path, r.upload_date, c.course_name 
    FROM resources r 
    LEFT JOIN courses c ON r.course_id = c.course_id 
    WHERE r.instructor_id = :instructor_id
";
$params = [':instructor_id' => $_SESSION['user_id']];

if (!empty($search_query)) {
    $query .= " AND r.title LIKE :search";
    $params[':search'] = '%' . $search_query . '%';
}
if ($selected_course > 0) {
    $query .= " AND r.course_id = :course_id";
    $params[':course_id'] = $selected_course;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resource Management - Instructor</title>
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
        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
            }
            .main-content {
                margin-left: 60px;
            }
        }
        /* Table Styling */
        .resource-table {
            border-collapse: collapse;
            width: 100%;
        }
        .resource-table th, .resource-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .resource-table th {
            background-color: #007bff;
            color: white;
        }
        .resource-table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .resource-table tr:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h5 class="text-center mt-3">Welcome, <?= htmlspecialchars($instructor['username']) ?>!</h5>
        <ul class="list-unstyled">
            <li><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i><span class="nav-text">Dashboard</span></a></li>
            <li><a href="my_courses.php" class="nav-link"><i class="fas fa-book me-2"></i><span class="nav-text">My Courses</span></a></li>
            <li><a href="upload_resources.php" class="nav-link active"><i class="fas fa-upload me-2"></i><span class="nav-text">Upload Resources</span></a></li>
            <li><a href="view_resources.php" class="nav-link"><i class="fas fa-eye me-2"></i><span class="nav-text">View Resources</span></a></li>
            <li><a href="discussions.php" class="nav-link"><i class="fas fa-comments me-2"></i><span class="nav-text">Discussions</span></a></li>
            <li><a href="students.php" class="nav-link"><i class="fas fa-users me-2"></i><span class="nav-text">Students</span></a></li>
            <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt me-2"></i><span class="nav-text">Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="header d-flex justify-content-between align-items-center">
            <button class="btn btn-light" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h4>Resource Management</h4>
        </div>

        <div class="container mt-5">
            <h2 class="mb-4">Manage Resources</h2>

            <!-- Search and Filter -->
            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search by title" value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="course">
                            <option value="">Filter by Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['course_id'] ?>" <?= $selected_course == $course['course_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['course_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </div>
            </form>

            <!-- Resource Table -->
            <table class="resource-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Course</th>
                        <th>Uploaded On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($resources)): ?>
                        <?php foreach ($resources as $resource): ?>
                            <tr>
                                <td><?= htmlspecialchars($resource['title']) ?></td>
                                <td><?= htmlspecialchars($resource['description']) ?></td>
                                <td><?= htmlspecialchars($resource['course_name']) ?: '-' ?></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($resource['upload_date']))) ?></td>
                                <td>
                                    <a href="<?= htmlspecialchars($resource['file_path']) ?>" class="btn btn-sm btn-success" target="_blank">
                                        <i class="fas fa-download me-1"></i> Download
                                    </a>
                                    <a href="edit_resource.php?resource_id=<?= $resource['resource_id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <a href="delete_resource.php?resource_id=<?= $resource['resource_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this resource?');">
                                        <i class="fas fa-trash me-1"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No resources found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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