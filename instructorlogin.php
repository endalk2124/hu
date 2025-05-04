<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        try {
            // Fetch user details from the database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'instructor'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Check user status
                    if ($user['status'] === 'pending') {
                        $error = "Your account is pending approval. Please wait for admin verification.";
                    } elseif ($user['status'] === 'approved') {
                        // Log the user in
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];

                        // Redirect to the instructor dashboard
                        header("Location: instructor_dashboard.php");
                        exit();
                    } else {
                        $error = "Your account is not active. Please contact the administrator.";
                    }
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Login - HU Informatics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style/instructor_login.css">
    <link rel="stylesheet" href="style/login.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="auth-container d-flex justify-content-center align-items-center vh-100">
    <div class="auth-card shadow-lg p-4">
        <div class="card-body text-center">
            <div class="mb-4">
                <img src="image/logo.jpg" alt="Logo" class="auth-logo mb-3" style="max-width: 100px;">
                <h3 class="auth-title">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Instructor Login
                </h3>
                <p class="text-muted">Sign in to access your HU Informatics account</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <!-- Username Field -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100 auth-btn">Login</button>
            </form>

            <!-- Forgot Password Link -->
            <div class="text-center mt-3">
                <a href="forgot_password.php?type=instructor" class="text-decoration-none text-muted small">
                    Forgot Password?
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="text-center text-muted py-3 fixed-bottom">
    Having trouble signing in? 
    <a href="contact_support.php" class="text-decoration-none">Contact Support</a>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>