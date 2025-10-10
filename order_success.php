<?php
session_start();
require_once 'classes/database.php';

$db = Database::getInstance();

if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit;
}

$order_id = intval($_GET['order_id']);
$order = $db->getCustomerOrder($order_id);

if (!$order) {
    header("Location: index.php");
    exit;
}

$orderItems = $db->getCustomerOrderItems($order_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Successful - Happy Sprays</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
* {margin:0; padding:0; box-sizing:border-box;}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f5f5;
    color: #000;
}

.top-nav {
    background: #fff;
    border-bottom: 1px solid #eee;
    padding: 20px;
    text-align: center;
}

.top-nav h1 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.success-card {
    background: #fff;
    padding: 40px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.success-icon {
    width: 80px;
    height: 80px;
    background: #4caf50;
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 50px;
}

.success-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    margin-bottom: 10px;
    color: #4caf50;
}

.success-message {
    color: #666;
    margin-bottom: 30px;
    line-height: 1.6;
}

.order-details {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
    margin: 20px 0;
    text-align: left;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    font-weight: 600;
}

.order-items {
    margin: 20px 0;
    text-align: left;
}

.order-items h3 {
    font-family: 'Playfair Display', serif;
    margin-bottom: 15px;
    font-size: 20px;
}

.item-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.item-info {
    flex: 1;
}

.item-name {
    font-weight: 600;
    margin-bottom: 5px;
}

.item-details {
    font-size: 14px;
    color: #666;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    justify-content: center;
}

.btn {
    padding: 12px 30px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
    display: inline-block;
}

.btn-primary {
    background: #000;
    color: #fff;
}

.btn-primary:hover {
    background: #333;
}

.btn-secondary {
    background: #fff;
    color: #000;
    border: 2px solid #000;
}

.btn-secondary:hover {
    background: #000;
    color: #fff;
}

.status-badge {
    display: inline-block;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
</head>
<body>

<div class="top-nav">
    <h1>Happy Sprays</h1>
</div>

<div class="container">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h1 class="success-title">Order Placed Successfully!</h1>
        <p class="success-message">
            Thank you for your purchase! Your order has been received and is being processed.
            We'll send you a confirmation email shortly.
        </p>
        
        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Order Number:</span>
                <span>#<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Order Date:</span>
                <span><?= date('M d, Y h:i A', strtotime($order['o_created_at'])) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span><?= strtoupper($order['payment_method']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="status-badge"><?= ucfirst($order['order_status']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Amount:</span>
                <span style="font-weight: 700; font-size: 18px;">₱<?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>
        
        <div class="order-items">
            <h3>Order Items</h3>
            <?php foreach ($orderItems as $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <div class="item-name"><?= htmlspecialchars($item['perfume_name']) ?></div>
                        <div class="item-details">
                            Quantity: <?= $item['order_quantity'] ?> × ₱<?= number_format($item['order_price'], 2) ?>
                        </div>
                    </div>
                    <div style="font-weight: 600;">
                        ₱<?= number_format($item['order_price'] * $item['order_quantity'], 2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="action-buttons">
            <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

</body>
</html>