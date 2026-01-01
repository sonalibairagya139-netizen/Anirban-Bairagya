<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Fetch ONLY issue_pending and return_pending requests
$stmt = $pdo->prepare("
    SELECT issues.id, books.title AS book_title, issues.issue_date, issues.due_date, issues.status
    FROM issues
    JOIN books ON issues.book_id = books.id
    WHERE issues.student_id = ? 
      AND (issues.status = 'issue_pending' OR issues.status = 'return_pending')
    ORDER BY issues.issue_date DESC
");
$stmt->execute([$student_id]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Requests</title>
    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #333;
            min-height: 100vh;
        }

        /* Navbar */
        nav {
            background: #0d47a1;
            padding: 15px;
            text-align: center;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
        }

        nav a {
            color: #fff;
            margin: 0 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.5px;
            transition: color 0.3s, transform 0.2s;
        }

        nav a:hover {
            color: #ffeb3b;
            transform: scale(1.1);
        }

        /* Container */
        .container {
            max-width: 1100px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            animation: fadeIn 0.6s ease-in-out;
        }

        .page-title {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #0d47a1;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        /* Book Card */
        .book-card {
            background: #fafafa;
            padding: 22px;
            border-radius: 14px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: transform 0.25s, box-shadow 0.25s;
            border-left: 6px solid #0d47a1;
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 22px rgba(0,0,0,0.2);
        }

        .book-card h3 {
            margin: 0 0 12px;
            font-size: 20px;
            color: #1a237e;
        }

        .book-card p {
            margin: 6px 0;
            font-size: 15px;
            color: #444;
        }

        /* Status Tags */
        .status {
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
        }

        .status.issue {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status.return {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        /* Return Date Badge */
        .return-date {
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
        }

        .return-date.valid {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .return-date.overdue {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Fine Label */
        .fine {
            display: inline-block;
            margin-top: 6px;
            font-size: 14px;
            font-weight: bold;
            color: #b71c1c;
        }

        /* Card (empty state) */
        .card {
            text-align: center;
            padding: 50px;
            background: #f9f9f9;
            border-radius: 14px;
            box-shadow: inset 0 3px 8px rgba(0,0,0,0.05);
            font-size: 18px;
            color: #777;
        }

        /* Animation */
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(20px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <a href="../student/dashboard.php">Dashboard</a>
        <a href="../student/issue_book.php">Issue Book</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <h2 class="page-title">📑 My Pending Book Requests</h2>

        <?php if (empty($requests)): ?>
            <div class="card">
                <p>No pending requests.</p>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($requests as $req): ?>
                    <div class="book-card">
                        <h3><?= htmlspecialchars($req['book_title']) ?></h3>
                        <p><strong>Requested On:</strong> 
                            <?= $req['issue_date'] ? date('F j, Y', strtotime($req['issue_date'])) : '—' ?>
                        </p>
                        <p><strong>Expected Return:</strong> 
                            <?php if ($req['due_date']): ?>
                                <?php 
                                    $dueDate = strtotime($req['due_date']);
                                    $today = strtotime(date('Y-m-d'));
                                    $isOverdue = $dueDate < $today;

                                    if ($isOverdue) {
                                        $daysLate = ceil(($today - $dueDate) / (60 * 60 * 24));
                                        $monthsLate = ceil($daysLate / 30);
                                        $fine = $monthsLate * 200;
                                    }
                                ?>
                                <span class="return-date <?= $isOverdue ? 'overdue' : 'valid' ?>">
                                    <?= date('F j, Y', $dueDate) ?>
                                    <?= $isOverdue ? '❌ Overdue' : '✅ On Time' ?>
                                </span>
                                <?php if ($isOverdue): ?>
                                    <div class="fine">💰 Fine: ₹<?= $fine ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </p>
                        <p><strong>Status:</strong>
                            <?php if ($req['status'] === 'issue_pending'): ?>
                                <span class="status issue">⏳ Issue Pending</span>
                            <?php elseif ($req['status'] === 'return_pending'): ?>
                                <span class="status return">📦 Return Pending</span>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
