<?php
session_start();
require_once 'classes/database.php';
$db = Database::getInstance();

if (!isset($_GET['order_id'])) {
    die("Invalid receipt.");
}

$order_id = intval($_GET['order_id']);

// Centralized calls
$order = $db->getOrderById($order_id);
if (!$order) {
    die("Order not found.");
}

$items = $db->getOrderItems($order_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #<?= $order_id ?></title>
<style>
    body { font-family: 'Segoe UI', sans-serif; background:#fff; color:#000; margin:30px; }
    .receipt-container { max-width:700px; margin:auto; border:1px solid #000; padding:20px; border-radius:6px; }
    .header { text-align:center; margin-bottom:20px; }
    .header img { width:80px; }
    h1 { margin:10px 0 0; }
    .store-info { font-size:14px; margin-bottom:20px; }
    table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    th, td { border:1px solid #000; padding:10px; text-align:center; }
    th { background:#f2f2f2; }
    .thank-you { text-align:center; margin-top:20px; font-style:italic; }
    .btn-print, .btn-back { 
        display:inline-block; padding:10px 20px; border:1px solid #000; 
        text-decoration:none; color:#000; border-radius:4px; margin:5px;
    }
    .btn-print:hover, .btn-back:hover { background:#000; color:#fff; }
    .proof { text-align:center; margin-top:20px; }
    .proof img { max-width:300px; border:1px solid #000; border-radius:6px; }
</style>
</head>
<body>
<div class="receipt-container">
    <div class="header">
        <img src="images/happysprayslogo1.png" alt="Logo">
        <h1>Happy Sprays Official</h1>
        <div class="store-info">
            📍 123 Sample Street, Manila <br>
            📞 0912-345-6789 | ✉️ support@happysprays.com
        </div>
        <hr>
    </div>

    <h2>Receipt #<?= $order['id'] ?></h2>
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?><br>
       <strong>Email:</strong> <?= htmlspecialchars($order['email']) ?><br>
       <strong>Address:</strong> <?= htmlspecialchars($order['address']) ?><br>
       <strong>Payment Method:</strong> <?= htmlspecialchars(strtoupper($order['payment_method'])) ?><br>
       <strong>Status:</strong> Pending<br>
       <strong>Date:</strong> <?= $order['created_at'] ?>
    </p>

    <table>
        <tr>
            <th>Product</th><th>Qty</th><th>Price</th><th>Total</th>
        </tr>
        <?php foreach($items as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td>₱<?= number_format($row['price'],2) ?></td>
            <td>₱<?= number_format($row['price']*$row['quantity'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
            <th colspan="3">Grand Total</th>
            <th>₱<?= number_format($order['total_amount'],2) ?></th>
        </tr>
    </table>

    <?php if ($order['payment_method'] == 'gcash' && !empty($order['gcash_proof'])): ?>
        <div class="proof">
            <h3>GCash Proof of Payment</h3>
            <img src="uploads/<?= htmlspecialchars($order['gcash_proof']) ?>" alt="GCash Proof">
        </div>
    <?php endif; ?>

    <div class="thank-you">
        Thank you for shopping with <strong>Happy Sprays!</strong><br>
        Please keep this receipt and screenshot as proof of your order.
    </div>

    <div style="text-align:center; margin-top:20px;">
        <a href="javascript:window.print()" class="btn-print">🖨️ Print</a>
        <a href="my_orders.php" class="btn-back">← Back to Orders</a>
    </div>
</div>
</body>
</html>
