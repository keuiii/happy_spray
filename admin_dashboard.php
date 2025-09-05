<?php
session_start();

// kung hindi naka-login, redirect
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// kung naka-login pero hindi admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once 'classes/database.php';
$db = Database::getInstance();

// Get counts using centralized methods
$totalOrders = $db->getOrdersCount();
$totalUsers = $db->getUsersCount();
$totalProducts = $db->getProductsCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Happy Sprays Admin Dashboard</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f5f5f5;
        color: #333;
        margin: 0;
        padding: 0;
    }

    header {
        padding: 20px;
        text-align: center;
        background: #fff;
        color: #333;
        border-bottom: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    header h1 {
        margin: 0;
        font-size: 26px;
        font-weight: 600;
    }

    header p {
        margin-top: 6px;
        font-size: 14px;
        color: #666;
    }

    header a {
        color: #333;
        text-decoration: none;
        font-weight: 500;
    }
    header a:hover {
        text-decoration: underline;
    }

    .container {
        padding: 30px 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        max-width: 1100px;
        margin: auto;
    }

    .card {
        border-radius: 10px;
        padding: 25px;
        text-align: center;
        background: #fff;
        border: 1px solid #e0e0e0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .card h2 {
        margin: 0;
        font-size: 18px;
        color: #555;
    }

    .card p {
        font-size: 30px;
        margin-top: 12px;
        font-weight: bold;
        color: #222;
    }

    nav {
        margin: 40px auto;
        text-align: center;
    }

    nav a {
        display: inline-block;
        margin: 6px;
        padding: 12px 22px;
        border: 1px solid #333;
        border-radius: 6px;
        text-decoration: none;
        color: #333;
        background: #fff;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    nav a:hover {
        background: #333;
        color: #fff;
    }
</style>
</head>
<body>
    <header>
        <h1>Happy Sprays Admin Dashboard</h1>
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> | <a href="logout.php">Logout</a></p>
    </header>

    <div class="container">
        <div class="card">
            <h2>Total Products</h2>
            <p><?= $totalProducts ?></p>
        </div>
        <div class="card">
            <h2>Total Orders</h2>
            <p><?= $totalOrders ?></p>
        </div>
        <div class="card">
            <h2>Total Users</h2>
            <p><?= $totalUsers ?></p>
        </div>
    </div>

    <nav>
        <a href="add_products.php">Manage Products</a>
        <a href="products_list.php">Products List</a>
        <a href="orders.php">Manage Orders</a>
        <a href="users.php">Manage Users</a>
        <a href="index.php">Back to Shop</a>
    </nav>
</body>
</html>
