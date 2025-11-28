<?php
/**
 * Feedback Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = sanitize($_POST['type'] ?? 'suggestion');
    $subject = sanitize($_POST['subject'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    
    if ($content) {
        executeQuery("INSERT INTO feedback (user_id, type, subject, content, rating) VALUES (?, ?, ?, ?, ?)",
                    [$userId, $type, $subject, $content, $rating ?: null]);
        setFlashMessage('success', 'Thank you for your feedback! We appreciate your input.');
        redirect('feedback.php');
    } else {
        setFlashMessage('danger', 'Please provide your feedback.');
    }
}

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Send Feedback</h1>
    <p class="page-subtitle">Help us improve <?php echo APP_NAME; ?></p>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-comment-dots"></i> Your Feedback</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-group">
                <label class="form-label required">Type of Feedback</label>
                <select name="type" class="form-control" required>
                    <option value="suggestion">💡 Suggestion</option>
                    <option value="praise">🌟 Praise / Compliment</option>
                    <option value="bug">🐛 Bug Report</option>
                    <option value="complaint">😟 Complaint</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="Brief subject (optional)">
            </div>
            
            <div class="form-group">
                <label class="form-label required">Your Feedback</label>
                <textarea name="content" class="form-control" rows="5" 
                          placeholder="Please share your thoughts, suggestions, or concerns..." required></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">How would you rate your experience? (Optional)</label>
                <div class="d-flex gap-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label style="cursor: pointer;">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" style="display: none;">
                            <span class="btn btn-outline btn-icon" style="font-size: 1.5rem;" onclick="selectRating(this, <?php echo $i; ?>)">
                                ⭐
                            </span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-paper-plane"></i> Submit Feedback
            </button>
        </form>
    </div>
</div>

<script>
function selectRating(el, rating) {
    document.querySelectorAll('[name="rating"]').forEach((r, i) => {
        const btn = r.parentElement.querySelector('.btn');
        if (i < rating) {
            btn.style.background = 'var(--warning-color)';
            btn.style.borderColor = 'var(--warning-color)';
        } else {
            btn.style.background = '';
            btn.style.borderColor = '';
        }
    });
    document.querySelector(`[name="rating"][value="${rating}"]`).checked = true;
}
</script>

<?php include __DIR__ . '/templates/footer.php'; ?>
