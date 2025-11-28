<?php
/**
 * Tasks Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();

// Handle task actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $dueDate = $_POST['due_date'] ?? null;
        
        if ($title) {
            executeQuery("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)",
                        [$userId, $title, $description, $dueDate ?: null]);
            setFlashMessage('success', 'Task added!');
        }
    } elseif ($action === 'toggle') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        executeQuery("UPDATE tasks SET is_completed = NOT is_completed, 
                     completed_at = CASE WHEN is_completed = FALSE THEN NOW() ELSE NULL END 
                     WHERE id = ? AND user_id = ?", [$taskId, $userId]);
    } elseif ($action === 'delete') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        executeQuery("DELETE FROM tasks WHERE id = ? AND user_id = ?", [$taskId, $userId]);
        setFlashMessage('success', 'Task deleted.');
    }
    redirect('tasks.php');
}

// Fetch tasks
$stmt = executeQuery("SELECT * FROM tasks WHERE user_id = ? ORDER BY is_completed ASC, due_date ASC, created_at DESC", [$userId]);
$tasks = $stmt ? $stmt->fetchAll() : [];

$pendingTasks = array_filter($tasks, function($t) { return !$t['is_completed']; });
$completedTasks = array_filter($tasks, function($t) { return $t['is_completed']; });

include __DIR__ . '/templates/header.php';
?>

<div class="page-header d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 class="page-title">My Tasks</h1>
        <p class="page-subtitle">Keep track of your to-dos and goals</p>
    </div>
    <button class="btn btn-primary" data-modal="new-task-modal">
        <i class="fas fa-plus"></i> Add Task
    </button>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- Pending Tasks -->
    <div class="flex-1" style="min-width: 350px;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks"></i> Pending Tasks</h3>
                <span class="badge badge-primary"><?php echo count($pendingTasks); ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendingTasks)): ?>
                    <div class="empty-state" style="padding: 2rem;">
                        <div class="empty-icon" style="width: 60px; height: 60px;"><i class="fas fa-check-circle"></i></div>
                        <div class="empty-title">All caught up!</div>
                        <div class="empty-desc">You have no pending tasks.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingTasks as $task): ?>
                        <div class="task-item">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="task-checkbox form-check-input" style="cursor: pointer;"></button>
                            </form>
                            <div class="task-content">
                                <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                <?php if ($task['description']): ?>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary);">
                                        <?php echo htmlspecialchars($task['description']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($task['due_date']): ?>
                                    <?php 
                                    $isOverdue = strtotime($task['due_date']) < time();
                                    ?>
                                    <div class="task-due <?php echo $isOverdue ? 'overdue' : ''; ?>">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo $isOverdue ? 'Overdue: ' : 'Due: '; ?>
                                        <?php echo formatDate($task['due_date']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="btn btn-icon btn-sm" style="color: var(--text-light);" 
                                        onclick="return confirm('Delete this task?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Completed Tasks -->
    <div style="width: 350px;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-check-double"></i> Completed</h3>
                <span class="badge badge-success"><?php echo count($completedTasks); ?></span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($completedTasks)): ?>
                    <div class="empty-state" style="padding: 2rem;">
                        <div class="empty-icon" style="width: 60px; height: 60px;"><i class="fas fa-clipboard-list"></i></div>
                        <div class="empty-title">No completed tasks</div>
                        <div class="empty-desc">Completed tasks will appear here.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($completedTasks as $task): ?>
                        <div class="task-item completed">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="task-checkbox form-check-input" style="cursor: pointer;" checked></button>
                            </form>
                            <div class="task-content">
                                <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                <div class="task-due">
                                    <i class="fas fa-check"></i> Completed <?php echo timeAgo($task['completed_at']); ?>
                                </div>
                            </div>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" class="btn btn-icon btn-sm" style="color: var(--text-light);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- New Task Modal -->
<div id="new-task-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New Task</h3>
            <button class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Task Title</label>
                    <input type="text" name="title" class="form-control" placeholder="What do you need to do?" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Add details (optional)..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
