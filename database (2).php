<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Database Connection
 * ========================================
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'nrrskfvk_usertester');
define('DB_PASS', 'Devin1922$');
define('DB_NAME', 'nrrskfvk_tester');
define('DB_CHARSET', 'utf8mb4');

// Create connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset(DB_CHARSET);

// Set timezone
$conn->query("SET time_zone = '+07:00'");
?>
