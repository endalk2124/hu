<?php
// instructor_sidebar.php

// Start the session to access session variables
session_start();

// Check if the user is logged in and has the role of instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: login.php");
    exit();
}

// Fetch user details from the session
$user = [
    'name' => $_SESSION['username'] ?? 'Instructor',
    'email' => $_SESSION['email'] ?? 'instructor@example.com'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Portal - HU Informatics</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: #f8f9fa;
            --sidebar-text: #212529;
            --sidebar-active: #e9ecef;
            --sidebar-hover: #e9ecef;
            --primary-color: #0d6efd;
            --success-color: #198754;
            --warning-color: #fd7e14;
            --danger-color: #dc3545;
        }
        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s;
            border-right: 1px solid #dee2e6;
        }
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -250px;
                position: fixed;
                z-index: 1000;
                height: 100vh;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--sidebar-text);
            padding: 10px 15px;
            transition: all 0.3s;
        }
        .sidebar-menu a.active,
        .sidebar-menu a:hover {
            background-color: var(--sidebar-hover);
        }
        .sidebar-menu a i {
            margin-right: 10px;
        }
        .sidebar-footer {
            border-top: 1px solid #dee2e6;
            padding: 15px;
        }
        .sidebar-footer .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-footer .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .sidebar-footer .logout-btn {
            width: 100%;
            justify-content: start;
        }
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
        }
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 d-md-block sidebar bg-white text-dark p-0">
        <div class="sidebar-header border-bottom px-3 py-4">
            <div class="d-flex align-items-center gap-2 font-semibold">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Instructor Portal</span>
            </div>
        </div>
        <div class="sidebar-content">
            <ul class="sidebar-menu list-unstyled m-0">
                <?php
                $sidebarLinks = [
                    ["title" => "Dashboard", "icon" => "home", "href" => "instructor_dashboard.php"],
                    ["title" => "My Courses", "icon" => "book", "href" => "instructor_courses.php"],
                    ["title" => "Content Management", "icon" => "file-alt", "href" => "content_management.php"],
                    ["title" => "Discussion Forums", "icon" => "comments", "href" => "discussion_forums.php"],
                    ["title" => "Schedule", "icon" => "calendar-alt", "href" => "schedule_room.php"],
                    ["title" => "Real-Time Discussions", "icon" => "comment-dots", "href" => "real_time_discussion.php"],
                    ["title" => "Reports & Analytics", "icon" => "chart-line", "href" => "reports.php"],
                    ["title" => "Settings", "icon" => "cog", "href" => "settings.php"],
                ];
                $currentPage = basename($_SERVER['PHP_SELF']);
                foreach ($sidebarLinks as $link) {
                    $isActive = ($currentPage === basename($link['href']));
                    echo '<li class="sidebar-menu-item">';
                    echo '<a href="' . htmlspecialchars($link['href']) . '" class="sidebar-menu-link ' . ($isActive ? 'active' : '') . '">';
                    echo '<i class="fas fa-' . htmlspecialchars($link['icon']) . ' me-2"></i>';
                    echo '<span>' . htmlspecialchars($link['title']) . '</span>';
                    echo '</a>';
                    echo '</li>';
                }
                ?>
            </ul>
        </div>
        <div class="sidebar-footer border-top p-3">
            <div class="user-info mb-3">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <?php echo strtoupper(htmlspecialchars(substr($user['name'], 0, 1))); ?>
                </div>
                <div>
                    <span class="fw-medium"><?php echo htmlspecialchars($user['name']); ?></span><br>
                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                </div>
            </div>
            <form action="logout.php" method="POST">
                <button type="submit" class="btn btn-outline-danger w-100 logout-btn">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar toggle functionality
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>