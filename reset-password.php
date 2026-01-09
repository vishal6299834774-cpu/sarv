<?php
require_once 'includes/functions.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Validate token
if (empty($token)) {
    $error = 'Invalid reset token';
} else {
    $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = 'Invalid or expired reset token';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Update password and clear token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $user['id']])) {
            $success = 'Password has been reset successfully. You can now login with your new password.';
            
            // Send confirmation email
            $stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $subject = 'Password Reset Confirmation - ' . get_setting('site_name');
            $message = "
                <h2>Password Reset Successful</h2>
                <p>Hello " . htmlspecialchars($user_info['name']) . ",</p>
                <p>Your password has been successfully reset for your account at " . get_setting('site_name') . ".</p>
                <p>If you didn't make this change, please contact our support team immediately.</p>
                <p>Best regards,<br>" . get_setting('site_name') . " Team</p>
            ";
            send_email($user_info['email'], $subject, $message);
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo get_setting('site_name'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <a href="index.php" class="logo">
                                <i class="fas fa-store fa-2x text-primary"></i>
                            </a>
                            <h2 class="mt-3">Reset Password</h2>
                            <p class="text-muted">Enter your new password</p>
                        </div>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                            <div class="text-center">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login Now
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if (empty($error)): ?>
                                <form method="POST" action="">
                                    <div class="form-group mb-3">
                                        <label for="password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>New Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password" name="password" 
                                                   minlength="6" required autofocus>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                                <i class="fas fa-eye" id="passwordToggle"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="confirm_password" class="form-label">
                                            <i class="fas fa-lock me-2"></i>Confirm New Password
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="confirm_password" 
                                                   name="confirm_password" minlength="6" required>
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                                <i class="fas fa-eye" id="confirmPasswordToggle"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Password Tips:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Use at least 6 characters</li>
                                                <li>Include uppercase and lowercase letters</li>
                                                <li>Add numbers for better security</li>
                                                <li>Consider using special characters</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 mb-3">
                                        <i class="fas fa-key me-2"></i>Reset Password
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                <a href="login.php">
                                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + 'Toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Auto-focus on password field
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            if (passwordField) {
                passwordField.focus();
            }
        });
    </script>
</body>
</html>
