<?php
session_start();
require '../db.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

// Handle Department Addition
if (isset($_POST['add_department'])) {
    $department_name = trim($_POST['department_name']);

    // Check if department already exists
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE department_name = ?");
    $stmt->execute([$department_name]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Department already exists!";
    } else {
        // Insert new department
        $stmt = $pdo->prepare("INSERT INTO departments (department_name) VALUES (?)");
        $stmt->execute([$department_name]);
        $_SESSION['message'] = "Department added successfully!";
    }
    header("Location: departments.php");
    exit();
}

// Fetch all departments
$stmt = $pdo->query("SELECT * FROM departments");
$departments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments</title>
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
        <a href="courses.php"><i class="fas fa-book"></i> Courses</a>
        <a href="../feedback.php"><i class="fas fa-comments"></i> Feedback</a>
        <a href="../logs.php"><i class="fas fa-history"></i> System Logs</a>
        <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <div class="container mt-5">
            <h2 class="mb-4">Manage Departments</h2>

            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search departments...">
            </div>

            <!-- Add Department Form -->
            <div class="card mb-4">
                <div class="card-header">Add New Department</div>
                <div class="card-body">
                    <form method="POST" id="addDepartmentForm">
                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" class="form-control" name="department_name" id="departmentName" required>
                            <small class="form-text text-muted" id="departmentValidationMessage"></small>
                        </div>
                        <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
                    </form>
                </div>
            </div>

            <!-- Departments List -->
            <div class="card">
                <div class="card-header">All Departments</div>
                <div class="card-body">
                    <table class="table table-bordered" id="departmentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td><?= htmlspecialchars($dept['department_id']) ?></td>
                                    <td><?= htmlspecialchars($dept['department_name']) ?></td>
                                    <td>
                                        <a href="edit_department.php?department_id=<?= $dept['department_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete_department.php?department_id=<?= $dept['department_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this department?')">Delete</a>
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

        // Real-Time Validation for Department Name Uniqueness
        document.getElementById('departmentName').addEventListener('blur', function () {
            const departmentName = this.value.trim();
            const validationMessage = document.getElementById('departmentValidationMessage');
            if (departmentName) {
                fetch('check_department_name.php?department_name=' + encodeURIComponent(departmentName))
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            validationMessage.textContent = 'This department already exists!';
                            validationMessage.style.color = 'red';
                        } else {
                            validationMessage.textContent = 'Department name is available.';
                            validationMessage.style.color = 'green';
                        }
                    })
                    .catch(error => console.error('Error checking department name:', error));
            } else {
                validationMessage.textContent = '';
            }
        });

        // Search and Filter Functionality
        document.getElementById('searchInput').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#departmentsTable tbody tr');

            rows.forEach(row => {
                const departmentName = row.cells[1].textContent.toLowerCase();
                if (departmentName.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>