<?php
session_start();
require_once __DIR__ . "/classes/database.php";

// Protect page: dapat naka-login at customer role
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login.php");
    exit;
}

$db = Database::getInstance(); // ✅ Correct for singleton


// Kunin orders ng current customer
$orders = $db->getCustomerOrders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 20px;
    }
    h1 {
      text-align: center;
      color: #333;
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
    .btn {
      display: inline-block;
      padding: 6px 12px;
      background: #ff69b4;
      color: white;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.3s;
    }
    .btn:hover {
      background: #ff1493;
    }
  </style>
</head>
<body>
  <h1>My Orders</h1>

  <?php if (empty($orders)): ?>
    <p style="text-align:center;">You have no orders yet.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Date</th>
          <th>Status</th>
          <th>Total</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
          <tr>
            <td>#<?= htmlspecialchars($order['id']) ?></td>
            <td><?= htmlspecialchars(date("M d, Y", strtotime($order['created_at']))) ?></td>
            <td>
              <span class="status <?= strtolower($order['status']) ?>">
                <?= ucfirst($order['status']) ?>
              </span>
            </td>
            <td>₱<?= number_format($order['total_amount'], 2) ?></td>
            <td>
              <a class="btn" href="order_details.php?id=<?= $order['id'] ?>">View Details</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</body>
</html>
