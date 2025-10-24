<?php
/**
 * ========================================
 * SITUNEO DIGITAL - Forgot Password Page
 * NIB: 20250-9261-4570-4515-5453
 * ========================================
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/functions.php';

$errors = [];
$success = '';

// Process forgot password form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $reset_token = generateToken();
            $reset_expiry = time() + 3600; // 1 hour from now
            
            // Update user with reset token
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
            $stmt->bind_param("sii", $reset_token, $reset_expiry, $user['id']);
            
            if ($stmt->execute()) {
                // Send reset email
                sendPasswordResetEmail($email, $reset_token);
                
                // Log activity
                logActivity($user['id'], 'Password reset requested');
                
                $success = 'Link reset password telah dikirim ke email Anda. Silakan periksa inbox/spam.';
            } else {
                $errors['general'] = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        } else {
            // Still show success message for security (don't reveal if email exists)
            $success = 'Jika email terdaftar, link reset password akan dikirim ke email Anda.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SITUNEO DIGITAL</title>
    <meta name="description" content="Reset password akun SITUNEO DIGITAL Anda">
    
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
                <p class="mb-4">Lupa password? Jangan khawatir! Kami akan mengirimkan link reset password ke email Anda.</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="text-white fs-4"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-white fs-4"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="auth-right">
                <h3 class="mb-4">Lupa Password</h3>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="login.php" class="btn btn-gold">Kembali ke Login</a>
                    </div>
                <?php else: ?>
                    <?php if (!empty($errors['general'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $errors['general'] ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email terdaftar" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                            <?php if (!empty($errors['email'])): ?>
                                <div class="invalid-feedback"><?= $errors['email'] ?></div>
                            <?php endif; ?>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i> Link reset password akan dikirim ke email ini
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-gold">Kirim Link Reset</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Ingat password Anda? <a href="login.php" class="auth-link">Masuk</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
