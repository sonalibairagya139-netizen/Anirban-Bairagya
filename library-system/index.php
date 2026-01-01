<?php
session_start(); 
require_once 'includes/config.php';
include 'includes/navbar.php';

// Fetch stats
$bookCount = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$studentCount = $pdo->query("SELECT COUNT(*) FROM students WHERE is_admin = 0")->fetchColumn();
$totalQuantity = $pdo->query("SELECT SUM(quantity) FROM books")->fetchColumn();
$issuedBooks = $pdo->query("SELECT COUNT(*) FROM issues WHERE status='Issued'")->fetchColumn();
$availableBooks = max($totalQuantity - $issuedBooks, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome - Library Portal</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css?v=<?= time(); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      background: #f8f9fa;
      color: #333;
    }

    .homepage-background {
      background: url('assets/images/library-bg.jpg') no-repeat center center/cover;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 50px 20px;
      position: relative;
    }

    .homepage-background::before {
      content: "";
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(0,0,0,0.4));
      z-index: 0;
    }

    .container {
      width: 95%;
      max-width: 1100px;
      margin: auto;
      position: relative;
      z-index: 1;
      color: white;
    }

    .shadow-box {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      padding: 30px;
      border-radius: 20px;
      margin: 20px auto;
      box-shadow: 0 8px 30px rgba(0,0,0,0.4);
      transition: transform 0.3s ease;
    }

    .shadow-box:hover {
      transform: translateY(-5px);
    }

    h1, h2 {
      font-weight: bold;
      margin-bottom: 15px;
    }

    .btn {
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      transition: 0.3s;
      margin: 10px;
      display: inline-block;
    }

    .btn-primary {
      background: linear-gradient(135deg, #f39c12, #e67e22);
      color: white;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #e67e22, #d35400);
    }

    .btn-secondary {
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: white;
    }
    .btn-secondary:hover {
      background: linear-gradient(135deg, #2980b9, #1f618d);
    }

    .features-list {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-top: 25px;
    }

    .features-list li {
      list-style: none;
      padding: 25px;
      background: rgba(255,255,255,0.9);
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      text-align: center;
      color: #333;
      transition: transform 0.3s ease;
    }

    .features-list li:hover {
      transform: translateY(-8px);
    }

    .features-list i {
      font-size: 2em;
      color: #f1c40f;
      margin-bottom: 10px;
    }

    .stats-box {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 25px;
    }

    .stats-card {
      background: rgba(255,255,255,0.9);
      color: #2c3e50;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.2);
      text-align: center;
      transition: 0.3s ease;
    }

    .stats-card:hover {
      transform: scale(1.05);
    }

    .stats-card strong {
      font-size: 1.8em;
      color: #3498db;
      display: block;
    }

    footer {
      margin-top: 40px;
      text-align: center;
      color: #ddd;
    }
    .footer-overlay {
        background-color: rgba(0, 0, 0, 0.6);
        padding: 20px;
        border-radius: 12px;
        margin: 30px auto;
        max-width: 800px;
        text-align: center;
        color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .tagline {
        text-align: center;
        margin: 20px auto;
        font-size: 1.2rem;
        font-weight: 500;
        color: #f8f9fa;
        max-width: 700px;
        line-height: 1.6;
    }

  </style>
</head>
<body>
<div class="homepage-background">
  <div class="container">
    <div class="welcome-box shadow-box text-center">
      <h1>📚 Welcome to Your Library 📚</h1>
      <p class="tagline">
          Issue, return, and manage your books with ease in our smart digital library system.
      </p>
      <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="button-group">
          <a href="auth/login.php" class="btn btn-primary">Login</a>
          <a href="auth/register.php" class="btn btn-secondary">Register</a>
        </div>
      <?php else: ?>
        <a href="student/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
      <?php endif; ?>
    </div>

    <div class="features-section shadow-box">
      <h2 class="section-title-why">✨ Why Choose Us? ✨</h2>
      <ul class="features-list">
        <li><i class="fas fa-book"></i><br><strong>Huge Collection</strong><br> Thousands of books available.</li>
        <li><i class="fas fa-cogs"></i><br><strong>Easy Management</strong><br> Track & manage books effortlessly.</li>
        <li><i class="fas fa-bell"></i><br><strong>Smart Alerts</strong><br> Never miss a return date again.</li>
      </ul>
    </div>

    <div class="stats-section shadow-box">
      <h2 class="section-title-stats">📊 System Statistics 📊</h2>
      <div class="stats-box">
        <div class="stats-card"><strong><?= $bookCount ?></strong>Total Titles</div>
        <div class="stats-card"><strong><?= $studentCount ?></strong>Students Registered</div>
        <div class="stats-card"><strong><?= $availableBooks ?></strong>Books Available</div>
      </div>
    </div>

    <div class="footer-overlay">
    <footer id="about">
        <h2>About Us</h2>
        <p>This platform is designed to make library book issuing and returning simple,
           fast, and reliable.</p>
    </footer>
</div>

  </div>
</div>
</body>
</html>
