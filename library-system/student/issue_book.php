<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$message = '';

// Handle single book request (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int) $_POST['book_id'];

    if ($book_id <= 0) {
        $message = "❌ Invalid book selected.";
    } else {
        try {
            // Check book exists
            $stmt = $pdo->prepare("SELECT id, quantity, title FROM books WHERE id = ?");
            $stmt->execute([$book_id]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                $message = "❌ Book not found.";
            } else {
                // Prevent duplicate requests: if already requested/issued/return_pending
                $check = $pdo->prepare("
                    SELECT COUNT(*) FROM issues 
                    WHERE student_id = ? AND book_id = ? AND status IN ('issue_pending','issued','return_pending')
                ");
                $check->execute([$student_id, $book_id]);
                if ($check->fetchColumn() > 0) {
                    $message = "⚠️ You already requested or have this book issued.";
                } else {
                    // Insert a new issue request with status 'issue_pending'
                    $ins = $pdo->prepare("
                        INSERT INTO issues (student_id, book_id, issue_date, due_date, status)
                        VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 5 MONTH), 'issue_pending')
                    ");
                    $ins->execute([$student_id, $book_id]);
                    $message = "✅ Request submitted. Waiting for admin approval.";
                }
            }
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch all books
$stmt = $pdo->query("SELECT * FROM books ORDER BY title ASC");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch student's current issue rows (only statuses that block new request)
$stmt2 = $pdo->prepare("SELECT book_id, status FROM issues WHERE student_id = ? AND status IN ('issue_pending','issued','return_pending')");
$stmt2->execute([$student_id]);
$studentIssues = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$issuedBookIds = [];
foreach ($studentIssues as $issue) {
    $issuedBookIds[$issue['book_id']] = $issue['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Request Issue - Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Page styling */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            margin: 0;
            padding: 40px 0;
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .container {
            width: 100%;
            max-width: 1000px;
            padding: 28px;
            background: rgba(0,0,0,0.85);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.6);
        }
        h2 { text-align:center; color:#f1c40f; margin-bottom: 18px; }
        table { width:100%; border-collapse: collapse; margin-top: 12px; }
        th, td { padding:12px 10px; border-bottom:1px solid rgba(255,255,255,0.06); text-align:left; vertical-align: middle; }
        th { color:#ffd27a; }
        .issue-btn {
            background: linear-gradient(135deg,#28a745,#19a30b);
            border: none; padding:8px 14px; border-radius:8px; color:#fff; cursor:pointer; font-weight:700;
        }
        .issue-btn:hover { transform: translateY(-1px); }
        .disabled-btn {
            background: #666; color:#ddd; border:none; padding:8px 14px; border-radius:8px; font-weight:700; cursor:not-allowed;
        }
        .msg { text-align:center; margin-bottom:12px; padding:10px; border-radius:8px; display:inline-block; }
        .success { background:#2ecc71; color:#042; }
        .error { background:#e74c3c; color:#fff; }
        .note { font-size:0.95rem; color:#ccc; }
        .links { text-align:center; margin-top:18px; }
        .home { color:#f1c40f; text-decoration:none; margin:0 10px; }
        .home:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="container">
    <h2><i class="fas fa-book"></i> Available Books</h2>

    <?php if ($message): ?>
        <div class="msg <?= strpos($message,'✅')!==false ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th style="width:110px;">Quantity</th>
                <th style="width:190px;text-align:center;">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['title']) ?></td>
                <td><?= htmlspecialchars($book['author']) ?></td>
                <td><?= (int)$book['quantity'] ?></td>
                <td style="text-align:center;">
                    <?php
                        $bid = $book['id'];
                        if (isset($issuedBookIds[$bid])) {
                            // show current status
                            $s = $issuedBookIds[$bid];
                            $label = ucfirst(str_replace('_', ' ', $s)); // e.g. issue_pending -> Issue pending
                            echo "<button class='disabled-btn'>Already: " . htmlspecialchars($label) . "</button>";
                        } elseif ((int)$book['quantity'] <= 0) {
                            echo "<button class='disabled-btn'>Out of Stock</button>";
                        } else {
                    ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="book_id" value="<?= $bid ?>">
                            <button type="submit" class="issue-btn"><i class="fas fa-plus"></i> Request Issue</button>
                        </form>
                    <?php } ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p class="note">Note: Quantity will decrease only after admin approves the request.</p>

    <div class="links">
        <a class="home" href="books.php"><i class="fas fa-arrow-left"></i> Back to Books</a>
        <a class="home" href="../index.php"><i class="fas fa-home"></i> Home</a>
    </div>
</div>
</body>
</html>
