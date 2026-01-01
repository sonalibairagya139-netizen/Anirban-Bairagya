<?php
session_start();

// Replace with actual database connection
$conn = new mysqli("localhost", "root", "", "library_system");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form inputs
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Query the users table
$sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password (assuming plain text for now)
    if ($user['password'] === $password) {
        $_SESSION['user'] = $user;

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../student/dashboard.php");
        }
        exit;
    }
}

// If login fails
header("Location: login.php?error=1");
exit;
