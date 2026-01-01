<?php
session_start();
require_once '../includes/config.php';

// Only admin can access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description']);
    $isbn = trim($_POST['isbn']);
    $quantity = intval($_POST['quantity']);
    $department = trim($_POST['department']);

    $stmt = $pdo->prepare("
        INSERT INTO books (title, author, description, isbn, quantity, department)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$title, $author, $description, $isbn, $quantity, $department]);

    header("Location: admin_books.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Book</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh; /* Ensures background fills full page */
            background: url('../assets/images/library-bg.jpg') no-repeat center center fixed;
            background-size: cover; /* Stretch to cover entire screen */
            font-family: Arial, sans-serif;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.75);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            width: 500px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.6);
            backdrop-filter: blur(6px);
        }
        h2 {
            text-align: center;
            color: #f1c40f;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            color: #f1f1f1;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border-radius: 8px;
            border: none;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            transform: scale(1.03);
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #f1c40f;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="form-container">
            <h2><i class="fas fa-plus-circle"></i> Add New Book</h2>
            <form method="POST">
                <label>Title</label>
                <input type="text" name="title" required>

                <label>Author</label>
                <input type="text" name="author" required>

                <label>Description</label>
                <textarea name="description" rows="3" required></textarea>

                <label>ISBN</label>
                <input type="text" name="isbn">

                <label>Quantity</label>
                <input type="number" name="quantity" min="1" required>

                <?php include '../includes/departments.php'; ?>
                <div class="form-group">
                    <label for="department">Department</label>
                    <select name="department" id="department" required>
                        <option value="" disabled selected>Select Department</option>
                        <?php foreach ($departments as $code => $name): ?>
                            <option value="<?= $code ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit"><i class="fas fa-save"></i> Save Book</button>
            </form>
            <a href="admin_books.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Manage Books</a>
        </div>
    </div>
</body>
</html>
