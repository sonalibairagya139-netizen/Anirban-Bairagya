<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_id = $_POST['issue_id'];
    $action = $_POST['action'];

    if ($action === 'approve_issue') {
        $stmt = $pdo->prepare("UPDATE issues SET status = 'approved' WHERE id = ?");
        $stmt->execute([$issue_id]);
    } elseif ($action === 'reject_issue') {
        $stmt = $pdo->prepare("UPDATE issues SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$issue_id]);
    } elseif ($action === 'approve_return') {
        // Delete the record since book is returned
        $stmt = $pdo->prepare("DELETE FROM issues WHERE id = ?");
        $stmt->execute([$issue_id]);
    } elseif ($action === 'reject_return') {
        $stmt = $pdo->prepare("UPDATE issues SET status = 'approved' WHERE id = ?");
        $stmt->execute([$issue_id]); // revert back to approved
    }
    header("Location: manage_issues.php?updated=1");
    exit;
}

// Fetch all issues
$stmt = $pdo->query("
    SELECT issues.id, issues.issue_date, issues.status,
           students.name AS student_name,
           books.title AS book_title, books.author
    FROM issues
    JOIN students ON students.id = issues.student_id
    JOIN books ON books.id = issues.book_id
    ORDER BY issues.issue_date DESC
");
$issues = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Issues</title>
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

        .container {
            width: 95%;
            max-width: 1200px;
            padding: 30px;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
            color: #f1c40f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background: #f39c12;
            color: #fff;
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }

        .status { font-weight: bold; }
        .pending { color: orange; }
        .approved { color: #2ecc71; }
        .rejected { color: #e74c3c; }
        .return_pending { color: #3498db; }

        .action-btn {
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin: 2px;
        }
        .approve { background: #2ecc71; color: white; }
        .reject { background: #e74c3c; color: white; }
        .approve:hover { background: #27ae60; }
        .reject:hover { background: #c0392b; }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #f1c40f;
            text-decoration: none;
        }

        .back-link:hover { text-decoration: underline; }

        .success {
            color: #2ecc71;
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if (isset($_GET['updated'])): ?>
        <div class="success">✅ Action performed successfully!</div>
    <?php endif; ?>

    <h2><i class="fas fa-tasks"></i> Manage Book Issues</h2>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Book</th>
                <th>Issued On</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($issues) > 0): ?>
                <?php foreach ($issues as $issue): ?>
                    <tr>
                        <td><?= htmlspecialchars($issue['student_name']) ?></td>
                        <td><?= htmlspecialchars($issue['book_title']) ?> <br><small><?= htmlspecialchars($issue['author']) ?></small></td>
                        <td><?= date('F j, Y', strtotime($issue['issue_date'])) ?></td>
                        <td class="status <?= strtolower(str_replace(' ', '_', $issue['status'])) ?>">
                            <?= ucfirst(str_replace('_', ' ', $issue['status'])) ?>
                        </td>
                        <td>
                            <?php if ($issue['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="issue_id" value="<?= $issue['id'] ?>">
                                    <button type="submit" name="action" value="approve_issue" class="action-btn approve">Approve</button>
                                    <button type="submit" name="action" value="reject_issue" class="action-btn reject">Reject</button>
                                </form>
                            <?php elseif ($issue['status'] === 'return_pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="issue_id" value="<?= $issue['id'] ?>">
                                    <button type="submit" name="action" value="approve_return" class="action-btn approve">Confirm Return</button>
                                    <button type="submit" name="action" value="reject_return" class="action-btn reject">Reject Return</button>
                                </form>
                            <?php else: ?>
                                <em>No action</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No book issues found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
