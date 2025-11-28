<?php
/**
 * Helper Functions
 * OneSinc - Social Services Platform
 */

/**
 * Sanitize user input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Redirect to a URL
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/**
 * Require user to be logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/**
 * Require specific role
 * @param string|array $roles
 */
function requireRole($roles) {
    requireLogin();
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    if (!in_array($_SESSION['user_role'], $roles)) {
        redirect('dashboard.php?error=unauthorized');
    }
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * @return string|null
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Flash message helpers
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'M j, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime
 * @param string $format
 * @return string
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    if (empty($datetime)) return '';
    return date($format, strtotime($datetime));
}

/**
 * Get time ago string
 * @param string $datetime
 * @return string
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get status badge HTML
 * @param string $status
 * @return string
 */
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'in_review' => '<span class="badge badge-info">In Review</span>',
        'in_progress' => '<span class="badge badge-primary">In Progress</span>',
        'fulfilled' => '<span class="badge badge-success">Fulfilled</span>',
        'closed' => '<span class="badge badge-secondary">Closed</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

/**
 * Get priority badge HTML
 * @param string $priority
 * @return string
 */
function getPriorityBadge($priority) {
    $badges = [
        'low' => '<span class="badge badge-success">Low</span>',
        'medium' => '<span class="badge badge-info">Medium</span>',
        'high' => '<span class="badge badge-warning">High</span>',
        'urgent' => '<span class="badge badge-danger">Urgent</span>'
    ];
    return $badges[$priority] ?? '<span class="badge badge-secondary">' . ucfirst($priority) . '</span>';
}

/**
 * Get category icon
 * @param string $category
 * @return string
 */
function getCategoryIcon($category) {
    $icons = [
        'food' => 'fas fa-utensils',
        'housing' => 'fas fa-home',
        'legal' => 'fas fa-gavel',
        'health' => 'fas fa-heartbeat',
        'mental_health' => 'fas fa-brain',
        'employment' => 'fas fa-briefcase',
        'education' => 'fas fa-graduation-cap',
        'childcare' => 'fas fa-baby',
        'transportation' => 'fas fa-car',
        'other' => 'fas fa-ellipsis-h'
    ];
    return $icons[$category] ?? 'fas fa-circle';
}

/**
 * Log activity
 * @param string $action
 * @param string|null $entityType
 * @param int|null $entityId
 * @param array|null $details
 */
function logActivity($action, $entityType = null, $entityId = null, $details = null) {
    $userId = getCurrentUserId();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $sql = "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($sql, [
        $userId,
        $action,
        $entityType,
        $entityId,
        $details ? json_encode($details) : null,
        $ipAddress,
        $userAgent
    ]);
}

/**
 * Send JSON response
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random token
 * @param int $length
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Truncate text
 * @param string $text
 * @param int $length
 * @param string $suffix
 * @return string
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get user's full name
 * @param array $user
 * @return string
 */
function getFullName($user) {
    return $user['first_name'] . ' ' . $user['last_name'];
}

/**
 * Get user's initials
 * @param array $user
 * @return string
 */
function getInitials($user) {
    return strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
}
