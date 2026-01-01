<?php
ob_start();
session_start();
require_once '../includes/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['is_admin'] == 1) {
        header("Location: ../admin/admin_books.php");
    } else {
        header("Location: ../student/issue_book.php");
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['is_admin'] = $user['is_admin'];

    if ($user['is_admin']) {
        header("Location: ../admin/admin_books.php");
    } else {
        header("Location: ../student/issue_book.php");
    }
    exit();
} else {
    $error = "❌ Invalid email or password.";
}

}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            height: 100vh;
            color: white;
        }

        .navbar.styled-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 0;
            padding: 20px 0;
            background: rgba(0, 0, 0, 0.6);
        }
        .navbar.styled-buttons a {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: background-color 0.2s ease;
        }
        .navbar.styled-buttons a:hover {
            background-color: #0056b3;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 100px);
        }

        .login-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            width: 350px;
            color: white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        }

        .login-box h2 {
            margin-bottom: 25px;
            font-size: 1.8rem;
            background: linear-gradient(45deg, #f1c40f, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .login-box input[type="email"],
        .login-box input[type="password"] {
            width: 90%;
            padding: 12px;
            margin: 12px 0;
            border-radius: 10px;
            border: none;
            outline: none;
            font-size: 1rem;
        }

        .login-box button {
            padding: 12px 25px;
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
            width: 95%;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s ease, background 0.3s ease;
        }
        .login-box button:hover {
            transform: scale(1.05);
            background: linear-gradient(45deg, #2980b9, #27ae60);
        }

        .error {
            background-color: rgba(231, 76, 60, 0.9);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

<div class="navbar styled-buttons">
    <a href="../index.php"><i class="fas fa-home"></i> Home</a>
    <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
</div>

<div class="login-container">
    <div class="login-box">
        <h2>Login to Library</h2>

        <?php if (!empty($error)) : ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    </div>
</div>

</body>
</html>

<?php ob_end_flush(); ?>
