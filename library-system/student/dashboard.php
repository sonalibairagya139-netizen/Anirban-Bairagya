<?php
require_once('../includes/config.php');
studentAuth();

$pageTitle = "Student Dashboard";
require_once '../includes/header.php';

// Fetch data
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

$booksIssued = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE student_id = ? AND status IN ('issued', 'overdue')");
$booksIssued->execute([$student_id]);
$issuedCount = $booksIssued->fetchColumn();

$booksReturned = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE student_id = ? AND status = 'returned'");
$booksReturned->execute([$student_id]);
$returnedCount = $booksReturned->fetchColumn();

$fineStmt = $pdo->prepare("SELECT SUM(fine) FROM issues WHERE student_id = ? AND fine > 0");
$fineStmt->execute([$student_id]);
$totalFine = $fineStmt->fetchColumn();
$totalFine = $totalFine ? $totalFine : 0;

// Get recent issues
$stmt = $pdo->prepare("SELECT i.*, b.title, b.author 
                       FROM issues i 
                       JOIN books b ON i.book_id = b.book_id 
                       WHERE i.student_id = ? 
                       ORDER BY i.issue_date DESC LIMIT 5");
$stmt->execute([$student_id]);
$recentIssues = $stmt->fetchAll();
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1><i class="fas fa-user"></i> Welcome, <?php echo htmlspecialchars($student_name); ?></h1>
        <p>Here is your current activity summary</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $issuedCount; ?></h3>
                <p>Books Currently Issued</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-book-reader"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $returnedCount; ?></h3>
                <p>Books Returned</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-danger">
                <i class="fas fa-rupee-sign"></i>
            </div>
            <div class="stat-info">
                <h3>₹<?php echo $totalFine; ?></h3>
                <p>Total Fines</p>
            </div>
        </div>
    </div>

    <div class="dashboard-section">
        <h2><i class="fas fa-history"></i> Recent Issued Books</h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Issue Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentIssues)): ?>
                        <tr><td colspan="6" class="text-center">No issue history available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentIssues as $issue): ?>
                            <tr>
                                <td><?php echo $issue['issue_id']; ?></td>
                                <td><?php echo htmlspecialchars($issue['title']); ?></td>
                                <td><?php echo htmlspecialchars($issue['author']); ?></td>
                                <td><?php echo date('d M Y', strtotime($issue['issue_date'])); ?></td>
                                <td><?php echo date('d M Y', strtotime($issue['return_date'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $issue['status'] == 'issued' ? 'warning' : 
                                             ($issue['status'] == 'returned' ? 'success' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($issue['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
