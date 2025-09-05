<?php
session_start();
require_once __DIR__ . "/classes/database.php";

// Protect page
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login.php");
    exit;
}

$db = new Database();

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch order info
$order = $db->getCustomerOrder($orderId);
if (!$order) {
    die("Order not found or you do not have permission to view it.");
}

// Fetch items for this order
$items = $db->getCustomerOrderItems($orderId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Details</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 20px;
    }
    h1, h2 {
      text-align: center;
      color: #333;
    }
    .order-info {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
    }
    .order-info p {
      margin: 8px 0;
      font-size: 1rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      margin-top: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    table th, table td {
      padding: 12px 15px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    table th {
      background: #ffb6c1;
      color: #333;
    }
    tr:hover {
      background: #f1f1f1;
    }
    .status {
      padding: 5px 10px;
      border-radius: 8px;
      font-size: 0.9em;
      font-weight: bold;
    }
    .pending { background: #ffeeba; color: #856404; }
    .completed { background: #c3e6cb; color: #155724; }
    .cancelled { background: #f5c6cb; color: #721c24; }
    .btn-back {
      display: inline-block;
      padding: 8px 15px;
      margin-top: 20px;
      background: #ff69b4;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.3s;
    }
    .btn-back:hover {
      background: #ff1493;
    }
    .product-img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 6px;
    }
  </style>
</head>
<body>
  <h1>Order Details</h1>

  <div class="order-info">
    <p><strong>Order #:</strong> <?= htmlspecialchars($order['id']) ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars(date("M d, Y h:i A", strtotime($order['created_at']))) ?></p>
    <p><strong>Status:</strong> 
      <span class="status <?= strtolower($order['status']) ?>">
        <?= ucfirst($order['status']) ?>
      </span>
    </p>
    <p><strong>Total Amount:</strong> ₱<?= number_format($order['total_amount'], 2) ?></p>
  </div>

  <h2>Items</h2>
  <?php if (empty($items)): ?>
    <p style="text-align:center;">No items found in this order.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Product</th>
          <th>Image</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['product_name'] ?? 'Unknown') ?></td>
            <td>
              <?php if (!empty($item['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" class="product-img" alt="">
              <?php else: ?>
                <span>No image</span>
              <?php endif; ?>
            </td>
            <td>₱<?= number_format($item['price'], 2) ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <div style="text-align:center;">
    <a href="my_orders.php" class="btn-back">← Back to Orders</a>
  </div>
</body>
</html>
