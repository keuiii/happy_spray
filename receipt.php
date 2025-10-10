<?php
session_start();
require_once 'classes/database.php';
$db = Database::getInstance();

if (!isset($_GET['order_id'])) {
    die("Invalid receipt.");
}

$order_id = intval($_GET['order_id']);
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
    body {
        font-family: "Courier New", Courier, monospace; 
        background:#fff; 
        color:#000; 
        margin:20px;
    }
    .receipt {
    width: 500px;
    margin: auto;
    border: 1px solid #000;
    padding: 25px;
    border-radius: 6px;
    min-height: 650px; /* nadagdagan para mas mahaba yung resibo */
}

    .receipt-header {
        text-align: center;
    }
    .receipt-header img {
        width: 90px; /* mas malaki logo */
        margin-bottom: 8px;
    }
    .receipt-header h2 {
        margin: 0;
        font-size: 22px; /* laki ng shop name */
    }
    .line {
        border-top: 1px dashed #000;
        margin: 12px 0;
    }
    .info p {
        margin: 4px 0;
        font-size: 15px; /* mas readable */
    }
   table {
    width:100%;
    border-collapse: collapse;
    margin: 12px 0;
    font-size: 15px;
    table-layout: fixed; /* para fixed yung width distribution */
}
th, td {
    padding: 6px 0;
}
th {
    border-bottom: 1px dashed #000;
    font-weight: bold;
}
td.qty, th.qty {
    width: 60px;
    text-align: center;
}
td.price, th.price {
    width: 90px;
    text-align: right;
}
td.total, th.total {
    width: 100px;
    text-align: right;
}

    .grand-total {
        border-top: 1px dashed #000;
        font-weight: bold;
        padding-top: 8px;
        font-size: 16px;
    }
    .thankyou {
        text-align: center;
        font-style: italic;
        font-size: 15px;
        margin-top: 20px;
    }
    .btns {
        text-align: center;
        margin-top: 20px;
    }
    .btns a {
        text-decoration: none;
        font-size: 14px;
        color: #000;
        border: 1px solid #000;
        padding: 8px 16px;
        margin: 4px;
        border-radius: 4px;
    }
    .btns a:hover {
        background:#000; color:#fff;
    }
    .proof {
        text-align: center;
        margin-top: 15px;
    }
    .proof img {
        max-width: 300px; /* mas laki proof */
        border: 1px solid #000;
        border-radius: 4px;
    }
</style>
</head>
<body>
<div class="receipt">
    <div class="receipt-header">
        <img src="images/happysprayslogo1.png" alt="Logo">
        <h2>Happy Sprays</h2>
        <small>Bonifacio Global City </small><br>
        <small>0945-103-8854</small>
    </div>
    <div class="line"></div>

    <div class="info">
        <p><strong>Receipt #:</strong> <?= $order['id'] ?></p>
        <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
        <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
        <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
        <p><strong>Payment:</strong> <?= strtoupper($order['payment_method']) ?></p>
    </div>

    <div class="line"></div>

    <table>
    <tr>
        <th>Item</th>
        <th class="qty">Qty</th>
        <th class="price">Price</th>
        <th class="total">Total</th>
    </tr>
    <?php foreach($items as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['product_name']) ?></td>
        <td class="qty"><?= $row['quantity'] ?></td>
        <td class="price">₱<?= number_format($row['price'],2) ?></td>
        <td class="total">₱<?= number_format($row['price']*$row['quantity'],2) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="3" class="grand-total">Grand Total</td>
        <td class="total grand-total">₱<?= number_format($order['total_amount'],2) ?></td>
    </tr>
</table>


    <?php if ($order['payment_method'] == 'gcash' && !empty($order['gcash_proof'])): ?>
    <div class="proof">
        <h4>GCash Proof</h4>
        <img src="uploads/<?= htmlspecialchars($order['gcash_proof']) ?>" alt="GCash Proof">
    </div>
    <?php endif; ?>

    <div class="thankyou">
        *** Thank you for shopping at Happy Sprays! ***<br>
        Please keep this as your proof of purchase.
    </div>

    <div class="btns">
        <a href="javascript:window.print()">🖨 Print</a>
        <a href="index.php">← Back</a>
    </div>
</div>
</body>
</html>
