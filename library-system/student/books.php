<?php
session_start();
require_once '../includes/config.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$message = '';

try {
    // Fetch all books along with issued + pending count
    $stmt = $pdo->query("
        SELECT b.*, 
               (SELECT COUNT(*) 
                FROM issues i 
                WHERE i.book_id = b.id 
                AND i.status IN ('issued', 'issue_pending')) AS issued_count
        FROM books b
    ");
    $allBooks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Filter only books with available copies
    $books = array_filter($allBooks, function($book) {
        return $book['quantity'] > $book['issued_count'];
    });

    // Handle issue request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
        $book_id = (int)$_POST['book_id'];
        $student_id = $_SESSION['user_id'];

        // Check if student already has this book requested or issued
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE student_id = ? AND book_id = ? AND status IN ('issued', 'issue_pending')");
        $stmt->execute([$student_id, $book_id]);
        $alreadyExists = $stmt->fetchColumn();

        if ($alreadyExists == 0) {
            // Insert new issue request with status = issue_pending
            $stmt = $pdo->prepare("INSERT INTO issues (book_id, student_id, issue_date, return_date, status) 
                                   VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 5 MONTH), 'issue_pending')");
            $stmt->execute([$book_id, $student_id]);

            // Fetch book title for success message
            $stmt = $pdo->prepare("SELECT title FROM books WHERE id = ?");
            $stmt->execute([$book_id]);
            $bookTitle = $stmt->fetchColumn();

            $message = "✅ '$bookTitle' requested successfully! Please wait for admin approval.";
        } else {
            $message = "❌ You already requested/issued this book.";
        }
    }
} catch (Exception $e) {
    $books = [];
    $message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            color: white;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            background-size: cover;
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
            position: sticky;
            top: 0;
            z-index: 100;
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
            margin: 0 0 50px 0;
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: 1px;
            color: #f1c40f;
            text-transform: uppercase;
            text-shadow: 2px 2px 6px rgba(0,0,0,0.7);
            border-bottom: 3px solid rgba(241, 196, 15, 0.7);
            display: inline-block;
            padding-bottom: 10px;
        }
        
        /* Book Grid */
        .book-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
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

        /* Button */
        .issue-btn {
            margin-top: 12px;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
        }

        .issue-btn:hover {
            background: linear-gradient(135deg, #00c6ff, #007bff);
            transform: scale(1.05);
        }

        /* Responsive */
        @media(max-width: 600px){
            .book-list {
                grid-template-columns: 1fr;
            }
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
<div class="overlay">

    <!-- Navbar -->
    <div class="navbar">
        <div>
            <a href="../index.php"><i class="fas fa-home"></i> Home</a>
        </div>
        <div>
            <span>✨ Welcome, <?= htmlspecialchars($_SESSION['name']) ?> |
                <a href="../auth/logout.php">Logout</a>
            </span>
        </div>
    </div>

    <div class="container">
        <h1><i class="fas fa-book"></i> Available Books</h1>

        <div class="book-list">
            <?php if (!empty($books)) : ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($book['description']) ?></p>
                        <p><strong>Available:</strong> <?= $book['quantity'] - $book['issued_count'] ?></p>
                        <form action="" method="POST">
                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="issue-btn"><i class="fas fa-plus-circle"></i> Issue</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center;">📚 No books available right now.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popup alert -->
    <?php if (!empty($message)) : ?>
        <script>
            alert("<?= addslashes($message) ?>");
        </script>
    <?php endif; ?>

</div>
</body>
</html>
