<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Quick mobile nav toggle style (optional) */
        .mobile-menu-btn {
            display: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .main-nav ul {
                display: none;
                flex-direction: column;
                background: white;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            }
            .main-nav ul.show {
                display: flex;
            }
            .mobile-menu-btn {
                display: block;
            }
        }

        .main-header {
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 1rem 0;
        }

        .main-header .logo img {
            height: 40px;
            vertical-align: middle;
        }

        .main-header .logo span {
            font-weight: 600;
            font-size: 1.2rem;
            margin-left: 0.5rem;
            color: #2c3e50;
        }

        .main-header nav ul {
            list-style: none;
            display: flex;
            gap: 1.2rem;
            margin: 0;
            padding: 0;
        }

        .main-header nav a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
        }

        .main-header nav a:hover {
            color: #3498db;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 0 1rem;
        }
    </style>
</head>
<body>

<?php if (empty($hideNavbar)): ?>
<header class="main-header">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="logo">
            <a href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Library Logo">
                <span><?php echo SITE_NAME; ?></span>
            </a>
        </div>
        <nav class="main-nav">
            <ul id="mainMenu">
                <li><a href="<?php echo BASE_URL; ?>"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="<?php echo BASE_URL; ?>/admin/books.php"><i class="fas fa-book"></i> Books</a></li>
                <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php elseif (isset($_SESSION['student_logged_in'])): ?>
                    <li><a href="<?php echo BASE_URL; ?>/student/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo BASE_URL; ?>/auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="mobile-menu-btn" onclick="document.getElementById('mainMenu').classList.toggle('show');">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</header>
<?php endif; ?>

<main class="container">
