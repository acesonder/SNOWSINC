<?php
/**
 * Change Password API
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$userId = getCurrentUserId();
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    jsonResponse(['success' => false, 'message' => 'All fields are required']);
}

if ($newPassword !== $confirmPassword) {
    jsonResponse(['success' => false, 'message' => 'New passwords do not match']);
}

$result = changePassword($userId, $currentPassword, $newPassword);

if ($result['success']) {
    logActivity('change_password', 'user', $userId);
}

jsonResponse($result);
