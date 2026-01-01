</main>

<footer class="main-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section about">
                <h3>About Us</h3>
                <p><?php echo SITE_NAME; ?> is a smart library system designed to manage book issues and returns efficiently.</p>
            </div>
            <div class="footer-section links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/books.php">Books</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/login.php">Login</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/auth/register.php">Register</a></li>
                </ul>
            </div>
            <div class="footer-section contact">
                <h3>Contact</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Library Street, Knowledge City</p>
                <p><i class="fas fa-phone"></i> +1 234 567 890</p>
                <p><i class="fas fa-envelope"></i> info@librarysystem.com</p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
    .main-footer {
        background-color: #f5f5f5;
        color: #333;
        padding: 2rem 0 1rem;
        margin-top: 3rem;
        border-top: 1px solid #e1e1e1;
        font-family: 'Poppins', sans-serif;
    }

    .main-footer .footer-content {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 2rem;
    }

    .main-footer .footer-section {
        flex: 1 1 250px;
    }

    .main-footer h3 {
        margin-bottom: 1rem;
        font-size: 1.25rem;
        color: #2c3e50;
    }

    .main-footer p, .main-footer li {
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 0.5rem;
    }

    .main-footer ul {
        list-style: none;
        padding: 0;
    }

    .main-footer ul li a {
        color: #2980b9;
        text-decoration: none;
        transition: color 0.3s;
    }

    .main-footer ul li a:hover {
        color: #1c6390;
    }

    .main-footer i {
        margin-right: 8px;
        color: #3498db;
    }

    .footer-bottom {
        text-align: center;
        margin-top: 2rem;
        font-size: 0.9rem;
        color: #777;
    }

    @media (max-width: 768px) {
        .main-footer .footer-content {
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
    }
</style>

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
