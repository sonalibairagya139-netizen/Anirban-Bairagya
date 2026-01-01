<?php
session_start();
require_once '../includes/config.php';

if ($_SESSION['is_admin'] != 1) {
    die("Unauthorized");
}

if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: admin_books.php");
