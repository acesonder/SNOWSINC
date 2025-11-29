<?php
/**
 * Landing Page Admin Tool
 * OneSinc - Social Services Platform
 * 
 * Password protected admin console for editing landing page elements
 */

session_start();

// Admin password - hardcoded as per requirements
define('LANDING_ADMIN_PASSWORD', '079777');

// Configuration file path
define('LANDING_CONFIG_FILE', __DIR__ . '/config/landing.json');

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['landing_admin_authenticated']);
    header('Location: landingpageadmin.php');
    exit;
}

// Handle login
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === LANDING_ADMIN_PASSWORD) {
        $_SESSION['landing_admin_authenticated'] = true;
    } else {
        $loginError = 'Invalid password. Please try again.';
    }
}

// Check authentication
$isAuthenticated = isset($_SESSION['landing_admin_authenticated']) && $_SESSION['landing_admin_authenticated'] === true;

// Handle configuration save
$saveMessage = '';
$saveError = '';
if ($isAuthenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['config'])) {
    try {
        $newConfig = $_POST['config'];
        
        // Validate JSON
        $configArray = json_decode($newConfig, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON format: ' . json_last_error_msg());
        }
        
        // Create backup
        if (file_exists(LANDING_CONFIG_FILE)) {
            copy(LANDING_CONFIG_FILE, LANDING_CONFIG_FILE . '.backup');
        }
        
        // Save configuration
        $result = file_put_contents(LANDING_CONFIG_FILE, json_encode($configArray, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        
        if ($result === false) {
            throw new Exception('Failed to write configuration file');
        }
        
        $saveMessage = 'Configuration saved successfully!';
    } catch (Exception $e) {
        $saveError = 'Error saving configuration: ' . $e->getMessage();
    }
}

// Handle section toggle
if ($isAuthenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_section'])) {
    try {
        $section = $_POST['toggle_section'];
        $enabled = $_POST['enabled'] === 'true';
        
        if (!file_exists(LANDING_CONFIG_FILE)) {
            throw new Exception('Configuration file not found');
        }
        $config = json_decode(file_get_contents(LANDING_CONFIG_FILE), true);
        
        if (isset($config[$section])) {
            $config[$section]['enabled'] = $enabled;
            file_put_contents(LANDING_CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $saveMessage = ucfirst($section) . ' section ' . ($enabled ? 'enabled' : 'disabled') . ' successfully!';
        }
    } catch (Exception $e) {
        $saveError = 'Error toggling section: ' . $e->getMessage();
    }
}

// Handle quick update
if ($isAuthenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_update'])) {
    try {
        if (!file_exists(LANDING_CONFIG_FILE)) {
            throw new Exception('Configuration file not found');
        }
        $config = json_decode(file_get_contents(LANDING_CONFIG_FILE), true);
        
        // Update values from form
        $updates = $_POST['quick_update'];
        foreach ($updates as $path => $value) {
            $keys = explode('.', $path);
            $ref = &$config;
            foreach ($keys as $key) {
                if (!isset($ref[$key])) {
                    $ref[$key] = [];
                }
                $ref = &$ref[$key];
            }
            $ref = $value;
        }
        
        file_put_contents(LANDING_CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $saveMessage = 'Quick update saved successfully!';
    } catch (Exception $e) {
        $saveError = 'Error saving update: ' . $e->getMessage();
    }
}

// Load current configuration
$config = [];
if (file_exists(LANDING_CONFIG_FILE)) {
    $config = json_decode(file_get_contents(LANDING_CONFIG_FILE), true) ?: [];
}

// Helper function to get nested config value
function getConfigValue($config, $path, $default = '') {
    $keys = explode('.', $path);
    $value = $config;
    foreach ($keys as $key) {
        if (!isset($value[$key])) {
            return $default;
        }
        $value = $value[$key];
    }
    return $value;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page Admin - OneSinc</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --secondary: #1e3a5f;
            --accent: #f59e0b;
            --bg: #f8fafc;
            --bg-dark: #f1f5f9;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --white: #ffffff;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --radius: 12px;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        
        /* Login Page Styles */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .login-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }
        
        .login-header {
            background: var(--secondary);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text);
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        
        .btn-secondary {
            background: var(--text-light);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text);
        }
        
        .btn-outline:hover {
            background: var(--bg);
        }
        
        .btn-block {
            width: 100%;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--danger);
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-left: 4px solid var(--success);
        }
        
        /* Admin Dashboard Styles */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            width: 280px;
            background: var(--secondary);
            color: white;
            padding: 1.5rem 0;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 1.5rem;
        }
        
        .sidebar-header h2 {
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-header p {
            font-size: 0.85rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }
        
        .sidebar-nav {
            list-style: none;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: var(--primary);
        }
        
        .sidebar-nav a i {
            width: 20px;
        }
        
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }
        
        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .admin-header h1 {
            font-size: 1.75rem;
            color: var(--secondary);
        }
        
        .admin-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        /* Cards */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            font-size: 1.1rem;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--border);
            border-radius: 26px;
            transition: 0.3s;
        }
        
        .toggle-slider::before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background: white;
            border-radius: 50%;
            transition: 0.3s;
        }
        
        .toggle-switch input:checked + .toggle-slider {
            background: var(--success);
        }
        
        .toggle-switch input:checked + .toggle-slider::before {
            transform: translateX(24px);
        }
        
        /* Section Grid */
        .section-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .section-item {
            background: var(--bg);
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .section-name {
            font-weight: 600;
        }
        
        .section-status {
            font-size: 0.8rem;
            color: var(--text-light);
        }
        
        /* Editor */
        .editor-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--border);
        }
        
        .editor-tab {
            padding: 0.75rem 1.5rem;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-light);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s ease;
        }
        
        .editor-tab:hover {
            color: var(--text);
        }
        
        .editor-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .editor-panel {
            display: none;
        }
        
        .editor-panel.active {
            display: block;
        }
        
        .code-editor {
            width: 100%;
            min-height: 400px;
            padding: 1rem;
            font-family: 'Fira Code', 'Monaco', monospace;
            font-size: 0.9rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            resize: vertical;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        
        /* Quick Edit Form */
        .quick-edit-section {
            margin-bottom: 2rem;
        }
        
        .quick-edit-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }
        
        .quick-edit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .form-group label {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-group .char-count {
            font-size: 0.75rem;
            color: var(--text-light);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Preview Button */
        .preview-frame {
            width: 100%;
            height: 600px;
            border: 2px solid var(--border);
            border-radius: 8px;
        }
        
        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25em 0.75em;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 50px;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 70px;
                padding: 1rem 0;
            }
            
            .sidebar-header h2 span,
            .sidebar-header p,
            .sidebar-nav a span {
                display: none;
            }
            
            .sidebar-nav a {
                justify-content: center;
                padding: 1rem;
            }
            
            .sidebar-nav a i {
                width: auto;
                font-size: 1.25rem;
            }
            
            .admin-main {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                display: none;
            }
            
            .admin-main {
                margin-left: 0;
                padding: 1rem;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
            
            .section-grid,
            .quick-edit-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php if (!$isAuthenticated): ?>
    <!-- Login Form -->
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-lock"></i>
                <h1>Landing Page Admin</h1>
                <p>Enter password to access the admin console</p>
            </div>
            <div class="login-body">
                <?php if ($loginError): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($loginError); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="password">Admin Password</label>
                        <input type="password" id="password" name="admin_password" class="form-control" 
                               placeholder="Enter password" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Access Admin
                    </button>
                </form>
                
                <p style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-light);">
                    <a href="index.php" style="color: var(--primary);">← Back to Landing Page</a>
                </p>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Admin Dashboard -->
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-cog"></i> <span>Admin Console</span></h2>
                <p>Landing Page Editor</p>
            </div>
            
            <ul class="sidebar-nav">
                <li>
                    <a href="#sections" class="active">
                        <i class="fas fa-layer-group"></i>
                        <span>Sections</span>
                    </a>
                </li>
                <li>
                    <a href="#quick-edit">
                        <i class="fas fa-edit"></i>
                        <span>Quick Edit</span>
                    </a>
                </li>
                <li>
                    <a href="#advanced">
                        <i class="fas fa-code"></i>
                        <span>Advanced</span>
                    </a>
                </li>
                <li>
                    <a href="#preview">
                        <i class="fas fa-eye"></i>
                        <span>Preview</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <a href="?logout=1" class="btn btn-danger btn-block">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1><i class="fas fa-desktop"></i> Landing Page Admin</h1>
                <div class="admin-actions">
                    <a href="index.php" target="_blank" class="btn btn-outline">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                </div>
            </div>
            
            <?php if ($saveMessage): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($saveMessage); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($saveError): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($saveError); ?>
            </div>
            <?php endif; ?>
            
            <!-- Sections Panel -->
            <div class="card" id="sections">
                <div class="card-header">
                    <h3><i class="fas fa-layer-group"></i> Landing Page Sections</h3>
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 1rem; color: var(--text-light);">
                        Enable or disable sections of the landing page. Changes take effect immediately.
                    </p>
                    <div class="section-grid">
                        <?php
                        $sections = [
                            'navbar' => ['icon' => 'fas fa-bars', 'name' => 'Navigation Bar'],
                            'hero' => ['icon' => 'fas fa-image', 'name' => 'Hero Section'],
                            'quickHelp' => ['icon' => 'fas fa-hand-holding-heart', 'name' => 'Quick Help Widget'],
                            'howItWorks' => ['icon' => 'fas fa-list-ol', 'name' => 'How It Works'],
                            'services' => ['icon' => 'fas fa-th-large', 'name' => 'Services'],
                            'testimonials' => ['icon' => 'fas fa-quote-left', 'name' => 'Testimonials'],
                            'helpers' => ['icon' => 'fas fa-hands-helping', 'name' => 'For Helpers'],
                            'impact' => ['icon' => 'fas fa-chart-line', 'name' => 'Impact Stats'],
                            'donation' => ['icon' => 'fas fa-gift', 'name' => 'Donation'],
                            'resources' => ['icon' => 'fas fa-link', 'name' => 'Quick Resources'],
                            'footer' => ['icon' => 'fas fa-columns', 'name' => 'Footer'],
                        ];
                        
                        foreach ($sections as $key => $info):
                            $enabled = getConfigValue($config, $key . '.enabled', true);
                        ?>
                        <div class="section-item">
                            <div class="section-info">
                                <div class="section-icon">
                                    <i class="<?php echo $info['icon']; ?>"></i>
                                </div>
                                <div>
                                    <div class="section-name"><?php echo $info['name']; ?></div>
                                    <div class="section-status">
                                        <span class="badge <?php echo $enabled ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo $enabled ? 'Enabled' : 'Disabled'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="toggle_section" value="<?php echo $key; ?>">
                                <input type="hidden" name="enabled" value="<?php echo $enabled ? 'false' : 'true'; ?>">
                                <label class="toggle-switch">
                                    <input type="checkbox" <?php echo $enabled ? 'checked' : ''; ?> 
                                           onchange="this.form.submit()">
                                    <span class="toggle-slider"></span>
                                </label>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Edit Panel -->
            <div class="card" id="quick-edit">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> Quick Edit</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <!-- Hero Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-image"></i> Hero Section</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        Headline
                                        <span class="char-count">Max 60 chars</span>
                                    </label>
                                    <input type="text" name="quick_update[hero.headline]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'hero.headline', '')); ?>"
                                           maxlength="60">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">
                                        Subheadline
                                        <span class="char-count">Max 150 chars</span>
                                    </label>
                                    <textarea name="quick_update[hero.subheadline]" class="form-control" 
                                              maxlength="150"><?php echo htmlspecialchars(getConfigValue($config, 'hero.subheadline', '')); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Primary CTA Button</label>
                                    <input type="text" name="quick_update[hero.primaryCta]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'hero.primaryCta', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Secondary CTA Button</label>
                                    <input type="text" name="quick_update[hero.secondaryCta]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'hero.secondaryCta', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Help Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-hand-holding-heart"></i> Quick Help Section</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="quick_update[quickHelp.title]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'quickHelp.title', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- How It Works Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-list-ol"></i> How It Works Section</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="quick_update[howItWorks.title]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'howItWorks.title', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="quick_update[howItWorks.subtitle]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'howItWorks.subtitle', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Average Response Time</label>
                                    <input type="text" name="quick_update[howItWorks.avgResponseTime]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'howItWorks.avgResponseTime', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Services Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-th-large"></i> Services Section</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="quick_update[services.title]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'services.title', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="quick_update[services.subtitle]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'services.subtitle', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testimonials Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-quote-left"></i> Testimonials Section</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="quick_update[testimonials.title]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'testimonials.title', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="quick_update[testimonials.subtitle]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'testimonials.subtitle', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Impact Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-chart-line"></i> Impact Section</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="quick_update[impact.title]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'impact.title', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="quick_update[impact.subtitle]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'impact.subtitle', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Donation Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-gift"></i> Donation Section</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Section Title</label>
                                    <input type="text" name="quick_update[donation.title]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'donation.title', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" name="quick_update[donation.subtitle]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'donation.subtitle', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer Section -->
                        <div class="quick-edit-section">
                            <h4 class="quick-edit-title"><i class="fas fa-columns"></i> Footer</h4>
                            <div class="quick-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" name="quick_update[footer.contactEmail]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'footer.contactEmail', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="text" name="quick_update[footer.contactPhone]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'footer.contactPhone', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="quick_update[footer.address]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'footer.address', '')); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Copyright Text</label>
                                    <input type="text" name="quick_update[footer.copyright]" class="form-control" 
                                           value="<?php echo htmlspecialchars(getConfigValue($config, 'footer.copyright', '')); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div style="text-align: right; margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Advanced Editor Panel -->
            <div class="card" id="advanced">
                <div class="card-header">
                    <h3><i class="fas fa-code"></i> Advanced Configuration</h3>
                </div>
                <div class="card-body">
                    <p style="margin-bottom: 1rem; color: var(--text-light);">
                        <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                        Edit the raw JSON configuration. Be careful - invalid JSON will cause errors.
                    </p>
                    <form method="POST">
                        <textarea name="config" class="code-editor" spellcheck="false"><?php echo htmlspecialchars(json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></textarea>
                        <div style="text-align: right; margin-top: 1rem;">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Preview Panel -->
            <div class="card" id="preview">
                <div class="card-header">
                    <h3><i class="fas fa-eye"></i> Live Preview</h3>
                    <a href="index.php" target="_blank" class="btn btn-outline">
                        <i class="fas fa-external-link-alt"></i> Open in New Tab
                    </a>
                </div>
                <div class="card-body">
                    <iframe src="index.php" class="preview-frame" title="Landing Page Preview"></iframe>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Smooth scroll to sections
        document.querySelectorAll('.sidebar-nav a').forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href.startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        
                        // Update active state
                        document.querySelectorAll('.sidebar-nav a').forEach(l => l.classList.remove('active'));
                        link.classList.add('active');
                    }
                }
            });
        });
        
        // Auto-resize textarea
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });
        
        // Confirm before leaving with unsaved changes
        let formChanged = false;
        document.querySelectorAll('input, textarea').forEach(el => {
            el.addEventListener('change', () => formChanged = true);
        });
        
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
        
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', () => formChanged = false);
        });
    </script>
    <?php endif; ?>
</body>
</html>
