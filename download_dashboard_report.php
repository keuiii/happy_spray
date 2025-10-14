<?php
// download_dashboard_report.php
session_start();
require_once 'classes/database.php';

$db = Database::getInstance();

// Check if user is logged in AND is an admin
if (!$db->isLoggedIn() || $db->getCurrentUserRole() !== 'admin') {
    header("Location: customer_login.php");
    exit;
}

// Get all dashboard data
$currentMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('-1 month'));
$currentYear = date('Y');

// Basic stats
$stats = [
    'total_products' => $db->getProductsCount(),
    'total_customers' => $db->getUsersCount(),
    'total_orders' => $db->getOrdersCount(),
    'unread_messages' => $db->getUnreadContactCount()
];

$orderStats = $db->getOrderStats();

// Sales comparison
$currentMonthSales = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE_FORMAT(o_created_at, '%Y-%m') = ?", [$currentMonth]);
$lastMonthSales = $db->fetch("SELECT COUNT(*) as count, COALESCE(SUM(total_amount), 0) as total FROM orders WHERE DATE_FORMAT(o_created_at, '%Y-%m') = ?", [$lastMonth]);

// Calculate growth
$revenueGrowth = 0;
if ($lastMonthSales['total'] > 0) {
    $revenueGrowth = (($currentMonthSales['total'] - $lastMonthSales['total']) / $lastMonthSales['total']) * 100;
}

$ordersGrowth = 0;
if ($lastMonthSales['count'] > 0) {
    $ordersGrowth = (($currentMonthSales['count'] - $lastMonthSales['count']) / $lastMonthSales['count']) * 100;
}

// Top selling products
$topProducts = $db->select("
    SELECT p.perfume_name, p.perfume_price, 
           SUM(oi.order_quantity) as total_sold, 
           SUM(oi.order_quantity * oi.order_price) as revenue
    FROM order_items oi
    JOIN perfumes p ON oi.perfume_id = p.perfume_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE YEAR(o.o_created_at) = ?
    GROUP BY oi.perfume_id, p.perfume_name, p.perfume_price
    ORDER BY total_sold DESC
    LIMIT 10
", [$currentYear]);

// Monthly sales data (last 6 months)
$monthlySales = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $monthName = date('F Y', strtotime("-$i months"));
    $sales = $db->fetch("SELECT COALESCE(SUM(total_amount), 0) as total, COUNT(*) as orders FROM orders WHERE DATE_FORMAT(o_created_at, '%Y-%m') = ?", [$month]);
    $monthlySales[] = [
        'month' => $monthName,
        'sales' => $sales['total'],
        'orders' => $sales['orders']
    ];
}

// Order status distribution
$statusDistribution = [
    'Pending' => $orderStats['pending'] ?? 0,
    'Processing' => $orderStats['processing'] ?? 0,
    'Out for Delivery' => $orderStats['out for delivery'] ?? 0,
    'Received' => $orderStats['received'] ?? 0,
    'Cancelled' => $orderStats['cancelled'] ?? 0
];

// Customer insights
$newCustomersThisMonth = $db->fetch("SELECT COUNT(*) as count FROM customers WHERE DATE_FORMAT(cs_created_at, '%Y-%m') = ?", [$currentMonth]);

// Low stock products
$lowStockProducts = $db->getLowStockProducts(20);

