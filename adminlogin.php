<?php
session_start();
require 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $user['username'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials!";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style/admin_login.css">
    <link rel="stylesheet" href="style/login.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="auth-container">
    <div class="auth-card shadow-lg">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="image/admin.jpg" alt="Admin Logo" class="auth-logo mb-3" style="max-width: 100px;">
                <h3 class="auth-title">
                    <i class="fas fa-user-shield me-2"></i>Admin Login
                </h3>
                <p class="text-muted">Sign in to access your Admin Panel</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="mb-4">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="auth-btn w-100">Login</button>
            </form>

            <div class="text-center mt-4">
                <a href="forgot_password.php?type=admin" class="text-decoration-none text-muted">
                    Forgot Password?
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
    Having trouble signing in? 
    <a href="contact_support.php">Contact Support</a>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>