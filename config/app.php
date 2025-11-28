<?php
/**
 * Application Configuration
 * OneSinc - Social Services Platform
 */

// Application settings
define('APP_NAME', 'OneSinc');
define('APP_VERSION', '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// Session settings
define('SESSION_NAME', 'onesinc_session');
define('SESSION_LIFETIME', 3600); // 1 hour

// Security settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_MIN_LENGTH', 8);

// File upload settings
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// User roles
define('ROLE_CLIENT', 'client');
define('ROLE_HELPER', 'helper');
define('ROLE_ADMIN', 'admin');

// Request status
define('STATUS_PENDING', 'pending');
define('STATUS_IN_REVIEW', 'in_review');
define('STATUS_IN_PROGRESS', 'in_progress');
define('STATUS_FULFILLED', 'fulfilled');
define('STATUS_CLOSED', 'closed');

// Service categories
define('SERVICE_CATEGORIES', [
    'food' => 'Food Assistance',
    'housing' => 'Housing & Shelter',
    'legal' => 'Legal Aid',
    'health' => 'Health Services',
    'mental_health' => 'Mental Health',
    'employment' => 'Employment Support',
    'education' => 'Education',
    'childcare' => 'Childcare',
    'transportation' => 'Transportation',
    'other' => 'Other Services'
]);

// Priority levels
define('PRIORITY_LEVELS', [
    'low' => 'Low',
    'medium' => 'Medium',
    'high' => 'High',
    'urgent' => 'Urgent'
]);
