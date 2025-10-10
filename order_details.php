<?php
session_start();
require_once 'classes/database.php';

$db = Database::getInstance();

// Check if customer is logged in
if (!$db->isUserLoggedIn() || $db->getCurrentUserRole() !== 'customer') {
    header("Location: customer_login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: my_orders.php");
    exit;
}

$order_id = intval($_GET['id']);
$order = $db->getCustomerOrder($order_id);

if (!$order) {
    header("Location: my_orders.php");
    exit;
}

$orderItems = $db->getCustomerOrderItems($order_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details #<?= $order['order_id'] ?> - Happy Sprays</title>
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
    max-width: 1000px;
    margin: 40px auto;
    padding: 0 20px;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    color: #000;
    text-decoration: none;
    font-weight: 600;
}

.back-link:hover {
    text-decoration: underline;
}

.order-header {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.order-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    margin-bottom: 15px;
}

.order-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.meta-item {
    display: flex;
    flex-direction: column;
}

.meta-label {
    font-size: 12px;
    color: #999;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.meta-value {
    font-weight: 600;
    font-size: 16px;
}

.order-status {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 20px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cfe2ff;
    color: #084298;
}

.status-preparing {
    background: #d1ecf1;
    color: #0c5460;
}

.status-shipping, .status-out-for-delivery {
    background: #d1ecf1;
    color: #0c5460;
}

.status-delivered, .status-received {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.order-items {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #000;
}

.item-row {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.item-row:last-child {
    border-bottom: none;
}

.item-image img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 5px;
}

.item-info {
    flex: 1;
}

.item-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 5px;
}

.item-details {
    color: #666;
    font-size: 14px;
}

.item-price {
    text-align: right;
}

.item-subtotal {
    font-weight: 700;
    font-size: 16px;
}

.order-summary {
    background: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}

.summary-row.total {
    border-top: 2px solid #000;
    margin-top: 10px;
    padding-top: 15px;
    font-weight: 700;
    font-size: 20px;
}

@media (max-width: 768px) {
    .item-row {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .item-image {
        text-align: center;
    }
    
    .item-price {
        text-align: left;
    }
}
</style>
</head>
<body>

<div class="top-nav">
    <h1>Happy Sprays</h1>
</div>

<div class="container">
    <a href="my_orders.php" class="back-link">← Back to Orders</a>
    
    <div class="order-header">
        <h1 class="order-title">Order #<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></h1>
        
        <div class="order-meta">
            <div class="meta-item">
                <span class="meta-label">Order Date</span>
                <span class="meta-value"><?= date('M d, Y h:i A', strtotime($order['o_created_at'])) ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Status</span>
                <span class="order-status status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>">
                    <?= ucfirst($order['order_status']) ?>
                </span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Payment Method</span>
                <span class="meta-value"><?= strtoupper($order['payment_method']) ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Total Amount</span>
                <span class="meta-value">₱<?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>
    </div>
    
    <div class="order-items">
        <h2 class="section-title">Order Items</h2>
        
        <?php foreach ($orderItems as $item): ?>
            <div class="item-row">
                <div class="item-image">
                    <?php if (!empty($item['image'])): ?>
                        <img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['perfume_name']) ?>">
                    <?php else: ?>
                        <div style="width:80px; height:80px; background:#f5f5f5; border-radius:5px;"></div>
                    <?php endif; ?>
                </div>
                <div class="item-info">
                    <div class="item-name"><?= htmlspecialchars($item['perfume_name']) ?></div>
                    <div class="item-details">
                        Quantity: <?= $item['order_quantity'] ?> × ₱<?= number_format($item['order_price'], 2) ?>
                    </div>
                </div>
                <div class="item-price">
                    <div class="item-subtotal">
                        ₱<?= number_format($item['order_price'] * $item['order_quantity'], 2) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
                        
    <div class="order-summary">
        <h2 class="section-title">Order Summary</h2>
        
        <div class="summary-row">
            <span>Subtotal:</span>
            <span>₱<?= number_format($order['total_amount'], 2) ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping:</span>
            <span>FREE</span>
        </div>
        <div class="summary-row total">
            <span>Total:</span>
            <span>₱<?= number_format($order['total_amount'], 2) ?></span>
        </div>
    </div>
</div>

</body>
</html>