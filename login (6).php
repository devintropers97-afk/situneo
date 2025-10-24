<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Login Page
 * NIB: 20250-9261-4570-4515-5453
 * ========================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';

$errors = [];
$success = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password harus diisi';
    }
    
    // If no errors, attempt login
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Check if email is verified
                if ($user['email_verified'] == 0) {
                    $errors['general'] = 'Akun Anda belum diverifikasi. Silakan periksa email Anda atau <a href="resend_verification.php?email=' . urlencode($email) . '" class="alert-link">kirim ulang verifikasi</a>.';
                } else {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['login_time'] = time();
                    
                    // Set remember me cookie if checked
                    if ($remember) {
                        $remember_token = generateToken();
                        $expiry = time() + REMEMBER_LIFETIME;
                        
                        // Store token in database
                        $stmt = $conn->prepare("UPDATE users SET remember_token = ?, remember_expiry = ? WHERE id = ?");
                        $stmt->bind_param("sii", $remember_token, $expiry, $user['id']);
                        $stmt->execute();
                        
                        // Set cookie
                        setcookie('remember_token', $remember_token, $expiry, '/', '', true, true);
                    }
                    
                    // Log activity
                    logActivity($user['id'], 'User logged in');
                    
                    // Redirect to intended page or dashboard
                    if ($user['role'] >= ROLE_ADMIN) {
                        $redirect_url = '/admin/dashboard.php';
                    } else {
                        $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '/user/dashboard.php';
                    }
                    unset($_SESSION['redirect_url']);
                    
                    header('Location: ' . $redirect_url);
                    exit;
                }
            } else {
                $errors['general'] = 'Email atau password salah';
            }
        } else {
            $errors['general'] = 'Email atau password salah';
        }
    }
}

// Check for remember me cookie
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    $remember_token = $_COOKIE['remember_token'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE remember_token = ? AND remember_expiry > ?");
    $current_time = time();
    $stmt->bind_param("si", $remember_token, $current_time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Log activity
        logActivity($user['id'], 'User logged in via remember me');
        
        // Redirect to intended page or dashboard
        if ($user['role'] >= ROLE_ADMIN) {
            header('Location: /admin/dashboard.php');
        } else {
            $redirect_url = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '/user/dashboard.php';
            unset($_SESSION['redirect_url']);
            header('Location: ' . $redirect_url);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SITUNEO DIGITAL</title>
    <meta name="description" content="Masuk ke akun SITUNEO DIGITAL untuk mengakses layanan digital kami">
    
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
        
        .form-check-input:checked {
            background-color: var(--gold);
            border-color: var(--gold);
        }
        
        .social-login {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .social-btn {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
        }
        
        .social-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: var(--white);
        }
        
        .divider {
            position: relative;
            text-align: center;
            margin: 20px 0;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
        }
        
        .divider span {
            background: rgba(15, 48, 87, 0.8);
            padding: 0 15px;
            position: relative;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
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
                <p class="mb-4">Selamat datang kembali! Masuk ke akun Anda untuk mengakses layanan digital kami.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="auth-right">
                <h3 class="mb-4">Masuk</h3>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $errors['general'] ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="" id="loginForm">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        <?php if (!empty($errors['email'])): ?>
                            <div class="invalid-feedback"><?= $errors['email'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <?php if (!empty($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Ingat saya
                            </label>
                        </div>
                        <a href="forgot-password.php" class="auth-link">Lupa password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-gold">Masuk</button>
                </form>
                
                <div class="text-center mt-4">
                    <p>Belum punya akun? <a href="register.php" class="auth-link">Daftar</a></p>
                </div>
                
                <div class="divider my-4 text-center">
                    <span class="px-3">atau masuk dengan</span>
                </div>
                
                <div class="social-login">
                    <a href="#" class="social-btn">
                        <i class="bi bi-google"></i> Google
                    </a>
                    <a href="#" class="social-btn">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
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
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email) {
                e.preventDefault();
                document.getElementById('email').focus();
                return false;
            }
            
            if (!password) {
                e.preventDefault();
                document.getElementById('password').focus();
                return false;
            }
        });
    </script>
</body>
</html>
