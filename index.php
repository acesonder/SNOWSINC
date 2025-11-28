<?php
/**
 * Index - Redirect to Login or Dashboard
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';

// If already logged in, go to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Otherwise, go to login
redirect('login.php');
