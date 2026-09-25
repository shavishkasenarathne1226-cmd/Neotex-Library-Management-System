<?php
// ============================================
// logout.php - Destroy Session and Logout
// ============================================
session_start();

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect to login page
header("Location: index.php?msg=logged_out");
exit();
?>
