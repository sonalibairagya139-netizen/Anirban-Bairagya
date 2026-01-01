<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

// Allowed statuses
$statuses = [
    "issue_pending" => "Issue Pending",
    "issued" => "Issued",
    "return_pending" => "Return Pending",
    "returned" => "Returned",
    "overdue" => "Overdue"
];

// Step 1: Update overdue books and fine in database
$fine_per_month = 200; // ₹200 per month
$updateStmt = $pdo->prepare("
    UPDATE issues
    SET status = 'overdue', 
        fine = CEIL(DATEDIFF(CURDATE(), due_date)/30) * :fine_per_month
    WHERE student_id = :student_id
      AND status = 'issued'
      AND due_date < CURDATE()
");
$updateStmt->execute([
    ':fine_per_month' => $fine_per_month,
    ':student_id' => $student_id
]);

// Step 2: Fetch active issues for student (including overdue)
$stmt = $pdo->prepare("
    SELECT i.*, b.title, b.author 
    FROM issues i 
    JOIN books b ON i.book_id = b.id 
    WHERE i.student_id = ? 
      AND LOWER(TRIM(i.status)) != 'returned'
    ORDER BY i.issue_date DESC
");
$stmt->execute([$student_id]);
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Step 3: Handle status update from dropdown (student requests return)
$message = '';
if (isset($_POST['update_status'])) {
    $issue_id = (int) $_POST['issue_id'];
    $new_status = $_POST['status'];

    if (array_key_exists($new_status, $statuses)) {
        $stmtCheck = $pdo->prepare("SELECT status FROM issues WHERE id=? AND student_id=?");
        $stmtCheck->execute([$issue_id, $student_id]);
        $current = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($current && strtolower(trim($current['status'])) === 'issued' && $new_status === 'return_pending') {
            $stmtUpdate = $pdo->prepare("UPDATE issues SET status=? WHERE id=? AND student_id=?");
            $stmtUpdate->execute([$new_status, $issue_id, $student_id]);
            $message = "Status updated successfully!";
        } else {
            $message = "You cannot update to this status!";
        }
    } else {
        $message = "Invalid status selected!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Issued Books</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { margin:0; font-family:'Segoe UI',sans-serif; background:url('../assets/images/library-bg.jpg') no-repeat center center/cover; color:#fff; }
.overlay { background:rgba(0,0,0,0.7); min-height:100vh; padding:40px; }
.container { max-width:900px; margin:auto; }
h1 { text-align:center; color:#ffcc00; margin-bottom:30px; }
.message { text-align:center; margin-bottom:20px; color:#00ff00; font-weight:bold; }
.book-card { background:rgba(0,0,0,0.85); padding:20px; margin:15px 0; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.6); }
.book-card h2 { color:#ffb400; margin-bottom:8px; }
.book-card p { margin:6px 0; }
.status { font-weight:bold; }
.status.issue_pending { color:#ff9800; }
.status.issued { color:#00bfff; }
.status.return_pending { color:#e67e22; }
.status.returned { color:#2ecc71; }
.status.overdue { color:#ff4444; font-weight:bold; }
.fine { color:#ff4444; font-weight:bold; }
.actions { margin-top:10px; }
.actions select, .actions button { padding:6px 12px; border-radius:6px; font-weight:bold; border:none; cursor:pointer; transition:0.3s; }
.actions select { border:1px solid #ccc; }
.actions button { background:#4CAF50; color:#fff; }
.actions button:hover { background:#45a049; }
.back-links { text-align:center; margin-top:30px; }
.back-links a { margin:0 10px; color:#ffd700; text-decoration:none; font-weight:bold; }
</style>
</head>
<body>
<div class="overlay">
<div class="container">
    <h1>📚 My Issued Books</h1>

    <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <?php if (empty($issues)): ?>
        <p>You have no active issued books.</p>
    <?php else: ?>
        <?php foreach ($issues as $issue): ?>
        <div class="book-card">
            <h2><?= htmlspecialchars($issue['title']) ?></h2>
            <p><strong>Author:</strong> <?= htmlspecialchars($issue['author']) ?></p>
            <p><strong>Issued On:</strong> <?= $issue['issue_date'] ? date('F j, Y', strtotime($issue['issue_date'])) : '—' ?></p>
            <p><strong>Expected Return:</strong> <?= $issue['due_date'] ? date('F j, Y', strtotime($issue['due_date'])) : '—' ?></p>
            
            <p class="status <?= strtolower($issue['status']) ?>">
                Status: <?= $statuses[$issue['status']] ?>
                <?= $issue['status'] === 'overdue' ? "(Overdue)" : "" ?>
            </p>

            <?php if (!empty($issue['fine'])): ?>
                <p class="fine">💰 Fine: ₹<?= $issue['fine'] ?></p>
            <?php endif; ?>

            <div class="actions">
                <?php if (strtolower(trim($issue['status'])) === 'issued'): ?>
                    <form method="post">
                        <input type="hidden" name="issue_id" value="<?= $issue['id'] ?>">
                        <select name="status" required>
                            <option value="return_pending">Return Pending</option>
                        </select>
                        <button type="submit" name="update_status">Update Status</button>
                    </form>
                <?php elseif (strtolower(trim($issue['status'])) === 'return_pending'): ?>
                    <p>⏳ Return request pending approval.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="back-links">
        <a href="issue_book.php">⬅ Back to Books</a>
        <a href="../index.php">🏠 Back to Home</a>
    </div>
</div>
</div>
</body>
</html>
