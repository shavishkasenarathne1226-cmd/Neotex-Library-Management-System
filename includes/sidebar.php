<?php
// ============================================
// includes/sidebar.php - Navigation Sidebar
// ============================================
// $current_page must be set before including this file
// Example: $current_page = 'dashboard';
?>
<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="brand-icon">📚</div>
        <div class="brand-text">
            <h2>Orion Library</h2>
            <p>Management System</p>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="sidebar-nav">
        <div class="nav-section-label">Main Menu</div>

        <a href="dashboard.php" class="<?= ($current_page === 'dashboard') ? 'active' : '' ?>">
            <span class="nav-icon">🏠</span> Dashboard
        </a>

        <div class="nav-section-label">Books</div>

        <a href="books.php" class="<?= ($current_page === 'books') ? 'active' : '' ?>">
            <span class="nav-icon">📖</span> Book List
        </a>

        <a href="add_book.php" class="<?= ($current_page === 'add_book') ? 'active' : '' ?>">
            <span class="nav-icon">➕</span> Add New Book
        </a>

        <div class="nav-section-label">Borrow</div>

        <a href="borrow.php" class="<?= ($current_page === 'borrow') ? 'active' : '' ?>">
            <span class="nav-icon">📤</span> Issue Book
        </a>

        <a href="borrow_list.php" class="<?= ($current_page === 'borrow_list') ? 'active' : '' ?>">
            <span class="nav-icon">📋</span> Borrow Records
        </a>
    </nav>

    <!-- Logout -->
    <div class="sidebar-footer">
        <a href="logout.php" onclick="return confirm('Are you sure you want to logout?')">
            <span>🚪</span> Logout
        </a>
    </div>
</aside>
