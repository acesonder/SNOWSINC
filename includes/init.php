<?php
/**
 * Session Initialization
 * OneSinc - Social Services Platform
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(defined('SESSION_NAME') ? SESSION_NAME : 'onesinc_session');
    session_start();
}

// Load configuration files
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Load helper functions
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Set timezone
date_default_timezone_set('America/New_York');

// Error reporting based on debug mode
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
