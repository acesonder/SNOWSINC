<?php
/**
 * Registration Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'role' => sanitize($_POST['role'] ?? ROLE_CLIENT)
    ];
    
    // Validation
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'First name is required';
    }
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Last name is required';
    }
    if (empty($data['email']) || !isValidEmail($data['email'])) {
        $errors['email'] = 'Valid email is required';
    }
    if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        $errors['password'] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
    }
    if ($data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    if (!in_array($data['role'], [ROLE_CLIENT, ROLE_HELPER])) {
        $data['role'] = ROLE_CLIENT;
    }
    
    if (empty($errors)) {
        $result = registerUser($data);
        
        if ($result['success']) {
            logActivity('registration', 'user', $result['user_id']);
            redirect('login.php?registered=1');
        } else {
            $error = $result['message'];
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
    <title>Register - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card" style="max-width: 520px;">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h1 class="auth-title">Join <?php echo APP_NAME; ?></h1>
                <p class="auth-subtitle">Create your account to get started</p>
            </div>
            
            <div class="auth-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="alert-icon fas fa-exclamation-circle"></i>
                        <div class="alert-content"><?php echo $error; ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php" data-validate>
                    <div class="form-group">
                        <label class="form-label required">I am a:</label>
                        <div class="d-flex gap-2">
                            <label class="form-check flex-1">
                                <input type="radio" name="role" value="client" class="form-check-input" 
                                       <?php echo (!isset($_POST['role']) || $_POST['role'] === 'client') ? 'checked' : ''; ?>>
                                <span class="form-check-label">
                                    <i class="fas fa-user"></i> Client
                                    <small style="display:block;color:var(--text-secondary);font-size:0.8rem;">I need help</small>
                                </span>
                            </label>
                            <label class="form-check flex-1">
                                <input type="radio" name="role" value="helper" class="form-check-input"
                                       <?php echo (isset($_POST['role']) && $_POST['role'] === 'helper') ? 'checked' : ''; ?>>
                                <span class="form-check-label">
                                    <i class="fas fa-user-nurse"></i> Helper
                                    <small style="display:block;color:var(--text-secondary);font-size:0.8rem;">I provide help</small>
                                </span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <div class="form-group flex-1">
                            <label class="form-label required" for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control <?php echo isset($errors['first_name']) ? 'error' : ''; ?>" 
                                   placeholder="First name" required
                                   value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                            <?php if (isset($errors['first_name'])): ?>
                                <div class="form-error"><?php echo $errors['first_name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group flex-1">
                            <label class="form-label required" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control <?php echo isset($errors['last_name']) ? 'error' : ''; ?>" 
                                   placeholder="Last name" required
                                   value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                            <?php if (isset($errors['last_name'])): ?>
                                <div class="form-error"><?php echo $errors['last_name']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="email">Email Address</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control <?php echo isset($errors['email']) ? 'error' : ''; ?>" 
                                   placeholder="Enter your email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <div class="form-error"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-phone"></i>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   placeholder="(Optional) Your phone number"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="password">Password</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control <?php echo isset($errors['password']) ? 'error' : ''; ?>" 
                                   placeholder="Create a password" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        </div>
                        <div class="form-help">At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="form-error"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required" for="confirm_password">Confirm Password</label>
                        <div class="input-group">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>" 
                                   placeholder="Confirm your password" required>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="form-error"><?php echo $errors['confirm_password']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="terms" class="form-check-input" required>
                            <span class="form-check-label">I agree to the <a href="#" class="auth-link">Terms of Service</a> and <a href="#" class="auth-link">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php" class="auth-link">Sign in</a></p>
            </div>
        </div>
    </div>
    
    <script src="js/app.js"></script>
</body>
</html>
