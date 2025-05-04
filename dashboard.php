<?php
session_start();
require 'db.php'; // Include the database connection

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

// Pagination for Pending Users
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of users per page
$offset = ($page - 1) * $limit;

// Fetch pending users with pagination
$stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'pending' LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total pending users for pagination
$totalPendingUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
$totalPages = ceil($totalPendingUsers / $limit);

// Fetch stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalDepartments = $pdo->query("SELECT COUNT(*) FROM departments")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        /* Navbar styling */
        .navbar {
            background-color: #343a40;
            color: white;
            padding: 10px 20px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000; /* Ensure navbar stays on top */
        }
        .navbar-brand {
            color: white !important;
        }

        /* Sidebar styling */
        .sidebar {
            height: calc(100vh - 60px); /* Adjust for navbar height */
            position: fixed;
            top: 60px; /* Below the navbar */
            left: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            transition: margin-left 0.3s ease;
            overflow-y: auto;
            z-index: 999; /* Ensure sidebar stays below navbar */
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

        /* Toggle Button Styling */
        .toggle-btn {
            position: fixed;
            top: 15px; /* Slightly below the navbar */
            left: 15px;
            z-index: 1001; /* Ensure toggle button is above navbar */
            background-color: #343a40;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        /* Main Content Styling */
        .content {
            margin-left: 250px; /* Same as sidebar width */
            margin-top: 60px; /* Below the navbar */
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
    <nav class="navbar">
        <div class="container-fluid">
            <span class="navbar-brand">Admin Dashboard</span>
        </div>
    </nav>

    <!-- Toggle Button for Sidebar -->
    <button class="toggle-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i> <!-- Font Awesome icon -->
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h4 class="text-center mt-3">Admin Panel</h4>
        <a href="homepage.php"><i class="fas fa-tachometer-alt"></i> home</a>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="approve_user.php"><i class="fas fa-user-check"></i> Approve Users</a>
        <a href="admin/departments.php"><i class="fas fa-building"></i> Departments</a>
        <a href="admin/courses.php"><i class="fas fa-book"></i> Courses</a>
        <a href="admin/feedback.php"><i class="fas fa-comments"></i> Feedback</a>
        <a href="logs.php"><i class="fas fa-history"></i> System Logs</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <!-- Display Success or Error Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?= htmlspecialchars($_SESSION['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="container mt-5">
            <h2 class="mb-4">Dashboard</h2>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Pending Users</h5>
                            <p class="card-text"><?= $pendingUsers ? count($pendingUsers) : 0 ?></p>
                            <a href="approve_user.php" class="btn btn-dark">Manage</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Users</h5>
                            <p class="card-text"><?= $totalUsers ?? 0 ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Departments</h5>
                            <p class="card-text"><?= $totalDepartments ?? 0 ?></p>
                            <a href="admin/departments.php" class="btn btn-light">Manage</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Users Table -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Pending Users</span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm m-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pendingUsers): ?>
                                <?php foreach ($pendingUsers as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['user_id'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($user['username'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                        <td><?= ucfirst(htmlspecialchars($user['role'] ?? 'N/A')) ?></td>
                                        <td>
                                            <a href="approve_user.php?user_id=<?= $user['user_id'] ?>&action=approve" class="btn btn-success btn-sm">Approve</a>
                                            <a href="approve_user.php?user_id=<?= $user['user_id'] ?>&action=reject" class="btn btn-danger btn-sm">Reject</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No pending users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity (Optional) -->
            <div class="card">
                <div class="card-header">Recent Activity</div>
                <div class="card-body">
                    <ul>
                        <li>User JohnDoe logged in at 10:00 AM.</li>
                        <li>Admin approved user JaneDoe at 9:45 AM.</li>
                        <li>New course "Mathematics 101" added at 9:30 AM.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript to toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('collapsed');
        }
    </script>
</body>
</html>