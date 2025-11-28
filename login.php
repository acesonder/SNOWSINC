<?php
/**
 * Login Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $user = authenticateUser($email, $password);
        
        if ($user) {
            startUserSession($user);
            logActivity('login', 'user', $user['id']);
            redirect('dashboard.php');
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title>Login - <?php echo APP_NAME; ?></title>
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
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h1 class="auth-title"><?php echo APP_NAME; ?></h1>
                <p class="auth-subtitle">Welcome back! Helping you help others.</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="alert-icon fas fa-exclamation-circle"></i>
                        <div class="alert-content"><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">
                        <i class="alert-icon fas fa-check-circle"></i>
                        <div class="alert-content">Registration successful! Please login with your credentials.</div>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['reset'])): ?>
                    <div class="alert alert-success">
                        <i class="alert-icon fas fa-check-circle"></i>
                        <div class="alert-content">Password reset successful! Please login with your new password.</div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="login.php" data-validate>
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" 
                                   placeholder="Enter your email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Enter your password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-between align-center mb-3">
                        <label class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input">
                            <span class="form-check-label">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="auth-link">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php" class="auth-link">Create one</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/app.js"></script>
</body>
</html>
