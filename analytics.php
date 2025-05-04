<?php
session_start();
require 'db.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get current month and year for analytics
$currentMonth = date('m');
$currentYear = date('Y');

try {
    // Check if 'last_login' and 'is_active' columns exist in the 'users' table
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    // Add 'last_login' column if it doesn't exist
    if (!in_array('last_login', $columns)) {
        $pdo->exec("ALTER TABLE users ADD last_login DATETIME NULL");
    }

    // Add 'is_active' column if it doesn't exist
    if (!in_array('is_active', $columns)) {
        $pdo->exec("ALTER TABLE users ADD is_active TINYINT(1) DEFAULT 1");
    }

    // Check if 'category' column exists in the 'courses' table
    $courseColumns = $pdo->query("SHOW COLUMNS FROM courses")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('category', $courseColumns)) {
        // Add the 'category' column if it doesn't exist
        $pdo->exec("ALTER TABLE courses ADD category VARCHAR(255) NULL");
    }

    // Total users count
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Active students (logged in within last 30 days and is_active = 1)
    $activeStudents = $pdo->query("SELECT COUNT(*) FROM users 
                                  WHERE role = 'student' 
                                  AND is_active = 1 
                                  AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

    // Active instructors
    $activeInstructors = $pdo->query("SELECT COUNT(*) FROM users 
                                     WHERE role = 'instructor' 
                                     AND is_active = 1 
                                     AND last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

    // New registrations this month
    $newRegistrations = $pdo->query("SELECT COUNT(*) FROM users 
                                    WHERE YEAR(created_at) = $currentYear 
                                    AND MONTH(created_at) = $currentMonth")->fetchColumn();

    // User activity data for chart (last 6 months)
    $activityStmt = $pdo->query("
        SELECT 
            DATE_FORMAT(date, '%b') AS month,
            SUM(role = 'student') AS students,
            SUM(role = 'instructor') AS instructors
        FROM (
            SELECT last_login AS date, role FROM users
            WHERE last_login >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            UNION ALL
            SELECT created_at AS date, role FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ) AS activity
        GROUP BY YEAR(date), MONTH(date)
        ORDER BY YEAR(date), MONTH(date)
    ");
    $userActivityData = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    // Course distribution data
    $courseStmt = $pdo->query("
        SELECT c.category AS name, COUNT(*) AS value 
        FROM courses c
        GROUP BY c.category
        ORDER BY value DESC
        LIMIT 5
    ");
    $courseDistribution = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: transform 0.3s;
            border-left: 4px solid transparent;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .stat-card.total-users { border-left-color: #3b7ddd; }
        .stat-card.active-students { border-left-color: #28a745; }
        .stat-card.active-instructors { border-left-color: #ffc107; }
        .stat-card.new-registrations { border-left-color: #dc3545; }
        .chart-container {
            height: 400px;
            min-height: 300px;
        }
        .card-header {
            background-color: rgba(0, 0, 0, 0.03);
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <?php include 'admin_header.php'; ?>
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Analytics Dashboard</h2>
                    <p class="text-muted mb-0">Platform performance metrics and statistics</p>
                </div>
                <div>
                    <select class="form-select form-select-sm" style="width: 200px;">
                        <option>Last 30 Days</option>
                        <option>This Month</option>
                        <option selected>Last 6 Months</option>
                        <option>This Year</option>
                    </select>
                </div>
            </div>
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card total-users h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-2">Total Users</h6>
                                    <h2 class="mb-0"><?= number_format($totalUsers) ?></h2>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-people-fill text-primary"></i>
                                </div>
                            </div>
                            <p class="text-muted mt-3 mb-0">
                                <span class="text-success me-1">+12%</span>
                                <span>from last month</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card active-students h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-2">Active Students</h6>
                                    <h2 class="mb-0"><?= number_format($activeStudents) ?></h2>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-person-check-fill text-success"></i>
                                </div>
                            </div>
                            <p class="text-muted mt-3 mb-0">
                                <span class="text-success me-1">+5%</span>
                                <span>from last month</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card active-instructors h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-2">Active Instructors</h6>
                                    <h2 class="mb-0"><?= number_format($activeInstructors) ?></h2>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-person-gear-fill text-warning"></i>
                                </div>
                            </div>
                            <p class="text-muted mt-3 mb-0">
                                <span class="text-success me-1">+2</span>
                                <span>new this month</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card new-registrations h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="text-uppercase text-muted mb-2">New Registrations</h6>
                                    <h2 class="mb-0"><?= number_format($newRegistrations) ?></h2>
                                </div>
                                <div class="bg-danger bg-opacity-10 p-3 rounded">
                                    <i class="bi bi-person-plus-fill text-danger"></i>
                                </div>
                            </div>
                            <p class="text-muted mt-3 mb-0">
                                <span>this month</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Charts Row -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">User Activity</h5>
                            <p class="text-muted mb-0">Monthly active users by role</p>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="userActivityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Course Distribution</h5>
                            <p class="text-muted mb-0">By category</p>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="courseDistributionChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Second Charts Row -->
            <div class="row g-4 mt-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Resource Types</h5>
                            <p class="text-muted mb-0">Uploaded content distribution</p>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="resourceTypesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Engagement</h5>
                            <p class="text-muted mb-0">Daily active users</p>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="engagementChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // User Activity Chart (Bar Chart)
        const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
        new Chart(userActivityCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($userActivityData, 'month')) ?>,
                datasets: [
                    {
                        label: 'Students',
                        data: <?= json_encode(array_column($userActivityData, 'students')) ?>,
                        backgroundColor: '#3b7ddd',
                        borderRadius: 4
                    },
                    {
                        label: 'Instructors',
                        data: <?= json_encode(array_column($userActivityData, 'instructors')) ?>,
                        backgroundColor: '#ffc107',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
        // Course Distribution Chart (Doughnut)
        const courseDistributionCtx = document.getElementById('courseDistributionChart').getContext('2d');
        new Chart(courseDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($courseDistribution, 'name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($courseDistribution, 'value')) ?>,
                    backgroundColor: [
                        '#3b7ddd', '#28a745', '#ffc107', '#dc3545', '#6c757d'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        // Resource Types Chart (Pie)
        const resourceTypesCtx = document.getElementById('resourceTypesChart').getContext('2d');
        new Chart(resourceTypesCtx, {
            type: 'pie',
            data: {
                labels: ['Documents', 'Videos', 'Quizzes', 'Presentations', 'Others'],
                datasets: [{
                    data: [45, 30, 15, 7, 3],
                    backgroundColor: [
                        '#3b7ddd', '#28a745', '#ffc107', '#dc3545', '#6c757d'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        // Engagement Chart (Line)
        const engagementCtx = document.getElementById('engagementChart').getContext('2d');
        new Chart(engagementCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Active Users',
                    data: [120, 190, 170, 220, 240, 280, 320],
                    fill: true,
                    backgroundColor: 'rgba(59, 125, 221, 0.1)',
                    borderColor: '#3b7ddd',
                    tension: 0.3,
                    pointBackgroundColor: '#3b7ddd',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>