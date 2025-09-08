
<?php
require_once 'classes/database.php';
session_start();

$db = Database::getInstance();

if (!$db->isLoggedIn() || $_SESSION['role'] !== 'customer') {
    header("Location: customer_login.php");
    exit;
}


$customer_id = $db->getCurrentCustomerId();

// Fetch customer orders
$orders = $db->getCustomerOrders($customer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Dashboard</title>
<style>
body {
  font-family: 'Segoe UI', sans-serif;
  background: #f5f5f5;
  margin: 0;
  padding: 0;
}
/* Top Navbar */
.top-nav {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  background: #fff;
  border-bottom: 1px solid #eee;
  padding: 10px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: 'Playfair Display', serif;
  font-size: 22px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  z-index: 1000;
}
.top-nav .logo { flex:1; text-align:center; }
.nav-actions { display:flex; align-items:center; gap:20px; position:absolute; right:20px; top:50%; transform:translateY(-50%); }
.icon-btn, .cart-link, .profile-link { background:none; border:none; cursor:pointer; padding:0; }
.icon-btn svg, .cart-link svg, .profile-link svg { stroke:#444; width:22px; height:22px; }
.icon-btn:hover svg, .cart-link:hover svg, .profile-link:hover svg { stroke:#666; }
/* Sub Navbar */
.sub-nav {
  position: fixed;
  top: 60px;
  left: 0;
  width: 100%;
  background: #fff;
  border-bottom: 1px solid #ddd;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 12px 20px;
  z-index: 999;
  font-family: 'Playfair Display', serif;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 1px;
}
.sub-nav .links {
  display: flex;
  gap: 25px;
  align-items: center;
}
.sub-nav .links a {
  text-decoration: none;
  color: #444;
  font-size: 16px;
}
.sub-nav .links a:hover { color: #666; }
.sub-nav .logout {
  margin-left: 40px;
  color: #555;
  text-decoration: none;
  font-weight: bold;
}
.sub-nav .logout:hover { color: #777; text-decoration: underline; }
/* Dashboard Container */
.container { max-width: 900px; margin: 120px auto 40px; padding: 0 20px; }
h2 { text-align:center; margin-bottom:30px; color:#333; }
/* Order Cards */
.order-card {
  background:#fff;
  border-radius:12px;
  box-shadow:0 4px 12px rgba(0,0,0,0.05);
  padding:20px;
  margin-bottom:20px;
  transition:0.3s;
}
.order-card:hover { box-shadow:0 6px 16px rgba(0,0,0,0.1); }
.order-card h3 { margin:0 0 10px; font-size:18px; color:#333; }
.order-card p { margin:4px 0; color:#555; font-size:14px; }
.order-card a { color:#007BFF; text-decoration:none; font-weight:bold; }
.order-card a:hover { text-decoration:underline; }
/* Status Labels */
.status-pending { color:#FFA500; font-weight:bold; }
.status-processing { color:#1E90FF; font-weight:bold; }
.status-shipped { color:#8A2BE2; font-weight:bold; }
.status-delivered { color:#32CD32; font-weight:bold; }
.status-completed { color:#228B22; font-weight:bold; }
.status-cancelled { color:#FF4500; font-weight:bold; }
</style>
</head>
<body>

<!-- Top Navbar -->
<div class="top-nav">
  <div class="logo">Happy Sprays</div>
  <div class="nav-actions">
    <button class="icon-btn" type="button">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </button>
    <a href="cart.php" class="cart-link">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M6 7h12l1 12H5L6 7z"/>
        <path d="M9 7V5a3 3 0 0 1 6 0v2"/>
      </svg>
    </a>
    <a href="customer_dashboard.php" class="profile-link" title="My Account">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
        <circle cx="12" cy="7" r="4"></circle>
      </svg>
    </a>
  </div>
</div>

<!-- Sub Navbar -->
<div class="sub-nav">
  <div class="links">
    <a href="index.php">Home</a>
    <a href="index.php?gender=Male">For Him</a>
    <a href="index.php?gender=Female">For Her</a>
    <a href="#contact">Contact</a>
    <a href="customer_logout.php" class="logout">Logout</a>
  </div>
</div>

<!-- Dashboard -->
<div class="container">
  <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>

  <?php if(count($orders) > 0): ?>
    <?php foreach($orders as $order): 
        $statusLabel = $db->formatOrderStatus($order['status']);
        $statusClass = $db->getOrderStatusClass($order['status']);
        $formattedDate = $db->formatOrderDate($order['created_at']);
        $formattedTotal = $db->formatPrice($order['total_amount']);
    ?>
      <div class="order-card">
        <h3>Order #<?= $order['id'] ?></h3>
        <p>Total: <?= $formattedTotal ?></p>
        <p>Status: <span class="<?= $statusClass ?>"><?= $statusLabel ?></span></p>
        <p>Created: <?= $formattedDate ?></p>
        <?php if($order['gcash_proof']): ?>
          <p><a href="uploads/<?= htmlspecialchars($order['gcash_proof']) ?>" target="_blank">View Proof</a></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p style="text-align:center; color:#555;">You have no orders yet.</p>
  <?php endif; ?>
</div>

</body>
</html>
