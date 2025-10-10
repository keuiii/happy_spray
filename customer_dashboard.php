<?php
session_start();
require_once 'classes/database.php';

$db = Database::getInstance();

// Check if customer is logged in
if (!$db->isUserLoggedIn() || $db->getCurrentUserRole() !== 'customer') {
    header("Location: customer_login.php?redirect_to=customer_dashboard.php");
    exit;
}

$dashboardData = $db->getCustomerDashboardData();
$customer = $dashboardData['customer'];
$recentOrders = $dashboardData['recent_orders'] ?? [];
$totalOrders = $dashboardData['total_orders'] ?? 0;
$pendingOrders = $dashboardData['pending_orders'] ?? 0;
$completedOrders = $dashboardData['completed_orders'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Dashboard - Happy Sprays</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<style>
* {margin:0; padding:0; box-sizing:border-box;}
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fff;
    color: #000;
}
/* Top Navbar */
.top-nav {
    background: #fff;
    border-bottom: 2px solid #000;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.top-nav h1 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    text-transform: uppercase;
    letter-spacing: 3px;
    font-weight: 700;
}
.top-nav h1 a {
    color: #000;
    text-decoration: none;
}
/* Navigation Right Side */
.nav-right {
    display: flex;
    align-items: center;
    gap: 25px;
}
.nav-icon {
    color: #000;
    font-size: 24px;
    text-decoration: none;
    transition: transform 0.2s;
}
.nav-icon:hover {
    transform: scale(1.1);
}
.logout-btn {
    background: #000;
    color: #fff;
    border: 2px solid #000;
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}
.logout-btn:hover {
    background: #fff;
    color: #000;
}
/* Dashboard Container */
.container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 50px 40px;
    background: #fafafa;
    min-height: calc(100vh - 80px);
}
/* Welcome Banner */
.welcome-banner {
    background: linear-gradient(135deg, #fff 0%, #f8f8f8 100%);
    border: 3px solid #000;
    padding: 50px 40px;
    margin-bottom: 50px;
    text-align: center;
    box-shadow: 8px 8px 0 #000;
    position: relative;
}
.welcome-banner::before {
    content: '👋';
    font-size: 48px;
    position: absolute;
    top: 20px;
    left: 30px;
}
.welcome-banner h2 { 
    font-family: 'Playfair Display', serif; 
    font-size: 42px; 
    margin-bottom: 15px;
    font-weight: 700;
    line-height: 1.2;
}
.welcome-banner p { 
    font-size: 18px; 
    color: #555;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}
/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}
.stat-card {
    border: 3px solid #000;
    padding: 40px 30px;
    text-align: center;
    transition: all 0.3s ease;
    background: #fff;
    box-shadow: 5px 5px 0 #000;
    position: relative;
    overflow: hidden;
}
.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: #000;
    transform: scaleX(0);
    transition: transform 0.3s ease;
}
.stat-card:hover::before {
    transform: scaleX(1);
}
.stat-card:hover { 
    transform: translateY(-8px);
    box-shadow: 10px 10px 0 #000;
}
.stat-value { 
    font-size: 56px; 
    font-weight: 700; 
    margin-bottom: 15px; 
    color: #000;
    font-family: 'Playfair Display', serif;
    line-height: 1;
}
.stat-label { 
    font-size: 13px; 
    text-transform: uppercase; 
    color: #666;
    font-weight: 700;
    letter-spacing: 2px;
}
/* Orders Section */
.dashboard-section {
    background: #fff;
    border: 3px solid #000;
    padding: 40px;
    box-shadow: 6px 6px 0 #000;
    margin-bottom: 50px;
}
.dashboard-section h3 {
    font-family: 'Playfair Display', serif;
    font-size: 32px;
    margin-bottom: 30px;
    font-weight: 700;
    padding-bottom: 20px;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 15px;
}
.dashboard-section h3::before {
    content: '📦';
    font-size: 32px;
}
.recent-orders { 
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px; 
}
.order-card {
    border: 2px solid #000;
    padding: 25px;
    transition: all 0.3s ease;
    background: #fff;
    cursor: pointer;
    box-shadow: 3px 3px 0 #000;
    position: relative;
}
.order-card::after {
    content: '→';
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 24px;
    opacity: 0;
    transition: all 0.3s;
}
.order-card:hover::after {
    opacity: 1;
    right: 15px;
}
.order-card:hover {
    transform: translateX(4px);
    box-shadow: 6px 6px 0 #000;
    background: #fafafa;
}

