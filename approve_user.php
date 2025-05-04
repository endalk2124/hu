<?php
session_start();
require 'db.php'; 

// Redirect if not admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location:adminlogin.php"); // Redirect to root-level admin login
    exit();
}

if (isset($_GET['user_id']) && isset($_GET['action'])) {
    $user_id = intval($_GET['user_id']);
    $action = $_GET['action'];

    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'approved' WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['message'] = "User approved successfully!";
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['message'] = "User rejected and deleted!";
        }
        header("Location:admin_dashboard.php"); // Redirect to root-level dashboard
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Action failed: " . $e->getMessage();
        header("Location:admin_dashboard.php"); // Redirect to root-level dashboard
        exit();
    }
}
?>