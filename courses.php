<?php
session_start();
require 'db.php';

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: adminlogin.php");
    exit();
}

// Fetch departments for dropdown
$stmt = $pdo->query("SELECT * FROM departments");
$departments = $stmt->fetchAll();

// Fetch instructors for dropdown
$stmt = $pdo->query("SELECT user_id, username FROM users WHERE role = 'instructor'");
$instructors = $stmt->fetchAll();

// Handle course creation
if (isset($_POST['add_course'])) {
    try {
        $course_name = trim($_POST['course_name']);
        $course_code = trim($_POST['course_code']);
        $department_id = intval($_POST['department_id']);
        $selected_instructors = isset($_POST['instructors']) ? $_POST['instructors'] : [];

        // Validate inputs
        if (empty($course_name) || empty($course_code) || empty($department_id)) {
            throw new Exception("All fields are required.");
        }

        // Insert course into the database
        $stmt = $pdo->prepare("INSERT INTO courses (course_name, course_code, department_id) VALUES (?, ?, ?)");
        $stmt->execute([$course_name, $course_code, $department_id]);
        $course_id = $pdo->lastInsertId();

        // Link selected instructors to the course
        if (!empty($selected_instructors)) {
            $stmt = $pdo->prepare("INSERT INTO instructor_courses (instructor_id, course_id) VALUES (?, ?)");
            foreach ($selected_instructors as $instructor_id) {
                $stmt->execute([$instructor_id, $course_id]);
            }
        }

        $_SESSION['message'] = "Course added successfully!";
        header("Location: courses.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: courses.php");
        exit();
    }
}

// Fetch courses with department and instructor details
$stmt = $pdo->query("
    SELECT c.course_id, c.course_name, c.course_code, d.department_name 
    FROM courses c 
    JOIN departments d ON c.department_id = d.department_id
");
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">Courses</h2>

        <!-- Display Success/Error Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']) ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Add Course Form -->
        <div class="card mb-4">
            <div class="card-header">Add Course</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" class="form-control" name="course_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Code</label>
                        <input type="text" class="form-control" name="course_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department_id" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assign Instructors</label>
                        <select class="form-select" name="instructors[]" multiple required>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= $instructor['user_id'] ?>"><?= htmlspecialchars($instructor['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl (or Command) to select multiple instructors.</small>
                    </div>
                    <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                </form>
            </div>
        </div>

        <!-- Courses List -->
        <div class="card">
            <div class="card-header">All Courses</div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= $course['course_id'] ?></td>
                                <td><?= htmlspecialchars($course['course_name']) ?></td>
                                <td><?= htmlspecialchars($course['course_code']) ?></td>
                                <td><?= htmlspecialchars($course['department_name']) ?></td>
                                <td>
                                    <a href="edit_course.php?course_id=<?= $course['course_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_course.php?course_id=<?= $course['course_id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>