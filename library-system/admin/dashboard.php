<?php
session_start();
require_once '../includes/config.php';

// Redirect to login if not logged in OR not admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            background-size: cover;
            color: #fff;
        }
        .overlay {
            background-color: rgba(0,0,0,0.85);
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: rgba(0,0,0,0.95);
            padding: 20px 0;
            box-shadow: 3px 0 15px rgba(0,0,0,0.5);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #f1c40f;
            font-size: 1.6rem;
        }
        .sidebar a {
            display: block;
            padding: 14px 25px;
            color: #fff;
            text-decoration: none;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            margin: 5px 15px;
            border-radius: 6px;
        }
        .sidebar a i {
            margin-right: 12px;
        }
        .sidebar a:hover {
            background: #007bff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 40px 50px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .topbar h2 {
            font-size: 1.8rem;
            color: #f39c12;
            margin: 0;
        }
        .topbar span a {
            color: #ffb74d;
            text-decoration: none;
            margin-left: 15px;
            transition: 0.3s;
        }
        .topbar span a:hover {
            color: #fff;
        }

        h1 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            color: #f1c40f;
        }
        p {
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 700px;
        }

        /* Dashboard Cards */
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 30px;
        }
        .card {
            background: rgba(255,255,255,0.1);
            padding: 25px 30px;
            border-radius: 10px;
            flex: 1 1 200px;
            min-width: 200px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 1.4rem;
            color: #f1c40f;
        }
        .card p {
            margin: 0;
            font-size: 1rem;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="overlay">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>📚 Admin Panel</h2>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="reports.php"><i class="fas fa-file-alt"></i> Reports</a>
        <a href="overdue_reports.php"><i class="fas fa-exclamation-triangle"></i> Overdue Reports</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> 👋</h2>
            <span>
                <a href="../index.php"><i class="fas fa-home"></i> Home</a> |
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </span>
        </div>

        <h1>Admin Dashboard</h1>
        <p>Use the sidebar to manage books, students, and view reports. Quick overview of the library system:</p>

        <div class="cards">
            <div class="card">
                <h3><i class="fas fa-book"></i> Total Books</h3>
                <p>120</p>
            </div>
            <div class="card">
                <h3><i class="fas fa-user-graduate"></i> Students</h3>
                <p>85</p>
            </div>
            <div class="card">
                <h3><i class="fas fa-file-alt"></i> Reports</h3>
                <p>15</p>
            </div>
            <div class="card">
                <h3><i class="fas fa-exclamation-circle"></i> Overdue</h3>
                <p>5</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
