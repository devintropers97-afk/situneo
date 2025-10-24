<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Helper Functions
 * ========================================
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        global $conn;
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    $user = getCurrentUser();
    if ($user) {
        return $user['role'] >= $role;
    }
    return false;
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /auth/login.php');
        exit;
    }
}

/**
 * Redirect if user doesn't have required role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /user/dashboard.php?error=unauthorized');
        exit;
    }
}


/**
 * Log user activity
 */
function logActivity($user_id, $action, $description = null) {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $user_id, $action, $description, $ip_address, $user_agent);
    $stmt->execute();
}

/**
 * Get user orders
 */
function getUserOrders($user_id, $limit = 10) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT o.*, s.name as service_name FROM orders o LEFT JOIN services s ON o.service_id = s.id WHERE o.user_id = ? ORDER BY o.created_at DESC LIMIT ?");
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    return $orders;
}

/**
 * Format currency
 */
function formatRupiah($amount) {
    return "Rp " . number_format($amount, 0, ',', '.');
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Create slug from string
 */
function createSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}
/**
 * Send Verification Email (NEW - SMTP VERSION)
 */
function sendVerificationEmail($email, $token) {
    $verificationLink = APP_URL . "/auth/verify-email.php?token=" . $token;
    
    $subject = "Verifikasi Email - " . APP_NAME;
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #1E5C99 0%, #0F3057 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1>SITUNEO DIGITAL</h1>
            <p>Digital Harmony for a Modern World</p>
        </div>
        
        <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
            <h2 style='color: #1E5C99;'>Verifikasi Email Anda</h2>
            <p>Terima kasih telah mendaftar di SITUNEO DIGITAL!</p>
            <p>Klik tombol di bawah untuk verifikasi email Anda:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$verificationLink}' style='background: linear-gradient(135deg, #FFD700 0%, #FFB400 100%); color: #0F3057; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;'>VERIFIKASI EMAIL</a>
            </div>
            
            <p>Atau salin link berikut:</p>
            <p style='background: white; padding: 15px; border: 1px solid #ddd; word-break: break-all;'>{$verificationLink}</p>
            
            <p><strong>Link ini akan kadaluarsa dalam 24 jam.</strong></p>
            <p style='color: #999; font-size: 12px;'>Jika Anda tidak mendaftar, abaikan email ini.</p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailSMTP($email, $subject, $message);
}

/**
 * Send Password Reset Email (NEW - SMTP VERSION)
 */
function sendPasswordResetEmail($email, $token) {
    $resetLink = APP_URL . "/auth/reset-password.php?token=" . $token;
    
    $subject = "Reset Password - " . APP_NAME;
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
    </head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;'>
        <div style='background: linear-gradient(135deg, #1E5C99 0%, #0F3057 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1>SITUNEO DIGITAL</h1>
            <p>Digital Harmony for a Modern World</p>
        </div>
        
        <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;'>
            <h2 style='color: #1E5C99;'>Reset Password</h2>
            <p>Kami menerima permintaan untuk reset password akun Anda.</p>
            <p>Klik tombol di bawah untuk membuat password baru:</p>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' style='background: linear-gradient(135deg, #FFD700 0%, #FFB400 100%); color: #0F3057; padding: 15px 40px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;'>RESET PASSWORD</a>
            </div>
            
            <p>Atau salin link berikut:</p>
            <p style='background: white; padding: 15px; border: 1px solid #ddd; word-break: break-all;'>{$resetLink}</p>
            
            <p style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;'>
                <strong>⚠️ Penting:</strong><br>
                • Link ini kadaluarsa dalam 1 jam<br>
                • Jika tidak meminta reset, abaikan email ini<br>
                • Password tidak berubah sampai Anda klik link
            </p>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailSMTP($email, $subject, $message);
}

/**
 * Send Email via SMTP (HELPER FUNCTION)
 */
function sendEmailSMTP($to, $subject, $message) {
    // SMTP Configuration
    $smtp_host = 'mail.situneo.my.id';
    $smtp_port = 587;
    $smtp_username = 'noreply@situneo.my.id';
    $smtp_password = 'Devin1922$'; // Ganti dengan password email Anda dari cPanel
    $smtp_from_email = 'noreply@situneo.my.id';
    $smtp_from_name = 'SITUNEO DIGITAL';
    
    // Email Headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $smtp_from_name . " <" . $smtp_from_email . ">\r\n";
    $headers .= "Reply-To: " . $smtp_from_email . "\r\n";
    $headers .= "Return-Path: " . $smtp_from_email . "\r\n";
    
    // Set PHP mail settings untuk SMTP
    ini_set('SMTP', $smtp_host);
    ini_set('smtp_port', $smtp_port);
    
    // Coba kirim email
    $result = mail($to, $subject, $message, $headers);
    
    // Log untuk debugging
    if (!$result) {
        error_log("Email gagal dikirim ke: $to. Subject: $subject");
    } else {
        error_log("Email berhasil dikirim ke: $to. Subject: $subject");
    }
    
    return $result;
}
?>

