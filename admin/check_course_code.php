<?php
require '../db.php';

$course_code = trim($_GET['course_code']);
$stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE course_code = ?");
$stmt->execute([$course_code]);
$exists = $stmt->fetchColumn() > 0;

echo json_encode(['exists' => $exists]);
?>