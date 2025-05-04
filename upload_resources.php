<?php
// Start session and validate admin privileges
// session_start();
// if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
//     header("Location: login.php");
//     exit();
// }

// Include database connection
require_once 'db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user logic
        $name = trim($_POST['name']);
        $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $role = trim($_POST['role']);

        if (!$email) {
            $_SESSION['error'] = "Invalid email address.";
        } else {
            // Generate a random password (should send this to the user via email)
            $temp_password = bin2hex(random_bytes(8));
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, password, email, first_name, last_name, role, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'active')
                ");
                $stmt->execute([$email, password_hash($temp_password, PASSWORD_DEFAULT), $email, $name, '', $role]);
                $_SESSION['success'] = "User added successfully!";
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error adding user: " . $e->getMessage();
            }
        }
    } elseif (isset($_POST['action'])) {
        // Handle user actions (activate/deactivate/delete)
        $user_id = intval($_POST['user_id']);
        $action = trim($_POST['action']);
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $_SESSION['success'] = "User deleted successfully!";
            } else {
                $new_status = ($action === 'activate') ? 'approved' : 'pending';
                $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
                $stmt->execute([$new_status, $user_id]);
                $_SESSION['success'] = "User status updated!";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error processing request: " . $e->getMessage();
        }
    }
}

// Fetch all users from the database
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $_SESSION['error'] = "Error fetching users: " . $e->getMessage();
}

// Filter users based on search and tab
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'all';

$filtered_users = array_filter($users, function ($user) use ($search, $tab) {
    $matches_search = empty($search) ||
        stripos($user['first_name'] . ' ' . $user['last_name'], $search) !== false ||
        stripos($user['email'], $search) !== false;

    $matches_tab = $tab === 'all' ||
        ($tab === 'students' && $user['role'] === 'student') ||
        ($tab === 'instructors' && $user['role'] === 'instructor') ||
        ($tab === 'admins' && $user['role'] === 'admin') ||
        ($tab === 'inactive' && $user['status'] !== 'approved');

    return $matches_search && $matches_tab;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 100;
            transition: transform 0.3s;
        }
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            transition: margin-left 0.3s;
        }
        .sidebar.hidden {
            transform: translateX(-100%);
        }
        .main-content.full-width {
            margin-left: 0;
        }
        /* Table Styles */
        .user-table th {
            font-weight: 500;
        }
        .user-table td {
            vertical-align: middle;
        }
        /* Role Badges */
        .role-badge {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .role-student {
            background-color: #0d6efd;
            color: #fff;
        }
        .role-instructor {
            background-color: #6c757d;
            color: #fff;
        }
        .role-admin {
            background-color: #dc3545;
            color: #fff;
        }
        /* Status Badges */
        .status-badge {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        .status-active {
            background-color: #28a745;
        }
        .status-pending {
            background-color: #ffc107;
        }
        .status-inactive {
            background-color: #6c757d;
        }
    </style>
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
            <!-- Alerts -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <!-- Page Header -->
            <h1 class="fw-bold">User Management</h1>
            <p class="text-muted">Manage all users in the system</p>

            <!-- Search and Filter -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <form method="GET" class="input-group">
                        <input type="text" class="form-control" placeholder="Search users..." name="search" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <a href="?tab=all" class="btn btn-outline-primary <?= $tab === 'all' ? 'active' : '' ?>">All Users</a>
                        <a href="?tab=students" class="btn btn-outline-primary <?= $tab === 'students' ? 'active' : '' ?>">Students</a>
                        <a href="?tab=instructors" class="btn btn-outline-primary <?= $tab === 'instructors' ? 'active' : '' ?>">Instructors</a>
                        <a href="?tab=admins" class="btn btn-outline-primary <?= $tab === 'admins' ? 'active' : '' ?>">Administrators</a>
                        <a href="?tab=inactive" class="btn btn-outline-primary <?= $tab === 'inactive' ? 'active' : '' ?>">Inactive</a>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover user-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Last Active</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filtered_users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No users found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($filtered_users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?= $user['role'] === 'student' ? 'role-student' : 
                                                       ($user['role'] === 'instructor' ? 'role-instructor' : 'role-admin') ?> 
                                                    role-badge">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge 
                                                    <?= $user['status'] === 'approved' ? 'status-active' : 
                                                       ($user['status'] === 'pending' ? 'status-pending' : 'status-inactive') ?>">
                                                </span>
                                                <?= ucfirst($user['status']) ?>
                                            </td>
                                            <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                            <td><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never' ?></td>
                                            <td class="text-end">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-h"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="actionsDropdown">
                                                        <li><a class="dropdown-item" href="edit_user.php?id=<?= $user['user_id'] ?>"><i class="fas fa-edit me-2"></i>Edit</a></li>
                                                        <li>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                                <input type="hidden" name="action" value="<?= $user['status'] === 'approved' ? 'deactivate' : 'activate' ?>">
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas <?= $user['status'] === 'approved' ? 'fa-user-slash' : 'fa-user-check' ?> me-2"></i>
                                                                    <?= $user['status'] === 'approved' ? 'Deactivate' : 'Activate' ?>
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                                <input type="hidden" name="action" value="delete">
                                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                                    <i class="fas fa-trash me-2"></i>Delete
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="student">Student</option>
                                <option value="instructor">Instructor</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Toggle sidebar functionality
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('sidebarToggle');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('full-width');
        });
    </script>
</body>
</html>