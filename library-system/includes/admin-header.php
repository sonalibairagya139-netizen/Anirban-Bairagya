<?php
// includes/admin-header.php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../includes/config.php';
adminAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    <div class="dashboard-wrapper">
        <aside class="dashboard-sidebar">
            <div class="dashboard-logo">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Logo">
                <h2><?php echo SITE_NAME; ?></h2>
            </div>
            <nav class="dashboard-nav">
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/books.php">Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/students.php">Students</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/issues.php">Issues</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/history.php">History</a></li> <!-- ✅ Fixed -->
                    <li><a href="<?php echo BASE_URL; ?>/admin/reports.php">Reports</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout.php" class="logout-btn">Logout</a></li>
                </ul>
            </nav>
        </aside>

        <main class="dashboard-content">
            <header class="dashboard-header">
                <button class="sidebar-toggle">☰</button>
                <h1>Admin Panel</h1>
            </header>
