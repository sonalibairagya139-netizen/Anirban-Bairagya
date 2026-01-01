<?php
session_start();
require_once '../includes/config.php';

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$fine_per_day = 10; // Set fine per overdue day
$today = new DateTime();

// 1. Update overdue books (status 'issued' and due_date < today)
$updateStmt = $pdo->prepare("
    UPDATE issues
    SET status = 'overdue', fine = DATEDIFF(CURDATE(), due_date) * :fine_per_day
    WHERE status = 'issued' AND due_date < CURDATE()
");
$updateStmt->execute([':fine_per_day' => $fine_per_day]);

// 2. Fetch issued books (include overdue now)
$issuedStmt = $pdo->prepare("
    SELECT i.id, b.title AS book_name, s.name AS student_name, i.issue_date, i.return_date, i.status
    FROM issues i
    JOIN books b ON i.book_id = b.id
    JOIN students s ON i.student_id = s.id
    WHERE i.status IN ('issued','issue_pending','overdue')
    ORDER BY i.issue_date DESC
");
$issuedStmt->execute();
$issuedBooks = $issuedStmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fetch returned books
$returnedStmt = $pdo->prepare("
    SELECT i.id, b.title AS book_name, s.name AS student_name, i.issue_date, i.return_date, i.status
    FROM issues i
    JOIN books b ON i.book_id = b.id
    JOIN students s ON i.student_id = s.id
    WHERE i.status IN ('returned','return_pending')
    ORDER BY i.return_date DESC
");
$returnedStmt->execute();
$returnedBooks = $returnedStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - History</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: url('../assets/images/library-bg.jpg') no-repeat center center/cover; background-size: cover; color: #fff; }
        .overlay { background-color: rgba(0,0,0,0.85); min-height: 100vh; display: flex; }
        .sidebar { width: 260px; background: rgba(0,0,0,0.95); padding: 20px 0; box-shadow: 3px 0 15px rgba(0,0,0,0.5); }
        .sidebar h2 { text-align: center; margin-bottom: 30px; color: #f1c40f; font-size: 1.6rem; }
        .sidebar a { display: block; padding: 14px 25px; color: #fff; text-decoration: none; font-size: 1.05rem; transition: all 0.3s ease; margin: 5px 15px; border-radius: 6px; }
        .sidebar a i { margin-right: 12px; }
        .sidebar a:hover { background: #007bff; box-shadow: 0 4px 8px rgba(0,0,0,0.3); }
        .main-content { flex: 1; padding: 40px 50px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; }
        .topbar h2 { font-size: 1.8rem; color: #f39c12; margin: 0; }
        .topbar span a { color: #ffb74d; text-decoration: none; margin-left: 15px; transition: 0.3s; }
        .topbar span a:hover { color: #fff; }
        h1 { font-size: 2.2rem; margin-bottom: 20px; color: #f1c40f; }
        h2.section-title { margin-top: 30px; color: #f39c12; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.2); color: #fff; }
        th { background-color: rgba(0,0,0,0.6); }
        tr:hover { background-color: rgba(255,255,255,0.15); }

        /* Status styles */
        .status-issued { color: #1976d2; font-weight: bold; }
        .status-overdue { color: #d32f2f; font-weight: bold; }
        .status-issue_pending { color: #fbc02d; font-weight: bold; }
        .status-returned { color: #388e3c; font-weight: bold; }
        .status-return_pending { color: #fbc02d; font-weight: bold; }
    </style>
</head>
<body>
<div class="overlay">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>📚 Admin Panel</h2>
        <a href="admin_books.php"><i class="fas fa-book"></i> Books</a>
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

        <h1>History of Book Issues</h1>
        <p>All issued and returned books are shown below</p>

        <!-- Issued Books -->
        <h2 class="section-title">Issued Books</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Student Name</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($issuedBooks): ?>
                    <?php foreach($issuedBooks as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['book_name']) ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['issue_date']) ?></td>
                            <td><?= htmlspecialchars($row['return_date']) ?></td>
                            <td class="status-<?= strtolower($row['status']) ?>"><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No issued books found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Returned Books -->
        <h2 class="section-title">Returned Books</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Book Name</th>
                    <th>Student Name</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($returnedBooks): ?>
                    <?php foreach($returnedBooks as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($row['book_name']) ?></td>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['issue_date']) ?></td>
                            <td><?= htmlspecialchars($row['return_date']) ?></td>
                            <td class="status-<?= strtolower($row['status']) ?>"><?= htmlspecialchars(ucfirst($row['status'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6">No returned books found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
