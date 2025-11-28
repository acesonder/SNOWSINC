<?php
/**
 * Notifications Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();

// Mark all as read
if (isset($_GET['mark_read'])) {
    executeQuery("UPDATE notifications SET is_read = TRUE WHERE user_id = ?", [$userId]);
    redirect('notifications.php');
}

// Fetch notifications
$stmt = executeQuery("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50", [$userId]);
$notifications = $stmt ? $stmt->fetchAll() : [];

include __DIR__ . '/templates/header.php';
?>

<div class="page-header d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Stay updated on your requests and messages</p>
    </div>
    <?php if (!empty($notifications)): ?>
        <a href="notifications.php?mark_read=1" class="btn btn-outline">
            <i class="fas fa-check-double"></i> Mark All as Read
        </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-bell"></i></div>
                <div class="empty-title">No notifications</div>
                <div class="empty-desc">You're all caught up! Check back later for updates.</div>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
                <div class="message-card <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
                    <div class="stat-icon <?php echo !$notif['is_read'] ? 'primary' : 'secondary'; ?>" 
                         style="width: 40px; height: 40px; font-size: 1rem;">
                        <?php 
                        $icons = [
                            'request_update' => 'fas fa-clipboard',
                            'message' => 'fas fa-envelope',
                            'task' => 'fas fa-tasks',
                            'system' => 'fas fa-info-circle'
                        ];
                        ?>
                        <i class="<?php echo $icons[$notif['type']] ?? 'fas fa-bell'; ?>"></i>
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender"><?php echo htmlspecialchars($notif['title']); ?></span>
                            <span class="message-time"><?php echo timeAgo($notif['created_at']); ?></span>
                        </div>
                        <div class="message-subject"><?php echo htmlspecialchars($notif['message']); ?></div>
                    </div>
                    <?php if ($notif['link']): ?>
                        <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="btn btn-sm btn-outline">
                            View
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
