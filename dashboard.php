<?php
// ============================================
// dashboard.php - Admin Dashboard
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

$current_page = 'dashboard';

// Get total number of books
$total_books_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM books");
$total_books = mysqli_fetch_assoc($total_books_result)['total'];

// Get total number of currently borrowed books
$borrowed_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow WHERE status = 'borrowed'");
$total_borrowed = mysqli_fetch_assoc($borrowed_result)['total'];

// Get total returned books
$returned_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM borrow WHERE status = 'returned'");
$total_returned = mysqli_fetch_assoc($returned_result)['total'];

// Get total categories
$cat_result = mysqli_query($conn, "SELECT COUNT(DISTINCT category) as total FROM books");
$total_categories = mysqli_fetch_assoc($cat_result)['total'];

// Get recent 5 borrow records
$recent_borrows = mysqli_query($conn, "SELECT * FROM borrow ORDER BY created_at DESC LIMIT 5");

// Get recent 5 books added
$recent_books = mysqli_query($conn, "SELECT * FROM books ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Orion Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main">

        <!-- Top Bar -->
        <div class="topbar">
            <div class="topbar-title">
                <h1>📊 Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($logged_in_user) ?>!</p>
            </div>
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($logged_in_user, 0, 1)) ?></div>
                <span><?= htmlspecialchars($logged_in_user) ?></span>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card" style="animation-delay:0.05s">
                    <div class="stat-icon blue">📚</div>
                    <div class="stat-info">
                        <div class="value"><?= $total_books ?></div>
                        <div class="label">Total Books</div>
                    </div>
                </div>

                <div class="stat-card" style="animation-delay:0.10s">
                    <div class="stat-icon orange">📤</div>
                    <div class="stat-info">
                        <div class="value"><?= $total_borrowed ?></div>
                        <div class="label">Currently Borrowed</div>
                    </div>
                </div>

                <div class="stat-card" style="animation-delay:0.15s">
                    <div class="stat-icon green">✅</div>
                    <div class="stat-info">
                        <div class="value"><?= $total_returned ?></div>
                        <div class="label">Books Returned</div>
                    </div>
                </div>

                <div class="stat-card" style="animation-delay:0.20s">
                    <div class="stat-icon red">🗂️</div>
                    <div class="stat-info">
                        <div class="value"><?= $total_categories ?></div>
                        <div class="label">Categories</div>
                    </div>
                </div>
            </div>

            <!-- Two column layout -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

                <!-- Recent Borrows -->
                <div class="card">
                    <div class="card-header">
                        <h2>📤 Recent Borrows</h2>
                        <a href="borrow_list.php" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Book</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_borrows) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($recent_borrows)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['student_name']) ?></td>
                                        <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?= htmlspecialchars($row['book_title']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $row['status'] === 'borrowed' ? 'badge-warning' : 'badge-success' ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">No records found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Books -->
                <div class="card">
                    <div class="card-header">
                        <h2>📖 Recent Books Added</h2>
                        <a href="books.php" class="btn btn-secondary btn-sm">View All</a>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_books) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($recent_books)): ?>
                                    <tr>
                                        <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </td>
                                        <td><span class="badge badge-primary"><?= htmlspecialchars($row['category']) ?></span></td>
                                        <td><?= $row['quantity'] ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">No books found</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div><!-- /page-content -->
    </div><!-- /main -->
</div><!-- /layout -->

<script>
    // Highlight active sidebar link
    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
</script>
</body>
</html>
