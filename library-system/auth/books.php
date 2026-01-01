<?php
require_once '../includes/config.php';

// Optional: Redirect logged-in users to their actual books page
if (isset($_SESSION['student_logged_in'])) {
    header('Location: ../student/books.php');
    exit();
} elseif (isset($_SESSION['admin_logged_in'])) {
    header('Location: ../admin/books.php');
    exit();
}

$pageTitle = "Books";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo SITE_NAME; ?> | <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            padding: 100px 20px;
        }
        .box {
            background-color: white;
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .box h2 {
            margin-bottom: 20px;
        }
        .box a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .box a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="box">
    <h2><i class="fas fa-lock"></i> Access Denied</h2>
    <p>You must be logged in to view the books.</p>
    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Go to Login</a>
</div>

</body>
</html>
