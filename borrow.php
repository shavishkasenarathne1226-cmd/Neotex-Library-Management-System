<?php
// ============================================
// borrow.php - Issue a Book to a Student
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

$current_page = 'borrow';
$errors = [];

// Fetch available books for dropdown
$books_result = mysqli_query($conn, "SELECT * FROM books WHERE quantity > 0 ORDER BY title");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_name = trim(mysqli_real_escape_string($conn, $_POST['student_name'] ?? ''));
    $book_id      = intval($_POST['book_id'] ?? 0);
    $borrow_date  = trim($_POST['borrow_date'] ?? '');
    $return_date  = trim($_POST['return_date'] ?? '');

    // ---- SERVER-SIDE VALIDATION ----
    if (empty($student_name))         $errors['student_name'] = "Student name is required.";
    elseif (strlen($student_name) < 3) $errors['student_name'] = "Name must be at least 3 characters.";

    if ($book_id <= 0)                $errors['book_id']      = "Please select a book.";
    if (empty($borrow_date))          $errors['borrow_date']  = "Borrow date is required.";
    if (empty($return_date))          $errors['return_date']  = "Return date is required.";

    // Date logic validation
    if (!empty($borrow_date) && !empty($return_date)) {
        if (strtotime($return_date) <= strtotime($borrow_date)) {
            $errors['return_date'] = "Return date must be after borrow date.";
        }
    }

    // Get book title and verify availability
    $book_title = '';
    if (empty($errors['book_id'])) {
        $book_check = mysqli_query($conn, "SELECT title, quantity FROM books WHERE id = $book_id AND quantity > 0 LIMIT 1");
        if (!$book_check || mysqli_num_rows($book_check) === 0) {
            $errors['book_id'] = "Selected book is not available.";
        } else {
            $book_data  = mysqli_fetch_assoc($book_check);
            $book_title = mysqli_real_escape_string($conn, $book_data['title']);
        }
    }

    // Insert borrow record and reduce book quantity
    if (empty($errors)) {
        mysqli_begin_transaction($conn);

        try {
            // Insert borrow record
            $sql1 = "INSERT INTO borrow (student_name, book_title, book_id, borrow_date, return_date, status)
                     VALUES ('$student_name', '$book_title', $book_id, '$borrow_date', '$return_date', 'borrowed')";
            mysqli_query($conn, $sql1);

            // Decrease book quantity by 1
            $sql2 = "UPDATE books SET quantity = quantity - 1 WHERE id = $book_id AND quantity > 0";
            mysqli_query($conn, $sql2);

            mysqli_commit($conn);
            header("Location: borrow_list.php?msg=Book+issued+successfully!&type=success");
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $errors['db'] = "Failed to issue book. Please try again.";
        }
    }
}

