<?php
require_once 'includes/config.php';

$name = "Admin";
$email = "admin@example.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$is_admin = 1;

$stmt = $pdo->prepare("INSERT INTO students (name, email, password, is_admin) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, $email, $password, $is_admin]);

echo "✅ Admin user created successfully!";
?>
