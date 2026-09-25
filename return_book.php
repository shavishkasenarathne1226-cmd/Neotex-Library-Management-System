<?php
// ============================================
// return_book.php - Mark Book as Returned
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

// Validate borrow record ID
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: borrow_list.php?msg=Invalid+record+ID&type=error");
    exit();
}

// Get borrow record to find which book to update
$result = mysqli_query($conn, "SELECT * FROM borrow WHERE id = $id AND status = 'borrowed' LIMIT 1");

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: borrow_list.php?msg=Record+not+found+or+already+returned&type=error");
    exit();
}

$borrow = mysqli_fetch_assoc($result);
$book_id = intval($borrow['book_id']);

// Start transaction: update borrow status and restore book quantity
mysqli_begin_transaction($conn);

try {
    // Mark as returned with today's actual return date
    $today = date('Y-m-d');
    mysqli_query($conn, "UPDATE borrow SET status = 'returned', return_date = '$today' WHERE id = $id");

    // Restore book quantity by 1 (only if book still exists)
    if ($book_id > 0) {
        mysqli_query($conn, "UPDATE books SET quantity = quantity + 1 WHERE id = $book_id");
    }

    mysqli_commit($conn);
    header("Location: borrow_list.php?msg=Book+returned+successfully!&type=success");

} catch (Exception $e) {
    mysqli_rollback($conn);
    header("Location: borrow_list.php?msg=Failed+to+process+return&type=error");
}
exit();
?>
