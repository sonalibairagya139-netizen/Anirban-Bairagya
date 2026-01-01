<?php
session_start();
require_once '../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all students
$students = $pdo->query("SELECT id, name FROM users WHERE role = 'student'")->fetchAll();

// Fetch books and calculate remaining copies for each
$booksStmt = $pdo->query("SELECT id, title, quantity FROM books");
$booksRaw = $booksStmt->fetchAll(PDO::FETCH_ASSOC);

$books = [];

foreach ($booksRaw as $book) {
    // Count issued and unreturned copies
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE book_id = ? AND returned = 0");
    $stmt->execute([$book['id']]);
    $issued = $stmt->fetchColumn();

    $remaining = $book['quantity'] - $issued;

    $books[] = [
        'id' => $book['id'],
        'title' => $book['title'],
        'remaining' => $remaining
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $book_id = $_POST['book_id'];
    $issue_date = $_POST['issue_date'];
    $return_date = $_POST['return_date'];

    // Get remaining copies
    $stmt = $pdo->prepare("SELECT quantity FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $totalQuantity = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE book_id = ? AND returned = 0");
    $stmt->execute([$book_id]);
    $issuedCount = $stmt->fetchColumn();

    if ($issuedCount >= $totalQuantity) {
        $error = "❌ All copies of this book are currently issued!";
    } else {
        $insert = $pdo->prepare("INSERT INTO issues (book_id, student_id, issue_date, return_date, returned) VALUES (?, ?, ?, ?, 0)");
        $insert->execute([$book_id, $student_id, $issue_date, $return_date]);
        $success = "✅ Book issued successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issue Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        form {
            max-width: 600px;
            margin: 40px auto;
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            color: white;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }

        button {
            padding: 10px 20px;
            background: #f39c12;
            border: none;
            color: white;
            cursor: pointer;
        }

        .msg {
            text-align: center;
            margin: 10px;
            font-weight: bold;
        }

        option:disabled {
            color: gray;
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>📚 Issue Book</h2>

        <?php if (!empty($error)) echo "<p class='msg' style='color:red;'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='msg' style='color:lightgreen;'>$success</p>"; ?>

        <label>Student</label>
        <select name="student_id" required>
            <option value="">Select Student</option>
            <?php foreach ($students as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Book</label>
        <select name="book_id" required>
            <option value="">Select Book</option>
            <?php foreach ($books as $b): ?>
                <option value="<?= $b['id'] ?>" <?= $b['remaining'] <= 0 ? 'disabled' : '' ?>>
                    <?= htmlspecialchars($b['title']) ?> 
                    <?= $b['remaining'] <= 0 ? "(Not available)" : "(Available: {$b['remaining']})" ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Issue Date</label>
        <input type="date" name="issue_date" required>

        <label>Return Date (optional)</label>
        <input type="date" name="return_date">

        <button type="submit">Issue Book</button>
    </form>
</body>
</html>
