<?php
// Prevent duplicate execution
if (!defined('FINES_UPDATED')) {
    define('FINES_UPDATED', true);

    require_once __DIR__ . '/../includes/config.php';

    // Fine rate (per month)
    $finePerMonth = 200;

    try {
        $today = date('Y-m-d');

        // Select records that are overdue (by date or status)
        $stmt = $pdo->query("
            SELECT * FROM issues
            WHERE (due_date < '$today' AND status = 'issued')
               OR status = 'overdue'
        ");

        while ($issue = $stmt->fetch()) {
            $dueDate = $issue['due_date'];
            $issueId = $issue['id'];

            $overdueMonths = 0;
            if (strtotime($today) > strtotime($dueDate)) {
                // Calculate difference in months
                $yearDiff  = date('Y', strtotime($today)) - date('Y', strtotime($dueDate));
                $monthDiff = date('m', strtotime($today)) - date('m', strtotime($dueDate));
                $dayDiff   = date('d', strtotime($today)) - date('d', strtotime($dueDate));

                $overdueMonths = ($yearDiff * 12) + $monthDiff;
                if ($dayDiff > 0) {
                    $overdueMonths += 1; // round up to next month if partial
                }
            }

            $fine = $overdueMonths * $finePerMonth;

            // Update DB
            $update = $pdo->prepare("UPDATE issues SET status = 'overdue', fine = ? WHERE id = ?");
            $update->execute([$fine, $issueId]);
        }

    } catch (PDOException $e) {
        error_log("❌ Error updating fines: " . $e->getMessage());
    }
}
