<?php
// ============================================
// delete_book.php - Delete a Book
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

// Get and validate book ID
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: books.php?msg=Invalid+book+ID&type=error");
    exit();
}

// Check book exists before deleting
$check = mysqli_query($conn, "SELECT id, title FROM books WHERE id = $id LIMIT 1");

if (!$check || mysqli_num_rows($check) === 0) {
    header("Location: books.php?msg=Book+not+found&type=error");
    exit();
}

// Delete the book from database
$sql = "DELETE FROM books WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    header("Location: books.php?msg=Book+deleted+successfully!&type=success");
} else {
    header("Location: books.php?msg=Failed+to+delete+book&type=error");
}
exit();
?>
