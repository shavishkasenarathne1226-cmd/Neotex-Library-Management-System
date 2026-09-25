<?php
// ============================================
// config.php - Database Connection
// Orion College Library Management System
// ============================================

// Database configuration settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change to your MySQL username
define('DB_PASS', '');            // Change to your MySQL password
define('DB_NAME', 'library_db');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection - if failed, show error and stop
if (!$conn) {
    die("<div style='font-family:sans-serif;color:red;padding:20px;'>
        <h3>Database Connection Failed!</h3>
        <p>Error: " . mysqli_connect_error() . "</p>
        <p>Please check your database settings in config.php</p>
    </div>");
}

// Set character encoding to UTF-8
mysqli_set_charset($conn, "utf8mb4");
?>
