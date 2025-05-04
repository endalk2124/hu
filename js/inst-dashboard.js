<?php
session_start();
require 'db.php'; // Include the database connection

// Redirect if not logged in or not an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header("Location: instructor_login.php");
    exit;
}

// Pagination for Resources
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Number of resources per page
$offset = ($page - 1) * $limit;

// Fetch instructor details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$instructor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$instructor) {
    die("Instructor not found.");
}

// Fetch courses taught by this instructor
$course_query = "
    SELECT c.course_id, c.course_name, c.course_code 
    FROM courses c
    JOIN instructor_courses ic ON c.course_id = ic.course_id
    WHERE ic.instructor_id = :instructor_id
";
$course_stmt = $pdo->prepare($course_query);
$course_stmt->bindParam(':instructor_id', $user_id, PDO::PARAM_INT);
$course_stmt->execute();
$courses = $course_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent resources uploaded by this instructor
$resource_query = "
    SELECT r.resource_id, r.title, r.upload_date, r.course_id 
    FROM resources r
    WHERE r.instructor_id = :instructor_id
    ORDER BY r.upload_date DESC 
    LIMIT :limit OFFSET :offset
";
$resource_stmt = $pdo->prepare($resource_query);
$resource_stmt->bindParam(':instructor_id', $user_id, PDO::PARAM_INT);
$resource_stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$resource_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$resource_stmt->execute();
$resources = $resource_stmt->fetchAll(PDO::FETCH_ASSOC);

// Total resources for pagination
$totalResourcesQuery = "SELECT COUNT(*) FROM resources WHERE instructor_id = :instructor_id";
$totalResourcesStmt = $pdo->prepare($totalResourcesQuery);
$totalResourcesStmt->bindParam(':instructor_id', $user_id, PDO::PARAM_INT);
$totalResourcesStmt->execute();
$totalResources = $totalResourcesStmt->fetchColumn();
$totalPages = ceil($totalResources / $limit);

// Fetch upcoming discussion rooms for this instructor
$room_query = "
    SELECT r.room_name, r.start_time, r.end_time 
    FROM realtime_discussion_rooms r
    WHERE r.instructor_id = :instructor_id AND r.start_time > NOW()
    ORDER BY r.start_time ASC 
    LIMIT 3
";
$room_stmt = $pdo->prepare($room_query);
$room_stmt->bindParam(':instructor_id', $user_id, PDO::PARAM_INT);
$room_stmt->execute();
$rooms = $room_stmt->fetchAll(PDO::FETCH_ASSOC);
?>