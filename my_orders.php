<?php
session_start();
require_once __DIR__ . "/classes/database.php";

$db = Database::getInstance();

// Protect page: must be logged in as customer
if (!$db->isUserLoggedIn() || $db->getCurrentUserRole() !== 'customer') {
    header("Location: customer_login.php");
    exit;
}

// Fetch customer orders
$orders = $db->getCustomerOrders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders - Happy Sprays</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #fff;
    margin: 0;
    padding: 20px;
    color: #000;
}
h1 {
    text-align: center;
    color: #000;
    margin-bottom: 20px;
}
.back-btn {
    display: inline-block;
    margin-bottom: 20px;
    padding: 8px 16px;
    border: 1px solid #000;
    border-radius: 6px;
    text-decoration: none;
    color: #000;
    font-weight: 600;
}
.back-btn:hover {
    background: #000;
    color: #fff;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
table th, table td {
    padding: 12px 15px;
    border-bottom: 1px solid #000;
    text-align: left;
}
table th {
    background: #000;
    color: #fff;
}
tr:hover {
    background: #f0f0f0;
}
.status {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 0.9em;
    font-weight: bold;
    text-transform: uppercase;
}
.pending { background: #fff; color: #000; border: 1px solid #000; }
.completed { background: #fff; color: #000; border: 1px solid #000; }
.cancelled { background: #fff; color: #000; border: 1px solid #000; }
.btn {
    display: inline-block;
    padding: 6px 12px;
    background: #000;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.3s;
}
.btn:hover {
    background: #444;
}
</style>
</head>
<body>

<a href="customer_dashboard.php" class="back-btn">← Back to Dashboard</a>

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
            <?php foreach ($orders as $order): 
                $orderId = $order['id'] ?? $order['order_id'] ?? 0;
                $createdAt = $order['created_at'] ?? $order['o_created_at'] ?? '';
                $status = $order['status'] ?? $order['order_status'] ?? 'Pending';
                $total = $order['total_amount'] ?? $order['total'] ?? 0;
            ?>
            <tr>
                <td>#<?= str_pad(htmlspecialchars($orderId), 6, '0', STR_PAD_LEFT) ?></td>
                <td><?= $createdAt ? htmlspecialchars(date("M d, Y", strtotime($createdAt))) : '-' ?></td>
                <td>
                    <span class="status <?= strtolower(str_replace(' ', '-', $status)) ?>">
                        <?= htmlspecialchars(ucfirst($status)) ?>
                    </span>
                </td>
                <td>₱<?= number_format($total, 2) ?></td>
                <td>
                    <a class="btn" href="order_status.php?id=<?= $orderId ?>">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
