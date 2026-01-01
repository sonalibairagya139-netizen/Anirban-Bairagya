<?php
require_once '../includes/config.php';
adminAuth();

// Handle student deletion
if(isset($_GET['delete'])) {
    $student_id = (int)$_GET['delete'];
    
    // Check if student has active book issues
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE student_id = ? AND status != 'returned'");
    $stmt->execute([$student_id]);
    $activeIssues = $stmt->fetchColumn();
    
    if($activeIssues > 0) {
        $_SESSION['error_message'] = 'Cannot delete student with active book issues';
    } else {
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
        if($stmt->execute([$student_id])) {
            $_SESSION['success_message'] = 'Student deleted successfully!';
        } else {
            $_SESSION['error_message'] = 'Failed to delete student';
        }
    }
    header("Location: students.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT * FROM students";
$params = [];

if(!empty($search)) {
    $query .= " WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR department LIKE ?";
    $searchTerm = "%$search%";
    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
}

$query .= " ORDER BY name";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();

$pageTitle = "Manage Students";
require_once '../includes/header.php';
?>

<!-- ===== Custom CSS for Manage Students Page ===== -->
<style>
.admin-section {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
    margin: 20px auto;
    max-width: 1200px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-header h1 {
    font-size: 24px;
    font-weight: 600;
    color: #333;
}

.section-actions .btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    transition: 0.3s;
}

.section-actions .btn-primary {
    background: #007bff;
    border: none;
    color: #fff;
}

.section-actions .btn-primary:hover {
    background: #0056b3;
}

/* Alerts */
.alert {
    padding: 12px 16px;
    margin-bottom: 15px;
    border-radius: 6px;
    font-size: 14px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
}

/* Search Box */
.search-box {
    margin-bottom: 20px;
}

.input-group {
    display: flex;
    gap: 10px;
}

.input-group input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.input-group .btn {
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
}

.input-group .btn-secondary {
    background: #6c757d;
    color: #fff;
    border: none;
}

.input-group .btn-secondary:hover {
    background: #565e64;
}

.input-group .btn-outline-secondary {
    border: 1px solid #6c757d;
    color: #6c757d;
    background: transparent;
}

.input-group .btn-outline-secondary:hover {
    background: #6c757d;
    color: #fff;
}

/* Table */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: #fff;
}

.table thead {
    background: #f1f1f1;
}

.table th, 
.table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eaeaea;
    font-size: 14px;
}

.table th {
    font-weight: 600;
    color: #333;
}

.table tbody tr:hover {
    background: #fafafa;
}

/* Action Buttons */
.btn-sm {
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 13px;
    margin-right: 5px;
}

.btn-info {
    background: #17a2b8;
    color: #fff;
    border: none;
}

.btn-info:hover {
    background: #117a8b;
}

.btn-danger {
    background: #dc3545;
    color: #fff;
    border: none;
}

.btn-danger:hover {
    background: #bd2130;
}
</style>

<div class="admin-section">
    <div class="section-header">
        <h1><i class="fas fa-users"></i> Manage Students</h1>
        <div class="section-actions">
            <a href="add-student.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Student
            </a>
        </div>
    </div>
    
    <?php if(isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    
    <div class="search-box">
        <form action="" method="get">
            <div class="input-group">
                <input type="text" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if(!empty($search)): ?>
                    <a href="students.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $student): ?>
                <tr>
                    <td><?php echo $student['student_id']; ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                    <td><?php echo htmlspecialchars($student['department']); ?></td>
                    <td><?php echo date('d M Y', strtotime($student['created_at'])); ?></td>
                    <td>
                        <a href="edit-student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-info" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="students.php?delete=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this student?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
