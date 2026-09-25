<?php
// ============================================
// books.php - Book List with Search
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

$current_page = 'books';

// Handle search functionality (BONUS)
$search = '';
$where  = '';

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim(mysqli_real_escape_string($conn, $_GET['search']));
    $where  = "WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR category LIKE '%$search%'";
}

// Fetch books from database
$sql   = "SELECT * FROM books $where ORDER BY id DESC";
$books = mysqli_query($conn, $sql);
$count = mysqli_num_rows($books);

// Success/error messages from other operations
$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - Orion Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <div class="topbar-title">
                <h1>📖 Book List</h1>
                <p>Manage all library books</p>
            </div>
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($logged_in_user, 0, 1)) ?></div>
                <span><?= htmlspecialchars($logged_in_user) ?></span>
            </div>
        </div>

        <div class="page-content">

            <!-- Alert Messages -->
            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?>">
                    <?= $type === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <!-- Card -->
            <div class="card">
                <div class="card-header">
                    <h2>📚 All Books <span style="font-size:13px;font-weight:400;color:var(--text-muted);">(<?= $count ?> found)</span></h2>

                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <!-- Search Bar (BONUS feature) -->
                        <form method="GET" action="">
                            <div class="search-bar">
                                <span class="search-icon">🔍</span>
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Search books..."
                                    value="<?= htmlspecialchars($search) ?>"
                                >
                            </div>
                        </form>

                        <a href="add_book.php" class="btn btn-primary btn-sm">➕ Add Book</a>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($count > 0): ?>
                                <?php $i = 1; while ($row = mysqli_fetch_assoc($books)): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><span class="badge badge-primary">BK-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></span></td>
                                    <td><strong><?= htmlspecialchars($row['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['author']) ?></td>
                                    <td><span class="badge badge-primary"><?= htmlspecialchars($row['category']) ?></span></td>
                                    <td>
                                        <span class="badge <?= $row['quantity'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $row['quantity'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="td-actions">
                                            <a href="edit_book.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['title'])) ?>')">🗑️ Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <div class="empty-icon">📭</div>
                                            <p><?= !empty($search) ? "No books found for \"$search\"" : "No books in the library yet." ?></p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <h3>🗑️ Confirm Delete</h3>
        <p>Are you sure you want to delete "<strong id="deleteBookTitle"></strong>"? This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</a>
        </div>
    </div>
</div>

<script>
    // Show delete confirmation modal
    function confirmDelete(id, title) {
        document.getElementById('deleteBookTitle').textContent = title;
        document.getElementById('confirmDeleteBtn').href = 'delete_book.php?id=' + id;
        document.getElementById('deleteModal').classList.add('active');
    }

    // Close modal
    function closeModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Auto-dismiss alert after 4 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.style.display = 'none';
    }, 4000);
</script>
</body>
</html>
