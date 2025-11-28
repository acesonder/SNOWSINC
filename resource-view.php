<?php
/**
 * Resource View Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$resourceId = (int)($_GET['id'] ?? 0);

if (!$resourceId) {
    redirect('resources.php');
}

$stmt = executeQuery("SELECT * FROM resources WHERE id = ? AND is_active = TRUE", [$resourceId]);
$resource = $stmt ? $stmt->fetch() : null;

if (!$resource) {
    setFlashMessage('danger', 'Resource not found.');
    redirect('resources.php');
}

// Get related resources
$stmt = executeQuery("SELECT * FROM resources WHERE category = ? AND id != ? AND is_active = TRUE LIMIT 3", 
                    [$resource['category'], $resourceId]);
$related = $stmt ? $stmt->fetchAll() : [];

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <nav style="font-size: 0.9rem; margin-bottom: 0.5rem;">
        <a href="resources.php" style="color: var(--text-secondary);">
            <i class="fas fa-arrow-left"></i> Back to Resources
        </a>
    </nav>
    <span class="badge badge-primary mb-2">
        <i class="<?php echo getCategoryIcon($resource['category']); ?>"></i>
        <?php echo SERVICE_CATEGORIES[$resource['category']] ?? ucfirst($resource['category']); ?>
    </span>
    <h1 class="page-title"><?php echo htmlspecialchars($resource['title']); ?></h1>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- Main Content -->
    <div class="flex-1" style="min-width: 400px;">
        <div class="card">
            <div class="card-body">
                <p style="font-size: 1.1rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                    <?php echo htmlspecialchars($resource['description']); ?>
                </p>
                
                <div style="white-space: pre-wrap; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($resource['content'])); ?>
                </div>
                
                <?php if ($resource['external_url']): ?>
                    <div class="mt-3 p-3" style="background: var(--bg-primary); border-radius: var(--border-radius);">
                        <h4><i class="fas fa-external-link-alt"></i> External Resource</h4>
                        <a href="<?php echo htmlspecialchars($resource['external_url']); ?>" target="_blank" rel="noopener" class="btn btn-primary mt-2">
                            Visit Website <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($resource['file_path']): ?>
                    <div class="mt-3 p-3" style="background: var(--bg-primary); border-radius: var(--border-radius);">
                        <h4><i class="fas fa-file-download"></i> Download</h4>
                        <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" class="btn btn-primary mt-2" download>
                            Download File <i class="fas fa-download"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer d-flex justify-between">
                <button class="btn btn-outline btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> Print
                </button>
                <span style="color: var(--text-secondary); font-size: 0.85rem;">
                    Last updated: <?php echo formatDate($resource['updated_at']); ?>
                </span>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div style="width: 300px;">
        <!-- Need Help? -->
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Need Help?</h3>
            </div>
            <div class="card-body">
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Would you like assistance with <?php echo strtolower(SERVICE_CATEGORIES[$resource['category']] ?? $resource['category']); ?>?
                </p>
                <a href="requests.php?action=new&category=<?php echo $resource['category']; ?>" class="btn btn-primary w-100">
                    <i class="fas fa-plus"></i> Request Help
                </a>
            </div>
        </div>
        
        <!-- Related Resources -->
        <?php if (!empty($related)): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Related Resources</h3>
            </div>
            <div class="card-body p-0">
                <?php foreach ($related as $rel): ?>
                    <a href="resource-view.php?id=<?php echo $rel['id']; ?>" class="message-card" style="text-decoration: none;">
                        <div class="stat-icon primary" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="<?php echo getCategoryIcon($rel['category']); ?>"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-sender"><?php echo htmlspecialchars($rel['title']); ?></div>
                            <div class="message-subject"><?php echo truncateText(htmlspecialchars($rel['description']), 60); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
