<?php
/**
 * Profile Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();
$user = getUserById($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitize($_POST['first_name'] ?? ''),
        'last_name' => sanitize($_POST['last_name'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'language_preference' => sanitize($_POST['language_preference'] ?? 'en')
    ];
    
    if (empty($data['first_name']) || empty($data['last_name'])) {
        setFlashMessage('danger', 'First name and last name are required.');
    } else {
        $result = updateUserProfile($userId, $data);
        if ($result) {
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            logActivity('update_profile', 'user', $userId);
            setFlashMessage('success', 'Profile updated successfully!');
        } else {
            setFlashMessage('danger', 'Failed to update profile.');
        }
    }
    redirect('profile.php');
}

// Get additional profile info
$profile = null;
if ($userRole === ROLE_CLIENT) {
    $stmt = executeQuery("SELECT * FROM client_profiles WHERE user_id = ?", [$userId]);
    $profile = $stmt ? $stmt->fetch() : null;
} elseif ($userRole === ROLE_HELPER) {
    $stmt = executeQuery("SELECT * FROM helper_profiles WHERE user_id = ?", [$userId]);
    $profile = $stmt ? $stmt->fetch() : null;
}

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Manage your personal information</p>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- Profile Card -->
    <div style="width: 300px;">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar avatar-xl" style="margin: 0 auto 1rem;">
                    <?php echo getInitials($user); ?>
                </div>
                <h3><?php echo htmlspecialchars(getFullName($user)); ?></h3>
                <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($user['email']); ?></p>
                <span class="badge badge-primary"><?php echo ucfirst($userRole); ?></span>
                
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                    <div class="d-flex justify-between mb-2">
                        <span style="color: var(--text-secondary);">Member since</span>
                        <span><?php echo formatDate($user['created_at']); ?></span>
                    </div>
                    <div class="d-flex justify-between">
                        <span style="color: var(--text-secondary);">Last login</span>
                        <span><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Form -->
    <div class="flex-1" style="min-width: 400px;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Profile</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="d-flex gap-2">
                        <div class="form-group flex-1">
                            <label class="form-label required">First Name</label>
                            <input type="text" name="first_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['first_name']); ?>">
                        </div>
                        <div class="form-group flex-1">
                            <label class="form-label required">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required
                                   value="<?php echo htmlspecialchars($user['last_name']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <div class="form-help">Email cannot be changed. Contact support if needed.</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="Your phone number"
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Preferred Language</label>
                        <select name="language_preference" class="form-control">
                            <option value="en" <?php echo ($user['language_preference'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="es" <?php echo ($user['language_preference'] ?? '') === 'es' ? 'selected' : ''; ?>>Español</option>
                            <option value="fr" <?php echo ($user['language_preference'] ?? '') === 'fr' ? 'selected' : ''; ?>>Français</option>
                            <option value="zh" <?php echo ($user['language_preference'] ?? '') === 'zh' ? 'selected' : ''; ?>>中文</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title">Change Password</h3>
            </div>
            <div class="card-body">
                <form action="api/change-password.php" method="POST" data-ajax>
                    <div class="form-group">
                        <label class="form-label required">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">New Password</label>
                        <input type="password" name="new_password" class="form-control" required minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                        <div class="form-help">At least <?php echo PASSWORD_MIN_LENGTH; ?> characters</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
