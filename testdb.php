<?php
// Include your database connection file
include 'db.php';

// Test the connection
try {
    // Run a simple query
    $stmt = $pdo->query("SELECT 'Connection successful!' AS message");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo $result['message'];
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>