<?php
/**
 * Logout Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';

if (isLoggedIn()) {
    logActivity('logout', 'user', getCurrentUserId());
    endUserSession();
}

redirect('login.php');
