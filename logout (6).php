<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Logout Handler
 * NIB: 20250-9261-4570-4515-5453
 * ========================================
 */

// Start session
session_start();

// Include config files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';

// Log activity before destroying session
if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], 'User logged out');
}

// Destroy session
session_unset();
session_destroy();

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Redirect to login page with success message
header('Location: /auth/login.php?message=logout_success');
exit;
?>