// Set headers for Excel download
$filename = "Happy_Sprays_Dashboard_Report_" . date('Y-m-d_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Start Excel output
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        .header { background-color: #000000; color: #FFFFFF; font-weight: bold; font-size: 14pt; text-align: center; }
        .section-title { background-color: #333333; color: #FFFFFF; font-weight: bold; font-size: 12pt; }
        .sub-header { background-color: #666666; color: #FFFFFF; font-weight: bold; }
        .positive { color: #10b981; }
        .negative { color: #ef4444; }
        .total-row { background-color: #f0f0f0; font-weight: bold; }
        .number { mso-number-format: "\#\,\#\#0\.00"; }
        .currency { mso-number-format: "₱\#\,\#\#0\.00"; }
    </style>
</head>
<body>

<!-- REPORT HEADER -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <td colspan="4" class="header">HAPPY SPRAYS - MONTHLY PERFORMANCE REPORT</td>
    </tr>
    <tr>
        <td colspan="4" style="text-align: center; font-style: italic;">Generated on: <?= date('F d, Y h:i A') ?></td>
    </tr>
    <tr><td colspan="4">&nbsp;</td></tr>
    
    <!-- FINANCIAL SUMMARY -->
    <tr>
        <td colspan="4" class="section-title">📊 FINANCIAL SUMMARY</td>
    </tr>
    <tr>
        <td style="width: 30%;"><strong>Total Revenue</strong></td>
        <td class="currency" style="width: 20%;"><?= number_format($orderStats['total_revenue'] ?? 0, 2) ?></td>
        <td style="width: 30%;"><strong>Revenue Growth</strong></td>
        <td style="width: 20%;" class="<?= $revenueGrowth >= 0 ? 'positive' : 'negative' ?>"><?= number_format($revenueGrowth, 2) ?>%</td>
    </tr>
    <tr>
        <td><strong>Total Orders</strong></td>
        <td class="number"><?= $stats['total_orders'] ?></td>
        <td><strong>Order Growth</strong></td>
        <td class="<?= $ordersGrowth >= 0 ? 'positive' : 'negative' ?>"><?= number_format($ordersGrowth, 2) ?>%</td>
    </tr>
    <tr>
        <td><strong>Average Order Value</strong></td>
        <td class="currency"><?= $stats['total_orders'] > 0 ? number_format($orderStats['total_revenue'] / $stats['total_orders'], 2) : '0.00' ?></td>
        <td><strong>Collection Rate</strong></td>
        <td>100.00%</td>
    </tr>
    <tr><td colspan="4">&nbsp;</td></tr>
    
    <!-- OPERATIONAL METRICS -->
    <tr>
        <td colspan="4" class="section-title">📈 OPERATIONAL METRICS</td>
    </tr>
    <tr>
        <td><strong>Total Products</strong></td>
        <td class="number"><?= $stats['total_products'] ?></td>
        <td><strong>Total Customers</strong></td>
        <td class="number"><?= $stats['total_customers'] ?></td>
    </tr>
    <tr>
        <td><strong>New Customers (This Month)</strong></td>
        <td class="number"><?= $newCustomersThisMonth['count'] ?></td>
        <td><strong>Unread Messages</strong></td>
        <td class="number"><?= $stats['unread_messages'] ?></td>
    </tr>
    <tr>
        <td><strong>Low Stock Items</strong></td>
        <td class="number"><?= count($lowStockProducts) ?></td>
        <td><strong>Order Completion Rate</strong></td>
        <td><?= $stats['total_orders'] > 0 ? number_format((($orderStats['received'] ?? 0) / $stats['total_orders']) * 100, 2) : '0.00' ?>%</td>
    </tr>
</table>

<br><br>

<!-- MONTHLY SALES TREND -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <td colspan="3" class="section-title">📅 MONTHLY SALES TREND (Last 6 Months)</td>
    </tr>
    <tr class="sub-header">
        <td style="width: 40%;">Month</td>
        <td style="width: 30%;">Total Sales</td>
        <td style="width: 30%;">Orders</td>
    </tr>
    <?php 
    $totalSales = 0;
    $totalOrders = 0;
    foreach ($monthlySales as $data): 
        $totalSales += $data['sales'];
        $totalOrders += $data['orders'];
    ?>
    <tr>
        <td><?= $data['month'] ?></td>
        <td class="currency"><?= number_format($data['sales'], 2) ?></td>
        <td class="number"><?= $data['orders'] ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="total-row">
        <td>TOTAL</td>
        <td class="currency"><?= number_format($totalSales, 2) ?></td>
        <td class="number"><?= $totalOrders ?></td>
    </tr>
</table>

<br><br>

<!-- ORDER STATUS DISTRIBUTION -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <td colspan="3" class="section-title">📦 ORDER STATUS DISTRIBUTION</td>
    </tr>
    <tr class="sub-header">
        <td style="width: 40%;">Status</td>
        <td style="width: 30%;">Count</td>
        <td style="width: 30%;">Percentage</td>
    </tr>
    <?php 
    $totalOrdersStatus = array_sum($statusDistribution);
    foreach ($statusDistribution as $status => $count): 
        $percentage = $totalOrdersStatus > 0 ? ($count / $totalOrdersStatus) * 100 : 0;
    ?>
    <tr>
        <td><?= $status ?></td>
        <td class="number"><?= $count ?></td>
        <td><?= number_format($percentage, 2) ?>%</td>
    </tr>
    <?php endforeach; ?>
    <tr class="total-row">
        <td>TOTAL</td>
        <td class="number"><?= $totalOrdersStatus ?></td>
        <td>100.00%</td>
    </tr>
</table>

<br><br>

<!-- TOP SELLING PRODUCTS -->
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <td colspan="5" class="section-title">🏆 TOP SELLING PRODUCTS (<?= $currentYear ?>)</td>
    </tr>
    <tr class="sub-header">
        <td style="width: 10%;">Rank</td>
        <td style="width: 35%;">Product Name</td>
        <td style="width: 20%;">Unit Price</td>
        <td style="width: 15%;">Units Sold</td>
        <td style="width: 20%;">Total Revenue</td>
    </tr>
    <?php 
    $totalRevenue = 0;
    $totalUnitsSold = 0;
    foreach ($topProducts as $index => $product): 
        $totalRevenue += $product['revenue'];
        $totalUnitsSold += $product['total_sold'];
    ?>
    <tr>
        <td style="text-align: center;"><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($product['perfume_name']) ?></td>
        <td class="currency"><?= number_format($product['perfume_price'], 2) ?></td>
        <td class="number"><?= $product['total_sold'] ?></td>
        <td class="currency"><?= number_format($product['revenue'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="total-row">
        <td colspan="3">TOTAL</td>
        <td class="number"><?= $totalUnitsSold ?></td>
        <td class="currency"><?= number_format($totalRevenue, 2) ?></td>
    </tr>
</table>

<br><br>

<!-- LOW STOCK ALERT -->
<?php if (!empty($lowStockProducts)): ?>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <td colspan="4" class="section-title">⚠️ LOW STOCK ALERT</td>
    </tr>
    <tr class="sub-header">
        <td style="width: 10%;">ID</td>
        <td style="width: 45%;">Product Name</td>
        <td style="width: 20%;">Current Stock</td>
        <td style="width: 25%;">Status</td>
    </tr>
    <?php foreach ($lowStockProducts as $product): ?>
    <tr>
        <td><?= $product['perfume_id'] ?></td>
        <td><?= htmlspecialchars($product['perfume_name']) ?></td>
        <td class="number" style="color: #dc2626; font-weight: bold;"><?= $product['stock'] ?></td>
        <td style="color: #dc2626; font-weight: bold;">LOW STOCK</td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<br><br>

<!-- REPORT FOOTER -->
<table border="0" cellpadding="5" cellspacing="0">
    <tr>
        <td colspan="4" style="text-align: center; font-style: italic; color: #666;">
            --- End of Report ---<br>
            This report is confidential and intended for internal use only.
        </td>
    </tr>
</table>

</body>
</html>