<?php
/**
 * Notifications API
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$userId = getCurrentUserId();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

switch ($action) {
    case 'list':
        $stmt = executeQuery("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20", [$userId]);
        $notifications = $stmt ? $stmt->fetchAll() : [];
        jsonResponse(['success' => true, 'notifications' => $notifications]);
        break;
        
    case 'count':
        $stmt = executeQuery("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = FALSE", [$userId]);
        $count = $stmt ? $stmt->fetch()['count'] : 0;
        jsonResponse(['success' => true, 'count' => $count]);
        break;
        
    case 'mark_read':
        $notifId = (int)($_POST['id'] ?? 0);
        if ($notifId) {
            executeQuery("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?", [$notifId, $userId]);
        } else {
            executeQuery("UPDATE notifications SET is_read = TRUE WHERE user_id = ?", [$userId]);
        }
        jsonResponse(['success' => true]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
