<?php
session_start();
require_once '../includes/config.php';

// Redirect if not admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

try {
    // ✅ Fixed subquery: use b.id instead of b.book_id
    $stmt = $pdo->query("
        SELECT b.*, 
               (SELECT COUNT(*) 
                FROM issues i 
                WHERE i.book_id = b.id AND i.status = 'Issued') AS issued_count
        FROM books b
        ORDER BY b.title ASC
    ");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $books = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            color: white;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.85);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: rgba(0, 0, 0, 0.65);
        }

        .navbar a {
            background: linear-gradient(135deg, #ffb400, #ff6a00);
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .navbar a:hover {
            background: linear-gradient(135deg, #ff6a00, #ffb400);
            transform: scale(1.05);
        }

        .navbar span {
            color: #ffd700;
            font-size: 0.95rem;
        }

        .navbar span a {
            color: #ff6a00;
            font-weight: bold;
            margin-left: 10px;
        }

        /* Container */
        .container {
            width: 90%;
            margin: auto;
            padding: 100px 0 50px 0;
            text-align: center;
        }

        h1 {
            margin: 0 0 30px 0;
            font-size: 2.5rem;
            font-weight: 700;
            color: #f1c40f;
            text-transform: uppercase;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
        }

        .top-btn {
            display: inline-block;
            margin-bottom: 30px;
            padding: 10px 20px;
            font-weight: bold;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .top-btn:hover {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            transform: scale(1.05);
        }

        /* Book Grid */
        .book-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }

        .book-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 6px 14px rgba(0,0,0,0.5);
            backdrop-filter: blur(6px);
            transition: transform 0.3s ease;
        }

        .book-card:hover {
            transform: translateY(-5px);
        }

        .book-card h3 {
            margin: 10px 0 8px;
            font-size: 1.3rem;
            color: #f39c12;
        }

        .book-card p {
            margin: 6px 0;
            font-size: 0.95rem;
            color: #ddd;
        }

        .action-btns {
            margin-top: 12px;
        }

        .action-btns a {
            display: inline-block;
            margin: 5px;
            padding: 7px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: bold;
            transition: 0.3s;
        }

        .edit-btn {
            background: #3498db;
            color: white;
        }
        .edit-btn:hover { background: #2980b9; }

        .delete-btn {
            background: #e74c3c;
            color: white;
        }
        .delete-btn:hover { background: #c0392b; }
    </style>
</head>
<body>
<div class="overlay">

    <!-- Navbar -->
    <div class="navbar">
        <div>
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
        <div>
            <span>👑 Admin: <?= htmlspecialchars($_SESSION['name']) ?> |
                <a href="../auth/logout.php">Logout</a>
            </span>
        </div>
    </div>

    <div class="container">
        <h1><i class="fas fa-book"></i> Manage Books</h1>

        <a href="add-book.php" class="top-btn"><i class="fas fa-plus"></i> Add New Book</a>

        <div class="book-list">
            <?php if (!empty($books)) : ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($book['description']) ?></p>
                        <p><strong>Total:</strong> <?= $book['quantity'] ?></p>
                        <p><strong>Issued:</strong> <?= $book['issued_count'] ?></p>
                        <p><strong>Available:</strong> <?= $book['quantity'] - $book['issued_count'] ?></p>

                        <div class="action-btns">
                            <a href="update_books.php?id=<?= $book['id'] ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_book.php?id=<?= $book['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this book?');"><i class="fas fa-trash"></i> Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">📚 No books found in library.</p>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
