<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_id'])) {
    $issue_id = (int)$_POST['issue_id'];
    $student_id = $_SESSION['user_id'];

    try {
        // Verify that the issue belongs to the logged-in student
        $stmt = $pdo->prepare("SELECT * FROM issues WHERE id = ? AND student_id = ?");
        $stmt->execute([$issue_id, $student_id]);
        $issue = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($issue) {
            if ($issue['status'] === 'Issued') {
                // Mark the book as return_pending
                $update = $pdo->prepare("UPDATE issues SET status = 'return_pending', return_date = NOW() WHERE id = ?");
                $update->execute([$issue_id]);

                $message = "✅ Return request submitted! Please wait for admin approval.";
            } else {
                $message = "⚠️ This book is not currently issued or already requested for return.";
            }
        } else {
            $message = "❌ Invalid issue ID or unauthorized request.";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Return Book</title>
<style>
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: url('../assets/images/library-bg.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #fff;
    }
    .return-container {
        max-width: 600px;
        margin: 80px auto;
        padding: 30px;
        background: rgba(0, 0, 0, 0.85);
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 8px 24px rgba(0,0,0,0.6);
    }
    h2 {
        color: #ffcc00;
        margin-bottom: 20px;
    }
    .message {
        margin: 15px 0;
        padding: 12px;
        border-radius: 8px;
        font-weight: bold;
    }
    .success { background: #28a745; color: #fff; }
    .error { background: #e74c3c; color: #fff; }
    .btn {
        display: inline-block;
        padding: 12px 20px;
        border-radius: 8px;
        margin: 10px 5px;
        cursor: pointer;
        border: none;
        font-weight: bold;
        transition: 0.3s ease;
        text-decoration: none;
        font-size: 1rem;
    }
    .btn-back {
        background: linear-gradient(135deg,#ffcc00,#ff9900);
        color: #000;
    }
    .btn-back:hover {
        background: linear-gradient(135deg,#ff9900,#ffcc00);
    }
</style>
</head>
<body>
<div class="return-container">
    <h2>📗 Return Book</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message,'✅')!==false ? 'success':'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <a href="my_issues.php" class="btn btn-back">← Back to My Issues</a>
    <a href="dashboard.php" class="btn btn-back">🏠 Back to Home</a>
</div>
</body>
</html>
