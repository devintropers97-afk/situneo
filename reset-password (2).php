<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Reset Password Page
 * NIB: 20250-9261-4570-4515-5453
 * ========================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';

$errors = [];
$success = '';
$token_valid = false;

// Check if token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $current_time = time();
    
    // Find user with this token and valid expiry
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_expiry > ?");
    $stmt->bind_param("si", $token, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $token_valid = true;
        
        // Process reset password form
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validation
            if (empty($password)) {
                $errors['password'] = 'Password harus diisi';
            } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
                $errors['password'] = 'Password minimal ' . MIN_PASSWORD_LENGTH . ' karakter';
            }
            
            if ($password !== $confirm_password) {
                $errors['confirm_password'] = 'Konfirmasi password tidak cocok';
            }
            
            // If no errors, reset password
            if (empty($errors)) {
                $hashed_password = hashPassword($password);
                
                // Update user password and clear reset token
                $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user['id']);
                
                if ($stmt->execute()) {
                    $success = 'Password berhasil direset! Anda sekarang dapat masuk dengan password baru.';
                    
                    // Log activity
                    logActivity($user['id'], 'Password reset completed');
                    
                    // Redirect to login after 3 seconds
                    header('refresh:3;url=login.php?message=password_reset_success');
                } else {
                    $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
                }
            }
        }
    } else {
        $errors['general'] = 'Token reset tidak valid atau sudah kadaluarsa. Silakan minta link reset password baru.';
    }
} else {
    $errors['general'] = 'Token reset tidak ditemukan. Silakan minta link reset password baru.';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SITUNEO DIGITAL</title>
    <meta name="description" content="Reset password akun SITUNEO DIGITAL">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="https://situneo.my.id/logo">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #1E5C99;
            --dark-blue: #0F3057;
            --gold: #FFB400;
            --bright-gold: #FFD700;
            --white: #ffffff;
            --text-light: #e9ecef;
            --gradient-primary: linear-gradient(135deg, #1E5C99 0%, #0F3057 100%);
            --gradient-gold: linear-gradient(135deg, #FFD700 0%, #FFB400 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--dark-blue);
            color: var(--white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .auth-container {
            background: rgba(15, 48, 87, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 180, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }
        
        .auth-row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .auth-left {
            flex: 1;
            min-width: 300px;
            background: var(--gradient-primary);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .auth-right {
            flex: 1;
            min-width: 300px;
            padding: 40px;
        }
        
        .logo {
            margin-bottom: 30px;
        }
        
        .logo img {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        
        .auth-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--white);
            transition: all 0.3s;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--gold);
            box-shadow: 0 0 0 0.25rem rgba(255, 180, 0, 0.25);
            color: var(--white);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .btn-gold {
            background: var(--gradient-gold);
            color: var(--dark-blue);
            border: none;
            padding: 12px 30px;
            font-weight: 700;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
            box-shadow: 0 5px 15px rgba(255, 180, 0, 0.3);
        }
        
        .btn-gold:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 180, 0, 0.6);
            color: var(--dark-blue);
        }
        
        .auth-link {
            color: var(--gold);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .auth-link:hover {
            color: var(--bright-gold);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #f8d7da;
        }
        
        .alert-success {
            background: rgba(25, 135, 84, 0.2);
            border: 1px solid rgba(25, 135, 84, 0.3);
            color: #d1e7dd;
        }
        
        .invalid-feedback {
            color: #ff6b6b;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
            transition: all 0.3s;
        }
        
        .strength-weak {
            background: #dc3545;
            width: 33%;
        }
        
        .strength-medium {
            background: #ffc107;
            width: 66%;
        }
        
        .strength-strong {
            background: #28a745;
            width: 100%;
        }
        
        .input-group .btn-outline-secondary {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-left: none;
            color: var(--white);
            border-radius: 0 10px 10px 0;
        }
        
        .input-group .btn-outline-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: var(--gold);
        }
        
        .input-group .form-control {
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        @media (max-width: 768px) {
            .auth-left, .auth-right {
                padding: 30px 20px;
            }
            
            .auth-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-row">
            <div class="auth-left">
                <div class="logo">
                    <img src="https://situneo.my.id/logo" alt="Situneo">
                </div>
                <h2 class="auth-title">SITUNEO DIGITAL</h2>
                <p class="mb-4">Buat password baru yang kuat untuk melindungi akun Anda.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="auth-right">
                <h3 class="mb-4">Reset Password</h3>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                    </div>
                    <div class="text-center mt-4">
                        <p>Anda akan dialihkan ke halaman login dalam beberapa detik...</p>
                        <a href="login.php" class="btn btn-gold">Masuk Sekarang</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $errors['general'] ?>
                        </div>
                        <div class="text-center mt-4">
                            <a href="forgot-password.php" class="btn btn-gold">Minta Link Reset Baru</a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($token_valid): ?>
                        <form method="post" action="" id="resetPasswordForm">
                            <div class="form-group">
                                <label for="password" class="form-label">Password Baru</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password baru">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                                <?php if (!empty($errors['password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password baru">
                                <?php if (!empty($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-gold">Reset Password</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
        
        // Password strength checker
        document.getElementById('password')?.addEventListener('input', function() {
            const password = this.value;
            const strengthElement = document.getElementById('passwordStrength');
            
            let strength = 0;
            
            // Check password strength
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;
            
            // Update strength indicator
            strengthElement.className = 'password-strength';
            
            if (password.length > 0) {
                if (strength <= 2) {
                    strengthElement.classList.add('strength-weak');
                } else if (strength <= 4) {
                    strengthElement.classList.add('strength-medium');
                } else {
                    strengthElement.classList.add('strength-strong');
                }
            }
        });
        
        // Form validation
        document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }
        });
    </script>
</body>
</html>
