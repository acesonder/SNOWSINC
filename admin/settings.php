<?php
/**
 * Admin - System Settings
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/../includes/init.php';
requireRole(ROLE_ADMIN);

include __DIR__ . '/../templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">System Settings</h1>
    <p class="page-subtitle">Configure application settings</p>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- General Settings -->
    <div class="flex-1" style="min-width: 400px;">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog"></i> General Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Application Name</label>
                    <input type="text" class="form-control" value="<?php echo APP_NAME; ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Application URL</label>
                    <input type="text" class="form-control" value="<?php echo APP_URL; ?>" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Version</label>
                    <input type="text" class="form-control" value="<?php echo APP_VERSION; ?>" disabled>
                </div>
                <div class="alert alert-info">
                    <i class="alert-icon fas fa-info-circle"></i>
                    <div class="alert-content">
                        Settings are configured through environment variables. Contact your system administrator to make changes.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Email Settings -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-envelope"></i> Email Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" class="form-control" placeholder="smtp.example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" class="form-control" placeholder="587">
                </div>
                <div class="form-group">
                    <label class="form-label">From Email</label>
                    <input type="email" class="form-control" placeholder="noreply@onesinc.org">
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Email Settings
                </button>
            </div>
        </div>
        
        <!-- Security Settings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-shield-alt"></i> Security Settings</h3>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input" checked>
                        <span class="form-check-label">
                            <strong>Require Strong Passwords</strong>
                            <small style="display: block; color: var(--text-secondary);">Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                        </span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" class="form-check-input">
                        <span class="form-check-label">
                            <strong>Enable Two-Factor Authentication</strong>
                            <small style="display: block; color: var(--text-secondary);">Require 2FA for all admin accounts</small>
                        </span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Session Timeout (minutes)</label>
                    <input type="number" class="form-control" value="60" style="width: 150px;">
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Security Settings
                </button>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div style="width: 350px;">
        <!-- System Status -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-heartbeat"></i> System Status</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-between mb-2">
                    <span>Database</span>
                    <?php if (getDBConnection()): ?>
                        <span class="badge badge-success">Connected</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Disconnected</span>
                    <?php endif; ?>
                </div>
                <div class="d-flex justify-between mb-2">
                    <span>PHP Version</span>
                    <span><?php echo phpversion(); ?></span>
                </div>
                <div class="d-flex justify-between mb-2">
                    <span>Server</span>
                    <span><?php echo php_uname('s'); ?></span>
                </div>
                <div class="d-flex justify-between">
                    <span>Memory Usage</span>
                    <span><?php echo round(memory_get_usage() / 1024 / 1024, 2); ?> MB</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <button class="btn btn-outline w-100 mb-2">
                    <i class="fas fa-sync"></i> Clear Cache
                </button>
                <button class="btn btn-outline w-100 mb-2">
                    <i class="fas fa-database"></i> Backup Database
                </button>
                <button class="btn btn-outline w-100 mb-2">
                    <i class="fas fa-file-alt"></i> View Logs
                </button>
                <button class="btn btn-outline w-100">
                    <i class="fas fa-paper-plane"></i> Test Email
                </button>
            </div>
        </div>
        
        <!-- Resources Management -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book"></i> Resources</h3>
            </div>
            <div class="card-body">
                <a href="resources-manage.php" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-plus"></i> Add Resource
                </a>
                <a href="../resources.php" class="btn btn-outline w-100">
                    <i class="fas fa-eye"></i> View All Resources
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
