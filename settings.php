<?php
session_start();
require 'db.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile logic
        $name = htmlspecialchars(trim($_POST['name']));
        $bio = htmlspecialchars(trim($_POST['bio']));
        try {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ? WHERE user_id = ?");
            $stmt->execute([$name, $bio, $_SESSION['user_id']]);
            $_SESSION['message'] = "Profile updated successfully";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
        }
    }

    if (isset($_POST['update_password'])) {
        // Password update logic
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                $_SESSION['message'] = "Password updated successfully";
            } else {
                $_SESSION['error'] = "New passwords don't match";
            }
        } else {
            $_SESSION['error'] = "Current password is incorrect";
        }
    }

    if (isset($_POST['update_settings'])) {
        // Update settings logic
        $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("UPDATE users SET dark_mode = ?, email_notifications = ?, push_notifications = ? WHERE user_id = ?");
            $stmt->execute([$dark_mode, $email_notifications, $push_notifications, $_SESSION['user_id']]);
            $_SESSION['message'] = "Settings updated successfully";

            // Set dark mode cookie
            if ($dark_mode) {
                setcookie('dark_mode', '1', time() + (86400 * 30), "/");
            } else {
                setcookie('dark_mode', '0', time() + (86400 * 30), "/");
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating settings: " . $e->getMessage();
        }
    }
}

// Get current user settings
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en" class="<?= isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] == '1' ? 'dark' : '' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Light Mode Styles */
        body {
            background-color: #f8f9fa;
            color: #212529;
            transition: background-color 0.3s, color 0.3s;
        }
        .card {
            background-color: #ffffff;
            border-color: #dee2e6;
        }
        .form-control, .form-select {
            background-color: #ffffff;
            border-color: #ced4da;
            color: #212529;
        }

        /* Dark Mode Styles */
        .dark body {
            background-color: #1a1a1a;
            color: #f8f9fa;
        }
        .dark .card {
            background-color: #2d2d2d;
            border-color: #444;
        }
        .dark .form-control, .dark .form-select {
            background-color: #333;
            border-color: #444;
            color: #f8f9fa;
        }
        .dark .text-muted {
            color: #adb5bd !important;
        }
        .dark .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .dark .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        textarea.form-control {
            min-height: 100px;
        }
    </style>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="main-content">
        <?php include 'admin_header.php'; ?>
        <div class="container-fluid p-4">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <div class="mb-4">
                <h2>Settings</h2>
                <p class="text-muted">Manage your account settings and preferences</p>
            </div>
            <ul class="nav nav-tabs mb-4" id="settingsTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general">General</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile">Profile</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security">Security</button>
                </li>
            </ul>
            <div class="tab-content">
                <!-- General Settings -->
                <div class="tab-pane fade show active" id="general">
                    <form method="POST">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Appearance</h5>
                                <p class="card-subtitle text-muted">Customize the application appearance</p>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="darkMode" name="dark_mode" <?= ($user['dark_mode'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="darkMode">Dark Mode</label>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="card-title">Notifications</h5>
                                <p class="card-subtitle text-muted">Configure how you receive notifications</p>
                            </div>
                            <div class="card-body">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="emailNotifications" name="email_notifications" <?= ($user['email_notifications'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="emailNotifications">Email Notifications</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="pushNotifications" name="push_notifications" <?= ($user['push_notifications'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pushNotifications">Push Notifications</label>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="update_settings" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Profile Settings -->
                <div class="tab-pane fade" id="profile">
                    <form method="POST">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Profile</h5>
                                <p class="card-subtitle text-muted">Update your personal information</p>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- Security Settings -->
                <div class="tab-pane fade" id="security">
                    <form method="POST">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Password</h5>
                                <p class="card-subtitle text-muted">Update your password</p>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle dark mode immediately when switched
        const darkModeCheckbox = document.getElementById('darkMode');
        const htmlElement = document.documentElement;

        // Check if dark mode is enabled from cookie
        const isDarkMode = document.cookie.split('; ').find(row => row.startsWith('dark_mode='));
        if (isDarkMode && isDarkMode.split('=')[1] === '1') {
            htmlElement.classList.add('dark');
            darkModeCheckbox.checked = true;
        }

        // Add event listener for dark mode toggle
        darkModeCheckbox.addEventListener('change', function () {
            htmlElement.classList.toggle('dark', this.checked);
            // Set cookie based on checkbox state
            document.cookie = `dark_mode=${this.checked ? '1' : '0'}; path=/; max-age=${this.checked ? 86400 * 30 : 0}`;
        });
    </script>
</body>
</html>