.order-card h4 { 
    font-size: 20px; 
    margin-bottom: 15px; 
    color: #000;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
}
.order-card p { 
    font-size: 15px; 
    margin-bottom: 8px; 
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}
.order-card p strong {
    color: #000;
    min-width: 60px;
}
.order-status {
    display: inline-block;
    padding: 8px 16px;
    font-weight: 700;
    border: 2px solid #000;
    text-transform: uppercase;
    font-size: 11px;
    color: #000;
    background: #fff;
    letter-spacing: 1px;
    margin-top: 12px;
}
.status-pending { background: #fff3cd; border-color: #000; }
.status-processing { background: #cfe2ff; border-color: #000; }
.status-shipped { background: #e7d6f0; border-color: #000; }
.status-delivered { background: #d1e7dd; border-color: #000; }
.status-completed { background: #d1e7dd; border-color: #000; }
.status-cancelled { background: #f8d7da; border-color: #000; }
/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 50px;
}
.action-btn {
    border: 3px solid #000;
    padding: 30px 25px;
    text-align: center;
    text-decoration: none;
    font-weight: 700;
    color: #000;
    transition: all 0.3s ease;
    background: #fff;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    box-shadow: 4px 4px 0 #000;
    position: relative;
    overflow: hidden;
}
.action-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: #000;
    transition: left 0.3s ease;
    z-index: 0;
}
.action-btn span {
    position: relative;
    z-index: 1;
}
.action-btn:hover::before {
    left: 0;
}
.action-btn:hover { 
    color: #fff;
    transform: translateY(-6px);
    box-shadow: 8px 8px 0 #000;
}
.no-orders {
    text-align: center;
    padding: 80px 20px;
    color: #666;
    font-size: 18px;
    background: #f9f9f9;
    border: 2px dashed #ccc;
}
.no-orders::before {
    content: '📭';
    display: block;
    font-size: 64px;
    margin-bottom: 20px;
}
/* Responsive */
@media(max-width:768px) {
    .top-nav {
        padding: 15px 20px;
    }
    .top-nav h1 {
        font-size: 20px;
        letter-spacing: 2px;
    }
    .nav-right {
        gap: 15px;
    }
    .logout-btn {
        padding: 8px 16px;
        font-size: 12px;
    }
    .container {
        padding: 30px 20px;
    }
    .welcome-banner {
        padding: 40px 25px;
    }
    .welcome-banner::before {
        font-size: 32px;
        top: 15px;
        left: 15px;
    }
    .welcome-banner h2 {
        font-size: 28px;
    }
    .welcome-banner p {
        font-size: 16px;
    }
    .stats-grid {
        gap: 20px;
        grid-template-columns: 1fr;
    }
    .stat-value {
        font-size: 44px;
    }
    .dashboard-section {
        padding: 25px 20px;
    }
    .dashboard-section h3 {
        font-size: 24px;
    }
    .recent-orders {
        grid-template-columns: 1fr;
    }
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
footer {
    background: #000;
    border-top: 2px solid #000;
    padding: 50px 20px;
    text-align: center;
    font-size: 14px;
    color: #fff;
    margin-top: 80px;
}
.footer-columns {
    display: flex;
    justify-content: center;
    gap: 100px;
    margin-bottom: 30px;
}
.footer-columns h4 {
    font-size: 16px;
    margin-bottom: 15px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.footer-columns a {
    display: block;
    text-decoration: none;
    color: #ccc;
    margin: 8px 0;
    transition: color 0.3s;
}
.footer-columns a:hover { color: #fff; }
.social-icons { margin-top: 20px; }
.social-icons a {
    margin: 0 12px;
    color: #ccc;
    text-decoration: none;
    font-size: 16px;
    transition: color 0.3s;
}
.social-icons a:hover { color: #fff; }
footer p {
    margin-top: 20px;
    color: #999;
}
</style>
</head>
<body>

<div class="top-nav">
    <h1><a href="index.php">Happy Sprays</a></h1>
    <div class="nav-right">
        <a href="index.php" class="nav-icon" title="Home">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
        </a>
        <a href="cart.php" class="nav-icon" title="Cart">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
        </a>
        <a href="customer_logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="container">
    <div class="welcome-banner">
        <h2>Welcome back, <?= htmlspecialchars($customer['customer_firstname']) ?>!</h2>
        <p>Manage your orders, track shipments, and explore new fragrances.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= $totalOrders ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $pendingOrders ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $completedOrders ?></div>
            <div class="stat-label">Completed Orders</div>
        </div>
    </div>

<div class="dashboard-section">
    <h3>Recent Orders</h3>
    <?php if(empty($recentOrders)): ?>
        <div class="no-orders">No orders yet. Start shopping to see your orders here!</div>
    <?php else: ?>
        <div class="recent-orders">
            <?php foreach($recentOrders as $order): ?>
                <a href="order_status.php?id=<?= $order['order_id'] ?>" style="text-decoration:none;">
                    <div class="order-card">
                        <h4>Order #<?= str_pad($order['order_id'], 6, '0', STR_PAD_LEFT) ?></h4>
                        <p><strong>Total:</strong> ₱<?= number_format($order['total_amount'],2) ?></p>
                        <p><strong>Date:</strong> <?= date('M d, Y', strtotime($order['o_created_at'])) ?></p>
                        <span class="order-status status-<?= strtolower(str_replace(' ', '-', $order['order_status'])) ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </span>
                        <?php if(!empty($order['gcash_proof'])): ?>
                            <p style="margin-top:10px;"><strong>📎 Payment Proof Submitted</strong></p>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



    <div class="quick-actions">
        <a href="index.php" class="action-btn"><span>Browse Products</span></a>
        <a href="cart.php" class="action-btn"><span>View Cart</span></a>
    </div>
</div>

<!-- Footer -->
<footer>
    <div class="footer-columns">
        <div>
            <h4>Company</h4>
            <a href="about.php">About</a>
            <a href="reviews.php">Reviews</a>
        </div>
        <div>
            <h4>Customer Service</h4>
            <a href="faq.php">FAQ</a>
            <a href="contact.php">Contact</a>
        </div>
    </div>
    <div class="social-icons">
        <a href="https://www.facebook.com/thethriftbytf">Facebook</a>
        <a href="https://www.instagram.com/thehappysprays/">Instagram</a>
    </div>
    <p>© 2025 Happy Sprays. All rights reserved.</p>
</footer>

</body>
</html>
