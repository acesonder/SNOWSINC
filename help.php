<?php
/**
 * Help & Support Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Help & Support</h1>
    <p class="page-subtitle">Find answers and get assistance</p>
</div>

<!-- Emergency Banner -->
<div class="alert alert-danger mb-3">
    <i class="alert-icon fas fa-exclamation-triangle"></i>
    <div class="alert-content">
        <div class="alert-title">In an Emergency?</div>
        If you are in immediate danger, please call <strong>911</strong> or use the SOS button.
        <br>Suicide & Crisis Lifeline: <strong>988</strong>
    </div>
</div>

<!-- Quick Help Cards -->
<div class="stats-grid mb-3">
    <a href="requests.php?action=new" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon primary"><i class="fas fa-plus-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="font-size: 1.25rem;">Request Help</div>
            <div class="stat-label">Submit a new assistance request</div>
        </div>
    </a>
    <a href="messages.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon success"><i class="fas fa-comments"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="font-size: 1.25rem;">Contact Support</div>
            <div class="stat-label">Message your assigned helper</div>
        </div>
    </a>
    <a href="resources.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-icon warning"><i class="fas fa-book"></i></div>
        <div class="stat-info">
            <div class="stat-value" style="font-size: 1.25rem;">Resources</div>
            <div class="stat-label">Browse helpful information</div>
        </div>
    </a>
</div>

<div class="d-flex gap-2" style="flex-wrap: wrap;">
    <!-- FAQ -->
    <div class="flex-1" style="min-width: 400px;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-question-circle"></i> Frequently Asked Questions</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h4>How do I request help?</h4>
                    <p style="color: var(--text-secondary);">
                        Click on "Request Help" or navigate to the Requests page and click "New Request". 
                        Fill out the form with details about what you need, and our team will review your request.
                    </p>
                </div>
                
                <div class="mb-3">
                    <h4>How long will it take to get help?</h4>
                    <p style="color: var(--text-secondary);">
                        Response times vary based on urgency and availability. Urgent requests are prioritized. 
                        You'll receive updates through the app and can check your request status anytime.
                    </p>
                </div>
                
                <div class="mb-3">
                    <h4>How do I contact my helper?</h4>
                    <p style="color: var(--text-secondary);">
                        Use the Messages feature to communicate with your assigned helper. 
                        You can also find their contact information on your request details page.
                    </p>
                </div>
                
                <div class="mb-3">
                    <h4>Is my information private?</h4>
                    <p style="color: var(--text-secondary);">
                        Yes! Your information is protected and only shared with authorized helpers assigned to your case. 
                        You can review and manage your privacy settings in the Settings page.
                    </p>
                </div>
                
                <div class="mb-3">
                    <h4>How do I update my profile?</h4>
                    <p style="color: var(--text-secondary);">
                        Click on your profile picture in the top right corner and select "My Profile" 
                        to update your personal information, contact details, and preferences.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contact Info -->
    <div style="width: 350px;">
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-phone"></i> Contact Us</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5>Phone Support</h5>
                    <p style="color: var(--text-secondary);">
                        <i class="fas fa-phone"></i> 1-800-ONESINC<br>
                        <small>Mon-Fri: 8am - 8pm EST</small>
                    </p>
                </div>
                
                <div class="mb-3">
                    <h5>Email Support</h5>
                    <p style="color: var(--text-secondary);">
                        <i class="fas fa-envelope"></i> support@onesinc.org<br>
                        <small>Response within 24-48 hours</small>
                    </p>
                </div>
                
                <div>
                    <h5>In Person</h5>
                    <p style="color: var(--text-secondary);">
                        <i class="fas fa-map-marker-alt"></i> Visit your local office<br>
                        <small>Find locations in the Resources section</small>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-comment-dots"></i> Feedback</h3>
            </div>
            <div class="card-body">
                <p style="color: var(--text-secondary); margin-bottom: 1rem;">
                    Your feedback helps us improve! Let us know how we're doing.
                </p>
                <a href="feedback.php" class="btn btn-outline w-100">
                    <i class="fas fa-paper-plane"></i> Send Feedback
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
