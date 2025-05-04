<?php
session_start();

// Redirect to login page if the user is not logged in or not an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructorlogin.php");
    exit();
}

require 'db.php'; // Ensure this file exists and contains the database connection logic

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $course_id = intval($_POST['course_id']);
        $resource_type = $_POST['resource_type'];
        $file = $_FILES['file'];

        // Validate required fields
        if (empty($title) || empty($description) || empty($course_id) || empty($resource_type) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("All fields are required.");
        }

        // Validate file type and size
        $allowed_types = ['application/pdf', 'video/mp4', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        $max_size = 10 * 1024 * 1024; // 10MB

        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception("Unsupported file type. Allowed types: PDF, MP4, PPTX.");
        }

        if ($file['size'] > $max_size) {
            throw new Exception("File size exceeds the maximum limit of 10MB.");
        }

        // Generate a unique file name and move the uploaded file
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the uploads directory if it doesn't exist
        }
        $file_name = uniqid() . '_' . basename($file['name']);
        $target_file = $target_dir . $file_name;

        if (!move_uploaded_file($file['tmp_name'], $target_file)) {
            throw new Exception("Failed to upload the file.");
        }

        // Insert resource details into the database
        $stmt = $pdo->prepare("
            INSERT INTO resources (title, description, file_path, resource_type, course_id, instructor_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $target_file, $resource_type, $course_id, $_SESSION['user_id']]);

        // Redirect to the view resources page with success message
        $_SESSION['success_message'] = "Resource uploaded successfully!";
        header("Location: view_resources.php");
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Resource - HU TLSS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?> <!-- Include the common dashboard header -->

    <div class="container mt-5">
        <h2>Upload Resource</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="course_id" class="form-label">Course</label>
                <select name="course_id" id="course_id" class="form-select" required>
                    <option value="">Select a Course</option>
                    <?php
                    // Fetch courses assigned to the instructor
                    $stmt = $pdo->prepare("
                        SELECT c.course_id, c.course_name 
                        FROM courses c 
                        JOIN instructor_courses ic ON c.course_id = ic.course_id 
                        WHERE ic.instructor_id = ?
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($courses as $course): ?>
                        <option value="<?= $course['course_id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="resource_type" class="form-label">Resource Type</label>
                <select name="resource_type" id="resource_type" class="form-select" required>
                    <option value="">Select a Type</option>
                    <option value="document">Document (PDF)</option>
                    <option value="video">Video (MP4)</option>
                    <option value="presentation">Presentation (PPTX)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">File</label>
                <input type="file" name="file" id="file" class="form-control" required>
                <small class="text-muted">Allowed types: PDF, MP4, PPTX. Max size: 10MB.</small>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>