// Get today's date for default value
$today       = date('Y-m-d');
$default_return = date('Y-m-d', strtotime('+14 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book - Orion Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <div class="topbar-title">
                <h1>📤 Issue Book</h1>
                <p>Borrow a book to a student</p>
            </div>
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($logged_in_user, 0, 1)) ?></div>
                <span><?= htmlspecialchars($logged_in_user) ?></span>
            </div>
        </div>

        <div class="page-content">

            <?php if (!empty($errors['db'])): ?>
                <div class="alert alert-danger">❌ <?= $errors['db'] ?></div>
            <?php endif; ?>

            <?php if (mysqli_num_rows($books_result) === 0): ?>
                <div class="alert alert-warning">⚠️ No books are currently available to issue. Please add books first.</div>
            <?php endif; ?>

            <div class="form-card card">
                <div class="card-header">
                    <h2>📋 Borrow Details</h2>
                    <a href="borrow_list.php" class="btn btn-secondary btn-sm">📋 View Records</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="borrowForm" novalidate>

                        <div class="form-grid">

                            <!-- Student Name -->
                            <div class="form-group full">
                                <label for="student_name">Student Name <span style="color:var(--danger)">*</span></label>
                                <input type="text" id="student_name" name="student_name"
                                    placeholder="e.g. Kamal Perera"
                                    value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>"
                                    class="<?= isset($errors['student_name']) ? 'error' : '' ?>">
                                <?php if (isset($errors['student_name'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['student_name'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-student">Student name is required (min 3 chars).</div>
                                <?php endif; ?>
                            </div>

                            <!-- Book Selection -->
                            <div class="form-group full">
                                <label for="book_id">Select Book <span style="color:var(--danger)">*</span></label>
                                <select id="book_id" name="book_id"
                                    class="<?= isset($errors['book_id']) ? 'error' : '' ?>">
                                    <option value="">-- Select Available Book --</option>
                                    <?php
                                    mysqli_data_seek($books_result, 0);
                                    while ($book = mysqli_fetch_assoc($books_result)):
                                        $sel = ($_POST['book_id'] ?? '') == $book['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $book['id'] ?>" <?= $sel ?>>
                                            <?= htmlspecialchars($book['title']) ?> (Available: <?= $book['quantity'] ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <?php if (isset($errors['book_id'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['book_id'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-book">Please select a book.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Borrow Date -->
                            <div class="form-group">
                                <label for="borrow_date">Borrow Date <span style="color:var(--danger)">*</span></label>
                                <input type="date" id="borrow_date" name="borrow_date"
                                    value="<?= $_POST['borrow_date'] ?? $today ?>"
                                    class="<?= isset($errors['borrow_date']) ? 'error' : '' ?>">
                                <?php if (isset($errors['borrow_date'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['borrow_date'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-borrow-date">Borrow date is required.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Return Date -->
                            <div class="form-group">
                                <label for="return_date">Expected Return Date <span style="color:var(--danger)">*</span></label>
                                <input type="date" id="return_date" name="return_date"
                                    value="<?= $_POST['return_date'] ?? $default_return ?>"
                                    class="<?= isset($errors['return_date']) ? 'error' : '' ?>">
                                <?php if (isset($errors['return_date'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['return_date'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-return-date">Return date must be after borrow date.</div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="submit" class="btn btn-success">📤 Issue Book</button>
                            <a href="borrow_list.php" class="btn btn-secondary">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('borrowForm').addEventListener('submit', function(e) {
        let valid = true;

        // Student name
        const student = document.getElementById('student_name');
        const errStudent = document.getElementById('err-student');
        student.classList.remove('error');
        errStudent.style.display = 'none';
        if (!student.value.trim() || student.value.trim().length < 3) {
            student.classList.add('error');
            errStudent.style.display = 'block';
            valid = false;
        }

        // Book selection
        const book = document.getElementById('book_id');
        const errBook = document.getElementById('err-book');
        book.classList.remove('error');
        errBook.style.display = 'none';
        if (!book.value) {
            book.classList.add('error');
            errBook.style.display = 'block';
            valid = false;
        }

        // Dates
        const borrowDate = document.getElementById('borrow_date');
        const returnDate = document.getElementById('return_date');
        const errBorrow  = document.getElementById('err-borrow-date');
        const errReturn  = document.getElementById('err-return-date');
        borrowDate.classList.remove('error'); errBorrow.style.display = 'none';
        returnDate.classList.remove('error'); errReturn.style.display = 'none';

        if (!borrowDate.value) {
            borrowDate.classList.add('error'); errBorrow.style.display = 'block'; valid = false;
        }
        if (!returnDate.value) {
            returnDate.classList.add('error'); errReturn.style.display = 'block'; valid = false;
        }
        if (borrowDate.value && returnDate.value && returnDate.value <= borrowDate.value) {
            returnDate.classList.add('error');
            errReturn.textContent = 'Return date must be after borrow date.';
            errReturn.style.display = 'block';
            valid = false;
        }

        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
