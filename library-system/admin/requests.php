<?php
session_start();
require_once '../includes/config.php';

// Redirect if not admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

$message = '';

// Handle approval (issue or return)
if (isset($_GET['approve_id'])) {
    $issue_id = (int)$_GET['approve_id'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM issues WHERE id = ?");
        $stmt->execute([$issue_id]);
        $issue = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($issue) {
            $book_id = $issue['book_id'];

            if ($issue['status'] === 'issue_pending') {
                // ISSUE BOOK LOGIC
                $stmtBook = $pdo->prepare("SELECT quantity FROM books WHERE id = ?");
                $stmtBook->execute([$book_id]);
                $book = $stmtBook->fetch(PDO::FETCH_ASSOC);

                if ($book && $book['quantity'] > 0) {
                    $pdo->prepare("UPDATE issues SET status='issued', issue_date=NOW(), due_date=DATE_ADD(NOW(), INTERVAL 5 MONTH) WHERE id=?")->execute([$issue_id]);
                    $pdo->prepare("UPDATE books SET quantity=quantity-1 WHERE id=?")->execute([$book_id]);
                    $message = "✅ Book issued successfully!";
                } else {
                    $message = "❌ Not enough quantity to issue this book.";
                }

            } elseif ($issue['status'] === 'return_pending') {
                // RETURN BOOK LOGIC
                $pdo->prepare("UPDATE issues SET status='returned', return_date=NOW() WHERE id=?")->execute([$issue_id]);
                $pdo->prepare("UPDATE books SET quantity=quantity+1 WHERE id=?")->execute([$book_id]);
                $message = "✅ Book return approved successfully!";
            } else {
                $message = "❌ This request cannot be approved.";
            }
        } else {
            $message = "❌ Invalid request.";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch pending issue requests
$issueRequests = $pdo->query("
    SELECT i.*, s.name AS student_name, b.title AS book_title
    FROM issues i
    JOIN students s ON i.student_id = s.id
    JOIN books b ON i.book_id = b.id
    WHERE i.status='issue_pending'
    ORDER BY i.issue_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending return requests
$returnRequests = $pdo->query("
    SELECT i.*, s.name AS student_name, b.title AS book_title
    FROM issues i
    JOIN students s ON i.student_id = s.id
    JOIN books b ON i.book_id = b.id
    WHERE i.status='return_pending'
    ORDER BY i.due_date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Requests</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body {
    margin:0; font-family:'Segoe UI',sans-serif; background:#1c1c1c; color:#fff;
}
.overlay {
    background: rgba(0,0,0,0.85); min-height:100vh; padding:20px 0;
}
.navbar {
    display:flex; justify-content:space-between; align-items:center;
    padding:15px 30px; background: rgba(0,0,0,0.7);
}
.navbar a { 
    background: linear-gradient(135deg,#ffb400,#ff6a00); color:#fff;
    padding:8px 16px; border-radius:8px; text-decoration:none; font-weight:bold; margin-right:10px;
    transition: all 0.3s ease;
}
.navbar a:hover { transform: scale(1.05); }
.navbar span { font-size:0.95rem; color:#ffd700; }
.container { width:90%; margin:auto; padding:50px 0; text-align:center; }
h1 { color:#f1c40f; font-size:2.5rem; margin-bottom:30px; text-shadow:2px 2px 6px rgba(0,0,0,0.7); }
h2 { color:#00bfff; font-size:1.8rem; margin-top:40px; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
table th, table td { padding:12px 15px; border:1px solid #444; text-align:center; }
table th { background:#f39c12; color:#000; }
table tr:nth-child(even) { background: rgba(255,255,255,0.05); }
table tr:hover { background: rgba(255,255,255,0.1); }
a.approve-btn {
    background: #28a745; color:#fff; padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:bold; transition:0.3s;
}
a.approve-btn:hover { background:#218838; }
.message { margin:15px 0; padding:12px; border-radius:6px; display:inline-block; }
.success { background:#28a745; color:#fff; }
.error { background:#e74c3c; color:#fff; }
.status-issued { color:#00bfff; font-weight:bold; }
.status-return { color:#ff6a00; font-weight:bold; }
</style>
</head>
<body>
<div class="overlay">
    <div class="navbar">
        <div><a href="../index.php"><i class="fas fa-home"></i> Home</a></div>
        <div><span>👑 Admin: <?= htmlspecialchars($_SESSION['name']) ?> | <a href="../auth/logout.php">Logout</a></span></div>
    </div>
    <div class="container">
        <h1>📋 Pending Book Requests</h1>

        <?php if ($message): ?>
            <div class="message <?= strpos($message,'✅')!==false ? 'success':'error' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <h2>📘 Issue Requests</h2>
        <?php if (!empty($issueRequests)): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Book</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($issueRequests as $req): ?>
            <tr>
                <td><?= $req['id'] ?></td>
                <td><?= htmlspecialchars($req['student_name']) ?></td>
                <td><?= htmlspecialchars($req['book_title']) ?></td>
                <td><?= date('F j, Y', strtotime($req['issue_date'])) ?></td>
                <td class="status-issued"><?= $req['status'] ?></td>
                <td><a href="?approve_id=<?= $req['id'] ?>" class="approve-btn" onclick="return confirm('Approve this issue request?')">✅ Approve</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <p>No pending issue requests.</p>
        <?php endif; ?>

        <h2>📗 Return Requests</h2>
        <?php if (!empty($returnRequests)): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Book</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php foreach ($returnRequests as $req): ?>
            <tr>
                <td><?= $req['id'] ?></td>
                <td><?= htmlspecialchars($req['student_name']) ?></td>
                <td><?= htmlspecialchars($req['book_title']) ?></td>
                <td><?= date('F j, Y', strtotime($req['due_date'])) ?></td>
                <td class="status-return"><?= $req['status'] ?></td>
                <td><a href="?approve_id=<?= $req['id'] ?>" class="approve-btn" onclick="return confirm('Approve this return request?')">✅ Approve</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <p>No pending return requests.</p>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
