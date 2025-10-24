<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Email Configuration
 * Using PHPMailer with cPanel SMTP
 * ========================================
 */

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ PATH SUDAH SESUAI DENGAN FOLDER ANDA
require __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

// SMTP Configuration
define('SMTP_HOST', 'mail.situneo.my.id'); // Atau tester.situneo.my.id
define('SMTP_PORT', 587); // Port TLS
define('SMTP_USERNAME', 'noreply@situneo.my.id');
define('SMTP_PASSWORD', 'Devin1922$'); // ⚠️ GANTI INI!
define('SMTP_FROM_EMAIL', 'noreply@situneo.my.id');
define('SMTP_FROM_NAME', 'SITUNEO DIGITAL');

/**
 * Send email using SMTP with PHPMailer
 */
function sendEmail($to, $subject, $message, $altMessage = '') {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Anti-spam settings
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = $altMessage ? $altMessage : strip_tags($message);
        
        // Send
        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log error
        error_log("PHPMailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
