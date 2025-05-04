<?php
// Database credentials
$host = 'localhost';
$dbname = 'ccs'; // Database name from your schema
$username = 'root'; // MySQL username (e.g., 'root' for local)
$password = ''; // MySQL password (empty for local)

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set error mode to exceptions for debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Test the connection
    echo "Database connection successful!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>