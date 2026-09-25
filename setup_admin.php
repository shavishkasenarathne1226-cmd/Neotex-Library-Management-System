<?php
// ============================================
// setup_admin.php - Run ONCE to create admin
// DELETE this file after first run!
// ============================================
require_once 'config.php';

$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

// Delete existing admin and re-insert with correct hash
mysqli_query($conn, "DELETE FROM users WHERE username = 'admin'");
$sql = "INSERT INTO users (username, password) VALUES ('$username', '$hash')";

if (mysqli_query($conn, $sql)) {
    echo "<h2 style='color:green;font-family:sans-serif'>✅ Admin user created successfully!</h2>";
    echo "<p style='font-family:sans-serif'>Username: <strong>admin</strong><br>Password: <strong>admin123</strong></p>";
    echo "<p style='color:red;font-family:sans-serif'>⚠️ DELETE this file (setup_admin.php) after use!</p>";
    echo "<a href='index.php' style='font-family:sans-serif'>Go to Login →</a>";
} else {
    echo "<h2 style='color:red;font-family:sans-serif'>❌ Error: " . mysqli_error($conn) . "</h2>";
}
?>
