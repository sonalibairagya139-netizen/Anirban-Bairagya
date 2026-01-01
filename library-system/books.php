<?php
session_start();
require_once 'includes/config.php';
include 'includes/navbar.php';

// Fetch only books with available quantity
$stmt = $pdo->query("SELECT * FROM books WHERE quantity > 0");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Books</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('assets/images/library-bg.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .book-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 0 25px rgba(0,0,0,0.2);
            max-width: 1000px;
            margin: auto;
        }
        .book-card {
            border-radius: 16px;
            transition: transform 0.3s ease;
        }
        .book-card:hover {
            transform: translateY(-5px);
        }
        .book-cover {
            height: 160px;
            object-fit: cover;
            border-radius: 10px;
        }

        /* No-books section styling */
        .no-books {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            max-width: 500px;
            margin: 40px auto;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
        }
        .no-books .icon {
            font-size: 48px;
            color: #0d6efd;
            margin-bottom: 20px;
        }
        .no-books h4 {
            color: #6c757d;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="book-section text-center">
        <h2 class="mb-4">
            <i class="fas fa-book text-primary"></i> <strong>Available Books</strong>
        </h2>

        <?php if (count($books) > 0): ?>
            <div class="row">
                <?php foreach ($books as $book): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card book-card shadow-sm h-100">
                            <img src="assets/images/book-placeholder.png" class="card-img-top book-cover" alt="Book Cover">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text">
                                    <strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?><br>
                                    <strong>Quantity:</strong> <?php echo $book['quantity']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-books">
                <div class="icon"><i class="fas fa-book-open"></i></div>
                <h4>No books available right now.</h4>
            </div>
        <?php endif; ?>

        <a href="index.php" class="btn btn-outline-primary mt-4">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>

</body>
</html>
