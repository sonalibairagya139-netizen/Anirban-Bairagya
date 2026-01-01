<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$issues = [];

try {
    $stmt = $pdo->prepare("
        SELECT i.id, i.issue_date, i.returned, 
               b.title, b.author, b.description
        FROM issues i
        JOIN books b ON i.book_id = b.id
        WHERE i.student_id = ? AND i.returned = 0
        ORDER BY i.issue_date DESC
    ");
    $stmt->execute([$student_id]);
    $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $issues = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Issued Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            min-height: 100vh;
            color: #fff;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.8);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: rgba(0, 0, 0, 0.65);
        }

        .navbar a {
            background: linear-gradient(135deg, #ffb400, #ff6a00);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            background: linear-gradient(135deg, #ff6a00, #ffb400);
            transform: scale(1.05);
        }

        .navbar span {
            color: #ffd700;
            font-size: 0.95rem;
        }

        .container {
            width: 90%;
            margin: auto;
            padding-top: 50px;
            text-align: center;
        }

        h1 {
            margin-bottom: 40px;
            font-size: 2.2rem;
            font-weight: bold;
            color: #f1c40f;
            text-transform: uppercase;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
        }

        .issue-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
        }

        .issue-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 6px 14px rgba(0,0,0,0.5);
            backdrop-filter: blur(6px);
            text-align: left;
        }

        .issue-card h3 {
            margin: 0 0 10px;
            font-size: 1.3rem;
            color: #f39c12;
        }

        .issue-card p {
            margin: 6px 0;
            font-size: 0.95rem;
            color: #ddd;
        }

        .empty {
            font-size: 1.1rem;
            margin-top: 20px;
            color: #bbb;
        }
    </style>
</head>
<body>
<div class="overlay">

    <!-- Navbar -->
    <div class="navbar">
        <div>
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
            <a href="books.php"><i class="fas fa-book"></i> Books</a>
        </div>
        <div>
            <span>👤 <?= htmlspecialchars($_SESSION['name']) ?> | 
                <a href="../auth/logout.php">Logout</a>
            </span>
        </div>
    </div>

    <div class="container">
        <h1><i class="fas fa-book-reader"></i> My Issued Books</h1>

        <div class="issue-list">
            <?php if (!empty($issues)) : ?>
                <?php foreach ($issues as $issue): ?>
                    <div class="issue-card">
                        <h3><?= htmlspecialchars($issue['title']) ?></h3>
                        <p><strong>Author:</strong> <?= htmlspecialchars($issue['author']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($issue['description']) ?></p>
                        <p><strong>Issued On:</strong> <?= date('d M Y', strtotime($issue['issue_date'])) ?></p>
                        <p><strong>Status:</strong> <span style="color:#2ecc71;">Active</span></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="empty">📭 You have not issued any books yet.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
