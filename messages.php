<?php
/**
 * Messages Page
 * OneSinc - Social Services Platform
 */

require_once __DIR__ . '/includes/init.php';
requireLogin();

$userId = getCurrentUserId();
$userRole = getCurrentUserRole();

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $subject = sanitize($_POST['subject'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    
    if ($receiverId && $content) {
        $sql = "INSERT INTO messages (sender_id, receiver_id, subject, content) VALUES (?, ?, ?, ?)";
        $result = executeQuery($sql, [$userId, $receiverId, $subject, $content]);
        
        if ($result) {
            setFlashMessage('success', 'Message sent successfully!');
        } else {
            setFlashMessage('danger', 'Failed to send message.');
        }
    }
    redirect('messages.php');
}

// Fetch conversations
$sql = "SELECT 
            CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as contact_id,
            MAX(created_at) as last_message_time,
            SUM(CASE WHEN receiver_id = ? AND is_read = FALSE THEN 1 ELSE 0 END) as unread_count
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?
        GROUP BY contact_id
        ORDER BY last_message_time DESC";
$stmt = executeQuery($sql, [$userId, $userId, $userId, $userId]);
$conversations = $stmt ? $stmt->fetchAll() : [];

// Enrich with user info
foreach ($conversations as &$conv) {
    $user = getUserById($conv['contact_id']);
    $conv['contact_name'] = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'Unknown';
    $conv['contact_role'] = $user ? $user['role'] : '';
    
    // Get last message
    $stmt = executeQuery("SELECT content FROM messages 
                         WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                         ORDER BY created_at DESC LIMIT 1", 
                         [$userId, $conv['contact_id'], $conv['contact_id'], $userId]);
    $lastMsg = $stmt ? $stmt->fetch() : null;
    $conv['last_message'] = $lastMsg ? truncateText($lastMsg['content'], 50) : '';
}

// View specific conversation
$viewConversation = isset($_GET['to']) ? (int)$_GET['to'] : null;
$conversationMessages = [];
$contactUser = null;

if ($viewConversation) {
    $contactUser = getUserById($viewConversation);
    
    // Mark messages as read
    executeQuery("UPDATE messages SET is_read = TRUE, read_at = NOW() 
                 WHERE sender_id = ? AND receiver_id = ? AND is_read = FALSE", 
                 [$viewConversation, $userId]);
    
    // Fetch messages
    $sql = "SELECT m.*, u.first_name, u.last_name 
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC";
    $stmt = executeQuery($sql, [$userId, $viewConversation, $viewConversation, $userId]);
    $conversationMessages = $stmt ? $stmt->fetchAll() : [];
}

// Get available contacts for new message
$contacts = [];
if ($userRole === ROLE_CLIENT) {
    // Clients can message their assigned helpers
    $stmt = executeQuery("SELECT DISTINCT u.id, u.first_name, u.last_name, u.role 
                         FROM users u 
                         INNER JOIN client_profiles cp ON cp.assigned_helper_id = u.id
                         WHERE cp.user_id = ?
                         UNION
                         SELECT DISTINCT u.id, u.first_name, u.last_name, u.role 
                         FROM users u 
                         INNER JOIN service_requests sr ON sr.helper_id = u.id
                         WHERE sr.client_id = ?", [$userId, $userId]);
} else {
    // Helpers/Admins can message anyone
    $stmt = executeQuery("SELECT id, first_name, last_name, role FROM users WHERE id != ? ORDER BY first_name", [$userId]);
}
$contacts = $stmt ? $stmt->fetchAll() : [];

include __DIR__ . '/templates/header.php';
?>

<div class="page-header">
    <h1 class="page-title">Messages</h1>
    <p class="page-subtitle">Communicate securely with your <?php echo $userRole === ROLE_CLIENT ? 'helpers' : 'clients'; ?></p>
</div>

<div class="d-flex gap-2" style="height: calc(100vh - 200px); min-height: 500px;">
    <!-- Conversation List -->
    <div class="card" style="width: 320px; display: flex; flex-direction: column;">
        <div class="card-header">
            <h3 class="card-title">Conversations</h3>
            <button class="btn btn-sm btn-primary" data-modal="new-message-modal">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="card-body p-0" style="flex: 1; overflow-y: auto;">
            <?php if (empty($conversations)): ?>
                <div class="empty-state" style="padding: 2rem;">
                    <div class="empty-icon" style="width: 60px; height: 60px;"><i class="fas fa-comments"></i></div>
                    <div class="empty-title">No messages</div>
                    <div class="empty-desc">Start a conversation!</div>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="messages.php?to=<?php echo $conv['contact_id']; ?>" 
                       class="message-card <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?> <?php echo $viewConversation == $conv['contact_id'] ? 'active' : ''; ?>"
                       style="text-decoration: none; <?php echo $viewConversation == $conv['contact_id'] ? 'background: var(--bg-primary);' : ''; ?>">
                        <div class="avatar">
                            <?php echo getInitials(['first_name' => explode(' ', $conv['contact_name'])[0], 'last_name' => explode(' ', $conv['contact_name'])[1] ?? '']); ?>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender"><?php echo htmlspecialchars($conv['contact_name']); ?></span>
                                <span class="message-time"><?php echo timeAgo($conv['last_message_time']); ?></span>
                            </div>
                            <div class="message-subject" style="color: var(--text-secondary);">
                                <?php echo htmlspecialchars($conv['last_message']); ?>
                            </div>
                        </div>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span class="badge badge-primary"><?php echo $conv['unread_count']; ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Chat Area -->
    <div class="card flex-1" style="display: flex; flex-direction: column;">
        <?php if ($viewConversation && $contactUser): ?>
            <div class="card-header">
                <div class="d-flex align-center gap-2">
                    <div class="avatar">
                        <?php echo getInitials(['first_name' => $contactUser['first_name'], 'last_name' => $contactUser['last_name']]); ?>
                    </div>
                    <div>
                        <h3 class="card-title" style="margin: 0;"><?php echo htmlspecialchars($contactUser['first_name'] . ' ' . $contactUser['last_name']); ?></h3>
                        <span style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo ucfirst($contactUser['role']); ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body chat-messages" style="flex: 1; overflow-y: auto;">
                <?php if (empty($conversationMessages)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-comment-dots"></i></div>
                        <div class="empty-title">No messages yet</div>
                        <div class="empty-desc">Send the first message!</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversationMessages as $msg): ?>
                        <div class="chat-message <?php echo $msg['sender_id'] == $userId ? 'outgoing' : ''; ?>">
                            <div class="avatar avatar-sm">
                                <?php echo getInitials(['first_name' => $msg['first_name'], 'last_name' => $msg['last_name']]); ?>
                            </div>
                            <div>
                                <div class="chat-bubble">
                                    <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
                                </div>
                                <div class="chat-time"><?php echo formatDateTime($msg['created_at'], 'M j, g:i A'); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="chat-input">
                <form method="POST" class="d-flex gap-2 w-100">
                    <input type="hidden" name="receiver_id" value="<?php echo $viewConversation; ?>">
                    <input type="text" name="content" class="form-control" placeholder="Type your message..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="card-body d-flex align-center justify-center" style="flex: 1;">
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-comments"></i></div>
                    <div class="empty-title">Select a conversation</div>
                    <div class="empty-desc">Choose a conversation from the list or start a new one.</div>
                    <button class="btn btn-primary mt-2" data-modal="new-message-modal">
                        <i class="fas fa-plus"></i> New Message
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- New Message Modal -->
<div id="new-message-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">New Message</h3>
            <button class="modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">To</label>
                    <select name="receiver_id" class="form-control" required>
                        <option value="">Select recipient...</option>
                        <?php foreach ($contacts as $contact): ?>
                            <option value="<?php echo $contact['id']; ?>">
                                <?php echo htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']); ?>
                                (<?php echo ucfirst($contact['role']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="subject" class="form-control" placeholder="Optional subject...">
                </div>
                <div class="form-group">
                    <label class="form-label required">Message</label>
                    <textarea name="content" class="form-control" rows="4" placeholder="Type your message..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/templates/footer.php'; ?>
