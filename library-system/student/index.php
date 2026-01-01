<?php
require_once '../includes/config.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$studentId = $_SESSION['student_id'];
$overdueAlert = '';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE student_id = ? AND status = 'overdue'");
    $stmt->execute([$studentId]);
    $overdueCount = $stmt->fetchColumn();

    if ($overdueCount > 0) {
        $overdueAlert = "<div class='alert overdue'>⚠️ You have $overdueCount overdue book(s). Please return them immediately!</div>";
    }
} catch (PDOException $e) {
    $overdueAlert = "<div class='alert overdue'>❌ Error checking overdue books: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .alert.overdue {
            background: #e74c3c;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin: 15px 0;
            font-weight: bold;
            text-align: center;
        }
        .overdue-list li {
            background: rgba(255,0,0,0.1);
            padding: 8px;
            margin: 5px 0;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    <div class="container">
        <?= $overdueAlert ?>
        <h1>Welcome to Your Dashboard</h1>
        <!-- Additional student dashboard content -->
    </div>

    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <div class="container">
        <h2>🚨 Overdue Student Alerts</h2>
        <?php
        try {
            $overdueStudents = $pdo->query("SELECT s.id, s.name, COUNT(*) AS overdue_count
                FROM issues i
                JOIN students s ON i.student_id = s.id
                WHERE i.status = 'overdue'
                GROUP BY s.id, s.name
                ORDER BY overdue_count DESC")->fetchAll();

            if (count($overdueStudents) > 0) {
                echo "<ul class='overdue-list'>";
                foreach ($overdueStudents as $student) {
                    echo "<li><strong>" . htmlspecialchars($student['name']) . "</strong> has " . $student['overdue_count'] . " overdue book(s).</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No overdue books currently.</p>";
            }
        } catch (PDOException $e) {
            echo "<p>Error fetching overdue students: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
    <?php endif; ?>
</body>
</html>
