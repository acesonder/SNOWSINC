<?php
/**
 * Settings Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();
$user = getUserById($userId);
$accessibilitySettings = $user['accessibility_settings'] ? json_decode($user['accessibility_settings'], true) : [];

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accessibility = [
        'high_contrast' => isset($_POST['high_contrast']),
        'large_text' => isset($_POST['large_text']),
        'dyslexia_font' => isset($_POST['dyslexia_font']),
        'reduced_motion' => isset($_POST['reduced_motion'])
    ];
    
    $result = updateUserProfile($userId, ['accessibility_settings' => $accessibility]);
    if ($result) {
        logActivity('update_settings', 'user', $userId);
        setFlashMessage('success', 'Settings saved successfully!');
    } else {
        setFlashMessage('danger', 'Failed to save settings.');
    }
    redirect('settings.php');
}

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Settings</h1>
    <p class="page-subtitle">Customize your experience</p>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- Main Settings -->
    <div class="flex-1" style="min-width: 400px;">
        <!-- Accessibility -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-universal-access"></i> Accessibility</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="high_contrast" class="form-check-input"
                                   data-toggle-accessibility="high-contrast"
                                   <?php echo ($accessibilitySettings['high_contrast'] ?? false) ? 'checked' : ''; ?>>
                            <span class="form-check-label">
                                <strong>High Contrast Mode</strong>
                                <small style="display: block; color: var(--text-secondary);">Increase contrast for better visibility</small>
                            </span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="large_text" class="form-check-input"
                                   data-toggle-accessibility="large-text"
                                   <?php echo ($accessibilitySettings['large_text'] ?? false) ? 'checked' : ''; ?>>
                            <span class="form-check-label">
                                <strong>Large Text</strong>
                                <small style="display: block; color: var(--text-secondary);">Increase text size throughout the app</small>
                            </span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="dyslexia_font" class="form-check-input"
                                   data-toggle-accessibility="dyslexia-font"
                                   <?php echo ($accessibilitySettings['dyslexia_font'] ?? false) ? 'checked' : ''; ?>>
                            <span class="form-check-label">
                                <strong>Dyslexia-Friendly Font</strong>
                                <small style="display: block; color: var(--text-secondary);">Use a font designed for easier reading</small>
                            </span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="reduced_motion" class="form-check-input"
                                   <?php echo ($accessibilitySettings['reduced_motion'] ?? false) ? 'checked' : ''; ?>>
                            <span class="form-check-label">
                                <strong>Reduce Motion</strong>
                                <small style="display: block; color: var(--text-secondary);">Minimize animations and transitions</small>
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Accessibility Settings
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bell"></i> Notifications</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" checked>
                        <span class="form-check-label">
                            <strong>Request Updates</strong>
                            <small style="display: block; color: var(--text-secondary);">Get notified when your requests are updated</small>
                        </span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" checked>
                        <span class="form-check-label">
                            <strong>New Messages</strong>
                            <small style="display: block; color: var(--text-secondary);">Get notified when you receive new messages</small>
                        </span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" checked>
                        <span class="form-check-label">
                            <strong>Task Reminders</strong>
                            <small style="display: block; color: var(--text-secondary);">Get reminded about upcoming tasks</small>
                        </span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input">
                        <span class="form-check-label">
                            <strong>Email Notifications</strong>
                            <small style="display: block; color: var(--text-secondary);">Receive notifications via email</small>
                        </span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Privacy -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Privacy & Data</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="alert-icon fas fa-info-circle"></i>
                    <div class="alert-content">
                        <div class="alert-title">Your Privacy Matters</div>
                        Your data is protected and only shared with authorized helpers assigned to your case.
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" checked disabled>
                        <span class="form-check-label">
                            <strong>Share with Assigned Helper</strong>
                            <small style="display: block; color: var(--text-secondary);">Required for case management</small>
                        </span>
                    </label>
                </div>
                
                <div class="mt-3 pt-3" style="border-top: 1px solid var(--border-color);">
                    <button class="btn btn-outline btn-sm">
                        <i class="fas fa-download"></i> Download My Data
                    </button>
                    <button class="btn btn-outline btn-sm text-danger" style="border-color: var(--danger-color); color: var(--danger-color);">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div style="width: 300px;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Links</h3>
            </div>
            <div class="card-body p-0">
                <a href="profile.php" class="sidebar-link">
                    <i class="fas fa-user"></i> Edit Profile
                </a>
                <a href="help.php" class="sidebar-link">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
                <a href="feedback.php" class="sidebar-link">
                    <i class="fas fa-comment-dots"></i> Send Feedback
                </a>
                <a href="logout.php" class="sidebar-link" style="color: var(--danger-color);">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
