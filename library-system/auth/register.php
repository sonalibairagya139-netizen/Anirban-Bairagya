<?php
session_start();
require_once '../includes/config.php';

// Debug mode (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? ''); 
    $user_type = $_POST['user_type'] ?? ''; // 0 = Student, 1 = Admin
    $department = ($user_type === '0') ? ($_POST['department'] ?? '') : null;

    // Validation
    if (!$name || !$email || !$password || !$phone || ($user_type === '0' && !$department)) {
        $error = "All required fields must be filled.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Check duplicate email
        $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "An account already exists with this email.";
        } else {
            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO students (name, email, password, phone, department, is_admin) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $name,
                $email,
                $hashedPassword,
                $phone,
                $department ?: null, // NULL if admin
                (int)$user_type      // 0 or 1
            ]);

            $_SESSION['success_message'] = "Registration successful! Please log in.";
            header("Location: ../auth/login.php");
            exit();
        }
    }
}

$pageTitle = "Register";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo SITE_NAME; ?> | <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Background */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('../assets/images/library-bg.jpg') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Navbar */
        .navbar {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
        }

        .navbar a {
            padding: 10px 18px;
            background: linear-gradient(135deg, #007bff, #00c6ff);
            border-radius: 8px;
            color: white;
            font-weight: 500;
            text-decoration: none;
            transition: 0.3s;
        }

        .navbar a:hover {
            background: linear-gradient(135deg, #0056b3, #0099cc);
            transform: translateY(-2px);
        }

        /* Auth Container */
        .auth-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 16px;
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            width: 380px;
            text-align: center;
        }

        .auth-container h2 {
            color: #fff;
            margin-bottom: 20px;
        }

        /* Form */
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            outline: none;
            background: rgba(255,255,255,0.8);
            font-size: 15px;
        }

        .form-group input:focus, 
        .form-group select:focus {
            border: 2px solid #007bff;
            background: #fff;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: linear-gradient(135deg, #218838, #198754);
            transform: translateY(-2px);
        }

        .auth-links {
            margin-top: 15px;
        }

        .auth-links a {
            color: #00c6ff;
            font-weight: bold;
            text-decoration: none;
        }

        .alert-danger {
            background: rgba(255, 0, 0, 0.1);
            color: #ff4d4d;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
    <script>
        function toggleDepartment(select) {
            var deptField = document.getElementById('departmentField');
            if (select.value === "0") {
                deptField.style.display = "block";
            } else {
                deptField.style.display = "none";
            }
        }
    </script>
</head>
<body>

<div class="navbar">
    <a href="/library-system/index.php"><i class="fas fa-home"></i> Home</a>
    <a href="/library-system/auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    <a href="/library-system/auth/register.php"><i class="fas fa-user-plus"></i> Register</a>
</div>

<div class="auth-container">
    <h2><i class="fas fa-user-plus"></i> Register</h2>

    <?php if (!empty($error)): ?>
        <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <input type="text" name="name" placeholder="Full Name" required>
        </div>

        <div class="form-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>

        <div class="form-group">
            <input type="text" name="phone" placeholder="Phone Number" required>
        </div>

        <div class="form-group">
            <select name="user_type" required onchange="toggleDepartment(this)">
                <option value="" disabled selected>Select User Type</option>
                <option value="0">Student</option>
                <option value="1"> Admin</option>
            </select>
        </div>

        <div class="form-group" id="departmentField" style="display:none;">
            <select name="department">
                <option value="" disabled selected>Select Department</option>
                <option value="CSE">CSE</option>
                <option value="IT">IT</option>
                <option value="CSE(DS)">CSE(DS)</option>
                <option value="CSE(AIML)">CSE(AIML)</option>
                <option value="CSE(CS)">CSE(CS)</option>
                <option value="ME">ME</option>
                <option value="EE">EE</option>
                <option value="CE">CE</option>
                <option value="ECE">ECE</option>
            </select>
        </div>

        <button type="submit">Register</button>
    </form>

    <div class="auth-links">
        <p>Already registered? <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></p>
    </div>
</div>

</body>
</html>
