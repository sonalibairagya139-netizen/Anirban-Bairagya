<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $student_id = $_SESSION['user_id'];
    $book_id = (int) $_POST['book_id'];

    // Check if already requested/issued/return_pending
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE student_id = ? AND book_id = ? AND status IN ('issue_pending','Issued','return_pending')");
    $stmt->execute([$student_id, $book_id]);
    $alreadyRequested = $stmt->fetchColumn();

    if ($alreadyRequested == 0) {
        // Insert request (admin will approve & reduce quantity later)
        $stmt = $pdo->prepare("
            INSERT INTO issues (student_id, book_id, issue_date, due_date, status) 
            VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 5 MONTH), 'issue_pending')
        ");
        $stmt->execute([$student_id, $book_id]);

        header("Location: issue_book.php?success=1");
        exit();
    } else {
        header("Location: issue_book.php?error=already");
        exit();
    }
} else {
    header("Location: issue_book.php?error=badrequest");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Issue</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            margin: 0;
            padding: 0;
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .message-box {
            background: rgba(0, 0, 0, 0.85);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            max-width: 500px;
            box-shadow: 0 0 15px rgba(0,0,0,0.6);
        }
        h2 {
            margin-bottom: 15px;
            color: #f1c40f;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #27ae60;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .btn:hover {
            background: #219150;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <h2><i class="fas fa-book"></i> Processing your request...</h2>
        <p>If you are not redirected, <a href="issue_book.php" class="btn">Go Back</a></p>
    </div>
</body>
</html>
