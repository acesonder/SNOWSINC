<?php
/**
 * Request View Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();
$requestId = (int)($_GET['id'] ?? 0);

if (!$requestId) {
    redirect('requests.php');
}

// Fetch request
$sql = "SELECT sr.*, 
        c.first_name as client_first, c.last_name as client_last, c.email as client_email,
        h.first_name as helper_first, h.last_name as helper_last
        FROM service_requests sr 
        LEFT JOIN users c ON sr.client_id = c.id 
        LEFT JOIN users h ON sr.helper_id = h.id
        WHERE sr.id = ?";
$stmt = executeQuery($sql, [$requestId]);
$request = $stmt ? $stmt->fetch() : null;

if (!$request) {
    setFlashMessage('danger', 'Request not found.');
    redirect('requests.php');
}

// Check permission
if ($userRole === ROLE_CLIENT && $request['client_id'] !== $userId) {
    setFlashMessage('danger', 'You do not have permission to view this request.');
    redirect('requests.php');
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && $userRole !== ROLE_CLIENT) {
        $newStatus = sanitize($_POST['status']);
        executeQuery("UPDATE service_requests SET status = ? WHERE id = ?", [$newStatus, $requestId]);
        logActivity('update_request_status', 'service_request', $requestId, ['status' => $newStatus]);
        setFlashMessage('success', 'Request status updated.');
        redirect("request-view.php?id=$requestId");
    }
    
    if ($_POST['action'] === 'assign' && $userRole === ROLE_HELPER) {
        executeQuery("UPDATE service_requests SET helper_id = ?, status = 'in_review' WHERE id = ?", [$userId, $requestId]);
        logActivity('assign_request', 'service_request', $requestId);
        setFlashMessage('success', 'You have been assigned to this request.');
        redirect("request-view.php?id=$requestId");
    }
    
    if ($_POST['action'] === 'add_update') {
        $content = sanitize($_POST['content']);
        $isInternal = isset($_POST['is_internal']) && $userRole !== ROLE_CLIENT;
        
        if ($content) {
            executeQuery("INSERT INTO request_updates (request_id, user_id, content, is_internal) VALUES (?, ?, ?, ?)",
                [$requestId, $userId, $content, $isInternal]);
            setFlashMessage('success', 'Update added.');
            redirect("request-view.php?id=$requestId");
        }
    }
}

// Handle assign action from URL
if (isset($_GET['action']) && $_GET['action'] === 'assign' && $userRole === ROLE_HELPER) {
    executeQuery("UPDATE service_requests SET helper_id = ?, status = 'in_review' WHERE id = ?", [$userId, $requestId]);
    logActivity('assign_request', 'service_request', $requestId);
    setFlashMessage('success', 'You have been assigned to this request.');
    redirect("request-view.php?id=$requestId");
}

// Fetch updates
$stmt = executeQuery("SELECT ru.*, u.first_name, u.last_name, u.role 
                     FROM request_updates ru 
                     LEFT JOIN users u ON ru.user_id = u.id 
                     WHERE ru.request_id = ? 
                     ORDER BY ru.created_at DESC", [$requestId]);
$updates = $stmt ? $stmt->fetchAll() : [];

// Filter internal updates for clients
if ($userRole === ROLE_CLIENT) {
    $updates = array_filter($updates, function($u) { return !$u['is_internal']; });
}

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <nav style="font-size: 0.9rem; margin-bottom: 0.5rem;">
        <a href="requests.php" style="color: var(--text-secondary);">
            <i class="fas fa-arrow-left"></i> Back to Requests
        </a>
    </nav>
    <h1 class="page-title"><?php echo htmlspecialchars($request['title']); ?></h1>
    <div class="d-flex gap-1 mt-1">
        <?php echo getStatusBadge($request['status']); ?>
        <?php echo getPriorityBadge($request['priority']); ?>
        <span class="badge badge-secondary">
            <i class="<?php echo getCategoryIcon($request['category']); ?>"></i>
            <?php echo SERVICE_CATEGORIES[$request['category']] ?? ucfirst($request['category']); ?>
        </span>
    </div>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- Main Content -->
    <div class="flex-1" style="min-width: 400px;">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Request Details</h3>
                <span style="color: var(--text-secondary); font-size: 0.9rem;">
                    <i class="fas fa-calendar"></i> <?php echo formatDateTime($request['created_at']); ?>
                </span>
            </div>
            <div class="card-body">
                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($request['description']); ?></p>
            </div>
        </div>
        
        <!-- Updates -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Updates & Comments</h3>
            </div>
            <div class="card-body">
                <!-- Add Update Form -->
                <form method="POST" class="mb-3">
                    <input type="hidden" name="action" value="add_update">
                    <div class="form-group mb-2">
                        <textarea name="content" class="form-control" rows="3" placeholder="Add an update or comment..." required></textarea>
                    </div>
                    <div class="d-flex justify-between align-center">
                        <?php if ($userRole !== ROLE_CLIENT): ?>
                            <label class="form-check">
                                <input type="checkbox" name="is_internal" class="form-check-input">
                                <span class="form-check-label">Internal note (not visible to client)</span>
                            </label>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-comment"></i> Add Update
                        </button>
                    </div>
                </form>
                
                <?php if (empty($updates)): ?>
                    <div class="empty-state" style="padding: 2rem;">
                        <div class="empty-icon" style="width: 60px; height: 60px; font-size: 1.5rem;"><i class="fas fa-comments"></i></div>
                        <div class="empty-title">No updates yet</div>
                        <div class="empty-desc">Updates and comments will appear here.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($updates as $update): ?>
                        <div class="message-card <?php echo $update['is_internal'] ? 'unread' : ''; ?>" style="margin: 0 -1.5rem; padding: 1rem 1.5rem;">
                            <div class="avatar avatar-sm">
                                <?php echo getInitials(['first_name' => $update['first_name'], 'last_name' => $update['last_name']]); ?>
                            </div>
                            <div class="message-content">
                                <div class="message-header">
                                    <span class="message-sender">
                                        <?php echo htmlspecialchars($update['first_name'] . ' ' . $update['last_name']); ?>
                                        <span class="badge badge-secondary" style="font-size: 0.7rem;"><?php echo ucfirst($update['role']); ?></span>
                                        <?php if ($update['is_internal']): ?>
                                            <span class="badge badge-warning" style="font-size: 0.7rem;">Internal</span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="message-time"><?php echo timeAgo($update['created_at']); ?></span>
                                </div>
                                <p style="margin: 0.5rem 0 0; white-space: pre-wrap;"><?php echo htmlspecialchars($update['content']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div style="width: 300px;">
        <!-- Status Actions -->
        <?php if ($userRole !== ROLE_CLIENT): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                <?php if (!$request['helper_id']): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="assign">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-hand-paper"></i> Take This Case
                        </button>
                    </form>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="action" value="update_status">
                    <div class="form-group mb-2">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-control">
                            <option value="pending" <?php echo $request['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_review" <?php echo $request['status'] === 'in_review' ? 'selected' : ''; ?>>In Review</option>
                            <option value="in_progress" <?php echo $request['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="fulfilled" <?php echo $request['status'] === 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
                            <option value="closed" <?php echo $request['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Client Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Client</h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-center gap-2 mb-2">
                    <div class="avatar">
                        <?php echo getInitials(['first_name' => $request['client_first'], 'last_name' => $request['client_last']]); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;">
                            <?php echo htmlspecialchars($request['client_first'] . ' ' . $request['client_last']); ?>
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">
                            <?php echo htmlspecialchars($request['client_email']); ?>
                        </div>
                    </div>
                </div>
                <?php if ($userRole !== ROLE_CLIENT): ?>
                    <a href="messages.php?to=<?php echo $request['client_id']; ?>" class="btn btn-outline btn-sm w-100">
                        <i class="fas fa-envelope"></i> Send Message
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Assigned Helper -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Assigned Helper</h3>
            </div>
            <div class="card-body">
                <?php if ($request['helper_id']): ?>
                    <div class="d-flex align-center gap-2 mb-2">
                        <div class="avatar">
                            <?php echo getInitials(['first_name' => $request['helper_first'], 'last_name' => $request['helper_last']]); ?>
                        </div>
                        <div>
                            <div style="font-weight: 600;">
                                <?php echo htmlspecialchars($request['helper_first'] . ' ' . $request['helper_last']); ?>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">Case Worker</div>
                        </div>
                    </div>
                    <?php if ($userRole === ROLE_CLIENT): ?>
                        <a href="messages.php?to=<?php echo $request['helper_id']; ?>" class="btn btn-outline btn-sm w-100">
                            <i class="fas fa-envelope"></i> Contact Helper
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center" style="color: var(--text-secondary);">
                        <i class="fas fa-user-clock" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                        <p>Not yet assigned</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
