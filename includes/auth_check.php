<?php
// ============================================
// includes/auth_check.php
// Include at top of every protected page
// ============================================
session_start();

// If not logged in, redirect to login page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Get logged-in username for display
$logged_in_user = $_SESSION['admin_username'] ?? 'Admin';
?>
