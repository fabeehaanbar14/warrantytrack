<?php
// Start the session on every page that includes this header.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WarrantyTrack</title>
    <link rel="stylesheet" href="/warrantytrack/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="/warrantytrack/index.php" class="logo">WarrantyTrack</a>
        <nav>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
                <a href="/warrantytrack/dashboard.php">My Warranties</a>
                <a href="/warrantytrack/my_claims.php">My Claims</a>
                <a href="/warrantytrack/logout.php">Log Out (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="/warrantytrack/admin_dashboard.php">All Warranties</a>
                <a href="/warrantytrack/manage_claims.php">Manage Claims</a>
                <a href="/warrantytrack/add_warranty.php">Add Warranty</a>
                <a href="/warrantytrack/logout.php">Log Out (<?php echo htmlspecialchars($_SESSION['name']); ?>)</a>
            <?php else: ?>
                <a href="/warrantytrack/login.php">Customer Login</a>
                <a href="/warrantytrack/register.php">Register</a>
                <a href="/warrantytrack/admin_login.php">Admin Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="page-content">
