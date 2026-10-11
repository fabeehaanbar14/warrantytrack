<?php
// Include this at the very top of any protected page (before includes/header.php).
// Usage: $required_role = 'customer';  then  include 'includes/auth_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

if (isset($required_role) && $_SESSION['role'] !== $required_role) {
    header("Location: index.php");
    exit();
}
