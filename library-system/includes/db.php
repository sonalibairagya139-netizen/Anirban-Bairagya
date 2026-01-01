<?php
$host = "sql105.infinityfree.com";       // Your InfinityFree MySQL Host
$user = "if0_39819977";          // Your Database Username
$pass = "your_password";          // Your Database Password
$dbname = "if0_39819977_library_db"; // Your Database Name

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
