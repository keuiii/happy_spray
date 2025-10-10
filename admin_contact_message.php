<?php
session_start();
require_once 'classes/database.php';

$db = Database::getInstance();

// Check if admin is logged in
if (!$db->isLoggedIn() || $db->getCurrentUserRole() !== 'admin') {
    header("Location: customer_login.php");
    exit;
}

// Handle mark as read
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $db->updateContactMessageStatus($id, 'read');
    header("Location: admin_contact_messages.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->deleteContactMessage($id);
    $message = "Message deleted successfully!";
    $messageType = "success";
}

// Get all contact messages
$messages = $db->getAllContactMessages('created_at DESC');
$unreadCount = $db->getUnreadContactCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Messages - Happy Sprays Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
* {margin:0; padding:0; box-sizing:border-box;}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f0f0f5;
    display: flex;
}

.sidebar {
    width: 260px;
    background: #fff;
    color: #333;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    box-shadow: 2px 0 10px rgba(0,0,0,0.05);
}

.sidebar-header {
    padding: 35px 30px;
    border-bottom: 1px solid #e8e8e8;
    background: #fff;
}

.sidebar-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #000;
    font-weight: 700;
}

.sidebar-menu {
    padding: 30px 0;
}

.menu-item {
    padding: 16px 30px;
    color: #666;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.3s;
    font-weight: 500;
    font-size: 15px;
    margin: 4px 15px;
    border-radius: 10px;
    position: relative;
}

.menu-item::before {
    content: '○';
    font-size: 18px;
}

.menu-item:hover {
    background: #f5f5f5;
    color: #000;
}

.menu-item.active {
    background: #000;
    color: #fff;
}

.menu-item.active::before {
    content: '●';
}

.unread-badge {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: #ef4444;
    color: #fff;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
}

.sidebar-footer {
    position: absolute;
    bottom: 30px;
    width: 100%;
    padding: 0 15px;
}

.logout-item {
    padding: 16px 30px;
    color: #d32f2f;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    font-size: 15px;
    margin: 4px 0;
    border-radius: 10px;
    transition: all 0.3s;
}

.logout-item:hover {
    background: #ffebee;
}

.main-content {
    margin-left: 260px;
    flex: 1;
    padding: 40px;
    background: #f0f0f5;
}

.top-bar {
    background: #fff;
    padding: 30px 35px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    font-weight: 700;
    color: #000;
}

.top-bar p {
    color: #666;
    margin-top: 10px;
    font-size: 14px;
}

.message {
    padding: 16px 20px;
    border-radius: 12px;
    margin-top: 20px;
    font-size: 14px;
    font-weight: 500;
}

.message.success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.messages-list {
    display: grid;
    gap: 25px;
}

.message-card {
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    position: relative;
    transition: all 0.3s;
}

.message-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.message-card.unread {
    border-left: 4px solid #3b82f6;
    background: #fafeff;
}

.message-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.sender-info {
    flex: 1;
}

.sender-name {
    font-weight: 700;
    font-size: 17px;
    margin-bottom: 6px;
    color: #000;
}

.sender-email {
    color: #888;
    font-size: 14px;
}

.message-meta {
    text-align: right;
}

.message-date {
    color: #999;
    font-size: 13px;
    display: block;
    margin-bottom: 8px;
}

.message-status {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-unread {
    background: #fef3c7;
    color: #92400e;
}

.status-read {
    background: #dbeafe;
    color: #1e40af;
}

.message-content {
    background: #fafafa;
    padding: 18px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    line-height: 1.7;
    color: #333;
    font-size: 14px;
}

.message-actions {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #3b82f6;
    color: #fff;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59,130,246,0.3);
}

.btn-delete {
    background: #ef4444;
    color: #fff;
}

.btn-delete:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239,68,68,0.3);
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #999;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.empty-state h3 {
    font-size: 24px;
    margin-bottom: 10px;
    color: #666;
}

@media (max-width: 992px) {
    .sidebar {
        width: 220px;
    }
    
    .main-content {
        margin-left: 220px;
        padding: 25px;
    }
    
    .message-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .message-meta {
        text-align: left;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 70px;
    }
    
    .main-content {
        margin-left: 70px;
        padding: 20px;
    }
    
    .message-card {
        padding: 20px;
    }
    
    .message-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Happy Sprays</h2>
    </div>
    <nav class="sidebar-menu">
        <a href="admin_dashboard.php" class="menu-item">Dashboard</a>
        <a href="orders.php" class="menu-item">Orders</a>
        <a href="products_list.php" class="menu-item">Products</a>
        <a href="users.php" class="menu-item">Customers</a>
        <a href="admin_contact_messages.php" class="menu-item active">
            Messages
            <?php if ($unreadCount > 0): ?>
                <span class="unread-badge"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="customer_logout.php" class="logout-item">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Log out
        </a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Contact Messages</h1>
        <?php if ($unreadCount > 0): ?>
            <p style="color: #666; margin-top: 10px;">You have <?= $unreadCount ?> unread message(s)</p>
        <?php endif; ?>
        
        <?php if (isset($message)): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (empty($messages)): ?>
        <div class="empty-state">
            <h3>No Messages Yet</h3>
            <p>Contact messages from customers will appear here.</p>
        </div>
    <?php else: ?>
        <div class="messages-list">
            <?php foreach ($messages as $msg): ?>
                <div class="message-card <?= $msg['status'] === 'unread' ? 'unread' : '' ?>">
                    <div class="message-header">
                        <div class="sender-info">
                            <div class="sender-name"><?= htmlspecialchars($msg['name']) ?></div>
                            <div class="sender-email"><?= htmlspecialchars($msg['email']) ?></div>
                        </div>
                        <div class="message-meta">
                            <span class="message-date"><?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?></span>
                            <span class="message-status status-<?= $msg['status'] ?>">
                                <?= ucfirst($msg['status']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="message-content">
                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                    </div>
                    
                    <div class="message-actions">
                        <?php if ($msg['status'] === 'unread'): ?>
                            <a href="admin_contact_messages.php?mark_read=<?= $msg['id'] ?>" class="btn btn-primary">
                                Mark as Read
                            </a>
                        <?php endif; ?>
                        <a href="admin_contact_messages.php?delete=<?= $msg['id'] ?>" 
                           class="btn btn-delete"
                           onclick="return confirm('Delete this message?')">
                            Delete
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>