<?php
/**
 * Service Requests Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();
$action = $_GET['action'] ?? 'list';

// Handle new request form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'new') {
    $data = [
        'client_id' => $userId,
        'category' => sanitize($_POST['category'] ?? ''),
        'title' => sanitize($_POST['title'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'priority' => sanitize($_POST['priority'] ?? 'medium')
    ];
    
    if (empty($data['title']) || empty($data['description']) || empty($data['category'])) {
        setFlashMessage('danger', 'Please fill in all required fields.');
    } else {
        $sql = "INSERT INTO service_requests (client_id, category, title, description, priority) VALUES (?, ?, ?, ?, ?)";
        $result = executeQuery($sql, [$data['client_id'], $data['category'], $data['title'], $data['description'], $data['priority']]);
        
        if ($result) {
            logActivity('create_request', 'service_request', getDBConnection()->lastInsertId());
            setFlashMessage('success', 'Your request has been submitted successfully!');
            redirect('requests.php');
        } else {
            setFlashMessage('danger', 'Failed to submit request. Please try again.');
        }
    }
}

// Fetch requests
$requests = [];
$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$sql = "SELECT sr.*, u.first_name, u.last_name, u.email 
        FROM service_requests sr 
        LEFT JOIN users u ON sr.client_id = u.id 
        WHERE 1=1";
$params = [];

if ($userRole === ROLE_CLIENT) {
    $sql .= " AND sr.client_id = ?";
    $params[] = $userId;
} elseif ($userRole === ROLE_HELPER) {
    $sql .= " AND (sr.helper_id = ? OR sr.helper_id IS NULL)";
    $params[] = $userId;
}

if ($statusFilter) {
    $sql .= " AND sr.status = ?";
    $params[] = $statusFilter;
}

if ($categoryFilter) {
    $sql .= " AND sr.category = ?";
    $params[] = $categoryFilter;
}

$sql .= " ORDER BY sr.created_at DESC";

$stmt = executeQuery($sql, $params);
$requests = $stmt ? $stmt->fetchAll() : [];

include __DIR__ . '/templates/header.php';
?>

<?php if ($action === 'new'): ?>
<!-- New Request Form -->
<div class="page-header">
    <h1 class="page-title">Request Help</h1>
    <p class="page-subtitle">Tell us how we can help you today.</p>
</div>

<div class="card" style="max-width: 700px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> New Service Request</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="requests.php?action=new">
            <div class="form-group">
                <label class="form-label required" for="category">What do you need help with?</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select a category...</option>
                    <?php foreach (SERVICE_CATEGORIES as $key => $label): ?>
                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label required" for="title">Brief Title</label>
                <input type="text" id="title" name="title" class="form-control" 
                       placeholder="e.g., Need help finding emergency shelter" required maxlength="255">
                <div class="form-help">A short description of what you need</div>
            </div>
            
            <div class="form-group">
                <label class="form-label required" for="description">Tell us more</label>
                <textarea id="description" name="description" class="form-control" rows="5" 
                          placeholder="Please provide details about your situation and what kind of help you need..." required></textarea>
                <div class="form-help">The more details you provide, the better we can help you</div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="priority">How urgent is this?</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low">Low - Can wait a few days</option>
                    <option value="medium" selected>Medium - Need help this week</option>
                    <option value="high">High - Need help within 24-48 hours</option>
                    <option value="urgent">Urgent - Need immediate help</option>
                </select>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
                <a href="requests.php" class="btn btn-secondary btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- Request List -->
<div class="page-header d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title"><?php echo $userRole === ROLE_CLIENT ? 'My Requests' : 'Service Requests'; ?></h1>
        <p class="page-subtitle">
            <?php echo $userRole === ROLE_CLIENT ? 'Track the status of your assistance requests' : 'Manage and respond to service requests'; ?>
        </p>
    </div>
    <?php if ($userRole === ROLE_CLIENT): ?>
        <a href="requests.php?action=new" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i> New Request
        </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body d-flex gap-2" style="flex-wrap: wrap;">
        <select class="form-control" style="width: auto;" onchange="window.location.href='requests.php?status='+this.value">
            <option value="">All Statuses</option>
            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="in_review" <?php echo $statusFilter === 'in_review' ? 'selected' : ''; ?>>In Review</option>
            <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
            <option value="fulfilled" <?php echo $statusFilter === 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
            <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed</option>
        </select>
        <select class="form-control" style="width: auto;" onchange="window.location.href='requests.php?category='+this.value">
            <option value="">All Categories</option>
            <?php foreach (SERVICE_CATEGORIES as $key => $label): ?>
                <option value="<?php echo $key; ?>" <?php echo $categoryFilter === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<!-- Request List -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-clipboard"></i></div>
                <div class="empty-title">No requests found</div>
                <div class="empty-desc">
                    <?php if ($userRole === ROLE_CLIENT): ?>
                        You haven't submitted any requests yet.
                        <br><a href="requests.php?action=new" class="btn btn-primary mt-2">Submit Your First Request</a>
                    <?php else: ?>
                        No requests match your current filters.
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($requests as $request): ?>
                <div class="request-card priority-<?php echo $request['priority']; ?>" style="margin: 0; border-radius: 0;">
                    <div class="request-header">
                        <div>
                            <h4 class="request-title">
                                <a href="request-view.php?id=<?php echo $request['id']; ?>" style="color: inherit;">
                                    <?php echo htmlspecialchars($request['title']); ?>
                                </a>
                            </h4>
                            <div class="request-meta mt-1">
                                <span class="request-category">
                                    <i class="<?php echo getCategoryIcon($request['category']); ?>"></i>
                                    <?php echo SERVICE_CATEGORIES[$request['category']] ?? ucfirst($request['category']); ?>
                                </span>
                                <span><i class="fas fa-clock"></i> <?php echo timeAgo($request['created_at']); ?></span>
                                <?php if ($userRole !== ROLE_CLIENT && isset($request['first_name'])): ?>
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex gap-1 align-center">
                            <?php echo getPriorityBadge($request['priority']); ?>
                            <?php echo getStatusBadge($request['status']); ?>
                        </div>
                    </div>
                    <p style="color: var(--text-secondary); margin: 0.5rem 0 0; font-size: 0.95rem;">
                        <?php echo truncateText(htmlspecialchars($request['description']), 150); ?>
                    </p>
                    <div class="mt-2">
                        <a href="request-view.php?id=<?php echo $request['id']; ?>" class="btn btn-sm btn-outline">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <?php if ($userRole !== ROLE_CLIENT && $request['status'] === 'pending'): ?>
                            <a href="request-view.php?id=<?php echo $request['id']; ?>&action=assign" class="btn btn-sm btn-primary">
                                <i class="fas fa-hand-paper"></i> Take Case
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/templates/footer.php'; ?>
