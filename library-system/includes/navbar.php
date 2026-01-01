<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/config.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.navbar {
    background: #333;
    padding: 10px;
}
.nav-links {
    list-style: none;
    display: flex;
    align-items: center;
    margin: 0;
    padding: 0;
}
.nav-links li {
    margin-right: 15px;
}
.nav-links a {
    color: #fff;
    text-decoration: none;
}
.notification {
    position: relative;
    display: inline-block;
}
.notification .badge {
    position: absolute;
    top: -8px;
    right: -15px;
    padding: 3px 8px;
    border-radius: 12px;
    background: red;
    color: white;
    font-size: 12px;
    font-weight: bold;
    white-space: nowrap;
}
</style>

<!-- includes/navbar.php -->
<nav class="navbar">
    <div class="nav-container">
        <ul class="nav-links">
            <li><a href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home"></i> Home</a></li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <!-- ================= Admin View ================= -->

                    <?php
                        // Calculate overdue count + total fines
                        $overdueStmt = $pdo->query("
                            SELECT COUNT(*) AS overdue_count, 
                                   COALESCE(SUM(fine), 0) AS total_fine
                            FROM issues 
                            WHERE status = 'overdue'
                        ");
                        $overdueData = $overdueStmt->fetch();
                        $overdueCount = $overdueData['overdue_count'] ?? 0;
                        $totalFine = $overdueData['total_fine'] ?? 0;
                    ?>

                    <li><a href="<?php echo BASE_URL; ?>/admin/books.php"><i class="fas fa-book"></i> View Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/requests.php"><i class="fas fa-inbox"></i> Requests</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/history.php"><i class="fas fa-users"></i> History</a></li> <!-- ✅ Fixed -->

                    <!-- Notification Icon -->
                    <li>
                        <a href="<?php echo BASE_URL; ?>/admin/overdue_reports.php" class="notification">
                            <i class="fas fa-bell"></i>
                            <?php if ($overdueCount > 0 || $totalFine > 0): ?>
                                <span class="badge">
                                    <?php echo $overdueCount . " | ₹" . $totalFine; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li><a href="<?php echo BASE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

                    <span style="color: #fff; margin-left: 10px; font-weight: bold;">
                        👑 Admin
                    </span>

                <?php else: ?>
                    <!-- ================= Student View ================= -->
                    <li><a href="<?php echo BASE_URL; ?>/student/books.php"><i class="fas fa-book"></i> Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/student/issue_book.php"><i class="fas fa-share-square"></i> Issue Book</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/student/my_issues.php"><i class="fas fa-book-reader"></i> My Issued Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/student/requests.php"><i class="fas fa-inbox"></i> My Requests</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>

                    <?php
                        $stmt = $pdo->prepare("SELECT name FROM students WHERE id = ?");
                        $stmt->execute([$_SESSION['user_id']]);
                        $user = $stmt->fetch();
                        echo '<span style="color: #fff; margin-left: 10px; font-weight: bold;">👤 ' . htmlspecialchars($user['name']) . '</span>';
                    ?>
                <?php endif; ?>
            <?php else: ?>
                <!-- ================= Guest View ================= -->
                <li><a href="<?php echo BASE_URL; ?>/auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>/auth/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
