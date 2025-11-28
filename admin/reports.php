<?php
/**
 * Admin - Reports & Analytics
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/../includes/init.php';
requireRole(ROLE_ADMIN);

$pdo = getDBConnection();
$stats = [];

if ($pdo) {
    // Total requests
    $stmt = executeQuery("SELECT COUNT(*) as total FROM service_requests");
    $stats['total_requests'] = $stmt ? $stmt->fetch()['total'] : 0;
    
    // Requests by status
    $stmt = executeQuery("SELECT status, COUNT(*) as count FROM service_requests GROUP BY status");
    $stats['by_status'] = $stmt ? $stmt->fetchAll() : [];
    
    // Requests by category
    $stmt = executeQuery("SELECT category, COUNT(*) as count FROM service_requests GROUP BY category ORDER BY count DESC");
    $stats['by_category'] = $stmt ? $stmt->fetchAll() : [];
    
    // Requests by priority
    $stmt = executeQuery("SELECT priority, COUNT(*) as count FROM service_requests GROUP BY priority");
    $stats['by_priority'] = $stmt ? $stmt->fetchAll() : [];
    
    // Recent activity
    $stmt = executeQuery("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 20");
    $stats['recent_activity'] = $stmt ? $stmt->fetchAll() : [];
    
    // Monthly requests
    $stmt = executeQuery("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count 
                         FROM service_requests 
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                         GROUP BY month ORDER BY month");
    $stats['monthly'] = $stmt ? $stmt->fetchAll() : [];
}

include __DIR__ . '/../templates/header.php';
?>

<div class="page-header d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title">Reports & Analytics</h1>
        <p class="page-subtitle">Monitor system performance and trends</p>
    </div>
    <div>
        <button class="btn btn-outline" onclick="window.print()">
            <i class="fas fa-print"></i> Print Report
        </button>
        <button class="btn btn-primary">
            <i class="fas fa-download"></i> Export CSV
        </button>
    </div>
</div>

<!-- Overview Stats -->
<div class="stats-grid mb-3">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-clipboard-list"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $stats['total_requests']; ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
    </div>
    <?php
    $pending = 0;
    $fulfilled = 0;
    foreach ($stats['by_status'] as $s) {
        if ($s['status'] === 'pending') $pending = $s['count'];
        if ($s['status'] === 'fulfilled') $fulfilled = $s['count'];
    }
    ?>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $pending; ?></div>
            <div class="stat-label">Pending</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $fulfilled; ?></div>
            <div class="stat-label">Fulfilled</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-percentage"></i></div>
        <div class="stat-info">
            <div class="stat-value">
                <?php echo $stats['total_requests'] > 0 ? round(($fulfilled / $stats['total_requests']) * 100) : 0; ?>%
            </div>
            <div class="stat-label">Completion Rate</div>
        </div>
    </div>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- Requests by Category -->
    <div class="card flex-1" style="min-width: 300px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-pie"></i> Requests by Category</h3>
        </div>
        <div class="card-body">
            <?php if (empty($stats['by_category'])): ?>
                <p style="color: var(--text-secondary);">No data available</p>
            <?php else: ?>
                <?php foreach ($stats['by_category'] as $cat): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-between mb-1">
                            <span>
                                <i class="<?php echo getCategoryIcon($cat['category']); ?>"></i>
                                <?php echo SERVICE_CATEGORIES[$cat['category']] ?? ucfirst($cat['category']); ?>
                            </span>
                            <span><?php echo $cat['count']; ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: <?php echo min(($cat['count'] / max($stats['total_requests'], 1)) * 100 * 3, 100); ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Requests by Status -->
    <div class="card flex-1" style="min-width: 300px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Requests by Status</h3>
        </div>
        <div class="card-body">
            <?php 
            $statusColors = [
                'pending' => 'warning',
                'in_review' => 'info',
                'in_progress' => 'primary',
                'fulfilled' => 'success',
                'closed' => 'secondary'
            ];
            foreach ($stats['by_status'] as $status): 
            ?>
                <div class="mb-2">
                    <div class="d-flex justify-between mb-1">
                        <span><?php echo ucwords(str_replace('_', ' ', $status['status'])); ?></span>
                        <span><?php echo $status['count']; ?></span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar <?php echo $statusColors[$status['status']] ?? ''; ?>" 
                             style="width: <?php echo min(($status['count'] / max($stats['total_requests'], 1)) * 100, 100); ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Requests by Priority -->
    <div class="card" style="width: 300px;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exclamation-circle"></i> By Priority</h3>
        </div>
        <div class="card-body">
            <?php 
            $priorityColors = ['low' => 'success', 'medium' => 'info', 'high' => 'warning', 'urgent' => 'danger'];
            foreach ($stats['by_priority'] as $priority): 
            ?>
                <div class="d-flex justify-between align-center mb-2">
                    <?php echo getPriorityBadge($priority['priority']); ?>
                    <span style="font-weight: 600;"><?php echo $priority['count']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card mt-3">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history"></i> Recent Activity</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Entity</th>
                        <th>IP Address</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['recent_activity'] as $activity): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($activity['action']); ?></td>
                            <td>
                                <?php 
                                if ($activity['user_id']) {
                                    $user = getUserById($activity['user_id']);
                                    echo $user ? htmlspecialchars(getFullName($user)) : 'Unknown';
                                } else {
                                    echo 'System';
                                }
                                ?>
                            </td>
                            <td><?php echo $activity['entity_type'] ? htmlspecialchars($activity['entity_type'] . ' #' . $activity['entity_id']) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($activity['ip_address'] ?? '-'); ?></td>
                            <td><?php echo timeAgo($activity['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
