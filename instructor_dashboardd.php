<?php
session_start();

// Redirect if not logged in or not an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}

require 'db.php'; // Include the database connection

try {
    // Fetch instructor details
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.username, i.first_name, i.last_name 
        FROM users u 
        JOIN instructors i ON u.user_id = i.user_id 
        WHERE u.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $instructor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$instructor) {
        die("Instructor not found.");
    }

    // Fetch quick stats (dynamic)
    $courseCount = $pdo->query("
        SELECT COUNT(*) 
        FROM instructor_courses 
        WHERE instructor_id = " . $_SESSION['user_id']
    )->fetchColumn();

    $recentForumPosts = $pdo->query("
        SELECT f.title, p.content, p.post_date 
        FROM forum_posts p 
        JOIN discussion_forums f ON p.forum_id = f.forum_id 
        ORDER BY p.post_date DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    $resourceUploads = $pdo->query("
        SELECT COUNT(*) 
        FROM resources 
        WHERE instructor_id = " . $_SESSION['user_id'] . " AND upload_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)"
    )->fetchColumn();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard - HU TLSS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        /* Sidebar Styling */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            color: white;
            transition: all 0.3s ease;
            overflow-y: auto;
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
        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
            padding: 20px;
        }
        /* Header Styling */
        .header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 0;
        }
        .header h4 {
            margin: 0;
        }
        /* Right Sidebar Styling */
        .right-sidebar {
            width: 300px;
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            background-color: #f8f9fa;
            padding: 20px;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
        }
        /* Card Styling */
        .dashboard-card {
            cursor: pointer;
            transition: transform 0.3s ease;
            background-color: #ffffff;
            color: #343a40;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .dashboard-card:hover {
            transform: scale(1.02);
        }
        /* Mobile Adaptation */
        @media (max-width: 992px) {
            .sidebar {
                width: 60px;
            }
            .main-content {
                margin-left: 60px;
            }
            .right-sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Left Navigation Menu -->
    <div class="sidebar" id="sidebar">
        <h5 class="text-center mt-3">Welcome, <?= htmlspecialchars($instructor['username']) ?>!</h5>
        <ul class="list-unstyled">
            <li><a href="#" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i><span class="nav-text">Dashboard</span></a></li>
            <li><a href="inst_course.php" class="nav-link"><i class="fas fa-book me-2"></i><span class="nav-text">My Courses</span></a></li>
            <li><a href="upload_resources.php" class="nav-link"><i class="fas fa-upload me-2"></i><span class="nav-text">Resources</span></a></li>
            <li><a href="discussions.php" class="nav-link"><i class="fas fa-comments me-2"></i><span class="nav-text">Discussions</span></a></li>
            <li><a href="students.php" class="nav-link"><i class="fas fa-users me-2"></i><span class="nav-text">Students</span></a></li>
            <li><a href="assessments.php" class="nav-link"><i class="fas fa-pencil-alt me-2"></i><span class="nav-text">Assessments</span></a></li>
            <li><a href="settings.php" class="nav-link"><i class="fas fa-cog me-2"></i><span class="nav-text">Settings</span></a></li>
            <li><a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt me-2"></i><span class="nav-text">Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header d-flex justify-content-between align-items-center">
            <button class="btn btn-light" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h4>HU Teaching and Learning Support System</h4>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light"><i class="fas fa-bell"></i></button>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?= htmlspecialchars($instructor['first_name']) ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Welcome Panel -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Welcome, <?= htmlspecialchars($instructor['first_name']) ?>!</h5>
                        <p class="card-text">Quick Actions:</p>
                        <div class="d-flex gap-2">
                            <a href="upload_resources.php" class="btn btn-primary"><i class="fas fa-upload me-2"></i> Upload Resource</a>
                            <a href="create_forum.php" class="btn btn-success"><i class="fas fa-comments me-2"></i> Create Forum</a>
                            <a href="schedule_discussion.php" class="btn btn-warning"><i class="fas fa-video me-2"></i> Schedule Live Session</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Summary Cards -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                        <h5 class="card-title">Active Courses</h5>
                        <p class="card-text"><?= $courseCount ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-comment-dots fa-2x mb-2"></i>
                        <h5 class="card-title">Recent Forum Posts</h5>
                        <ul class="list-group list-group-flush">
                            <?php if (!empty($recentForumPosts)): ?>
                                <?php foreach ($recentForumPosts as $post): ?>
                                    <li class="list-group-item small">
                                        <strong><?= htmlspecialchars($post['title']) ?>:</strong> <?= htmlspecialchars(substr($post['content'], 0, 30)) ?>...
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($post['post_date']) ?></small>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="list-group-item">No recent posts.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card">
                    <div class="card-body text-center">
                        <i class="fas fa-file-upload fa-2x mb-2"></i>
                        <h5 class="card-title">Resource Uploads</h5>
                        <p class="card-text"><?= $resourceUploads ?> New Downloads This Week</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Management -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title">Course Management</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Fetch courses assigned to the instructor
                        $stmt = $pdo->prepare("
                            SELECT c.course_id, c.course_name, c.course_code 
                            FROM courses c 
                            JOIN instructor_courses ic ON c.course_id = ic.course_id 
                            WHERE ic.instructor_id = ?
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <div class="row">
                            <?php if (!empty($courses)): ?>
                                <?php foreach ($courses as $course): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($course['course_name']) ?></h5>
                                                <p class="card-text"><?= htmlspecialchars($course['course_code']) ?></p>
                                                <div class="progress mb-3">
                                                    <div class="progress-bar" role="progressbar" style="width: 70%;" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100">Syllabus 70% complete</div>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <small>Enrolled: 50 students</small>
                                                    <small>New submissions: 5</small>
                                                </div>
                                                <a href="course_details.php?course_id=<?= $course['course_id'] ?>" class="btn btn-primary mt-2">Enter Course</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-info">No courses assigned to you yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="right-sidebar" id="rightSidebar">
        <h5>Today's Schedule</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">Live Q&A at 2 PM</li>
            <li class="list-group-item">Assignment Deadline: CS101 Assignment 1</li>
        </ul>

        <h5 class="mt-4">To-Do List</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item">Grade Assignment 1</li>
            <li class="list-group-item">Respond to Feedback</li>
        </ul>

        <h5 class="mt-4">Quick Links</h5>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><a href="#">University Policies</a></li>
            <li class="list-group-item"><a href="#">Tech Support</a></li>
            <li class="list-group-item"><a href="#">Training Guides</a></li>
        </ul>
    </div>

    <!-- JavaScript for Sidebar Toggle -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const rightSidebar = document.getElementById('rightSidebar');

            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('collapsed');
                sidebar.style.width = '250px';
                mainContent.style.marginLeft = '250px';
                rightSidebar.style.marginLeft = '250px';
            } else {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('collapsed');
                sidebar.style.width = '60px';
                mainContent.style.marginLeft = '60px';
                rightSidebar.style.marginLeft = '60px';
            }
        }
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>