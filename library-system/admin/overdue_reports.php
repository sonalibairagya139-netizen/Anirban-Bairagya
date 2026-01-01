<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/update_fines.php'; // ensure fines are updated

// Handle Paid click
if (isset($_POST['mark_paid'])) {
    $issueId = intval($_POST['issue_id']);
    $update = $pdo->prepare("UPDATE issues SET status = 'paid' WHERE id = ?");
    $update->execute([$issueId]);
    header("Location: overdue_reports.php");
    exit();
}

// Handle Return click
if (isset($_POST['mark_returned'])) {
    $issueId = intval($_POST['issue_id']);
    $update = $pdo->prepare("UPDATE issues SET status = 'returned', fine = 0 WHERE id = ?");
    $update->execute([$issueId]);
    header("Location: overdue_reports.php");
    exit();
}

// ✅ Fetch only overdue and paid
$stmt = $pdo->query("
    SELECT i.id, s.name AS student_name, b.title AS book_title, 
           i.issue_date, i.return_date, i.fine, i.status
    FROM issues i
    JOIN students s ON i.student_id = s.id
    JOIN books b ON i.book_id = b.id
    WHERE i.status IN ('overdue','paid')
    ORDER BY i.return_date ASC
");
$issues = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fine Reports</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root{
            --bg1:#0f172a;
            --bg2:#0b1025;
            --card:#0b122b;
            --muted:#94a3b8;
            --text:#e2e8f0;
            --brand:#22d3ee;
            --brand2:#818cf8;
            --danger:#ef4444;
            --warn:#f59e0b;
            --ok:#10b981;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family: ui-sans-serif, system-ui, "Segoe UI", Roboto, Helvetica, Arial;
            color:var(--text);
            background:
                radial-gradient(1200px 600px at -10% -10%, rgba(34,211,238,.15), transparent 40%),
                radial-gradient(900px 500px at 110% 10%, rgba(129,140,248,.12), transparent 35%),
                linear-gradient(180deg, var(--bg2), var(--bg1));
            min-height:100vh;
            padding:32px;
        }
        .container{max-width:1150px;margin:0 auto}
        h2{margin:0 0 18px;font-size:22px;font-weight:700;letter-spacing:.3px}
        table{ width:100%; border-collapse:collapse }
        thead th{
            text-align:left;
            font-size:12px;
            text-transform:uppercase;
            letter-spacing:.08em;
            color:var(--muted);
            padding:14px 16px;
            background:linear-gradient(180deg, rgba(255,255,255,.04), transparent);
            border-bottom:1px solid rgba(255,255,255,.06);
        }
        tbody td{
            padding:14px 16px;
            border-bottom:1px dashed rgba(255,255,255,.06);
            vertical-align:middle;
        }
        tbody tr:hover{ background:rgba(255,255,255,.025) }
        .fine{ color:var(--danger); font-weight:800 }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-paid {
            background: #ff9800;
            color: #fff;
        }
        .btn-paid:hover {
            background: #e68900;
        }
        .btn-return {
            background: #2196f3;
            color: #fff;
        }
        .btn-return:hover {
            background: #1976d2;
        }
        .status-returned {
            color: #4caf50;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
  <h2>💰 Fine Reports <span style="color:lime;font-size:14px;">Live</span></h2>
  <table>
    <thead>
      <tr>
        <th>Student</th>
        <th>Book</th>
        <th>Issued</th>
        <th>Due</th> <!-- ✅ Ensure Due Date column -->
        <th>Fine (₹)</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($issues) > 0): ?>
        <?php foreach ($issues as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['student_name']); ?></td>
            <td><?= htmlspecialchars($row['book_title']); ?></td>
            <td><?= $row['issue_date']; ?></td>
            <td><?= $row['return_date']; ?></td> <!-- ✅ Show Due Date -->
            <td style="color:red;">₹<?= number_format($row['fine'], 2); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td>
              <?php if ($row['status'] === 'overdue'): ?>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="issue_id" value="<?= $row['id']; ?>">
                  <button type="submit" name="mark_paid" class="btn-action btn-paid">Paid</button>
                </form>
              <?php elseif ($row['status'] === 'paid'): ?>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="issue_id" value="<?= $row['id']; ?>">
                  <button type="submit" name="mark_returned" class="btn-action btn-return">Return</button>
                </form>
              <?php elseif ($row['status'] === 'returned'): ?>
                <span class="status-returned">Returned</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7">✅ No overdue reports.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <br>
  <a href="../index.php"><i class="fas fa-home"></i> Home</a> 
</div>
</body>
</html>
