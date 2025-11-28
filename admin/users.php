<?php
/**
 * Admin - User Management
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/../includes/init.php';
requireRole(ROLE_ADMIN);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_active' && $userId) {
        executeQuery("UPDATE users SET is_active = NOT is_active WHERE id = ?", [$userId]);
        setFlashMessage('success', 'User status updated.');
    } elseif ($action === 'change_role' && $userId) {
        $newRole = sanitize($_POST['role'] ?? '');
        if (in_array($newRole, [ROLE_CLIENT, ROLE_HELPER, ROLE_ADMIN])) {
            executeQuery("UPDATE users SET role = ? WHERE id = ?", [$newRole, $userId]);
            setFlashMessage('success', 'User role updated.');
        }
    }
    redirect('users.php');
}

// Fetch users
$roleFilter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($roleFilter) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

if ($search) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$sql .= " ORDER BY created_at DESC";
$stmt = executeQuery($sql, $params);
$users = $stmt ? $stmt->fetchAll() : [];

include __DIR__ . '/../templates/header.php';
?>

<div class="page-header d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="page-subtitle">Manage all system users</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2" style="flex-wrap: wrap;">
            <div class="input-group flex-1" style="min-width: 250px;">
                <i class="input-icon fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search users..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <select name="role" class="form-control" style="width: auto;">
                <option value="">All Roles</option>
                <option value="client" <?php echo $roleFilter === 'client' ? 'selected' : ''; ?>>Clients</option>
                <option value="helper" <?php echo $roleFilter === 'helper' ? 'selected' : ''; ?>>Helpers</option>
                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admins</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid mb-3">
    <?php
    $clientCount = count(array_filter($users, function($u) { return $u['role'] === 'client'; }));
    $helperCount = count(array_filter($users, function($u) { return $u['role'] === 'helper'; }));
    $adminCount = count(array_filter($users, function($u) { return $u['role'] === 'admin'; }));
    ?>
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo count($users); ?></div>
            <div class="stat-label">Total Users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-user"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $clientCount; ?></div>
            <div class="stat-label">Clients</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-user-nurse"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $helperCount; ?></div>
            <div class="stat-label">Helpers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger"><i class="fas fa-user-shield"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $adminCount; ?></div>
            <div class="stat-label">Admins</div>
        </div>
    </div>
</div>

<!-- User Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <div class="avatar avatar-sm">
                                        <?php echo getInitials($user); ?>
                                    </div>
                                    <span><?php echo htmlspecialchars(getFullName($user)); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" class="form-control" style="width: auto; padding: 0.25rem 0.5rem;" 
                                            onchange="this.form.submit()">
                                        <option value="client" <?php echo $user['role'] === 'client' ? 'selected' : ''; ?>>Client</option>
                                        <option value="helper" <?php echo $user['role'] === 'helper' ? 'selected' : ''; ?>>Helper</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td><?php echo $user['last_login'] ? formatDateTime($user['last_login']) : 'Never'; ?></td>
                            <td>
                                <div class="table-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline" 
                                                title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                        </button>
                                    </form>
                                    <a href="../messages.php?to=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline" title="Message">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
