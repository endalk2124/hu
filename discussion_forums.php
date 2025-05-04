<?php
// Start the session at the very beginning


// Check if the user is authenticated and is an instructor


// Default user data in case session data is missing (fallback)
$user = [
    'id' => $_SESSION['user']['id'] ?? null,
    'name' => $_SESSION['user']['name'] ?? 'Instructor',
    'email' => $_SESSION['user']['email'] ?? 'instructor@example.com',
    'role' => $_SESSION['user']['role'] ?? 'instructor'
];

// Database connection
$host = 'localhost';
$dbname = 'ccs';
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle POST requests for forum management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_forum':
                // Create a new discussion forum
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $course_id = intval($_POST['course_id']);

                if (!empty($title) && !empty($description)) {
                    $stmt = $pdo->prepare("INSERT INTO discussion_forums (title, description, course_id, instructor_id) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $course_id, $user['id']]);
                    echo "<script>alert('Forum created successfully!');</script>";
                } else {
                    echo "<script>alert('Please fill all required fields.');</script>";
                }
                break;

            case 'delete_post':
                // Delete a post from a forum
                $post_id = intval($_POST['post_id']);
                $stmt = $pdo->prepare("DELETE FROM forum_posts WHERE post_id = ?");
                $stmt->execute([$post_id]);
                echo "<script>alert('Post deleted successfully!');</script>";
                break;

            case 'edit_post':
                // Edit a post in a forum
                $post_id = intval($_POST['post_id']);
                $new_content = trim($_POST['new_content']);
                if (!empty($new_content)) {
                    $stmt = $pdo->prepare("UPDATE forum_posts SET content = ? WHERE post_id = ?");
                    $stmt->execute([$new_content, $post_id]);
                    echo "<script>alert('Post updated successfully!');</script>";
                } else {
                    echo "<script>alert('Content cannot be empty.');</script>";
                }
                break;
        }
    }
}

// Fetch instructor's forums
$stmt = $pdo->prepare("SELECT * FROM discussion_forums WHERE instructor_id = ?");
$stmt->execute([$user['id']]);
$forums = $stmt->fetchAll();

// Fetch participation statistics
$stmt = $pdo->prepare("
    SELECT f.title AS forum_title, COUNT(p.post_id) AS total_posts
    FROM discussion_forums f
    LEFT JOIN forum_posts p ON f.forum_id = p.forum_id
    WHERE f.instructor_id = ?
    GROUP BY f.forum_id
");
$stmt->execute([$user['id']]);
$statistics = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion Forums - Instructor Portal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .forum-card {
            margin-bottom: 20px;
        }
        .stats-table {
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar bg-white text-dark p-3">
            <?php include 'instructor_sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 ms-sm-auto col-lg-10 main-content bg-light">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
                <div class="container-fluid">
                    <button class="btn btn-light me-2 d-lg-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                    <span class="navbar-brand fw-bold">HU Informatics - Instructor</span>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="container-fluid p-4">
                <header>
                    <h2 class="mb-4">Discussion Forums</h2>
                    <p class="text-muted">Manage and moderate discussion forums for your courses.</p>
                </header>

                <!-- Create New Forum Modal -->
                <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#createForumModal">
                    <i class="fas fa-plus me-2"></i>Create New Forum
                </button>

                <!-- Manage Forums -->
                <?php if (count($forums) > 0): ?>
                    <?php foreach ($forums as $forum): ?>
                        <div class="card forum-card">
                            <div class="card-header">
                                <?= htmlspecialchars($forum['title']) ?>
                            </div>
                            <div class="card-body">
                                <p><?= htmlspecialchars($forum['description']) ?></p>
                                <h5>Posts</h5>
                                <?php
                                $stmt = $pdo->prepare("SELECT * FROM forum_posts WHERE forum_id = ?");
                                $stmt->execute([$forum['forum_id']]);
                                $posts = $stmt->fetchAll();
                                ?>
                                <ul class="list-group">
                                    <?php foreach ($posts as $post): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>User ID: <?= htmlspecialchars($post['user_id']) ?></strong><br>
                                                <?= htmlspecialchars($post['content']) ?>
                                            </div>
                                            <div>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                                    <input type="hidden" name="action" value="delete_post">
                                                    <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                </form>
                                                <button class="btn btn-warning btn-sm ms-2" onclick="editPost(<?= $post['post_id'] ?>, '<?= addslashes($post['content']) ?>')">Edit</button>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-comments fa-3x text-muted mb-3"></i>
                        <h4>No discussion forums yet</h4>
                        <p>Create your first discussion forum for students to engage with.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createForumModal">
                            <i class="fa-solid fa-plus me-2"></i>Create Discussion Forum
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Participation Statistics -->
                <h2>Participation Statistics</h2>
                <table class="table stats-table">
                    <thead>
                        <tr>
                            <th>Forum Title</th>
                            <th>Total Posts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statistics as $stat): ?>
                            <tr>
                                <td><?= htmlspecialchars($stat['forum_title']) ?></td>
                                <td><?= $stat['total_posts'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create New Forum Modal -->
    <div class="modal fade" id="createForumModal" tabindex="-1" aria-labelledby="createForumModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createForumModalLabel">Create New Discussion Forum</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_forum">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="course_id" class="form-label">Course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select Course</option>
                                <?php
                                $courses = $pdo->query("SELECT * FROM courses")->fetchAll();
                                foreach ($courses as $course) {
                                    echo "<option value='{$course['course_id']}'>{$course['title']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Forum</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPost(postId, currentContent) {
            const newContent = prompt("Edit Post Content:", currentContent);
            if (newContent !== null) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=edit_post&post_id=${postId}&new_content=${encodeURIComponent(newContent)}`
                }).then(() => location.reload());
            }
        }

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