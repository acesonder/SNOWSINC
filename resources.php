<?php
/**
 * Resources Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Fetch resources
$sql = "SELECT * FROM resources WHERE is_active = TRUE";
$params = [];

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if ($search) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR content LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

$sql .= " ORDER BY is_featured DESC, created_at DESC";
$stmt = executeQuery($sql, $params);
$resources = $stmt ? $stmt->fetchAll() : [];

// Group featured resources
$featured = array_filter($resources, function($r) { return $r['is_featured']; });
$regular = array_filter($resources, function($r) { return !$r['is_featured']; });

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Resource Center</h1>
    <p class="page-subtitle">Find helpful resources and information to support you</p>
</div>

<!-- Search and Filter -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2" style="flex-wrap: wrap;">
            <div class="input-group flex-1" style="min-width: 250px;">
                <i class="input-icon fas fa-search"></i>
                <input type="text" name="search" class="form-control" placeholder="Search resources..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <select name="category" class="form-control" style="width: auto;">
                <option value="">All Categories</option>
                <?php foreach (SERVICE_CATEGORIES as $key => $label): ?>
                    <option value="<?php echo $key; ?>" <?php echo $category === $key ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
            <?php if ($search || $category): ?>
                <a href="resources.php" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Category Quick Links -->
<div class="quick-actions mb-3" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
    <?php 
    $categoryIcons = [
        'food' => 'fas fa-utensils',
        'housing' => 'fas fa-home',
        'legal' => 'fas fa-gavel',
        'health' => 'fas fa-heartbeat',
        'mental_health' => 'fas fa-brain',
        'employment' => 'fas fa-briefcase'
    ];
    foreach (array_slice(SERVICE_CATEGORIES, 0, 6) as $key => $label): 
    ?>
        <a href="resources.php?category=<?php echo $key; ?>" 
           class="quick-action-card <?php echo $category === $key ? 'active' : ''; ?>"
           style="padding: 1rem; <?php echo $category === $key ? 'border-color: var(--primary-color);' : ''; ?>">
            <div class="quick-action-icon" style="width: 48px; height: 48px; font-size: 1.25rem; margin-bottom: 0.5rem;">
                <i class="<?php echo $categoryIcons[$key] ?? 'fas fa-folder'; ?>"></i>
            </div>
            <div class="quick-action-title" style="font-size: 0.9rem;"><?php echo $label; ?></div>
        </a>
    <?php endforeach; ?>
</div>

<?php if (!empty($featured)): ?>
<!-- Featured Resources -->
<h2 class="mb-2"><i class="fas fa-star text-warning"></i> Featured Resources</h2>
<div class="d-flex gap-2 mb-3" style="flex-wrap: wrap;">
    <?php foreach ($featured as $resource): ?>
        <div class="resource-card" style="flex: 1; min-width: 280px; max-width: 400px;">
            <div class="resource-image">
                <i class="<?php echo getCategoryIcon($resource['category']); ?>"></i>
            </div>
            <div class="resource-body">
                <div class="resource-category"><?php echo SERVICE_CATEGORIES[$resource['category']] ?? ucfirst($resource['category']); ?></div>
                <h3 class="resource-title"><?php echo htmlspecialchars($resource['title']); ?></h3>
                <p class="resource-desc"><?php echo truncateText(htmlspecialchars($resource['description']), 100); ?></p>
                <a href="resource-view.php?id=<?php echo $resource['id']; ?>" class="btn btn-outline btn-sm">
                    Learn More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- All Resources -->
<h2 class="mb-2"><?php echo $category ? SERVICE_CATEGORIES[$category] : 'All Resources'; ?></h2>

<?php if (empty($resources)): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-book"></i></div>
                <div class="empty-title">No resources found</div>
                <div class="empty-desc">
                    <?php if ($search): ?>
                        No resources match your search. Try different keywords.
                    <?php else: ?>
                        No resources available in this category yet.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <?php foreach ($regular as $resource): ?>
                <div class="request-card" style="margin: 0; border-radius: 0; display: flex; gap: 1rem; align-items: flex-start;">
                    <div class="stat-icon primary" style="width: 48px; height: 48px; flex-shrink: 0;">
                        <i class="<?php echo getCategoryIcon($resource['category']); ?>"></i>
                    </div>
                    <div class="flex-1">
                        <span class="badge badge-primary mb-1"><?php echo SERVICE_CATEGORIES[$resource['category']] ?? ucfirst($resource['category']); ?></span>
                        <h4 class="request-title" style="margin-bottom: 0.25rem;">
                            <a href="resource-view.php?id=<?php echo $resource['id']; ?>" style="color: inherit;">
                                <?php echo htmlspecialchars($resource['title']); ?>
                            </a>
                        </h4>
                        <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;">
                            <?php echo truncateText(htmlspecialchars($resource['description']), 200); ?>
                        </p>
                    </div>
                    <a href="resource-view.php?id=<?php echo $resource['id']; ?>" class="btn btn-outline btn-sm">
                        View <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/templates/footer.php'; ?>
