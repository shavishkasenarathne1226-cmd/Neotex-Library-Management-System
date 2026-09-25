<?php
// ============================================
// edit_book.php - Edit Existing Book
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

$current_page = 'books';
$errors = [];

// Get book ID from URL - validate it exists
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: books.php?msg=Invalid+book+ID&type=error");
    exit();
}

// Fetch existing book data
$result = mysqli_query($conn, "SELECT * FROM books WHERE id = $id LIMIT 1");
if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: books.php?msg=Book+not+found&type=error");
    exit();
}

$book = mysqli_fetch_assoc($result);

// Handle form submission (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title    = trim(mysqli_real_escape_string($conn, $_POST['title']    ?? ''));
    $author   = trim(mysqli_real_escape_string($conn, $_POST['author']   ?? ''));
    $category = trim(mysqli_real_escape_string($conn, $_POST['category'] ?? ''));
    $quantity = intval($_POST['quantity'] ?? 0);

    // Server-side validation
    if (empty($title))    $errors['title']    = "Book title is required.";
    if (empty($author))   $errors['author']   = "Author name is required.";
    if (empty($category)) $errors['category'] = "Category is required.";
    if ($quantity < 1)    $errors['quantity'] = "Quantity must be at least 1.";

    // Check duplicate title (excluding current book)
    if (empty($errors['title'])) {
        $check = mysqli_query($conn, "SELECT id FROM books WHERE title = '$title' AND id != $id LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $errors['title'] = "Another book with this title already exists.";
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        $sql = "UPDATE books SET
                    title    = '$title',
                    author   = '$author',
                    category = '$category',
                    quantity = $quantity
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: books.php?msg=Book+updated+successfully!&type=success");
            exit();
        } else {
            $errors['db'] = "Database error: " . mysqli_error($conn);
        }
    }

    // Update $book array with submitted values for re-display
    $book = ['title' => $_POST['title'], 'author' => $_POST['author'],
             'category' => $_POST['category'], 'quantity' => $_POST['quantity']];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - Orion Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <div class="topbar-title">
                <h1>✏️ Edit Book</h1>
                <p>Update book information</p>
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

            <div class="form-card card">
                <div class="card-header">
                    <h2>📝 Edit Book Details</h2>
                    <a href="books.php" class="btn btn-secondary btn-sm">← Back to List</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="editBookForm" novalidate>

                        <div class="form-grid">

                            <div class="form-group full">
                                <label for="title">Book Title <span style="color:var(--danger)">*</span></label>
                                <input type="text" id="title" name="title"
                                    value="<?= htmlspecialchars($book['title']) ?>"
                                    class="<?= isset($errors['title']) ? 'error' : '' ?>">
                                <?php if (isset($errors['title'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['title'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-title">Book title is required.</div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="author">Author Name <span style="color:var(--danger)">*</span></label>
                                <input type="text" id="author" name="author"
                                    value="<?= htmlspecialchars($book['author']) ?>"
                                    class="<?= isset($errors['author']) ? 'error' : '' ?>">
                                <?php if (isset($errors['author'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['author'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-author">Author name is required.</div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="category">Category <span style="color:var(--danger)">*</span></label>
                                <select id="category" name="category"
                                    class="<?= isset($errors['category']) ? 'error' : '' ?>">
                                    <option value="">-- Select Category --</option>
                                    <?php
                                    $categories = ['Computer Science', 'Web Technology', 'Networking', 'Mathematics', 'Science', 'Language', 'Engineering', 'Business', 'Other'];
                                    foreach ($categories as $cat):
                                        $selected = $book['category'] === $cat ? 'selected' : '';
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

                            <div class="form-group">
                                <label for="quantity">Quantity <span style="color:var(--danger)">*</span></label>
                                <input type="number" id="quantity" name="quantity"
                                    value="<?= htmlspecialchars($book['quantity']) ?>"
                                    min="1" max="999"
                                    class="<?= isset($errors['quantity']) ? 'error' : '' ?>">
                                <?php if (isset($errors['quantity'])): ?>
                                    <div class="error-msg" style="display:block"><?= $errors['quantity'] ?></div>
                                <?php else: ?>
                                    <div class="error-msg" id="err-quantity">Quantity must be at least 1.</div>
                                <?php endif; ?>
                            </div>

                        </div>

                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <button type="submit" class="btn btn-warning">💾 Update Book</button>
                            <a href="books.php" class="btn btn-secondary">Cancel</a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.getElementById('editBookForm').addEventListener('submit', function(e) {
        let valid = true;
        const fields = [
            { id: 'title',    errId: 'err-title' },
            { id: 'author',   errId: 'err-author' },
            { id: 'category', errId: 'err-category' },
            { id: 'quantity', errId: 'err-quantity' }
        ];
        fields.forEach(f => {
            const input = document.getElementById(f.id);
            const err   = document.getElementById(f.errId);
            if (!input || !err) return;
            input.classList.remove('error');
            err.style.display = 'none';
            const val = input.value.trim();
            const isInvalid = f.id === 'quantity' ? (!val || parseInt(val) < 1) : !val;
            if (isInvalid) {
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
