<?php
/**
 * Authentication Functions
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Authenticate user
 * @param string $email
 * @param string $password
 * @return array|false
 */
function authenticateUser($email, $password) {
    $sql = "SELECT id, email, password_hash, first_name, last_name, role, is_active 
            FROM users WHERE email = ?";
    $stmt = executeQuery($sql, [$email]);
    
    if (!$stmt) {
        return false;
    }
    
    $user = $stmt->fetch();
    
    if (!$user) {
        return false;
    }
    
    if (!$user['is_active']) {
        return false;
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }
    
    // Update last login
    executeQuery("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);
    
    return $user;
}

/**
 * Start user session
 * @param array $user
 */
function startUserSession($user) {
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['login_time'] = time();
}

/**
 * End user session
 */
function endUserSession() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Register new user
 * @param array $data
 * @return array
 */
function registerUser($data) {
    // Check if email already exists
    $stmt = executeQuery("SELECT id FROM users WHERE email = ?", [$data['email']]);
    if ($stmt && $stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (email, password_hash, first_name, last_name, phone, role) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $result = executeQuery($sql, [
        $data['email'],
        $passwordHash,
        $data['first_name'],
        $data['last_name'],
        $data['phone'] ?? null,
        $data['role'] ?? ROLE_CLIENT
    ]);
    
    if (!$result) {
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    $userId = getDBConnection()->lastInsertId();
    
    // Create profile based on role
    if ($data['role'] === ROLE_CLIENT || !isset($data['role'])) {
        executeQuery("INSERT INTO client_profiles (user_id) VALUES (?)", [$userId]);
    } elseif ($data['role'] === ROLE_HELPER) {
        executeQuery("INSERT INTO helper_profiles (user_id) VALUES (?)", [$userId]);
    }
    
    return ['success' => true, 'user_id' => $userId, 'message' => 'Registration successful'];
}

/**
 * Get user by ID
 * @param int $userId
 * @return array|false
 */
function getUserById($userId) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = executeQuery($sql, [$userId]);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Get user by email
 * @param string $email
 * @return array|false
 */
function getUserByEmail($email) {
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = executeQuery($sql, [$email]);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Update user profile
 * @param int $userId
 * @param array $data
 * @return bool
 */
function updateUserProfile($userId, $data) {
    $fields = [];
    $values = [];
    
    $allowedFields = ['first_name', 'last_name', 'phone', 'language_preference', 'accessibility_settings'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $values[] = is_array($data[$field]) ? json_encode($data[$field]) : $data[$field];
        }
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $values[] = $userId;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    
    return executeQuery($sql, $values) !== false;
}

/**
 * Change user password
 * @param int $userId
 * @param string $currentPassword
 * @param string $newPassword
 * @return array
 */
function changePassword($userId, $currentPassword, $newPassword) {
    $user = getUserById($userId);
    
    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }
    
    if (!password_verify($currentPassword, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
    }
    
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $result = executeQuery("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $userId]);
    
    return $result ? ['success' => true, 'message' => 'Password changed successfully'] : ['success' => false, 'message' => 'Failed to update password'];
}

/**
 * Create password reset token
 * @param string $email
 * @return array
 */
function createPasswordResetToken($email) {
    $user = getUserByEmail($email);
    
    if (!$user) {
        return ['success' => false, 'message' => 'Email not found'];
    }
    
    $token = generateToken(64);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
    $result = executeQuery($sql, [$user['id'], $token, $expiresAt]);
    
    return $result ? ['success' => true, 'token' => $token, 'user_id' => $user['id']] : ['success' => false, 'message' => 'Failed to create reset token'];
}

/**
 * Verify password reset token
 * @param string $token
 * @return array|false
 */
function verifyPasswordResetToken($token) {
    $sql = "SELECT pr.*, u.email FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = FALSE";
    $stmt = executeQuery($sql, [$token]);
    
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Reset password with token
 * @param string $token
 * @param string $newPassword
 * @return array
 */
function resetPasswordWithToken($token, $newPassword) {
    $reset = verifyPasswordResetToken($token);
    
    if (!$reset) {
        return ['success' => false, 'message' => 'Invalid or expired token'];
    }
    
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
    }
    
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $result = executeQuery("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $reset['user_id']]);
    
    if (!$result) {
        return ['success' => false, 'message' => 'Failed to reset password'];
    }
    
    // Mark token as used
    executeQuery("UPDATE password_resets SET used = TRUE WHERE id = ?", [$reset['id']]);
    
    return ['success' => true, 'message' => 'Password reset successfully'];
}
