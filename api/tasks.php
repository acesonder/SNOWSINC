<?php
/**
 * Tasks API
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
        $stmt = executeQuery("SELECT * FROM tasks WHERE user_id = ? ORDER BY is_completed ASC, due_date ASC", [$userId]);
        $tasks = $stmt ? $stmt->fetchAll() : [];
        jsonResponse(['success' => true, 'tasks' => $tasks]);
        break;
        
    case 'add':
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $dueDate = $_POST['due_date'] ?? null;
        
        if (empty($title)) {
            jsonResponse(['success' => false, 'message' => 'Title is required']);
        }
        
        $result = executeQuery("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)",
                              [$userId, $title, $description, $dueDate ?: null]);
        
        if ($result) {
            $taskId = getDBConnection()->lastInsertId();
            jsonResponse(['success' => true, 'task_id' => $taskId, 'message' => 'Task added']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to add task']);
        }
        break;
        
    case 'toggle':
        $taskId = (int)($_POST['task_id'] ?? 0);
        if (!$taskId) {
            jsonResponse(['success' => false, 'message' => 'Task ID required']);
        }
        
        $result = executeQuery("UPDATE tasks SET is_completed = NOT is_completed, 
                               completed_at = CASE WHEN is_completed = FALSE THEN NOW() ELSE NULL END 
                               WHERE id = ? AND user_id = ?", [$taskId, $userId]);
        
        jsonResponse(['success' => $result !== false]);
        break;
        
    case 'delete':
        $taskId = (int)($_POST['task_id'] ?? 0);
        if (!$taskId) {
            jsonResponse(['success' => false, 'message' => 'Task ID required']);
        }
        
        $result = executeQuery("DELETE FROM tasks WHERE id = ? AND user_id = ?", [$taskId, $userId]);
        jsonResponse(['success' => $result !== false]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
