<?php
/**
 * Dashboard Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();
$user = getUserById($userId);

// Get dashboard stats based on role
$stats = [];
$recentRequests = [];
$tasks = [];
$notifications = [];

// Fetch stats
$pdo = getDBConnection();
if ($pdo) {
    if ($userRole === ROLE_CLIENT) {
        // Client stats
        $stmt = executeQuery("SELECT COUNT(*) as total FROM service_requests WHERE client_id = ?", [$userId]);
        $stats['total_requests'] = $stmt ? $stmt->fetch()['total'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as pending FROM service_requests WHERE client_id = ? AND status IN ('pending', 'in_review')", [$userId]);
        $stats['pending_requests'] = $stmt ? $stmt->fetch()['pending'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as fulfilled FROM service_requests WHERE client_id = ? AND status = 'fulfilled'", [$userId]);
        $stats['fulfilled_requests'] = $stmt ? $stmt->fetch()['fulfilled'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as unread FROM messages WHERE receiver_id = ? AND is_read = FALSE", [$userId]);
        $stats['unread_messages'] = $stmt ? $stmt->fetch()['unread'] : 0;
        
        // Recent requests
        $stmt = executeQuery("SELECT * FROM service_requests WHERE client_id = ? ORDER BY created_at DESC LIMIT 5", [$userId]);
        $recentRequests = $stmt ? $stmt->fetchAll() : [];
        
    } elseif ($userRole === ROLE_HELPER) {
        // Helper stats
        $stmt = executeQuery("SELECT COUNT(*) as assigned FROM service_requests WHERE helper_id = ?", [$userId]);
        $stats['assigned_cases'] = $stmt ? $stmt->fetch()['assigned'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as active FROM service_requests WHERE helper_id = ? AND status IN ('in_review', 'in_progress')", [$userId]);
        $stats['active_cases'] = $stmt ? $stmt->fetch()['active'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as resolved FROM service_requests WHERE helper_id = ? AND status IN ('fulfilled', 'closed')", [$userId]);
        $stats['resolved_cases'] = $stmt ? $stmt->fetch()['resolved'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as urgent FROM service_requests WHERE helper_id = ? AND priority = 'urgent' AND status NOT IN ('fulfilled', 'closed')", [$userId]);
        $stats['urgent_cases'] = $stmt ? $stmt->fetch()['urgent'] : 0;
        
        // Recent assigned requests
        $stmt = executeQuery("SELECT sr.*, u.first_name, u.last_name FROM service_requests sr 
                            LEFT JOIN users u ON sr.client_id = u.id 
                            WHERE sr.helper_id = ? ORDER BY sr.created_at DESC LIMIT 5", [$userId]);
        $recentRequests = $stmt ? $stmt->fetchAll() : [];
        
    } else {
        // Admin stats
        $stmt = executeQuery("SELECT COUNT(*) as total FROM users WHERE role = 'client'");
        $stats['total_clients'] = $stmt ? $stmt->fetch()['total'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as total FROM users WHERE role = 'helper'");
        $stats['total_helpers'] = $stmt ? $stmt->fetch()['total'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as total FROM service_requests");
        $stats['total_requests'] = $stmt ? $stmt->fetch()['total'] : 0;
        
        $stmt = executeQuery("SELECT COUNT(*) as pending FROM service_requests WHERE status = 'pending'");
        $stats['pending_requests'] = $stmt ? $stmt->fetch()['pending'] : 0;
        
        // Recent requests
        $stmt = executeQuery("SELECT sr.*, u.first_name, u.last_name FROM service_requests sr 
                            LEFT JOIN users u ON sr.client_id = u.id 
                            ORDER BY sr.created_at DESC LIMIT 5");
        $recentRequests = $stmt ? $stmt->fetchAll() : [];
    }
    
    // Get tasks for all users
    $stmt = executeQuery("SELECT * FROM tasks WHERE user_id = ? AND is_completed = FALSE ORDER BY due_date ASC LIMIT 5", [$userId]);
    $tasks = $stmt ? $stmt->fetchAll() : [];
    
    // Get notifications
    $stmt = executeQuery("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$userId]);
    $notifications = $stmt ? $stmt->fetchAll() : [];
}

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</h1>
    <p class="page-subtitle">Here's what's happening with your <?php echo $userRole === ROLE_CLIENT ? 'requests' : 'cases'; ?> today.</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <?php if ($userRole === ROLE_CLIENT): ?>
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['total_requests']; ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['pending_requests']; ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['fulfilled_requests']; ?></div>
                <div class="stat-label">Fulfilled</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-envelope"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['unread_messages']; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>
    <?php elseif ($userRole === ROLE_HELPER): ?>
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['assigned_cases']; ?></div>
                <div class="stat-label">Assigned Cases</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-spinner"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['active_cases']; ?></div>
                <div class="stat-label">Active Cases</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-double"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['resolved_cases']; ?></div>
                <div class="stat-label">Resolved</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['urgent_cases']; ?></div>
                <div class="stat-label">Urgent Cases</div>
            </div>
        </div>
    <?php else: ?>
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['total_clients']; ?></div>
                <div class="stat-label">Total Clients</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-user-nurse"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['total_helpers']; ?></div>
                <div class="stat-label">Total Helpers</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['total_requests']; ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon danger"><i class="fas fa-hourglass-half"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?php echo $stats['pending_requests']; ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Quick Actions -->
<div class="quick-actions">
    <?php if ($userRole === ROLE_CLIENT): ?>
        <a href="requests.php?action=new" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-plus"></i></div>
            <div class="quick-action-title">Request Help</div>
            <div class="quick-action-desc">Submit a new request for assistance</div>
        </a>
        <a href="requests.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-list-alt"></i></div>
            <div class="quick-action-title">Check Status</div>
            <div class="quick-action-desc">View your request statuses</div>
        </a>
        <a href="messages.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-comments"></i></div>
            <div class="quick-action-title">Contact Advocate</div>
            <div class="quick-action-desc">Send a message to your helper</div>
        </a>
        <a href="resources.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-book"></i></div>
            <div class="quick-action-title">Resources</div>
            <div class="quick-action-desc">Browse helpful resources</div>
        </a>
    <?php elseif ($userRole === ROLE_HELPER): ?>
        <a href="requests.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-tasks"></i></div>
            <div class="quick-action-title">My Cases</div>
            <div class="quick-action-desc">View and manage assigned cases</div>
        </a>
        <a href="requests.php?status=pending" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-inbox"></i></div>
            <div class="quick-action-title">Pending Queue</div>
            <div class="quick-action-desc">Review pending requests</div>
        </a>
        <a href="messages.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-envelope"></i></div>
            <div class="quick-action-title">Messages</div>
            <div class="quick-action-desc">Communicate with clients</div>
        </a>
        <a href="resources.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-folder-open"></i></div>
            <div class="quick-action-title">Resources</div>
            <div class="quick-action-desc">Access and share resources</div>
        </a>
    <?php else: ?>
        <a href="admin/users.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-users-cog"></i></div>
            <div class="quick-action-title">Manage Users</div>
            <div class="quick-action-desc">View and manage all users</div>
        </a>
        <a href="requests.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-clipboard-check"></i></div>
            <div class="quick-action-title">All Requests</div>
            <div class="quick-action-desc">Monitor all service requests</div>
        </a>
        <a href="admin/reports.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-chart-bar"></i></div>
            <div class="quick-action-title">Reports</div>
            <div class="quick-action-desc">View analytics and reports</div>
        </a>
        <a href="admin/settings.php" class="quick-action-card">
            <div class="quick-action-icon"><i class="fas fa-cog"></i></div>
            <div class="quick-action-title">Settings</div>
            <div class="quick-action-desc">System configuration</div>
        </a>
    <?php endif; ?>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- Recent Requests -->
    <div class="card flex-1" style="min-width: 350px;">
        <div class="card-header">
            <h3 class="card-title">Recent <?php echo $userRole === ROLE_CLIENT ? 'Requests' : 'Cases'; ?></h3>
            <a href="requests.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentRequests)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-clipboard"></i></div>
                    <div class="empty-title">No requests yet</div>
                    <div class="empty-desc">
                        <?php if ($userRole === ROLE_CLIENT): ?>
                            Submit your first request to get started.
                        <?php else: ?>
                            No cases have been assigned to you yet.
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($recentRequests as $request): ?>
                    <div class="request-card priority-<?php echo $request['priority']; ?>" style="margin: 0; border-radius: 0; border-left-width: 3px;">
                        <div class="request-header">
                            <h4 class="request-title"><?php echo htmlspecialchars($request['title']); ?></h4>
                            <?php echo getStatusBadge($request['status']); ?>
                        </div>
                        <div class="request-meta">
                            <span class="request-category">
                                <i class="<?php echo getCategoryIcon($request['category']); ?>"></i>
                                <?php echo SERVICE_CATEGORIES[$request['category']] ?? ucfirst($request['category']); ?>
                            </span>
                            <span><i class="fas fa-clock"></i> <?php echo timeAgo($request['created_at']); ?></span>
                            <?php if (isset($request['first_name'])): ?>
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Tasks -->
    <div class="card" style="min-width: 300px; width: 350px;">
        <div class="card-header">
            <h3 class="card-title">My Tasks</h3>
            <a href="tasks.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tasks)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-tasks"></i></div>
                    <div class="empty-title">No tasks</div>
                    <div class="empty-desc">You're all caught up!</div>
                </div>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item">
                        <input type="checkbox" class="task-checkbox form-check-input" data-task-id="<?php echo $task['id']; ?>">
                        <div class="task-content">
                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <?php if ($task['due_date']): ?>
                                <div class="task-due <?php echo strtotime($task['due_date']) < time() ? 'overdue' : ''; ?>">
                                    <i class="fas fa-calendar"></i> <?php echo formatDate($task['due_date']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($userRole === ROLE_CLIENT): ?>
<!-- SOS Button -->
<button class="sos-button" data-modal="sos-modal" title="Emergency Help">
    SOS
</button>

<!-- SOS Modal -->
<div id="sos-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header" style="background: var(--danger-color); color: #fff;">
            <h3 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Emergency Help</h3>
            <button class="modal-close" style="color: #fff;"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p>If you are in immediate danger, please contact:</p>
            <div class="mt-2">
                <a href="tel:911" class="btn btn-danger w-100 mb-2">
                    <i class="fas fa-phone"></i> Call 911 (Emergency)
                </a>
                <a href="tel:988" class="btn btn-warning w-100 mb-2">
                    <i class="fas fa-heart"></i> Call 988 (Suicide & Crisis Lifeline)
                </a>
                <a href="tel:1-800-799-7233" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-shield-alt"></i> Domestic Violence Hotline
                </a>
            </div>
            <p class="mt-3 text-center" style="font-size: 0.9rem; color: var(--text-secondary);">
                Your safety is our priority. Help is available 24/7.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/templates/footer.php'; ?>
