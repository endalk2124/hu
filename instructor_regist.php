<?php
require 'db.php';
session_start();

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $qualification = trim($_POST['qualification']); // New field for instructor
    $role = 'instructor'; // Role is explicitly set to 'instructor'

    try {
        $pdo->beginTransaction();

        // Insert into users table with explicit 'pending' status
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$username, $password, $email, $role]);
        $user_id = $pdo->lastInsertId();

        // Insert into instructors table
        $stmt = $pdo->prepare("INSERT INTO instructors (user_id, first_name, last_name, phone, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $first_name, $last_name, $phone, $email]);

        $pdo->commit();
        $success = true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style/registration.css">
    <!-- JavaScript Validation -->
    <script src="js/validation.js" defer></script>
</head>
<body class="bg-light">
<?php include 'navbar.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="registration-card shadow-lg">
                <div class="card-body p-5">
                    <h3 class="card-title text-center mb-3 text-primary">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Instructor Registration
                    </h3>
                    <p class="text-center text-muted mb-4">Join the HU Informatics learning platform as an instructor</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if (!$success): ?>
                        <form method="POST" id="registrationForm">
                            <!-- Form fields -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="first_name" id="first_name" required>
                                    <div class="invalid-feedback" id="first_name_error"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" id="last_name" required>
                                    <div class="invalid-feedback" id="last_name_error"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required>
                                <div class="invalid-feedback" id="username_error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                                <div class="invalid-feedback" id="email_error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                                <div class="password-hint">Minimum 8 characters with numbers and letters</div>
                                <div class="invalid-feedback" id="password_error"></div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                                <div class="invalid-feedback" id="confirm_password_error"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" id="phone" placeholder="e.g., 0912345678">
                                    <div class="invalid-feedback" id="phone_error"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Qualification</label>
                                    <input type="text" class="form-control" name="qualification" id="qualification" placeholder="e.g., PhD in Computer Science">
                                    <div class="invalid-feedback" id="qualification_error"></div>
                                </div>
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>
                            </div>
                            <p class="text-center mt-3 text-muted">
                                Already have an account? 
                                <a href="instructorlogin.php" class="text-primary">Sign In</a>
                            </p>
                        </form>
                    <?php else: ?>
                        <script>
                            window.onload = function () {
                                const toast = document.getElementById('toast');
                                toast.style.display = 'block';

                                setTimeout(() => {
                                    toast.style.animation = 'slideOut 0.5s ease-out forwards';
                                    setTimeout(() => {
                                        window.location.href = "instructorlogin.php";
                                    }, 2000);
                                }, 2000);
                            };
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" style="display: none;">
    <div class="toast-header bg-warning text-dark">
        <strong class="me-auto">Registration Submitted</strong>
    </div>
    <div class="toast-body">
        Your account has been created and is pending admin approval. 
        You'll be redirected to the login page shortly.
    </div>
</div>

<!-- Footer -->
<footer class="footer">
    <div class="text-center">
        By registering, you agree to our 
        <a href="/terms-of-service">Terms of Service</a> and 
        <a href="/privacy-policy">Privacy Policy</a>.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>