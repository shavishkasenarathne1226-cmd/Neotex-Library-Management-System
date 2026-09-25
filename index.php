<?php


session_start();
require_once 'config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    // Validate: check for empty fields
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Query the database for matching username
        $sql  = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Verify password using bcrypt
            if (password_verify($password, $user['password'])) {
                // Login successful - set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = $user['username'];
                $_SESSION['admin_id']        = $user['id'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password. Please try again.";
            }
        } else {
            $error = "Invalid username or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Orion Library System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <!-- Logo & Title -->
        <div class="login-logo">
            <div class="icon">📚</div>
            <h1>Orion College</h1>
            <p>Library Management System</p>
        </div>

        <h2>Admin Login</h2>

        <!-- Error message -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" action="" id="loginForm" novalidate>

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter your username"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    autocomplete="username"
                >
                <div class="error-msg" id="err-username">Username is required.</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                >
                <div class="error-msg" id="err-password">Password is required.</div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" id="loginBtn">
                🔐 Login to System
            </button>
        </form>

        <p style="text-align:center;margin-top:18px;font-size:12px;color:var(--text-muted);">
            Default: admin / admin123
        </p>
    </div>

    <script>
        // Client-side form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            let valid = true;

            const username = document.getElementById('username');
            const password = document.getElementById('password');
            const errUser = document.getElementById('err-username');
            const errPass = document.getElementById('err-password');

            // Reset errors
            username.classList.remove('error');
            password.classList.remove('error');
            errUser.style.display = 'none';
            errPass.style.display = 'none';

            // Validate username
            if (username.value.trim() === '') {
                username.classList.add('error');
                errUser.style.display = 'block';
                valid = false;
            }

            // Validate password
            if (password.value.trim() === '') {
                password.classList.add('error');
                errPass.style.display = 'block';
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    </script>
</body>
</html>
