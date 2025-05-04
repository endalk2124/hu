<?php
session_start();
require 'db.php'; // Include the database connection

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

// Fetch stats data
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCourses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalResources = $pdo->query("SELECT COUNT(*) FROM resources")->fetchColumn();
$pendingUsersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();

// Pagination for Pending Users
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'pending' LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPendingUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'pending'")->fetchColumn();
$totalPages = ceil($totalPendingUsers / $limit);

// Recent activities
$activities = [
    [
        'title' => 'New User Registered',
        'details' => 'John Smith registered as a student',
        'time' => '10 minutes ago'
    ],
    [
        'title' => 'Course Added',
        'details' => 'Intro to Cybersecurity was added by Dr. Johnson',
        'time' => '2 hours ago'
    ],
    [
        'title' => 'System Update',
        'details' => 'File storage system was upgraded',
        'time' => 'Yesterday, 11:30 PM'
    ],
    [
        'title' => 'User Role Changed',
        'details' => 'Maria Garcia was promoted to instructor',
        'time' => '2 days ago'
    ]
];

// System metrics
$systemMetrics = [
    [
        'title' => 'System Uptime',
        'value' => '99.98%',
        'trend' => 'Last 30 days'
    ],
    [
        'title' => 'Database Size',
        'value' => '24.6 GB',
        'trend' => '+1.2 GB this month'
    ],
    [
        'title' => 'API Requests',
        'value' => '532K',
        'trend' => 'Daily average'
    ],
    [
        'title' => 'Active Sessions',
        'value' => '247',
        'trend' => 'Current'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    
</head>
<body>
    <!-- Sidebar -->
    <?php include 'admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <?php include 'admin_header.php'; ?>

        <!-- Dashboard Content -->
        <div class="container-fluid p-4">
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

            <!-- Welcome Header -->
            <div class="mb-4">
                <h2 class="fw-bold">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></h2>
                <p class="text-muted">System administration dashboard overview</p>
            </div>

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Users</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">System</button>
                </li>
            </ul>

            <!-- Tabs Content -->
            <div class="tab-content" id="dashboardTabsContent">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <!-- Stats Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card card p-4">
                                <div class="card-icon text-primary">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h6 class="card-title">Total Users</h6>
                                <div class="card-value"><?= number_format($totalUsers) ?></div>
                                <p class="text-muted small">+12% from last month</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card card p-4">
                                <div class="card-icon text-success">
                                    <i class="bi bi-book"></i>
                                </div>
                                <h6 class="card-title">Total Courses</h6>
                                <div class="card-value"><?= number_format($totalCourses) ?></div>
                                <p class="text-muted small">+3 new this week</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card card p-4">
                                <div class="card-icon text-warning">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <h6 class="card-title">Resources</h6>
                                <div class="card-value"><?= number_format($totalResources) ?></div>
                                <p class="text-muted small">+24 this week</p>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <div class="stat-card card p-4">
                                <div class="card-icon text-danger">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <h6 class="card-title">Pending Users</h6>
                                <div class="card-value"><?= number_format($pendingUsersCount) ?></div>
                                <p class="text-muted small">Need approval</p>
                            </div>
                        </div>
                    </div>
                    <!-- Quick Actions -->
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="user-management.php" class="btn btn-outline-primary d-flex align-items-center">
                            <i class="bi bi-people me-2"></i>
                            <span>Manage Users</span>
                        </a>
                        <a href="analytics.php" class="btn btn-outline-primary d-flex align-items-center">
                            <i class="bi bi-bar-chart me-2"></i>
                            <span>View Analytics</span>
                        </a>
                        <a href="add-course.php" class="btn btn-outline-primary d-flex align-items-center">
                            <i class="bi bi-plus-circle me-2"></i>
                            <span>Add New Course</span>
                        </a>
                    </div>
                    <div class="row g-4">
                        <!-- Recent Activities -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Recent Activities</h5>
                                </div>
                                <div class="card-body p-0">
                                    <?php foreach ($activities as $activity): ?>
                                        <div class="activity-item">
                                            <h6 class="mb-1 fw-semibold"><?= $activity['title'] ?></h6>
                                            <p class="mb-1 text-muted small"><?= $activity['details'] ?></p>
                                            <small class="text-muted"><?= $activity['time'] ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="p-3 text-center border-top">
                                        <a href="logs.php" class="btn btn-link">View all activity</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pending Users -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Pending Approvals</h5>
                                    <span class="badge bg-danger"><?= $pendingUsersCount ?> pending</span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if ($pendingUsers): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($pendingUsers as $user): ?>
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <h6 class="mb-0"><?= htmlspecialchars($user['username']) ?></h6>
                                                            <small class="text-muted"><?= ucfirst(htmlspecialchars($user['role'])) ?></small>
                                                        </div>
                                                        <div class="btn-group">
                                                            <a href="approve_user.php?action=approve&id=<?= $user['user_id'] ?>" class="btn btn-sm btn-success">
                                                                <i class="bi bi-check"></i>
                                                            </a>
                                                            <a href="approve_user.php?action=reject&id=<?= $user['user_id'] ?>" class="btn btn-sm btn-danger">
                                                                <i class="bi bi-x"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="p-3 border-top">
                                            <nav aria-label="Page navigation">
                                                <ul class="pagination pagination-sm justify-content-center mb-0">
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
                                    <?php else: ?>
                                        <div class="p-3 text-center">
                                            <p class="text-muted mb-0">No pending users found.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Users Tab -->
                <div class="tab-pane fade" id="users" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">User Management</h5>
                            <a href="user-management.php" class="btn btn-primary">
                                <i class="bi bi-people me-2"></i>
                                <span>Manage Users</span>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6>User Statistics</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <tr>
                                                        <td>Total Users</td>
                                                        <td class="text-end fw-bold"><?= number_format($totalUsers) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Active Users</td>
                                                        <td class="text-end fw-bold"><?= number_format($totalUsers - $pendingUsersCount) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Pending Approval</td>
                                                        <td class="text-end fw-bold"><?= number_format($pendingUsersCount) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td>New This Month</td>
                                                        <td class="text-end fw-bold">124</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>User Distribution</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Students</td>
                                                    <td class="text-end fw-bold">1,050 (84%)</td>
                                                </tr>
                                                <tr>
                                                    <td>Instructors</td>
                                                    <td class="text-end fw-bold">165 (13%)</td>
                                                </tr>
                                                <tr>
                                                    <td>Administrators</td>
                                                    <td class="text-end fw-bold">30 (3%)</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- System Tab -->
                <div class="tab-pane fade" id="system" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">System Metrics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-4">
                                        <h6>System Statistics</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tbody>
                                                    <?php foreach ($systemMetrics as $metric): ?>
                                                        <tr>
                                                            <td><?= $metric['title'] ?></td>
                                                            <td class="text-end fw-bold"><?= $metric['value'] ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>System Status</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Database</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-success">Operational</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>API Services</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-success">Operational</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Storage System</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-success">Operational</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Authentication</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-success">Operational</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('sidebar-show');
        });
        // Search functionality for pending users
        document.getElementById('searchInput')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#pendingUsersTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>