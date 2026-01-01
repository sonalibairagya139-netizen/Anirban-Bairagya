<?php
require_once '../includes/config.php';
session_start(); // Required for session access
adminAuth();

// Fetch books with issued count
$stmt = $pdo->prepare("
    SELECT books.*, COUNT(issues.id) AS issued_count
    FROM books
    LEFT JOIN issues 
        ON books.id = issues.book_id 
        AND issues.return_date IS NULL
    GROUP BY books.id
");
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/admin-header.php'; ?>

<div class="dashboard">
    <div class="dashboard-header">
        <h1><i class="fas fa-book"></i> Library Books</h1>
        <p>Overview of all books with issued status</p>
    </div>

    <div class="dashboard-section">
        <?php if (count($books) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Book Name</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Total Copies</th>
                            <th>Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td>
                                    <span class="badge badge-primary">
                                        <?php echo htmlspecialchars($book['total_copies']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($book['issued_count'] > 0): ?>
                                        <span class="badge badge-warning">
                                            <?php echo $book['issued_count']; ?> issued
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No books found in the library.</div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>
