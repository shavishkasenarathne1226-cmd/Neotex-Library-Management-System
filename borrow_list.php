<?php
// ============================================
// borrow_list.php - View All Borrow Records
// ============================================
require_once 'includes/auth_check.php';
require_once 'config.php';

$current_page = 'borrow_list';

// Filter by status
$filter = $_GET['filter'] ?? 'all';
$where  = '';
if ($filter === 'borrowed') $where = "WHERE status = 'borrowed'";
if ($filter === 'returned') $where = "WHERE status = 'returned'";

// Fetch borrow records
$records = mysqli_query($conn, "SELECT * FROM borrow $where ORDER BY id DESC");
$count   = mysqli_num_rows($records);

// Status message
$msg  = $_GET['msg']  ?? '';
$type = $_GET['type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Records - Orion Library</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="topbar">
            <div class="topbar-title">
                <h1>📋 Borrow Records</h1>
                <p>Track all book issues and returns</p>
            </div>
            <div class="topbar-user">
                <div class="user-avatar"><?= strtoupper(substr($logged_in_user, 0, 1)) ?></div>
                <span><?= htmlspecialchars($logged_in_user) ?></span>
            </div>
        </div>

        <div class="page-content">

            <!-- Alert -->
            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?>">
                    <?= $type === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>📤 Borrow Records <span style="font-size:13px;font-weight:400;color:var(--text-muted);">(<?= $count ?> records)</span></h2>

                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                        <!-- Filter buttons -->
                        <a href="?filter=all"      class="btn btn-sm <?= $filter === 'all'      ? 'btn-primary' : 'btn-secondary' ?>">All</a>
                        <a href="?filter=borrowed" class="btn btn-sm <?= $filter === 'borrowed' ? 'btn-primary' : 'btn-secondary' ?>">📤 Borrowed</a>
                        <a href="?filter=returned" class="btn btn-sm <?= $filter === 'returned' ? 'btn-primary' : 'btn-secondary' ?>">✅ Returned</a>
                        <a href="borrow.php" class="btn btn-success btn-sm">➕ Issue Book</a>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Borrow ID</th>
                                <th>Student Name</th>
                                <th>Book Title</th>
                                <th>Borrow Date</th>
                                <th>Return Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($count > 0): ?>
                                <?php $i = 1; while ($row = mysqli_fetch_assoc($records)): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><span class="badge badge-primary">BR-<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></span></td>
                                    <td><strong><?= htmlspecialchars($row['student_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['book_title']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['borrow_date'])) ?></td>
                                    <td>
                                        <?php if ($row['return_date']): ?>
                                            <?php
                                            $returnDate = strtotime($row['return_date']);
                                            $today = time();
                                            $isOverdue = $row['status'] === 'borrowed' && $returnDate < $today;
                                            ?>
                                            <span style="color:<?= $isOverdue ? 'var(--danger)' : 'inherit' ?>">
                                                <?= date('d M Y', $returnDate) ?>
                                                <?= $isOverdue ? '⚠️' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted)">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $row['status'] === 'borrowed' ? 'badge-warning' : 'badge-success' ?>">
                                            <?= $row['status'] === 'borrowed' ? '📤 Borrowed' : '✅ Returned' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'borrowed'): ?>
                                            <a href="return_book.php?id=<?= $row['id'] ?>"
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('Mark this book as returned?')">
                                                ↩️ Return
                                            </a>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted);font-size:12px;">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <div class="empty-icon">📭</div>
                                            <p>No borrow records found.</p>
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

<script>
    // Auto-dismiss alert
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.style.display = 'none';
    }, 4000);
</script>
</body>
</html>
