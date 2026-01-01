<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants
define('SITE_NAME', 'Library Management System');
define('BASE_URL', '/library-system');

// Database configuration
$host     = 'localhost';
$db       = 'library_db';
$user     = 'root';
$pass     = '';
$charset  = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Auto-update fines for overdue books
try {
    $finePerDay = 5;
    $today = date('Y-m-d');

    $stmt = $pdo->query("SELECT * FROM issues WHERE return_date < '$today' AND status = 'issued'");
    while ($issue = $stmt->fetch()) {
        $returnDate = $issue['return_date'];
        $issueId = $issue['id'];

        $overdueDays = floor((strtotime($today) - strtotime($returnDate)) / (60 * 60 * 24));
        $fine = $overdueDays * $finePerDay;

        $update = $pdo->prepare("UPDATE issues SET status = 'overdue', fine = ? WHERE id = ?");
        $update->execute([$fine, $issueId]);
    }
} catch (PDOException $e) {
    // Optional: log error or ignore
}

// Admin authentication check
function adminAuth(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// Student authentication check
function studentAuth(): void {
    if (empty($_SESSION['student_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

// Automatically update fines and overdue statuses
require_once __DIR__ . '/../admin/update_fines.php';
