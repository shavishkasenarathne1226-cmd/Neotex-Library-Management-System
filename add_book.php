<?php
// ============================================
// add_book.php - Add New Book
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

$current_page = 'add_book';
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize inputs
    $title    = trim(mysqli_real_escape_string($conn, $_POST['title']    ?? ''));
    $author   = trim(mysqli_real_escape_string($conn, $_POST['author']   ?? ''));
    $category = trim(mysqli_real_escape_string($conn, $_POST['category'] ?? ''));
    $quantity = intval($_POST['quantity'] ?? 0);

    // ---- SERVER-SIDE VALIDATION ----
    if (empty($title))    $errors['title']    = "Book title is required.";
    if (empty($author))   $errors['author']   = "Author name is required.";
    if (empty($category)) $errors['category'] = "Category is required.";
    if ($quantity < 1)    $errors['quantity'] = "Quantity must be at least 1.";

    // Check if book with same title already exists
    if (empty($errors['title'])) {
        $check = mysqli_query($conn, "SELECT id FROM books WHERE title = '$title' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $errors['title'] = "A book with this title already exists.";
        }
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $sql = "INSERT INTO books (title, author, category, quantity)
                VALUES ('$title', '$author', '$category', $quantity)";

        if (mysqli_query($conn, $sql)) {
            // Redirect with success message
            header("Location: books.php?msg=Book+added+successfully!&type=success");
            exit();
        } else {
            $errors['db'] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book - Orion Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <div class="topbar-title">
                <h1>➕ Add New Book</h1>
                <p>Add a new book to the library</p>
            </div>
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($logged_in_user, 0, 1)) ?></div>
                <span><?= htmlspecialchars($logged_in_user) ?></span>
            </div>
        </div>

        <div class="page-content">

            <!-- Database error -->
            <?php if (!empty($errors['db'])): ?>
                <div class="alert alert-danger">❌ <?= $errors['db'] ?></div>
            <?php endif; ?>

            <div class="form-card card">
                <div class="card-header">
                    <h2>📝 Book Information</h2>
                    <a href="books.php" class="btn btn-secondary btn-sm">← Back to List</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="addBookForm" novalidate>

                        <div class="form-grid">

                            <!-- Title -->
                            <div class="form-group full">
                                <label for="title">Book Title <span style="color:var(--danger)">*</span></label>
                                <input
                                    type="text"
                                    id="title"
                                    name="title"
                                    placeholder="e.g. Introduction to Programming"
                                    value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                    class="<?= isset($errors['title']) ? 'error' : '' ?>"
                                >
                                <?php if (isset($errors['title'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['title'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-title">Book title is required.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Author -->
                            <div class="form-group">
                                <label for="author">Author Name <span style="color:var(--danger)">*</span></label>
                                <input
                                    type="text"
                                    id="author"
                                    name="author"
                                    placeholder="e.g. John Smith"
                                    value="<?= htmlspecialchars($_POST['author'] ?? '') ?>"
                                    class="<?= isset($errors['author']) ? 'error' : '' ?>"
                                >
                                <?php if (isset($errors['author'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['author'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-author">Author name is required.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Category -->
                            <div class="form-group">
                                <label for="category">Category <span style="color:var(--danger)">*</span></label>
                                <select
                                    id="category"
                                    name="category"
                                    class="<?= isset($errors['category']) ? 'error' : '' ?>"
                                >
                                    <option value="">-- Select Category --</option>
                                    <?php
                                    $categories = ['Computer Science', 'Web Technology', 'Networking', 'Mathematics', 'Science', 'Language', 'Engineering', 'Business', 'Other'];
                                    foreach ($categories as $cat):
                                        $selected = ($_POST['category'] ?? '') === $cat ? 'selected' : '';
                                    ?>
                                        <option value="<?= $cat ?>" <?= $selected ?>><?= $cat ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['category'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-category">Please select a category.</div>
                                <?php endif; ?>
                            </div>

                            <!-- Quantity -->
                            <div class="form-group">
                                <label for="quantity">Quantity <span style="color:var(--danger)">*</span></label>
                                <input
                                    type="number"
                                    id="quantity"
                                    name="quantity"
                                    placeholder="e.g. 5"
                                    min="1"
                                    max="999"
                                    value="<?= htmlspecialchars($_POST['quantity'] ?? '1') ?>"
                                    class="<?= isset($errors['quantity']) ? 'error' : '' ?>"
                                >
                                <?php if (isset($errors['quantity'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['quantity'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-quantity">Quantity must be at least 1.</div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <!-- Buttons -->
                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="submit" class="btn btn-primary">💾 Save Book</button>
                            <a href="books.php" class="btn btn-secondary">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Client-side validation
    document.getElementById('addBookForm').addEventListener('submit', function(e) {
        let valid = true;

        const fields = [
            { id: 'title',    errId: 'err-title',    msg: 'Book title is required.' },
            { id: 'author',   errId: 'err-author',   msg: 'Author name is required.' },
            { id: 'category', errId: 'err-category', msg: 'Please select a category.' },
            { id: 'quantity', errId: 'err-quantity',  msg: 'Quantity must be at least 1.' }
        ];

        fields.forEach(f => {
            const input = document.getElementById(f.id);
            const err   = document.getElementById(f.errId);
            if (!input || !err) return;

            input.classList.remove('error');
            err.style.display = 'none';

            const val = input.value.trim();

            if (f.id === 'quantity') {
                if (!val || parseInt(val) < 1) {
                    input.classList.add('error');
                    err.style.display = 'block';
                    valid = false;
                }
            } else if (!val) {
                input.classList.add('error');
                err.style.display = 'block';
                valid = false;
            }
        });

        if (!valid) e.preventDefault();
    });
</script>
</body>
</html>
