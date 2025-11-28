<?php
/**
 * Header Template
 * OneSinc - Social Services Platform
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$userRole = getCurrentUserRole();
$userName = $_SESSION['user_name'] ?? 'User';

// Get unread notification count
$unreadCount = 0;
$pdo = getDBConnection();
if ($pdo) {
    $stmt = executeQuery("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE", [getCurrentUserId()]);
    if ($stmt) {
        $unreadCount = $stmt->fetch()['count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
    <title><?php echo ucfirst($currentPage); ?> - <?php echo APP_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- OpenDyslexic font for accessibility -->
    <link href="https://fonts.cdnfonts.com/css/opendyslexic" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- 3D Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="navbar-brand">
            <div class="navbar-brand-icon">
                <i class="fas fa-hands-helping"></i>
            </div>
            <span><?php echo APP_NAME; ?></span>
        </a>
        
        <div class="navbar-menu">
            <a href="dashboard.php" class="navbar-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="requests.php" class="navbar-item <?php echo $currentPage === 'requests' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span><?php echo $userRole === ROLE_CLIENT ? 'My Requests' : 'Cases'; ?></span>
            </a>
            <a href="messages.php" class="navbar-item <?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                <span>Messages</span>
            </a>
            <a href="resources.php" class="navbar-item <?php echo $currentPage === 'resources' ? 'active' : ''; ?>">
                <i class="fas fa-book-open"></i>
                <span>Resources</span>
            </a>
            <?php if ($userRole === ROLE_ADMIN): ?>
                <a href="admin/users.php" class="navbar-item">
                    <i class="fas fa-users-cog"></i>
                    <span>Admin</span>
                </a>
            <?php endif; ?>
            
            <!-- Notifications -->
            <a href="notifications.php" class="navbar-item <?php echo $currentPage === 'notifications' ? 'active' : ''; ?>">
                <i class="fas fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="notification-badge"><?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?></span>
                <?php endif; ?>
            </a>
        </div>
        
        <!-- User Menu -->
        <div class="user-menu">
            <div class="user-avatar" data-dropdown="user-dropdown">
                <?php echo getInitials(splitFullName($userName)); ?>
            </div>
            <div id="user-dropdown" class="user-dropdown">
                <div class="user-dropdown-header">
                    <div class="user-dropdown-name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-dropdown-role"><?php echo htmlspecialchars($userRole); ?></div>
                </div>
                <a href="profile.php" class="user-dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="settings.php" class="user-dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="tasks.php" class="user-dropdown-item">
                    <i class="fas fa-tasks"></i> My Tasks
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="help.php" class="user-dropdown-item">
                    <i class="fas fa-question-circle"></i> Help & Support
                </a>
                <a href="logout.php" class="user-dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            </div>
        </div>
        
        <!-- Mobile menu toggle -->
        <button class="btn btn-icon" data-sidebar-toggle style="display: none; margin-left: auto; color: #fff;">
            <i class="fas fa-bars"></i>
        </button>
    </nav>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Main Menu</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="dashboard.php" class="sidebar-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="requests.php" class="sidebar-link <?php echo $currentPage === 'requests' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-list"></i> <?php echo $userRole === ROLE_CLIENT ? 'My Requests' : 'Cases'; ?>
                    </a>
                </li>
                <li>
                    <a href="messages.php" class="sidebar-link <?php echo $currentPage === 'messages' ? 'active' : ''; ?>">
                        <i class="fas fa-comments"></i> Messages
                    </a>
                </li>
                <li>
                    <a href="tasks.php" class="sidebar-link <?php echo $currentPage === 'tasks' ? 'active' : ''; ?>">
                        <i class="fas fa-tasks"></i> Tasks
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Resources</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="resources.php" class="sidebar-link <?php echo $currentPage === 'resources' ? 'active' : ''; ?>">
                        <i class="fas fa-book-open"></i> Resource Center
                    </a>
                </li>
                <li>
                    <a href="resources.php?category=food" class="sidebar-link">
                        <i class="fas fa-utensils"></i> Food Assistance
                    </a>
                </li>
                <li>
                    <a href="resources.php?category=housing" class="sidebar-link">
                        <i class="fas fa-home"></i> Housing
                    </a>
                </li>
                <li>
                    <a href="resources.php?category=health" class="sidebar-link">
                        <i class="fas fa-heartbeat"></i> Health Services
                    </a>
                </li>
                <li>
                    <a href="resources.php?category=employment" class="sidebar-link">
                        <i class="fas fa-briefcase"></i> Employment
                    </a>
                </li>
            </ul>
        </div>
        
        <?php if ($userRole === ROLE_ADMIN): ?>
        <div class="sidebar-section">
            <div class="sidebar-title">Administration</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="admin/users.php" class="sidebar-link">
                        <i class="fas fa-users-cog"></i> User Management
                    </a>
                </li>
                <li>
                    <a href="admin/reports.php" class="sidebar-link">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                <li>
                    <a href="admin/settings.php" class="sidebar-link">
                        <i class="fas fa-cog"></i> System Settings
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="sidebar-section">
            <div class="sidebar-title">Account</div>
            <ul class="sidebar-nav">
                <li>
                    <a href="profile.php" class="sidebar-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="sidebar-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-sliders-h"></i> Settings
                    </a>
                </li>
                <li>
                    <a href="help.php" class="sidebar-link">
                        <i class="fas fa-question-circle"></i> Help
                    </a>
                </li>
            </ul>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?> mb-3">
                <i class="alert-icon fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'danger' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                <div class="alert-content"><?php echo $flash['message']; ?></div>
            </div>
        <?php endif; ?>
