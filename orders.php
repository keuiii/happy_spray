<?php
session_start();
require_once 'classes/database.php';

$db = Database::getInstance();
$message = '';
$messageType = '';

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'] ?? 'processing';

    $result = $db->updateOrderStatus($order_id, $new_status);
    
    if ($result['success']) {
        $message = $result['message'];
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

// Handle search
$search_term = $_GET['search'] ?? '';
if (!empty($search_term)) {
    $orders = $db->searchOrders($search_term);
} else {
    $orders = $db->getAllOrders();
}

// Get order statistics
$stats = $db->getOrderStats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Orders - Admin Dashboard</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background: #fff;
        color: #000;
        padding: 30px;
        margin: 0;
    }
    
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    h1 {
        margin: 0;
        font-size: 28px;
    }
    
    .back-btn {
        display: inline-block;
        padding: 10px 20px;
        border: 1px solid #000;
        text-decoration: none;
        color: #000;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .back-btn:hover {
        background: #000;
        color: #fff;
    }
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #e9ecef;
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        color: #666;
        text-transform: uppercase;
    }
    
    .search-section {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .search-input {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        width: 300px;
    }
    
    .search-btn {
        padding: 10px 20px;
        border: 1px solid #000;
        background: #000;
        color: #fff;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }
    
    .search-btn:hover {
        background: #333;
    }
    
    .clear-search {
        padding: 10px 15px;
        border: 1px solid #666;
        background: #fff;
        color: #666;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }
    
    .message {
        padding: 12px 20px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    
    .message.success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .message.error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    th, td {
        border: 1px solid #dee2e6;
        padding: 12px;
        text-align: center;
    }
    
    th {
        background: #f8f9fa;
        font-weight: 600;
        color: #495057;
    }
    
    tr:nth-child(even) {
        background: #f8f9fa;
    }
    
    tr:hover {
        background: #e9ecef;
    }
    
    .view-btn {
        padding: 6px 12px;
        border: 1px solid #007bff;
        text-decoration: none;
        border-radius: 4px;
        color: #007bff;
        font-size: 13px;
        transition: all 0.3s;
    }
    
    .view-btn:hover {
        background: #007bff;
        color: #fff;
    }
    
    .no-orders {
        text-align: center;
        margin: 50px 0;
        font-size: 18px;
        color: #666;
    }
    
    select, button {
        padding: 6px 8px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: #fff;
        cursor: pointer;
    }
    
    button[type="submit"] {
        margin-left: 6px;
        background: #28a745;
        color: #fff;
        border-color: #28a745;
    }
    
    button[type="submit"]:hover {
        background: #218838;
    }
    
    .status-form {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }
    
    .status-processing { background: #fff3cd; color: #856404; }
    .status-preparing { background: #cce5ff; color: #004085; }
    .status-out-for-delivery { background: #d4edda; color: #155724; }
    .status-received { background: #e7f3ff; color: #0c5460; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
</style>
</head>
<body>

<div class="header">
    <h1>Order Management</h1>
    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<!-- Order Statistics -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['total_orders'] ?? 0) ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">₱<?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['processing'] ?? 0) ?></div>
        <div class="stat-label">Processing</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($stats['preparing'] ?? 0) ?></div>
        <div class="stat-label">Preparing</div>
    </div>
</div>

<!-- Search Section -->
<div class="search-section">
    <form method="GET" style="display: flex; gap: 10px; align-items: center;">
        <input type="text" name="search" class="search-input" 
               placeholder="Search by order ID, customer name, or email..." 
               value="<?= htmlspecialchars($search_term) ?>">
        <button type="submit" class="search-btn">Search</button>
        <?php if (!empty($search_term)): ?>
            <a href="<?= $_SERVER['PHP_SELF'] ?>" class="clear-search">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Success/Error Messages -->
<?php if (!empty($message)): ?>
    <div class="message <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Orders Table -->
<?php if (!empty($orders)): ?>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Total Amount</th>
                <th>Payment Method</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><strong>#<?= htmlspecialchars($order['id']) ?></strong></td>
                <td><?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($order['email'] ?? 'N/A') ?></td>
                <td><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                <td><?= htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')) ?></td>
                <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                <td>
                    <form method="POST" class="status-form">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                        <select name="status" onchange="this.form.submit()">
                            <?php
                            $statuses = $db->getOrderStatuses();
                            foreach ($statuses as $val => $label) {
                                $selected = ($order['status'] === $val) ? ' selected' : '';
                                echo '<option value="'.htmlspecialchars($val).'"'.$selected.'>'.htmlspecialchars($label).'</option>';
                            }
                            ?>
                        </select>
                        <button type="submit" name="update_status">Update</button>
                    </form>
                </td>
                <td>
                    <a class="view-btn" href="receipt.php?order_id=<?= (int)$order['id'] ?>" target="_blank">
                        View Receipt
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="no-orders">
        <?php if (!empty($search_term)): ?>
            No orders found matching "<?= htmlspecialchars($search_term) ?>"
        <?php else: ?>
            No orders found.
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>