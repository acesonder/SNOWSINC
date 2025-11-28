<?php
/**
 * Forgot Password Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email) || !isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $result = createPasswordResetToken($email);
        
        // Always show success to prevent email enumeration
        $success = 'If an account exists with this email, you will receive password reset instructions.';
        
        // In a real app, send email here
        // For demo purposes, we just show success message
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="auth-title">Reset Password</h1>
                <p class="auth-subtitle">We'll send you instructions to reset your password</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="alert-icon fas fa-exclamation-circle"></i>
                        <div class="alert-content"><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="alert-icon fas fa-check-circle"></i>
                        <div class="alert-content"><?php echo $success; ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>
                
                <div class="mt-3 text-center">
                    <p style="color: var(--text-secondary);">
                        <i class="fas fa-phone"></i> Need help? Call us at <strong>1-800-ONESINC</strong>
                    </p>
                </div>
            </div>
            
            <div class="auth-footer">
                <p>Remember your password? <a href="login.php" class="auth-link">Sign in</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/app.js"></script>
</body>
</html>
