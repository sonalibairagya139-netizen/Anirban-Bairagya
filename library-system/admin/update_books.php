<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

$bookId = $_GET['id'] ?? null;

if (!$bookId) {
    echo "Invalid book ID.";
    exit();
}

// Fetch book details
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    echo "Book not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $description = $_POST['description'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $department = $_POST['department'] ?? '';

    $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, description=?, isbn=?, quantity=?, department=? WHERE id=?");
    $stmt->execute([$title, $author, $description, $isbn, $quantity, $department, $bookId]);

    header("Location: admin_books.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .edit-panel {
            background: rgba(0, 0, 0, 0.75);
            padding: 40px;
            border-radius: 15px;
            max-width: 550px;
            width: 90%;
            color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }

        .edit-panel:hover {
            transform: scale(1.02);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #f1c40f;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            font-size: 15px;
            color: #333;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #3498db;
            outline: none;
            background-color: #fff;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-align: center;
        }

        .btn-submit:hover {
            background: #2980b9;
        }

        .back-btn {
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            padding: 10px 15px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .back-btn i {
            margin-right: 8px;
        }

        .back-btn:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

<div class="edit-panel">
    <h2><i class="fas fa-edit"></i> Edit Book</h2>
    <form method="POST">
        <div class="form-group">
            <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" placeholder="Title" required>
        </div>
        <div class="form-group">
            <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" placeholder="Author" required>
        </div>
        <div class="form-group">
            <textarea name="description" placeholder="Description"><?= htmlspecialchars($book['description']) ?></textarea>
        </div>
        <div class="form-group">
            <input type="text" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>" placeholder="ISBN">
        </div>
        <div class="form-group">
            <input type="number" name="quantity" value="<?= htmlspecialchars($book['quantity']) ?>" placeholder="Quantity" required>
        </div>
        <?php include '../includes/departments.php'; ?>
        <div class="form-group">
            <label for="department">Department</label>
                <select name="department" id="department" required>
                    <option value="" disabled>Select Department</option>
                        <?php foreach ($departments as $code => $name): ?>
                            <option value="<?= $code ?>" <?= ($book['department'] == $code) ? 'selected' : '' ?>>
                                    <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                </select>
        </div>

        <button type="submit" class="btn-submit">Update Book</button>
    </form>
    <a href="admin_books.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Admin</a>
</div>

</body>
</html>
